<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AuthModel;
use App\Models\BloodBankModel;
use App\Models\BloodDonationModel;
use App\Models\HospitalModel;
use App\Models\WalletModel;

class DashboardController extends BaseController
{
    protected   $authModel;
    protected   $user_data;
    protected   $token_expiry_period    = 31536000;
    protected   $otp_expiry_period      = 900;  // 15mins
    protected   $arrayOfAcceptableAccountTypes;
    protected   $hospitalModel;
    protected   $bloodBankModel;
    protected   $walletModel;
    protected   $accountAuthID;
    protected   $accountAuthType;
    protected   $user_info;
    protected   $outgoingEmails;

    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank'
        ];

        $this->isUserSignedIn();

        $this->confirmAccountCompletion();
        
        $this->authModel = new AuthModel();
        $this->hospitalModel = new HospitalModel();
        $this->bloodBankModel = new BloodBankModel();
        $this->walletModel = new WalletModel();

        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $this->user_info = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);
        $this->outgoingEmails = new SendOutgoingEmailController();
    }

    public function index()
    {
        $noOfRequests = 0;
        $noOfPendingActivities = 0;
        $noOfInstitutions = 0;
        $userProfileInformation = $this->user_info;

        if ($this->accountAuthType == 'hospital') {
            $noOfRequests = $this->hospitalModel->getTotalNumberOfRequestsMade($userProfileInformation->id);
            $noOfInstitutions = $this->hospitalModel->getBloodBanksHospitalHasDoneWithBusinessWith($userProfileInformation->id);
        }
        elseif ($this->accountAuthType == 'blood-bank') {
            $noOfPendingActivities = $this->bloodBankModel->getTotalNumberOfPendingActivities($userProfileInformation->id);
            $noOfInstitutions = $this->bloodBankModel->getHospitalsBloodBankHasDoneWithBusinessWith($userProfileInformation->id);
        }

        $data['v'] = 'dashboard';
        $data['results']['page_title'] = 'Dashboard';
        $data['results']['no_of_requests'] = $noOfRequests;
        $data['results']['no_of_pending_activities'] = $noOfPendingActivities;
        $data['results']['no_of_institutions'] = $noOfInstitutions;

        echo view('webapp/template', $data);
    }

    public function requestForBloodDonation()
    {
        if ($this->accountAuthType != 'hospital') {
            return redirect()->route('dashboard');
        }

        $userProfileInformation = $this->user_info;

        $data['v'] = 'request-blood-form';
        $data['results']['page_title'] = 'Request Blood';
        $data['results']['userProfileInformation']= $userProfileInformation;

        echo view('webapp/template', $data);
    }

    public function processRequestForBloodDonation()
    {
        $response = [
            'status' => 401,
            'message' => 'You have an error in your form',
        ];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_short_desc" => [
                    "label" => "Description", 
                    "rules" => "permit_empty|min_length[2]"
                ],
                "wp_urgency_level" => [
                    "label" => "Urgency Level", 
                    "rules" => "required",
                    'errors' => [
                        'is_unique' => 'Invalid Urgency Level submitted',
                    ],
                ],
                "wp_due_date" => [
                    "label" => "Due Date", 
                    "rules" => "required|valid_date",
                    'errors' => [
                        'is_unique' => 'Invalid due date provided',
                    ],
                ],
                "wp_blood_pint" => [
                    "label" => "Blood Pint", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $bloodRequestModel = new BloodDonationModel();
                $getEstablishmentInformation = $this->user_info;

                $data = [
                    "auth_id" => $getEstablishmentInformation->id,
                    "comments" => $this->request->getVar("wp_short_desc"),
                    "blood_group" => '',
                    "auth_type" => 'hospital',
                    "no_of_pints" => 0,
                    "urgency" => $this->request->getVar("wp_urgency_level"),
                    "hospital_name" => $getEstablishmentInformation->name,
                    "address" => $getEstablishmentInformation->Address,
                    "state_id" => $getEstablishmentInformation->state,
                    "city_id" => $getEstablishmentInformation->city,
                    "country_id" => $getEstablishmentInformation->country,
                    "due_date" => strtotime($this->request->getVar("wp_due_date")), 
                    "created_at" => time(),
                    "status" => "pending",
                ];

                $requestID = $bloodRequestModel->saveRequestToDB($data);
                
                if ($requestID) {
                    $bloodGroupToPint = $this->request->getVar("wp_blood_pint");

                    $bloodRequestModel->saveBloodGroupToBloodRequest($requestID, $bloodGroupToPint);
                    $this->outgoingEmails->sendOutNewBloodRequestEmail();

                    $response["status"] = 200;
                    $response["accept_donor_url"] = base_url().'/browse-blood-offers/'.$requestID;
                    $response["message"] = "Request has been saved successfully.";
                }
                else {
                    $response["status"] = 401;
                    $response["message"] = "We encountered an error while trying to complete your request.";
                }
            }
            else {
                $response["status"] = 401;
                $response["validation"] = $validation->getErrors();
            }
            
            return $this->response->setJSON($response);
        }
    }

    public function fetchAllNearByRequestsForWeb()
    {
        $bloodDonationModel = new BloodDonationModel();
        
        $data['v'] = 'browse-blood-requests';
        $data['results']['page_title'] = 'Browse Blood Requests';
        $data['results']['allRequests'] = $bloodDonationModel->fetchAllDonationRequestsForWeb();

        echo view('webapp/template', $data);
    }

    public function getAllBloodOffersFromDonors()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $bloodRequestID = 0;
        $userProfileInformation = $this->user_info;
    
        if (isset($exploded_url[2])) {
            $bloodRequestID = $exploded_url[2];
        }

        if (!$bloodRequestID) {
            return redirect()->route('dashboard');
        }
        $bloodDonationModel = new BloodDonationModel();
        
        if (!$request_info = $bloodDonationModel->didHospitalMakeBloodRequest($bloodRequestID, $userProfileInformation->id)) {
            return redirect()->route('dashboard');
        }

        $allBloodDonationOffers = $bloodDonationModel->getOffersForBloodRequest($bloodRequestID);
        $hasRequestLeft = $this->hospitalModel->getRequestLeftAfterAccepted($bloodRequestID);
        
        $data['v'] = 'browse-blood-offers';
        $data['results']['page_title'] = 'Browse Blood Offers';
        $data['results']['allOffers'] = $allBloodDonationOffers;
        $data['results']['hasRequestLeft'] = $hasRequestLeft;
        $data['results']['request_info'] = $request_info;

        echo view('webapp/template', $data);
    }

    public function sendRequestOfferForWeb()
    {
        $response = [];
        helper(['form']);

        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $bloodRequestID = 0;
    
        if (isset($exploded_url[2])) {
            $bloodRequestID = $exploded_url[2];
        }

        if (!$bloodRequestID) {
            return $this->response->setJSON(['status' => 401]);
        }

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            
            $rules = [
                "wp_blood_offer" => [
                    "label" => "Blood Offer", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $bloodDonationModel = new BloodDonationModel();
                $arrayOfValues = $this->request->getVar("wp_blood_offer");
                $getBloodBankInformaion = $this->user_info;

                if ($getBloodBankInformaion) {
                    $donationSubmitted = $bloodDonationModel->submitBloodDonationOffers($bloodRequestID, $getBloodBankInformaion->id, $arrayOfValues);

                    $this->outgoingEmails->sentOutEmailForOfferSentByBloodBank($getBloodBankInformaion->id, $bloodRequestID);

                    if ($donationSubmitted) {
                        $response['status'] = 200;
                    }
                }
                
                if (!isset($response['status'])) {
                    $response['status'] = 401;
                }
            }
        }
        
        return $this->response->setJSON($response);
    }

    public function allHospitalVisitors()
    {
        if ($this->accountAuthType == 'hospital') {
            $baseController = new BaseController();

            $startDateToRender = '';
            $endDateToRender = '';

            if (count($_GET)) {
                if ($_GET['start_date'] && $_GET['end_date']) {
                    $startDateToRender = strtotime(date("Y-m-d 00:00:00", strtotime($_GET['start_date'])));
                    $endDateToRender = strtotime(date("Y-m-d 23:59:59"), strtotime($_GET['end_date']));
                }
            }

            $accountInformation = $this->user_info;
    
            $allVisitorsRendered = $this->hospitalModel->getAllVisitors($accountInformation->id, $startDateToRender, $endDateToRender);
            
            $data['v'] = 'view-visitors-list';
            $data['results']['page_title'] = 'Visitors List';
            $data['results']['allVisitorsRendered'] = $allVisitorsRendered;
    
            echo view('webapp/template', $data);
        }
        else {
            return redirect()->route('dashboard');
        }
    }

    public function getAllActivitiesSaved()
    {
        $userProfileInformation = $this->user_info;

        $allActivities = $this->bloodBankModel->fetchActivities($userProfileInformation->id);
        
        $data['v'] = 'browse-activities';
        $data['results']['page_title'] = 'All Activites';
        $data['results']['allActivities'] = $allActivities;

        echo view('webapp/template', $data);
    }

    public function reviewTheActivity()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $activityID = $requestType = '';
    
        if (isset($exploded_url[2])) {
            $requestType = $exploded_url[2];
        }
    
        if (isset($exploded_url[3])) {
            $activityID = $exploded_url[3];
        }

        if (!$activityID || !$requestType) {
            return redirect()->route('dashboard');
        }

        if ($requestType == 'blood-request') {
            $bloodDonationModel = new BloodDonationModel();

            $userProfileInformation = $this->user_info;
    
            $allActivities = $this->bloodBankModel->fetchActivities($userProfileInformation->id, $activityID);

            if ($allActivities) {
                $allActivities->offer = $bloodDonationModel->getOffersForPassedRequest($allActivities->id, $userProfileInformation->id);
                $confirmPaymentMade = $this->bloodBankModel->confirmRequestPaymentMade($allActivities->id);

                if (!$allActivities->offer) {
                    return redirect()->route('dashboard');
                }
        
                if ($allActivities) {
                    $data['v'] = 'review-activity';
                    $data['results']['page_title'] = 'Review Activity for Blood Request #'.$allActivities->id;
                    $data['results']['allActivities'] = $allActivities;
                    $data['results']['confirmPaymentMade'] = $confirmPaymentMade;
            
                    echo view('webapp/template', $data);
                }
            }
        }

        if (!$allActivities) {
            return redirect()->route('dashboard');
        }
    }

    public function viewAllRequestForOffers()
    {
        $allHospitalRequests = $this->hospitalModel->fetchAllHospitalPendingRequests($this->user_info->id);

        $data['v'] = 'pending-blood-requests';
        $data['results']['page_title'] = 'Pending blood requests';
        $data['results']['allHospitalRequests'] = $allHospitalRequests;

        echo view('webapp/template', $data);

    }

    public function withdrawRequestOffer()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        $userProfileInformation = $this->user_info;
        
        $thisActivityInfo = $this->bloodBankModel->fetchActivities($userProfileInformation->id, $requestID);

        if ($thisActivityInfo) {
            $withdrawOffer = $this->bloodBankModel->withdrawOfferSent($requestID, $userProfileInformation->id);
            $alertMessage = $withdrawOffer['message'];

            if ($withdrawOffer['status'] == 200) {
                return redirect()->route('browse-activities');
            }
            else {
                return redirect()->route('review-activity/blood-request/'.$requestID);
            }
        }
        else {
            return redirect()->route('browse-activities');
        }
    }

    public function getBreakdownToAcceptRequest()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_offer" => [
                    "label" => "Offer", 
                    "rules" => "required"
                ],
                "wp_request" => [
                    "label" => "Request", 
                    "rules" => "required",
                ],
                "wp_offer_type" => [
                    "label" => "Offer Type", 
                    "rules" => "required",
                ],
            ];

            if ($this->validate($rules)) {
                $offer = $this->request->getVar("wp_offer");
                $requestID = $this->request->getVar("wp_request");
                $offerType = $this->request->getVar("wp_offer_type");

                $brokenDownInformation = $this->hospitalModel->fetchOfferBreakDownForHospital($requestID, $offer, $offerType);

                if (!empty($brokenDownInformation)) {
                    $response['status'] = 200;
                    $response['breakdown'] = $brokenDownInformation;
                }
                else {
                    $response['status'] = 401;
                    $response['message'] = 'We could not complete your request. Please try again.';
                }
            }
            else {
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function recordAcceptedOfferForRequest()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_confirmed_data" => [
                    "label" => "Confirmed Data", 
                    "rules" => "required"
                ],
                "wp_offer" => [
                    "label" => "Offer", 
                    "rules" => "required"
                ],
                "wp_request" => [
                    "label" => "Request", 
                    "rules" => "required",
                ],
                "wp_offer_type" => [
                    "label" => "Offer Type", 
                    "rules" => "required",
                ],
            ];

            if ($this->validate($rules)) {
                $dataToProcess = $this->request->getVar("wp_confirmed_data");
                $offer = $this->request->getVar("wp_offer");
                $requestID = $this->request->getVar("wp_request");
                $offerType = $this->request->getVar("wp_offer_type");

                $brokenDownInformation = $this->hospitalModel->fetchOfferBreakDownForHospital($requestID, $offer, $offerType);
                $allWentWell = false;
                $proceedWithAccept = false;

                foreach ($dataToProcess as $dis_value) {
                    if ($dis_value) {
                        $proceedWithAccept = true;
                    }
                }

                if ($proceedWithAccept) {
                    foreach ($dataToProcess as $key => $value) {
                        if ($value) {
                            if ($value <= $brokenDownInformation[$key]['no_of_pints_left']) {
                                $data = [
                                    'confirmed' => '1',
                                    'status' => 'confirmed',
                                    'no_of_pints_confirmed' => $value,
                                    'updated_at' => time(),
                                ];

                                $this->hospitalModel->recordOfferAcceptedByHospital($requestID, $offer, $offerType, $key, $data);

                                $allWentWell = true;
                            }
                        }
                        else {
                            $data = [
                                'confirmed' => '0',
                                'status' => 'declined',
                                'no_of_pints_confirmed' => $value,
                                'updated_at' => time(),
                            ];

                            $this->hospitalModel->recordOfferAcceptedByHospital($requestID, $offer, $offerType, $key, $data);
                        }
                    }

                    if ($allWentWell) {
                        if ($offerType == 'blood-bank') {
                            $this->outgoingEmails->sentOutEmailForAcceptedOfferByHospitalToBloodBank($offer, $requestID);
                        }
                        $response['status'] = 200;
                        $response['message'] = 'Offer has been accepted successfully.';
                    }
                    else {
                        $response['status'] = 401;
                        $response['message'] = 'We could not complete your request. Please try again.';
                    }
                }
                else {
                    $response["message"] = 'You must accept at least one offer to continue';
                }
            }
            else {
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function hospitalBloodRequestActivites()
    {
        $requestsWithTransactions = $this->hospitalModel->fetchAllHospitalNonPendingRequests();

        $data['v'] = 'hospital-activities-breakdown';
        $data['results']['page_title'] = 'Activities';
        $data['results']['requestsWithTransactions'] = $requestsWithTransactions;

        echo view('webapp/template', $data);
    }

    public function viewHospitalDeliveryInformation()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $activityID = '';

        if (isset($exploded_url[2])) {
            $activityID = $exploded_url[2];
        }

        if ($activityID) {
            $deliveryInformationBreakdown = $this->hospitalModel->deliveryInformationBreakdown($activityID);

            if ($deliveryInformationBreakdown) {
                $data['v'] = 'delivery-information-breakdown';
                $data['results']['page_title'] = 'Activities';
                $data['results']['deliveryInformationBreakdown'] = $deliveryInformationBreakdown;

                echo view('webapp/template', $data);
            }
            else {
                return redirect()->route('dashboard');
            }
        }
        else {
            return redirect()->route('dashboard');
        }
    }

    public function verifyDeliveryOTPInformation()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        $response = [
            'status' => 401,
            'message' => 'We could not complete your request. Please try again.',
        ];
        helper(['form']);

        $verifyBloodBankMadeOffer = $this->bloodBankModel->confirmRequestPaymentMade($requestID);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post" && $verifyBloodBankMadeOffer) {
            $validation =  \Config\Services::validation();

            $rules = [
                "otp" => [
                    "label" => "OTP", 
                    "rules" => "required|min_length[4]|max_length[4]"
                ],
            ];

            if ($this->validate($rules)) {
                $otpValue = $this->request->getVar("otp");

                $allWentWell = $this->bloodBankModel->confirmDeliveryMade($requestID, $otpValue);

                if ($allWentWell) {
                    $response['status'] = 200;
                    $response['message'] = 'OTP confirmed. Blood delivered successfully.';
                }
                else {
                    $response['message'] = 'Invalid OTP submitted.';
                }
            }
            else {
                $response = [];
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function walletPage()
    {
        $walletBreakdown = $this->walletModel->getAccountWalletBreakdown();
        $allTransactions = $this->bloodBankModel->getAllHospitalThatHasConfirmedBloodBanktTransactions();
        $bankDetails = $this->bloodBankModel->getBankDetailsInformation($this->user_info->id);
        $allWithdrawalsMade = $this->bloodBankModel->getWithdrawalBreakdownHistory($this->user_info->id);

        if (!$bankDetails) {
            header('Location: '.base_url().'/settings/bank-information?verify=true');
            exit();
        }

        $data['v'] = 'wallet';
        $data['results']['page_title'] = 'Wallet';
        $data['results']['walletBreakdown'] = $walletBreakdown;
        $data['results']['walletAmount'] = $walletBreakdown ? $walletBreakdown->balance : 0;
        $data['results']['walletBookBalance'] = $walletBreakdown ? $walletBreakdown->available_balance : 0;
        $data['results']['allTransactions'] = $allTransactions;
        $data['results']['bankDetails'] = $bankDetails;
        $data['results']['allWithdrawalsMade'] = $allWithdrawalsMade;

        echo view('webapp/template', $data);
    }

    public function processWithdrawalDisbursement()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_amount_withdraw" => [
                    "label" => "Amount to Withdraw", 
                    "rules" => "required"
                ],
            ];

            $amountWithdrawable = 0;

            $amountToWithdraw = $this->request->getVar('wp_amount_withdraw');
            $walletBreakdown = $this->walletModel->getAccountWalletBreakdown();
            $allowWithdrawal = false;

            if ($walletBreakdown) {
                if ($walletBreakdown->balance >= $amountToWithdraw) {
                    $allowWithdrawal = true;
                }

                $amountWithdrawable = $walletBreakdown->balance;
            }

            if ($this->validate($rules) && $allowWithdrawal) {
                $allWentWell = false;
                // $bankDetails = $this->bloodBankModel->getBankDetailsInformation($this->user_info->id);
                // $initiateTransfer = $this->initiateBulkTransfer($bankDetails->recipient_code, $amountToWithdraw);

                $data = [
                    'auth_id' => $this->user_info->id,
                    'auth_type' => $this->accountAuthType,
                    'amount' => $amountToWithdraw,
                    'trasnfer_code' => '',
                    'status' => 'pending',
                    'created_at' => time(),
                ];

                $recordWithdrawalAttempt = $this->bloodBankModel->saveWithdrawalRequest($data);
                $debitBloodBankWallet = $this->walletModel->debitBloodBankWallet($this->user_info->id, $amountToWithdraw);

                $allWentWell = true;
                

                if ($allWentWell) {
                    $response['status'] = 200;
                    $response['message'] = 'A withdrawal request has been initiated. Your account will be credited once request has been approved.';
                }
                else {
                    $response['status'] = 401;
                    $response['message'] = 'We could not complete your request. Please try again.';
                }
            }
            elseif (!$allowWithdrawal) {
                $response["validation"] = [
                    'wp_amount_withdraw' => 'You cannot make a withdrawal of more than NGN '.number_format($amountWithdrawable)
                ];
            }
            else {
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function deleteRequestForHospital()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
        $bloodDonationModel = new BloodDonationModel();
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        if (!$request_info = $bloodDonationModel->didHospitalMakeBloodRequest($requestID, $this->user_info->id)) {
            return redirect()->route('dashboard');
        }

        $bloodDonationModel->deleteRequest($requestID);
        return redirect()->route('accept-donors');
    }

    public function checkNewOfferFromTime()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        $time = $this->request->getVar('check_time');

        if (!$time) {
            $time = time();
        }

        $areThereNewOffersFromLastChecked = $this->hospitalModel->areThereNewOffersSinceLastChecked($requestID, $time);

        if ($areThereNewOffersFromLastChecked) {
            return 'true';
        }

        return 'false';
    }

    public function getTime()
    {
        echo(time());
        exit();
    }
}
