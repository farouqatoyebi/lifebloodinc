<?php

namespace App\Controllers;

use App\Controllers\BaseController;
// use App\Libraries\Flutterwave;
use App\Libraries\Paystack;
use App\Models\RequestModel;
use App\Models\BloodBankModel;
use App\Models\UserModel;
use App\Controllers\Coupon;
use Phpqrcode\QRcode;

class BloodRequestController extends BaseController
{
    
    protected $pint_rate = 10000;    // This is used to calc. the amount to be paid by patient

	public function __construct (){
        
        helper(['Auth']);

        $this->requestModel = new RequestModel();
    }



    public function applyDonor()
    {
        $userModel = new UserModel();

        $user = $userModel->find(auth_id());
        
        $age = getAge($user->dob);
        
        if ($user->donor == 'yes') {
            
            $response = [
                'status' => 200,
                'error' => false,
                'message' => "You are already a donor",
                'data' => null,
            ];
                 
            return $this->respond($response);
        }


        if (empty($user->blood_group) || 
            empty($user->dob) || 
            empty($user->gender) ||
            empty($user->genotype) ||
            empty($user->latitude) ||
            empty($user->longitude) ||
            empty($user->logo) ||
            empty($user->address)
        ) 
        {
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Please update your profile and try again!",
                'data' => null,
            ];
                 
            return $this->respond($response);
            
        }

        if ($age < 16 || $age > 60) {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "Sorry you cannot apply to be a donor as you are not within the age bracket which is from 16yrs to 60yrs",
                'data' => null,
            ];
                 
