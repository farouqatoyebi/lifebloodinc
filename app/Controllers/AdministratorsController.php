<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Administrators;
use App\Models\AdminPosts;
use App\Models\BloodBankModel;
use App\Models\WalletModel;
use App\Models\AuthModel;

class AdministratorsController extends BaseController
{
    public function __construct()
    {
        $this->isAdminUserSignedIn();   

        $this->adminAuth = new Administrators();
        $this->bloodBankModel = new BloodBankModel();
        $this->walletModel = new WalletModel();
        $this->authModel = new AuthModel();
    }

    public function loginPage()
    {
        echo view('admin/admin-sign-in');
    }

    public function processAdminLoginProcessing()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_email" => [
                    "label" => "Email", 
                    "rules" => "required|valid_email"
                ],
                "wp_password" => [
                    "label" => "Password", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $adminAuthRegData = [
                    "email" => $this->request->getVar("wp_email"),
                    "password" => $this->request->getVar("wp_password"),
                ];

                $userInformation = $this->adminAuth->authenticateAdminInformation($adminAuthRegData);

                if ($userInformation) {
                    $this->webAppAdminSetLoginInformation($userInformation);
                }

                $response = [
                    'status' => 200,
                    'message' => $this->adminAuth->message,
                ];

                if ($this->adminAuth->auth_status == 'failed') {
                    $response['status'] = 401;
                }
            }
            else {
                $response = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function index()
    {
        $data = [];
        $data['v'] = 'admin-dashboard';
        $data['totalUsers'] = $this->adminAuth->countOfUsers();
        $withdrawals = $this->adminAuth->getAllPendingWithdrawals();
        $totalAmount = 0;

        if ($withdrawals) {
            foreach ($withdrawals as $value) {
                $totalAmount += $value->amount;
            }
        }
        $data['withdrawalAmount'] = $totalAmount;

        echo view('admin/admin-template', $data);
    }

    public function getAllPendingWithdrawals()
    {
        $data = [];
        $data['v'] = 'admin-manage-withdrawals';
        $data['withdrawals'] = $this->adminAuth->getAllPendingWithdrawals();
        $data['page_type'] = 'pending';

        echo view('admin/admin-template', $data);
    }

    public function getAllApprovedWithdrawals()
    {
        $data = [];
        $data['v'] = 'admin-manage-withdrawals';
        $data['withdrawals'] = $this->adminAuth->getAllApprovedWithdrawals();
        $data['page_type'] = 'approved';

        echo view('admin/admin-template', $data);
    }

    public function getAllRejectedWithdrawals()
    {
        $data = [];
        $data['v'] = 'admin-manage-withdrawals';
        $data['withdrawals'] = $this->adminAuth->getAllRejectedWithdrawals();
        $data['page_type'] = 'rejected';

        echo view('admin/admin-template', $data);
    }

    public function approveWithdrawalForInstitution()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $withdrawalID = 0;
    
        if (isset($exploded_url[3])) {
            $withdrawalID = $exploded_url[3];
        }

        if ($withdrawalID) {
            $getWithdrawalInfo = $this->adminAuth->getWithdrawalInfo($withdrawalID);

            if ($getWithdrawalInfo) {
                $bankDetails = $this->bloodBankModel->getBankDetailsInformation($getWithdrawalInfo->auth_id);
                $initiateTransfer = $this->initiateBulkTransfer($bankDetails->recipient_code, $getWithdrawalInfo->amount);

                if ($initiateTransfer) {
                    $initiateTransfer = $initiateTransfer[0];
                    $data = [
                        'status' => 'approved',
                        'trasnfer_code' => $initiateTransfer->transfer_code,
                        'updated_at' => time(),
                    ];

                    $this->adminAuth->updateWithdrawalRequest($getWithdrawalInfo->id, $data);
                    $this->walletModel->debitBloodBankAccountBalance($getWithdrawalInfo->auth_id, $getWithdrawalInfo->amount);

                    
                    if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                        $subject = "Withdrawal Request Approved";
                        $email_message = "<p>Your withdrawal request of NGN ".number_format($getWithdrawalInfo->amount)." has been approved. Your account will be credited shortly. See below for details.</p>";
                        $email_message .= "<p>Bank Name:<strong>".$bankDetails->bank_name."</strong></p>";
                        $email_message .= "<p>Account Name: <strong>".$bankDetails->acct_name."</strong></p>";
                        $email_message .= "<p>Account Number: <strong>".$bankDetails->acct_number."</strong></p>";
                        $email_message .= "<p>Amount Credited: <strong>NGN ".number_format($getWithdrawalInfo->amount)."</strong></p>";
                        $email_message .= "<p>Best Regards,</p><p>BetaLife</p>";

                        $getAuthID = $this->getAccountInformationBasedOnID($getWithdrawalInfo->auth_type, $getWithdrawalInfo->auth_id);
                        $email = '';

                        if ($getAuthID) {
                            if (isset($getAuthID->email)) {
                                $email = $getAuthID->email;
                            }
                            else {
                                $authInfo = $this->authModel->find($getAuthID->auth_id);

                                if ($authInfo) {
                                    if (isset($authInfo->email)) {
                                        $email = $authInfo->email;
                                    }
                                }
                            }
                        }

                        if ($email) {
                            // Send Approval confirmation email
                            sendEmail($email, $subject, $email_message);
                        }
                    }
                }
            }
        }

        return redirect()->to('/admin/pending-withdrawals');
    }

    public function rejectWithdrawalForInstitution()
    {
        $response = [];
        helper(['form']);
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $withdrawalID = 0;
    
        if (isset($exploded_url[3])) {
            $withdrawalID = $exploded_url[3];
        }

        if ($withdrawalID) {
            $getWithdrawalInfo = $this->adminAuth->getWithdrawalInfo($withdrawalID);
        }

        // Only post requests are accepted
        if ($this->request->getMethod() == "post" && $getWithdrawalInfo) {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_reason" => [
                    "label" => "Reason", 
                    "rules" => "required|min_length[5]"
                ],
                "wp_reject_type" => [
                    "label" => "Reject Type", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $reason = $this->request->getVar("wp_reason");
                $reject_type = $this->request->getVar("wp_reject_type");

                

                if ($reject_type == 'reject_and_refund') {
                    $this->walletModel->creditBloodBankWallet($getWithdrawalInfo->auth_id, $getWithdrawalInfo->amount, false);
                }
                
                $data = [
                    'status' => 'rejected',
                    'reason' => $reason,
                    'updated_at' => time()
                ];

                $this->adminAuth->updateWithdrawalRequest($getWithdrawalInfo->id, $data);

                    
                if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                    $subject = "Withdrawal Request Rejected";
                    $email_message = "<p>Your withdrawal request of NGN ".number_format($getWithdrawalInfo->amount)." has been rejected. See below for reason.</p>";
                    $email_message .= "<p><strong>".nl2br($reason)."</strong></p>";
                    $email_message .= "<p></p>";
                    $email_message .= "<p>Best Regards,</p><p>BetaLife</p>";

                    $getAuthID = $this->getAccountInformationBasedOnID($getWithdrawalInfo->auth_type, $getWithdrawalInfo->auth_id);
                    $email = '';

                    if ($getAuthID) {
                        if (isset($getAuthID->email)) {
                            $email = $getAuthID->email;
                        }
                        else {
                            $authInfo = $this->authModel->find($getAuthID->auth_id);

                            if ($authInfo) {
                                if (isset($authInfo->email)) {
                                    $email = $authInfo->email;
                                }
                            }
                        }
                    }

                    if ($email) {
                        // Send Approval confirmation email
                        sendEmail($email, $subject, $email_message);
                    }
                }

                $response['status'] = 200;
                $response['message'] = 'Rejected succesfully';
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }
        else{
            $response['status'] = 401;
            $response['message'] = 'Error. Please try again!';
        }

        return $this->response->setJSON($response);
    }

    public function getAllHospitalsList()
    {
        $data = [];
        $data['v'] = 'admin-manage-hospitals';
        $data['hospitalsBreakdown'] = $this->adminAuth->getAllAccountsInDB('hospital');

        echo view('admin/admin-template', $data);
    }

    public function getAllBloodBanksList()
    {
        $data = [];
        $data['v'] = 'admin-manage-blood-banks';
        $data['bloodBanksBreakdown'] = $this->adminAuth->getAllAccountsInDB('blood-bank');

        echo view('admin/admin-template', $data);
    }

    public function getAllPharmaciesList()
    {
        $data = [];
        $data['v'] = 'admin-manage-pharmacies';
        $data['pharmaciesBreakdown'] = $this->adminAuth->getAllAccountsInDB('pharmacy');

        echo view('admin/admin-template', $data);
    }

    public function getAllUsersList()
    {
        $data = [];
        $data['v'] = 'admin-manage-users';
        $data['usersBreakdown'] = $this->adminAuth->getAllAccountsInDB();

        echo view('admin/admin-template', $data);
    }

    public function myProfile()
    {
        $data = [];
        $data['v'] = 'admin-profile';
        $data['allCountries'] = $this->getAllCountriesList();
        $data['allStates'] = $this->getAllStatesList();
        $data['allCities'] = $this->getAllCitiesList();
        $data['myProfile'] = $this->adminAuth->getProfileInformation(session('admin_email'));

        echo view('admin/admin-template', $data);
    }

    public function processProfileFormSubmit()
    {
        $response = [];
        helper(['form']);
        $response['status'] = 401;
        $response['message'] = 'Error! We could not complete your request. Please try again.';

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_first_name" => [
                    "label" => "First Name", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_last_name" => [
                    "label" => "Last Name", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_country" => [
                    "label" => "Country", 
                    "rules" => "required"
                ],
                "wp_state" => [
                    "label" => "State", 
                    "rules" => "required"
                ],
                "wp_city" => [
                    "label" => "City", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_address" => [
                    "label" => "Address", 
                    "rules" => "required|min_length[5]"
                ],
                "wp_facebook" => [
                    "label" => "Facebook", 
                    "rules" => "permit_empty|min_length[5]"
                ],
                "wp_twitter" => [
                    "label" => "Twitter", 
                    "rules" => "permit_empty|min_length[5]"
                ],
                "wp_whatsapp" => [
                    "label" => "WhatsApp", 
                    "rules" => "permit_empty|min_length[5]"
                ],
                "wp_about_me" => [
                    "label" => "About Me", 
                    "rules" => "permit_empty|min_length[10]"
                ],
            ];

            if (!session('profile_photo')) {
                $rules['wp_profile_photo'] = [
                    "label" => "Profile Photo", 
                    "rules" => "uploaded[wp_profile_photo]"
                ];
            }

            if ($this->validate($rules)) {
                $getCityInformationID = $this->getPassedCityID($this->request->getVar("wp_city"), $this->request->getVar("wp_state"), $this->request->getVar("wp_country"));

                $adminProfileData = [
                    "first_name" => $this->request->getVar("wp_first_name"),
                    "last_name" => $this->request->getVar("wp_last_name"),
                    "country" => $this->request->getVar("wp_country"),
                    "state" => $this->request->getVar("wp_state"),
                    "city" => $getCityInformationID,
                    "address" => $this->request->getVar("wp_address"),
                    "facebook" => $this->request->getVar("wp_facebook"),
                    "twitter" => $this->request->getVar("wp_twitter"),
                    "whatsapp" => $this->request->getVar("wp_whatsapp"),
                    "about_me" => $this->request->getVar("wp_about_me"),
                    "profile_photo" => $this->moveUploadedFileToDestination('wp_profile_photo', 'profile_photo'),
                    "updated_at" => time(),
                ];

                $adminUpdatedInformation = $this->adminAuth->updateAdminProfileInformation(session('admin_email'), $adminProfileData);

                if ($adminUpdatedInformation) {
                    $this->webAppAdminSetLoginInformation($adminUpdatedInformation);
                }

                $response = [
                    'status' => 200,
                    'message' => 'Updated successfully.',
                ];
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function getAllPosts()
    {
        $adminPosts = new AdminPosts();
        $posts = $adminPosts->allAdminPosts();

        $data = [];
        $data['v'] = 'admin-manage-posts';
        $data['posts'] = $posts;

        echo view('admin/admin-template', $data);
    }

    public function addPostForm()
    {
        $data = [];
        $data['v'] = 'admin-add-post';

        echo view('admin/admin-template', $data);
    }

    public function addPost()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_title" => [
                    "label" => "Title", 
                    "rules" => "required"
                ],
                "wp_body" => [
                    "label" => "Body", 
                    "rules" => "required|min_length[5]|max_length[500]",
                ],
                "wp_image" => [
                    "label" => "Image", 
                    "rules" => "uploaded[wp_image]",
                    'errors' => [
                        'uploaded' => 'Image is required',
                    ],
                ],
            ];

            if ($this->validate($rules)) {
                $adminPosts = new AdminPosts();
                $uploadedFileName = $this->moveUploadedFileToDestination('wp_image', 'post');
                $title = $this->request->getVar('wp_title');
                $body = $this->request->getVar('wp_body');

                $data = [
                    'title' => $title,
                    'content' => $body,
                    'image' => $uploadedFileName,
                    'created_at' => time(),
                ];

                $adminPosts->insert($data);

                $response["status"] = 200;
                $response["message"] = 'Post has been submitted successfully.';
            }
            else {
                $response["status"] = 401;
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function editPostForm()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $postID = 0;
    
        if (isset($exploded_url[3])) {
            $postID = $exploded_url[3];
        }

        if ($postID) {
            $adminPost = new AdminPosts();

            $data = [];
            $data['v'] = 'admin-edit-post';
            $data['post'] = $adminPost->postDetails($postID);
    
            if ($data['post']) {
                echo view('admin/admin-template', $data);
                exit();
            }
        }

        return redirect()->to('/admin/manage-posts');
    }

    public function editPost()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $postID = 0;
    
        if (isset($exploded_url[3])) {
            $postID = $exploded_url[3];
        }

        if ($postID) {
            $response = [];
            helper(['form']);
    
            // Only post requests are accepted
            if ($this->request->getMethod() == "post") {
                $validation =  \Config\Services::validation();
    
                $rules = [
                    "wp_title" => [
                        "label" => "Title", 
                        "rules" => "required"
                    ],
                    "wp_body" => [
                        "label" => "Body", 
                        "rules" => "required|min_length[5]|max_length[500]",
                    ],
                ];

                if (trim($this->request->getFile('wp_image'))) {
                    $rules['wp_image'] = [
                        "label" => "Image",
                        "rules" => "uploaded[wp_image]",
                        'errors' => [
                            'uploaded' => 'Image is required',
                        ],
                    ];
                }
    
                if ($this->validate($rules)) {
                    $adminPosts = new AdminPosts();
                    $uploadedFileName = '';

                    if (trim($this->request->getFile('wp_image'))) {
                        $uploadedFileName = $this->moveUploadedFileToDestination('wp_image', 'post');
                    }

                    $title = $this->request->getVar('wp_title');
                    $body = $this->request->getVar('wp_body');
    
                    $data = [
                        'title' => $title,
                        'content' => $body,
                        'updated_at' => time(),
                    ];

                    if ($uploadedFileName) {
                        $data['image'] = $uploadedFileName;
                    }
    
                    $adminPosts->update($postID, $data);
    
                    $response["status"] = 200;
                    $response["message"] = 'Post has been submitted successfully.';
                }
                else {
                    $response["status"] = 401;
                    $response["validation"] = $validation->getErrors();
                }
            }
        }

        return $this->response->setJSON($response);
    }

    public function deletePost()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $postID = 0;
    
        if (isset($exploded_url[3])) {
            $postID = $exploded_url[3];
        }

        if ($postID) {
            $adminPost = new AdminPosts();
            $adminPost->deletePost($postID);

            return redirect()->to('/admin/manage-posts');
        }
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/admin-login');
    }
}
