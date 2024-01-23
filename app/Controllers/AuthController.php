<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\UserModel;
use Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \AfricasTalking\SDK\AfricasTalking;
use App\Models\BloodBankModel;
use App\Models\HospitalModel;

class AuthController extends BaseController
{
    protected   $authModel;
    protected   $user_data;
    protected   $token_expiry_period    = 31536000;
    protected   $otp_expiry_period      = 900;  // 15mins
    protected   $arrayOfAcceptableAccountTypes;

    public function __construct (){

        // $this->otp_expiry_period = strtotime('+15 minutes', time());  // 15mins
        // $this->token_expiry = strtotime('+1 years', time());    // 1year
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank'
        ];
        
        helper(['form', 'filesystem']);

        $this->authModel = new AuthModel();
    }


    public function webLoginPage()
    {
        if (session('isLoggedIn')) {
            return redirect()->route("dashboard");
        }

        echo view('webapp/sign-in');
    }

    public function processUserLogin()
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
                $accountAuth = new AuthModel();

                $accountAuthRegData = [
                    "email" => $this->request->getVar("wp_email"),
                    "password" => $this->request->getVar("wp_password"),
                ];

                $userInformation = $accountAuth->authenticateUserInformation($accountAuthRegData);

                $response = [
                    'status' => 200,
                    'auth_status' => $accountAuth->auth_status,
                    'message' => $accountAuth->message,
                ];

                if ($userInformation) {
                    $this->webAppSetLoginInformation($userInformation);

                    if ($userInformation->status == 0) {
                        $uniq_token = $userInformation->token;
                        
                        if (!$uniq_token) {
                            $uniq_token = md5(uniqid($userInformation->email));
                            $this->authModel->update($userInformation->id, ["token" => $uniq_token]);
                        }
                        
                        $response['otp_url'] = base_url().'/verify-otp/'.$uniq_token;
                    }
                }

                if ($accountAuth->auth_status == 'failed') {
                    $response['status'] = 401;
                }
            }
            else {
                $response = $validation->getErrors();
            }
        }
        
        return $this->response->setJSON($response);
    }

    public function webRegisterPage()
    {
        if (session('isLoggedIn')) {
            return redirect()->route("dashboard");
        }

        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $accountType = '';

        if (isset($exploded_url[2])) {
            $accountType = $exploded_url[2];

            if (!in_array($accountType, $this->arrayOfAcceptableAccountTypes)) {
                return redirect()->route('register');
            }
        }

        $data = [];

        $data['allCountries'] = $this->getAllCountriesList();
        $data['allStates'] = $this->getAllStatesList();
        $data['allCities'] = $this->getAllCitiesList();

        echo view('webapp/sign-up', $data);
    }

    public function verifyWebUserOTP()
    {
        if (session('isLoggedIn')) {
            return redirect()->route("dashboard");
        }

        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $verification_token = '';
    
        if (isset($exploded_url[2])) {
            $verification_token = $exploded_url[2];
        }

        $data = [];

        $data['otpInformation'] = $this->authModel->verifyOtpToken($verification_token);

        echo view('webapp/verify-otp', $data);
    }

    public function processUserOtpVerification()
    {
        $response = [];
        helper(['form']);
        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_otp" => [
                    "label" => "Name", 
                    "rules" => "required"
                ],
                "_token" => [
                    "label" => "Token", 
                    "rules" => "required",
                ],
            ];

            if ($this->validate($rules)) {
                $accountAuth = new AuthModel();

                $accountAuthRegData = [
                    "otp" => $this->request->getVar("wp_otp"),
                    "token" => $this->request->getVar("_token"),
                ];

                $otpVerified = $accountAuth->verifyUserOTPToken($accountAuthRegData);

                if ($otpVerified) {
                    if ($otpVerified->token_expiry && $otpVerified->token_expiry > time()) {
                        $data = [
                            "otp_status" => 1,
                            "status" => 1,
                            "acct_status" => "active",
                            'token' => '',    
                            'token_date' => time(),
                            'token_expiry' => strtotime("-15 minutes"), // Expire the otp already
                            'updated_at' => time(),
                        ];

                        $this->authModel->update($otpVerified->id, $data);

                        $userInformation = $accountAuth->findUser($otpVerified->email);
                        $this->webAppSetLoginInformation($userInformation);

                        $response["status"] = 200;
                        $response["message"] = "OTP verification successful.";
                    }
                    else {
                        $response["status"] = 401;
                        $response["message"] = "The OTP submitted has expired. Please request a new OTP to continue.";
                    }
                }
                else {
                    $response["status"] = 401;
                    $response["message"] = "Invalid OTP submitted.";
                }
            } 
            else {
                $response["status"] = 401;
                $response['message'] = 'Invalid OTP submitted.';
            }
        }

        return $this->response->setJSON($response);
    }

    public function resendUserOTPVerificationCode()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $verification_token = '';
    
        if (isset($exploded_url[2])) {
            $verification_token = $exploded_url[2];
        }

        $data = [];

        $otpInformation = $this->authModel->verifyOtpToken($verification_token);

        if ($otpInformation) {
            $strtotimeTodayTime = strtotime("today");
            $strtotimeExpiry = strtotime("+15 minutes");
            $generatedOTP = $this->generateOTP();

            $data = [
                "updated_at" => $strtotimeTodayTime,
                "verification_otp" => $generatedOTP,
                "token_expiry" => $strtotimeExpiry,
            ];
            
            $this->authModel->update($otpInformation->id, $data);

            $subject = "New OTP Requested";
            $email_message = "<p>Welcome to BetaLife.</p> You can complete your registration with your new OTP as shown below. <br> <h1> $generatedOTP </h1>";
            $email_message .= "<p>We are excited to have you on board.</p> <p>Best Regards,</p><p>BetaLife</p>";

            $sms_message = "Your new OTP is $generatedOTP";

            if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                // Send OTP to Email
                sendEmail($otpInformation->email, $subject, $email_message);
            }

            // if($this->sendSMS($this->request->getVar("wp_phone"), $subject, $sms_message)){
            //     $sms_status = true;
            // }

            $response["status"] = 200;
            $response["message"] = "A new OTP has been sent to your inbox. Kindly login to your mailbox to get your new OTP.";
        }
        else {
            $response["status"] = 401;
            $response["message"] = "We could not complete your request. Please try again.";
        }

        return $this->response->setJSON($response);
    }

    public function webAppRegistrationComplete()
    {
        echo view('webapp/registration-complete');
    }

    public function processUserRegistration()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_name" => [
                    "label" => "Name", 
                    "rules" => "required"
                ],
                "wp_email" => [
                    "label" => "Email", 
                    "rules" => "required|valid_email|is_unique[auth_tbl.email]",
                    'errors' => [
                        'is_unique' => 'Email Address already exists',
                    ],
                ],
                "wp_phone" => [
                    "label" => "Phone Number", 
                    "rules" => "required|is_unique[auth_tbl.phone]|min_length[10]",
                    'errors' => [
                        'is_unique' => 'Phone Number is already registered',
                    ],
                ],
                "wp_password" => [
                    "label" => "Password", 
                    "rules" => "required|min_length[8]"
                ],
                "wp_confirm_password" => [
                    "label" => "Confirm Password", 
                    "rules" => "matches[wp_password]"
                ],
            ];

            $account_type = str_replace(array(' ', '_'), '-', trim($this->request->getVar("wp_acct_type")));

            if (!in_array($account_type, $this->arrayOfAcceptableAccountTypes)) {
                return $this->response->setJSON(['wp_acct_type' => 'Invalid account type selected.']);
            }

            if ($this->validate($rules)) {
                $accountAuth = new AuthModel();
                $uniq_token = md5(uniqid($this->request->getVar("wp_email")));
                $strtotimeTodayTime = strtotime("today");
                $strtotimeExpiry = strtotime("+15 minutes");
                $generatedOTP = $this->generateOTP();

                $accountAuthRegData = [
                    "email" => $this->request->getVar("wp_email"),
                    "phone" => '234'.$this->request->getVar("wp_phone"),
                    "password" => password_hash($this->request->getVar("wp_password"), PASSWORD_DEFAULT),
                    "acct_type" => $account_type,
                    "created_at" => $strtotimeTodayTime,
                    "verification_otp" => $generatedOTP,
                    "token" => $uniq_token,
                    "token_date" => $strtotimeTodayTime,
                    "token_expiry" => $strtotimeExpiry,
                ];

                $userAuthIDSubmitted = $accountAuth->saveToTable('auth_tbl', $accountAuthRegData);

                if ($userAuthIDSubmitted) {
                    if ($account_type == 'blood-bank') {
                        $profileToInsert = new BloodBankModel();
                    }
                    elseif ($account_type == 'hospital') {
                        $profileToInsert = new HospitalModel();
                    }

                    $profileToInsert->insert([
                        'auth_id' => $userAuthIDSubmitted, 
                        'name' => trim($this->request->getVar("wp_name")),
                        'email' => trim($this->request->getVar("wp_email")),
                        'phone' => '234'.$this->request->getVar("wp_phone"),
                        'reg_no' => trim($this->request->getVar("wp_email")), // Temporarily store their email as reg no until they are required to fill it
                        'created_at' => time(),
                    ]);

                    $response["status"] = 200;
                    $response["otp_token"] = $uniq_token;
                    $response["message"] = "Registration Successful.";
                }
                else {
                    $response["status"] = 401;
                    $response["message"] = "We could not complete your request. Please try again.";
                }
            } 
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function webAppLogout()
    {
        session()->destroy();

        return redirect()->route('login');
    }

}