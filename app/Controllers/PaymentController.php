<?php

namespace App\Controllers;
use App\Models\AuthModel;
use App\Models\BloodBankModel;
use App\Models\BloodDonationModel;
use App\Models\HospitalModel;
use App\Libraries\Paystack;

use App\Controllers\BaseController;

class PaymentController extends BaseController
{
    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank', 'pharmacy'
        ];

        $this->isUserSignedIn();

        $this->confirmAccountCompletion();
        
        $this->authModel = new AuthModel();
        $this->bloodBankModel = new BloodBankModel();
        $this->bloodDonationModel = new BloodDonationModel();
        $this->hospitalModel = new HospitalModel();
        $this->outgoingEmails = new SendOutgoingEmailController();
    }

    public function index()
    {

    }

    public function seePaymentBreakdownSummary()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        if ($requestID) {
            $allAcceptedOffers = $this->bloodDonationModel->getAllAcceptedOffersForRequest($requestID);
            $offersLeftToAccept = $this->hospitalModel->getRequestLeftAfterAccepted($requestID);
            $request_info = $this->hospitalModel->fetchRequestInformation($requestID);

            $data['v'] = 'payment-summary';
            $data['results']['page_title'] = 'Payment Summary';
            $data['results']['allAcceptedOffers'] = $allAcceptedOffers;
            $data['results']['offersLeftToAccept'] = $offersLeftToAccept;
            $data['results']['requestID'] = $requestID;
            $data['results']['request_info'] = $request_info;

            echo view('webapp/template', $data);
        }
    }

    public function createNewRequestFromWhatsLeft()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        if ($requestID) {
            $response = [];
            helper(['form']);

            // Only post requests are accepted
            if ($this->request->getMethod() == "post") {
                $validation =  \Config\Services::validation();

                
                $rules = [
                    "create_new_request" => [
                        "label" => "Old Request", 
                        "rules" => "required"
                    ],
                    "due_date" => [
                        "label" => "Due Date", 
                        "rules" => "required|valid_date"
                    ],
                ];

                if ($this->validate($rules)) {
                    $requestResponse = $this->request->getVar('create_new_request');
                    $due_date = $this->request->getVar('due_date');

                    if ($due_date <= date("Y-m-d")) {
                        return $this->response->setJSON([
                            "status" => 401,
                            "validation" => [
                                'due_date' => '* Please select a future due date.',
                            ],
                        ]);
                    }

                    if ($requestResponse == 'yes') {
                        $offersLeftToAccept = $this->hospitalModel->getRequestLeftAfterAccepted($requestID);
                        $response['status'] = 200;
                        $requestCreated = false;

                        if ($offersLeftToAccept) {
                            $requestCreated = $this->hospitalModel->createNewRequestFromThisRequest($requestID, $offersLeftToAccept, $due_date);

                            if ($requestCreated) {
                                $response = [
                                    "status" => 200,
                                    "message" => 'A new request has been generated successfully. Preparing your payment information.'
                                ];
                            }
                            else {
                                $response = [
                                    "status" => 401,
                                    "message" => 'We could not generate the new request. Please try again.'
                                ];
                            }
                        }
                    }
                    else {
                        $response = [
                            "status" => 200,
                            "message" => 'No new request created'
                        ];
                    }
                }
                else {
                    $response = [
                        "status" => 401,
                        "validation" => $validation->getErrors() 
                    ];
                }
            }
        }

        return $this->response->setJSON($response);
    }

    public function fetchPaymentInformationDetails()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
        $paystackModel = new Paystack();
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        if ($requestID) {
            $allAcceptedOffers = $this->bloodDonationModel->getAllAcceptedOffersForRequest($requestID);

            if ($allAcceptedOffers) {
                $finalAmount = 0;
                $serviceChargeFee = $this->getServiceChargeFee('web-app', 'amount');

                $serviceChargeFee = $serviceChargeFee ? $serviceChargeFee : 5000;

                foreach ($allAcceptedOffers as $key => $value) {
                    $finalAmount += ($value->amount_per_pint * $value->no_of_pints_confirmed);
                }

                if ($finalAmount) {
                    $response = [
                        'status' => 200,
                        'amount' => $finalAmount + $serviceChargeFee,
                        's_key' => $paystackModel->get_public_key(),
                        'email' => session('email'),
                    ];
                }
                else {
                    $response = [
                        'status' => 200,
                        'amount' => 0,
                        's_key' => '',
                        'email' => session('email'),
                    ];
                }
            }
        }

        return $this->response->setJSON($response);
    }

    public function recordTransactionPaymentInformation()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $requestID = 0;
        $paystackModel = new Paystack();
        $response = [];
    
        if (isset($exploded_url[2])) {
            $requestID = $exploded_url[2];
        }

        $recordTransaction = false;

        if ($requestID) {
            $allAcceptedOffers = $this->bloodDonationModel->getAllAcceptedOffersForRequest($requestID);
            $finalAmount = 0;
            $serviceChargeFee = $this->getServiceChargeFee('web-app', 'amount');
            $tx_ref = $this->request->getVar('tx_ref');
            $action = $this->request->getVar('action');

            $serviceChargeFee = $serviceChargeFee ? $serviceChargeFee : 5000;
            $status = 'pending';

            foreach ($allAcceptedOffers as $key => $value) {
                $finalAmount += ($value->amount_per_pint * $value->no_of_pints_confirmed);
            }

            $finalAmount = $finalAmount + $serviceChargeFee;

            if ($action == 'validate') {
                if ($paystackModel->verifyPayment($tx_ref, $finalAmount)) {
                    $status = 'success';
                }
            }

            $recordTransaction = $this->hospitalModel->recordTransactionPayment($requestID, $finalAmount, $tx_ref, $status);
            $this->outgoingEmails->sendOutPaymentReceiptForPaymentMade(session('email'), $finalAmount);
        }

        return $this->response->setJSON($response);
    }
}
