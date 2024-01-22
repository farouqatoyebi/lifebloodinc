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

                    $subject = "Registration Successful";
                    $email_message = "<p>You have successfully completed your registration with BetaLife.</p> You can complete your registration with the OTP below <br> <h1> $generatedOTP </h1>";
                    $email_message .= "<p>We can't wait to have you on board.</p> <p>See you soon.</p> <p>Best Regards,</p><p>BetaLife</p>";

                    $sms_message = "Your registration OTP is $generatedOTP";


                    // if($this->sendSMS($this->request->getVar("wp_phone"), $subject, $sms_message)){
                    //     $sms_status = true;
                    // }

                    if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                        // Send OTP to Email
                        sendEmail($this->request->getVar("wp_email"), $subject, $email_message);
                    }

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

    // This is for testing and debugging purposes. Will be removed after development
    public function deleteUSer()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->email) || empty($post_data->email)) {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
        }

        if ($this->authModel->where('email', $post_data->email)->first())
        {
            
            if($this->authModel->where('email', $post_data->email)->delete('', true))
            {
                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => "Record deleted",
                    'data' => null
                ];

                return $this->respond($response);

            }else{

                $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => "Unable to delete record try again",
                    'data' => null
                ];

                return $this->respond($response);
            }

        }else{

            $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => "No record found for this user",
                    'data' => null
                ];

            return $this->respond($response);
        }


    }



    public function mobileLogin()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->user)) {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }elseif (empty($post_data->user)) {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Please provide email or phone",
                'data' => null
            ];

            return $this->respond($response);
        }


        // Find the user in auth table
        if(!$user = $this->authModel->findUser($post_data->user)){

            $response = [
                'status' => 404,
                'error' => true,
                'message' => "User not found",
                'data' => null
            ];

            return $this->respond($response);
        }

        // check if account is active or not
        if ($user->acct_status !== "active" ) {
            
            $response = [
                'status' => 401,
                'error' => true,
                'message' => "Account not activated! Please activate account",
                'data' => null
            ];

            return $this->respond($response);
        }


        if (!empty($user->token)) {
            
            $data = [
                'token' => '',
                'token_date' => '',
                'token_expiry' => '',
            ];
            
            $this->authModel->update($user->id, $data);
        }

        // Check if otp is active and not used
        if ($user->otp_expiry > time() && $user->otp_status == 0) {
            
            $response = [
                'status' => 200,
                'error' => false,
                'message' => "OTP sent already.",
                'data' => null
            ];

            return $this->respondCreated($response);

        }

        // Check if the user is loginin with phone
        if ($post_data->user == $user->phone) {
            
            // Check if phone is verified
            if (!$user->verified_phone) {
                
                $response = [
                    'status' => 404,
                    'error' => true,
                    'message' => "Please login using your email and verify your phone number",
                    'data' => null
                ];

                return $this->respondCreated($response);
            }
        }

        // Send OTP
        if (!$this->sendLoginOTP($user)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => "Something went wrong Please try again",
                'data' => null
            ];

        }else{

            $response = [
                'status' => 200,
                'error' => false,
                'message' => "OTP sent",
                'data' => null
            ];

        }

        return $this->respondCreated($response);
    }



    public function mobileLogout()
    {
        $post_data = $this->request->getJSON();

         if (!isset($post_data->user) || empty($post_data->user)) {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }        

        if(!$user = $this->authModel->findUser($post_data->user))
        {

             $response = [
                'status' => 404,
                'error' => true,
                'message' => "unauthorised request",
                'data' => null
            ];

            return $this->respond($response);

        }


        $data = [
            'token' => '',
            'token_date' => '',
            'token_expiry' => '',
        ];
                
        $this->authModel->update($user->id, $data);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "Logged out",
            'data' => null
        ];

        return $this->respond($response);

    }



    public function sendLoginOTP($user){


        // Set Message Sending default state to false
        $status=false;

        $otp = $this->generateOTP();

        $subject = "Login Authorization";
        $email_message = "<p>A sign in attempt has been made on your account. Please enter the OTP below to login to your account. You can ignore this message if you did not initiate this request.</p> <br> <h1> $otp </h1>";

        $sms_message = "Your Login OTP is $otp";

        // Send OTP to Email
        if(!sendEmail($user->email, $subject, $email_message)){
            return false;
        }

        // if($this->sendSMS($user->phone, $subject, $sms_message)){
        //     $sms_status = true;
        // }

        // $otp_expiry_period = strtotime('+15 minutes', time());

        $data = [
            'otp' => $otp,
            'otp_expiry' => $this->otp_expiry_period + time(),
            'otp_status' => 0
        ];

        $this->authModel->update($user->id, $data);

        return true;


    }

    public function completeLogin()
    {

        $post_data = $this->request->getJSON();

        if (!isset($post_data->user) || !isset($post_data->otp)) {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }

        if(!$user = $this->verifyOTP($post_data->otp))
        {
            $response = [
                'status' => 404,
                'error' => true,
                'message' => "Wrong or expired OTP",
                'data' => null
            ];

            return $this->respond($response);
        }
        
        
        if($post_data->user != $user->email && $post_data->user != $user->phone)
        {
           
            $response = [
                'status' => 404,
                'error' => true,
                'message' => "Invalid OTP",
                'data' => null
            ];

            return $this->respond($response);
        }


        if (!$this->setAccessToken($user->id, $user)) {
            
            $response = [
                'status' => 500,
                'error' => true,
                'message' => 'Something went wrong',
                'data' => null,
            ];
         
            return $this->respond($response, 200);
        }

        $userModel = new UserModel();
        $user_data = $userModel->find($user->id);

        // Success
        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'Logged In',
            'data' => $user_data,
        ];
         
        return $this->respond($response, 200);
    
    }


    protected function verifyOTP($otp)
    {

        if(!$user = $this->authModel->where('otp', $otp)->first())
        {   
            return false;

        }elseif ($user->otp_expiry < time() || $user->otp_status == 1) {
            
            return false;
            
        }

        $data = [
            "otp_status" => 1,
        ];

        $this->authModel->update($user->id, $data);
       
        return $user;

    }


    public function register()
    {

        $post_data = $this->request->getJSON();

        if (!isset($post_data->email) || !isset($post_data->phone) || !isset($post_data->firstname) || !isset($post_data->lastname))
        {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }

        if (empty($post_data->email) || empty($post_data->phone) || empty($post_data->firstname) || empty($post_data->lastname))
        {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Please provide all required fields",
                'data' => null
            ];

            return $this->respond($response);
        }

        if ($user = $this->authModel->where("email", $post_data->email)->first()) {
            
            if ($user->acct_status == "active") {
            
                $response = [
                    'status' => 200,
                    "error" => true,
                    'message' => 'this email already exists, Please login',
                    'data' => null
                ];

                return $this->respond($response);

            }

            // Check if existing otp has not expired
            if ($user->otp_expiry > time()) {
                
                $response = [
                    'status' => 200,
                    "error" => false,
                    'message' => 'Activation OTP sent to your email already',
                    'data' => null
                ];
        
                return $this->respondCreated($response);
            }

            // Send Verification Otp
            if (!$this->sendVerificationOTP($user->id, $post_data)) {
                
                $response = [
                    'status' => 500,
                    "error" => true,
                    'message' => 'Could not send Otp. Please try again',
                    'data' => null
                ];

                 return $this->respondCreated($response);
            }
        
            $response = [
                'status' => 200,
                "error" => false,
                'message' => 'Activation OTP has been sent to your email',
                'data' => null
            ];
    
            return $this->respondCreated($response);

        }   // End user exists check block

        
        // Continue to creating new User

        $data = [
            "email" => $post_data->email,
            "phone" => $post_data->phone,
            "acct_type" => 'user',
            "firstname" => $post_data->firstname,
            "lastname" => $post_data->lastname,

        ];


        if (!$aid = $this->authModel->insert($data)) {

            $response = [
                'status' => 500,
                "error" => true,
                'message' => $this->authModel->errors(),
                'data' => null
            ];

            return $this->respond($response);
        }


        $userModel = new UserModel();

        $data = [
            "auth_id" => $aid,
            "firstname" => $post_data->firstname,
            "lastname" => $post_data->lastname,

        ];


        if (!$userModel->insert($data)) {

            $response = [
                'status' => 500,
                "error" => true,
                'message' => $userModel->errors(),
                'data' => null
            ];

            return $this->respond($response);
        }


        // Send Verification Otp
        if (!$this->sendVerificationOTP($aid, $post_data)) {
            
            $response = [
                'status' => 500,
                "error" => true,
                'message' => 'Something went wrong try again',
                'data' => null
            ];


        }
            
        $response = [
            'status' => 200,
            "error" => false,
            'message' => 'Activation OTP sent to your email',
            'data' => null
        ];
        
        return $this->respondCreated($response);

    }   //  End register method




    public function completeRegistration()
    {

        $post_data = $this->request->getJSON();

        if (!isset($post_data->otp) || empty($post_data->otp) || !isset($post_data->user) || empty($post_data->user)) 
        {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }


        if (!$user = $this->verifyOTP($post_data->otp)) {
            
            $response = [
                'status' => 404,
                'error' => true,
                'message' => "Wrong or expired OTP",
                'data' => null
            ];

            return $this->respond($response);
        }

        $this->authModel->update($user->id, ['acct_status' => 'active']);

        // Set login Token and Header
        if(!$this->setAccessToken($user->id, $user))
        {
 
            $response = [
                'status' => 500,
                'error' => true,
                'message' => 'Something went wrong try again',
                'data' => null,
            ];
            
            return $this->respond($response);

        }

        $userModel = new UserModel();

        $user_data = $userModel->find($user->id);
        
        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'Logged In',
            'data' => $user_data,
        ];
             
        return $this->respondCreated($response, 200);
    
    }



    protected function setAccessToken($aid, $user)
    {
        
        $key = getenv('JWT_SECRET_KEY');
        $iat = time();
        $exp = $this->token_expiry_period + time();
 
        $payload = [
            "iss" => base_url(),
            "aud" => "Betalife Mobile App",
            "sub" => "Login Authorization Token",
            "iat" => $iat,
            "exp" => $exp,
            "data" => [
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            "aid" => $aid,
        ];
         
         // Generate Token
        $token = JWT::encode($payload, $key, 'HS256');

        $token_data = [
            'otp_status' => 1,    
            'token' => $token,    
            'token_date' => time(),    
            'token_expiry' => $exp,    
            'updated_at' => time(),    
        ];

        $this->authModel->update($user->id, $token_data);

        // Set Auth Header with Access token
        $this->response->setHeader('Authorization', 'Bearer '.$token);

        return true;
    }



    public function verifyAccessToken()
    {
        $request = service('request');

        $key = getenv('JWT_SECRET_KEY');


        // extract the token from the header
        if(!$authHeader = $request->getHeader("Authorization"))
        {
            return false;
            
        }else{

            $raw_token = $authHeader->getValue();

            if (preg_match('/Bearer\s(\S+)/', $raw_token, $matches)) {
                
                $token = $matches[1];
            
            }else{

                return false;
            }
        }


        try {

            if (!$decoded = JWT::decode($token, new Key($key, 'HS256'))) {

                return false;
            }
                
            if ($decoded->exp < time() || !isset($decoded->aid) || empty($decoded->aid)) {

                return false;
            }


            if (!$user = $this->authModel->where('token', $token)->first()) {
                return false;
            }

            if ($user->id !== $decoded->aid) {
                return false;
            }

            return $decoded;
            
        
        } catch (Exception $ex) {

            return false;
        }
    }



    public function sendVerificationOTP($aid, $user){

        $otp = $this->generateOTP();

        $subject = "Activate your Account";
        $message = "<p>Please enter the OTP below on the Mobile App to activate your account</p> <br> <h1> $otp </h1>";

        if (!sendEmail($user->email, $subject, $message)) {

            $status = false;

        }else{

            $status = true;

            $data = [
                'otp' => $otp,
                'otp_expiry' => time() + $this->otp_expiry_period,
                'otp_status' => 0,
                'updated_at' => time(),
            ];

            $this->authModel->update($aid, $data);

        }


        return $status;

    }




     public function resendOTP(){

        $post_data = $this->request->getJSON();

         if (!isset($post_data->user)) {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Invalid request",
                'data' => null
            ];

            return $this->respond($response);
            
        }


        if(!$user = $this->authModel->where('email',$post_data->user)->first())
        {
            $response = [
                'status' => 404,
                'error' => true,
                'message' => "User not found",
                'data' => null
            ];

            return $this->respond($response);
        }

        if ($user->acct_status == 'active') {
            
            $response = [
                'status' => 200,
                'error' => true,
                'message' => "Account already active please login",
                'data' => null
            ];

            return $this->respond($response);
        }

        if ($user->otp_expiry > time()) {
            
            $response = [
                'status' => 200,
                'error' => false,
                'message' => "OTP has been sent to your email already.",
                'data' => null
            ];

            return $this->respondCreated($response);

        }


        $otp = $this->generateOTP();

        $subject = "Activate your Account";
        $message = "<p>Please enter the OTP below on the Mobile App to activate your account</p> <br> <h1> $otp </h1>";

        // if (!$this->sendSMS($user_data->email, $subject, $message)) {

        //     $status = false;

        // }else{

        //     $status = true;
        // }

        if (!sendEmail($user->email, $subject, $message)) {

            $response = [
                'status' => 404,
                'error' => true,
                'message' => "Could not send OTP! please try again",
                'data' => null
            ];

            return $this->respondCreated($response);

        }


        $data = [
            'otp' => $otp,
            'otp_expiry' => time() + $this->otp_expiry_period,
            'otp_status' => 0,
            'updated_at' => time(),
        ];

        $this->authModel->update($user->id, $data);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "OTP has been sent to your email",
            'data' => null
        ];

        return $this->respondCreated($response);

    }

}