            return $this->respond($response);
        }


        if(!$userModel->update(auth_id(), ['donor' => 'yes']))
        {

            $response = [
                'status' => 400,
                'error' => true,
                'message' => "try again",
                'data' => null,
            ];
                 
            return $this->respond($response);
        }


        $subject = "Congratulations {$user->firstname} {$user->lastname}";
        $message = "<h4>Thank you for applying to be a donor. Donating money is Great, but donating Blood Is even better, Your droplets of blood may create an ocean of happiness. There is a hope of life to someone in your blood donation.</h4>
                <h2>Some Blood Donation Tips</h2>
                    <p>Donation frequency should be Every 56 days from Donation.</p>
                    <p>You must be in good health condition.</p>
                    <p>You must be at least 16 years above to donate</p>
                    <p>You must weigh at least 50 KG</p>
                    <h4>It feels good, It makes me Proud, I am a blood donor. Cheers!!!</h4>";

        sendEmail($user->email, $subject, $message);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "You are now a donor.",
            'data' => null,
        ];
             
        return $this->respond($response);

    }



    // Method for handling blood request
    public function postRequest()
    {
    	
    	$post_data = $this->request->getJSON();

        $post_data->auth_id = auth_id();

    	$comparison = [
    		'auth_id' => $post_data->auth_id,
    		'blood_group' => $post_data->blood_group,
    		'no_of_pints' => $post_data->no_of_pints,
    		'status' => 'pending'
    	];


    	if ($result = $this->requestModel->where($comparison)->first())
    	{
            $time_diff = time() - $result->created_at;

            if ($time_diff < 5) {
    		
                $response = [
    	            'status' => 200,
    	            'error' => true,
    	            'message' => "Duplicate record/request found",
    	            'data' => null,
    	        ];
                 
            	return $this->respond($response);
            }
    	}

    	$post_data->status = 'pending';

    	if (!$request_id = $this->requestModel->insert($post_data)) {
    		
    		$response = [
	            'status' => 500,
	            'error' => true,
	            'message' => $this->requestModel->errors(),
	            'data' => null,
	        ];
             
        	return $this->respond($response);
    	}

    	$latt = $post_data->latitude;
    	$long = $post_data->longitude;



    	$data = $this->requestModel->findNearbyDonors($latt, $long);

        $bloodBank = new BloodBankModel();

        $blood_banks = $bloodBank->allowCallbacks()->where('country_id', $post_data->country_id)->find();

        // Merge the blood bank and Users Data to send Notification
    	$merged_data = array_merge($data->users, $blood_banks);

        $this->notify($merged_data);

    	$response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
            	'request_id' => $request_id,
                'no_of_pints' => $post_data->no_of_pints,
            	'radius_range' => $data->radius_range,
                'users' => $data->users,
                'blood_banks' => $blood_banks,
            	],
        ];
             
        return $this->respond($response);

    }



    public function availableDonors($request_id)
    {
        $donors = $this->requestModel->fetchAvailableDonors($request_id);
        
        if (!is_null($donors)) {

            $donors = assignLogoUrl($donors, $this->thumb_path);
        }


        $blood_banks = $this->requestModel->fetchAvailableBloodBanks($request_id);

        if (!is_null($blood_banks)) {

            $blood_banks = assignLogoUrl($blood_banks, $this->thumb_path);
        }

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'donors' => $donors,
                'blood_banks' => $blood_banks,
                ],
        ];
             
        return $this->respond($response);
    }



    public function activeRequests()
    {

        $userModel = new UserModel();

        $user = $userModel->find(auth_id());
    	
        $requests = $this->requestModel->fetchNearbyRequests($user->latitude, $user->longitude);

        $requests = assignLogoUrl($requests, $this->thumb_path);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'requests' => $requests
            ],
        ];
             
        return $this->respond($response);
    }


    public function viewSingleRequest($request_id)
    {
        $data = $this->requestModel->details($request_id);

        $request = $data['request'];

        if (empty($request)) {
            return $this->failNotFound();
        }

        $no_of_donors = $this->no_of_donors($request_id);

        $request->logo = base_url($this->thumb_path.$request->logo);

        $request->pints_left = $request->no_of_pints - $no_of_donors;

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => $request
        ];
             
        return $this->respond($response);
    }




    /*
    *   This method is is used to handle request acceptance by donors
    */

    public function acceptRequest($request_id)
    {
        $data = [
            'request_id' => $request_id, 
            'donor_id' => auth_id(), 
            'donor_type' => 'user', 
            'created_at' => time(),
        ];

        $table = 'request_donors_tbl';

        $id = $this->requestModel->saveToTable($table, $data);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => null
        ];
             
        return $this->respond($response);

    }



    /*
    *   This method is is used to handle request acceptance by donors
    */

    public function donorStatus($request_id)
    {
        // Check if the user is in list of accepted Donors
        if(!$this->requestModel->donorsList($request_id, auth_id())){

            $request = $this->requestModel->find($request_id);

            // Check the Status of the request
            if ($request->status == 'paid' || $request->status == 'complete') {
                
                $response = [
                    'status' => '200',
                    'error' => false,
                    'message' => "Request has been Completed",
                    'data' => [
                        'status' => 'complete'
                    ]
                ];
                     
                return $this->respond($response);

            }elseif($request->status == 'cancelled'){

                $response = [
                    'status' => '200',
                    'error' => false,
                    'message' => "User has cancelled the request",
                    'data' => [
                        'status' => 'cancelled'
                    ]
                ];
                     
                return $this->respond($response);

            }else{

                $response = [
                    'status' => 200,
                    'error' => false,
                    'message' => "success",
                    'data' => [
                        'status' => 'pending'
                    ]
                ];
                     
                return $this->respond($response);
            }
        }else{

            $request_detail = $this->requestModel->details($request_id);

            $request_detail = assignLogoUrl($request_detail, $this->thumb_path);

            $response = [
                'status' => 200,
                'error' => false,
                'message' => "success",
                'data' => [
                        'status' => 'confirmed',
                        'request' => $request_detail,
                    ]
            ];
                 
            return $this->respond($response);
        }

    }



    protected function no_of_donors($request_id)
    {
        if(!$donors = $this->requestModel->donorsList($request_id)){
            return 0;
        }else{

            return count($donors);
        }

    }


    /**
        * This method is is used to handle patient's acceptance of donors. 
        * The request must be a post requst with the following parameters
        * @param donor_id
        * @param request_id
    */

    public function confirmDonor()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->donors) || empty($post_data->donors) ||
            !isset($post_data->request_id) || empty($post_data->request_id)
        ){

            return $this->failValidationError();
        }


       if(!$this->requestModel->updateDonorStatus($post_data->request_id, $post_data->donors))
       {
            return $this->failValidationError('Unable to update Record(s)');
       }

       $no_of_donors = $this->no_of_donors($post_data->request_id);

       $cost = $this->calculate_request_cost($no_of_donors);

       $paystack = new Paystack();

        $public_key  = $paystack->get_public_key();

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'before_discount' => $cost['before_discount'],
                'discount' => $cost['discount'],
                'after_discount' => $cost['after_discount'],
                'currency_code' => 'NGN',
                'public_key' => $public_key
            ]
        ];
             
        return $this->respond($response);

    }



    /**
        * This method is used to add Coupon to request 
        * @param request_id
    */

    public function useCoupon()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->coupon) || empty($post_data->coupon) ||
            !isset($post_data->request_id) || empty($post_data->request_id)
        ){
            return $this->failValidationError();
        }

            
        $coupon = new Coupon();

        // check if Coupon is valid
        if(!$coupon_data = $coupon->validateCoupon($post_data->coupon))
        {
            $response = [
                'status' => 400,
                'error' => true,
                'message' => $coupon->error(),
                'data' => null
            ];

            return $this->respond($response);
        }
        

        // Get no of Donors Selected
        $no_of_donors = $this->no_of_donors($post_data->request_id);
        $cost = $this->calculate_request_cost($no_of_donors, $coupon_data);

        $data = [
            'amount' => $cost['before_discount'],
            'paid_amount' => $cost['after_discount'],
            'discount' => $cost['discount']
        ];

        $this->requestModel->update($post_data->request_id, $data);

        $data2 = [
            'user_id' => auth_id(),
            'coupon_id' => $coupon_data->id,
            'request_id' => $post_data->request_id,
            'date_used' => time(),
        ];

        $coupon->addUser($data2);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'before_discount' => $cost['before_discount'],
                'discount' => $cost['discount'],
                'after_discount' => $cost['after_discount'],
                'currency_code' => 'NGN'
            ]
        ];
             
        return $this->respond($response);


    }



    /**
        * This method is used to initialise payment from the mobile endpoint
        * The request must be a post request with the following parameters
        * @param request_id
    */

    public function initialisePayment()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->request_id) || empty($post_data->request_id)){

            return $this->failValidationError();
        }

        $txn_ref  = "bdr_".time();  // Transaction Reference

        $request_data = $this->requestModel->find($post_data->request_id);

        if (!is_null($request_data->discount)) 
        {
            $amount = $request_data->paid_amount;

            $this->requestModel->update($post_data->request_id, ['txn_ref' => $txn_ref]);

        }else{

            // Get no of Donors Selected
            $no_of_donors = $this->no_of_donors($post_data->request_id);

            // Calculate Cost
            $cost = $this->calculate_request_cost($no_of_donors);
            
            $amount = (float) $cost['after_discount'];

            $data = [
                'amount' => $cost['before_discount'],
                'paid_amount' => $cost['after_discount'],
                'discount' => $cost['discount'],
                'txn_ref' => $txn_ref,
            ];

            $this->requestModel->update($post_data->request_id, $data);
        }

        $paystack = new Paystack();

        $public_key  = $paystack->get_public_key();

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'public_key' => $public_key,
                'amount' => $amount,
                'txn_ref' => $txn_ref
            ]
        ];
             
        return $this->respond($response);

    }



    /**
        *   This method is used to verify payment for the blood request
        *   A post request having the following informations
        * @param txn_ref
        * @param request_id
    
    */

    public function verifyPayment()
    {
        $post_data = $this->request->getJSON();

        if (!isset($post_data->request_id) || empty($post_data->request_id) ||
            !isset($post_data->txn_ref) || empty($post_data->txn_ref)
        ){

            return $this->failValidationError();

        }

        $comparison = ['id' => $post_data->request_id, 'txn_ref' => $post_data->txn_ref];

        $request_data = $this->requestModel->select('paid_amount, discount, txn_ref')->where($comparison)->first();

        if (empty($request_data)) {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "data not found",
                'data' => null
            ];

            return $this->respond($response);
        }


        if ($request_data->paid_amount == 0) {
            
            $coupon = new Coupon();

            if ($cp = $coupon->isUsed(auth_id(), $post_data->request_id)) {
                
                if ($coupon->worth($cp->coupon_id) != 100) {

                    $response = [
                        'status' => 200,
                        'error' => true,
                        'message' => "Invalid Transaction, could not be verified",
                        'data' => null
                    ];

                    return $this->respond($response);
                }
            }
        
        }else{

            $paystack = new Paystack();

            if (!$paystack->verifyPayment($request_data->txn_ref, $request_data->paid_amount)){

                $response = [
                    'status' => 400,
                    'error' => true,
                    'message' => "This transaction could not be verified",
                    'data' => null
                ];

                return $this->respond($response);
            }
        }

        $qr = $this->generateQRCode();

        $data = [
            'status' => 'paid',
            'qrc_id' => $qr->id,
            'qrc_file' => $qr->file,
        ];

        $this->requestModel->update($post_data->request_id, $data);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "Payment verified",
            'data' => null
        ];
             
        return $this->respond($response);

    }



    public function requestData($request_id)
    {
        $request_data = $this->requestModel->details($request_id, true);
        
        $donors = $request_data['donors'];
        $request = $request_data['request'];

        $donors = assignLogoUrl($donors, $this->thumb_path);

        $request->logo = base_url($this->thumb_path.$request->logo);
        $request->qrcode = base_url($this->qrcode_path.$request->qrcode);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'request' => $request,
                'donors' => $donors,
            ]
        ];
             
        return $this->respond($response);
    }



    public function rewardDonor($qr_id)
    {
        if(!$request = $this->requestModel->where('qrc_id', $qr_id)->first())
        {
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "No record Found for this request",
                'data' => null
            ];
             
            return $this->respond($response);  
        }

        // Verify if this user is amongst the donor to be rewarded for this request

        if (!$donor = $this->requestModel->donorsList($request->id, auth_id()) ) {
            
            $response = [
                'status' => 400,
                'error' => true,
                'message' => "user not found in the list of donors for this donation",
                'data' => null
            ];
             
            return $this->respond($response);
        }


        // Verify that donor has not Recieved a reward for such request
        $data = ['request_id' => $request->id, 'donor_id' => auth_id()];

        if ($this->requestModel->checkDonorReward($data)) {
            
            $response = [
                'status' => 200,
                'error' => true,
                'message' => "You have claimed your Reward for this donation already",
                'data' => null
            ];
             
            return $this->respond($response);
        }

        $reward = $this->calc_donor_reward();

        $data = [
            'donor_id' => auth_id(),
            'request_id' => $request->id,
            'amount' => $reward,
            'description' => 'blood donation',
            'created_at' => time(),
        ];

        $this->requestModel->saveDonorReward($data);

        $wallet = new \App\Models\WalletModel();

        if ($wallet->find(auth_id())) {

            // updated exiting record
            $wallet->updateDonationWallet(auth_id(), $reward);

        }else{

            // New Entry
            $data = [
                'auth_id' => auth_id(),
                'balance' => $reward,
            ];

            $wallet->insert($data);
        }

        $reward = number_format($reward);
        $response = [
            'status' => 200,
            'error' => false,
            'message' => "Thank you for saving a life today. You have recieved N$reward as your reward for saving a life",
            'data' => null,
        ];

        return $this->respondCreated($response);

    }


    /**
        * This method is used to calc. the amount given to donor
        * @param request_id
    */

    protected function calc_donor_reward(){

        $rate_data = $this->requestModel->betalife_rate(true);

        $service_charge = $rate_data->amount * $rate_data->service_rate / 100;

        $donor_reward = $rate_data->amount - $service_charge;

        return $donor_reward;
    }


    /**
        * To view the QRCode of a request
        * @param request_id
    */

    public function showQRCode($request_id)
    {

        $request = $this->requestModel->select('qrc_file')->find($request_id);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => [
                'qrcode_path' => base_url($this->qrcode_path.$request->qrc_file),
            ]
        ];
             
        return $this->respond($response);
    }


    // This Handles and returns the list of Urgency to the urgency Endpoint 
    public function getUrgency()
    {
        $urgencies = $this->requestModel->fetchTable('urgency_tbl');

        $response = [
            'status' => 200,
            'error' => false,
            'message' => "success",
            'data' => $urgencies
        ];
             
        return $this->respond($response);

    }



    /**
        * Calculates the cost of the Donation Request and returns an array 
        * of discount, total amount (before_discount) and discounted Amount (after_discount)
        * @param no_of_donors in the request
        * @param discount to be minused
        * @param pint_rate is the rate 
    */

    protected function calculate_request_cost(int $no_of_donors, $coupon_data = null)
    {
        $discount = 0;

        if (is_array($coupon_data)) {
            $coupon_data = (object) $coupon_data;
        }

        $before_discount = $this->requestModel->betalife_rate() * $no_of_donors;

        if (!is_null($coupon_data)) {

            // Determine Discount type
            if ($coupon_data->type == 'percentage') {
                
                $discount = $before_discount * $coupon_data->worth / 100;

            }else{

               $discount = $coupon_data->worth;
            }
        }

        
        $after_discount = $before_discount - $discount;

        $data = [
            'before_discount' => $before_discount,
            'discount' => $discount,
            'after_discount' => $after_discount
        ];

        return $data;
    }



    /**
        * This method is used to calculate the radius between two Points
        * The request must be a post request with the following parameters
        * @param latt1 is the lattitude of point A
        * @param latt2 is the lattitude of point B
        * @param long1 is the Longitude of point A
        * @param long2 is the Longitude of point B
    */

    public function calculateDistance($latt1, $long1, $latt2, $Long2)
    {
    	$latt_diff = $latt1 - $latt2;
    	$long_diff = $long1 - $latt2;
    	$val = pow(sin($latt_diff/2), 2) + cos($latt1)*cos($latt2)*pow(sin($long_diff/2), 2);

    	$b = 2 * asin(sqrt($val));

    	$mean_radius = 3958.756;
    	$distance = $b * $mean_radius;

    	return $distance;

    }

    protected function generateQRCode($qr_id = null)
    {
    	$Qr = new QRcode();

    	if (is_null($qr_id)) {
         
            $qr_id = base64_encode(uniqid().time());
        }


        $filename = "qr_".time().".png";

        $path = $this->qrcode_path.$filename;

        // $qr_url = base_url("blood-donation/donor/get-reward/$qr_id");

        $Qr->png($qr_id, $path, 'H', 20);

        $data = (object) ['id' => $qr_id, 'file' => $filename, 'path' => $path];
        
        return $data;
    }




    /**

    *   This method is used to send notifications to users
    *   @param

    */
    protected function notify($users){

        
        $email_array = array();
        $phone_array = array();

        foreach($users as $user) {

            array_push($email_array, $user->email);
            array_push($phone_array, $user->phone);
            
        }

        $emails = implode(',', $email_array);
        $phone_nos = implode(',', $phone_array);


        $subject = "New Blood Request";
        $html_message = "<h3>There is a new blood request. please log on to your betalife app to save a life today</h3>";
        $text_message = "There is a new blood request. please log on to your betalife app to save a life today";

        sendEmail($emails, $subject, $html_message);

        // sendSMS($phone_nos, $text_message);

        return true;

    }

    
}
