<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AuthModel;
use App\Models\BloodDonationModel;
use App\Models\HospitalModel;
use App\Models\BloodBankModel;

class SettingsPageController extends BaseController
{
    protected   $authModel;
    protected   $user_data;
    protected   $arrayOfAcceptableAccountTypes;

    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank', 'pharmacy'
        ];

        $this->isUserSignedIn();
        
        $this->authModel = new AuthModel();
        $this->bloodBankModel = new BloodBankModel();

        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $this->user_info = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);
    }

    public function settings()
    {
        $data = [];

        $data['v'] = 'settings';
        $data['results']['page_title'] = 'Settings';

        echo view('webapp/template', $data);
    }

    public function bloodBankSetRates()
    {
        $profileInformation = $this->user_info;;
        $bloodBankRates = $this->bloodBankModel->bloodBankBloodGroupRates($profileInformation->id);

        $data = [];

        $data['v'] = 'set-rates';
        $data['results']['page_title'] = 'Set Rates';
        $data['results']['bloodBankRates'] = $bloodBankRates;

        if (!$bloodBankRates) {
            $data['results']['bloodBankRates'] = $this->getAllBloodGroups();
        }

        echo view('webapp/template', $data);
    }

    public function bloodBankStoreSetRates()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_blood_price" => [
                    "label" => "Blood Price", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $profileInformation = $this->user_info;
                $bloodGroupToPrice = $this->request->getVar("wp_blood_price");

                $ratesSaved = $this->bloodBankModel->setBloodBankBloodGroupRates($profileInformation->id, $bloodGroupToPrice);

                if ($ratesSaved) {
                    $response['status'] = 200;
                    $response['message'] = 'Rates saved successfully.';
                }
                else {
                    $response['status'] = 401;
                    $response['message'] = 'We could not complete your request. Please try again.';
                }
            }
            else {
                $response = $validation->getErrors();
            }
        }
        
        return $this->response->setJSON($response);
    }

    public function BankInformationPage()
    {
        $profileInformation = $this->user_info;
        $bankDetails = $this->bloodBankModel->getBankDetailsInformation($profileInformation->id);
        $availableBanks = $this->getAvailableBanks();
        $displayWarning = false;

        if (count($_GET)) {
            if ($_GET['verify'] && !$bankDetails) {
                $displayWarning = true;
            }
        }

        $data = [];

        $data['v'] = 'bank-details';
        $data['results']['page_title'] = 'Bank Information';
        $data['results']['bankDetails'] = $bankDetails;
        $data['results']['availableBanks'] = $availableBanks;
        $data['results']['displayWarning'] = $displayWarning;

        echo view('webapp/template', $data);
    }

    public function storeBankInformation()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();

            $rules = [
                "wp_bank_name" => [
                    "label" => "Bank Name", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_acct_name" => [
                    "label" => "Account Name", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_account_number" => [
                    "label" => "Account Number", 
                    "rules" => "required|min_length[2]"
                ],
                "wp_sort_code" => [
                    "label" => "Sort Code", 
                    "rules" => "permit_empty|min_length[2]"
                ],
            ];

            if ($this->validate($rules)) {
                $profileInformation = $this->user_info;
                $bankCode = $this->request->getVar("wp_bank_name");
                $accountName = $this->request->getVar("wp_acct_name");
                $accountNumber = $this->request->getVar("wp_account_number");
                $sortCode = $this->request->getVar("wp_sort_code");

                $bankDbInformation = $this->getBankInformationFromDB($bankCode);
                $bankDetailsInformation = $this->verifyBankAccountInformation($accountNumber, $bankCode);

                if ($bankDetailsInformation && $bankDbInformation) {
                    if (trim(strtolower($bankDetailsInformation->account_name)) == strtolower($accountName) || true) {
                        $data = [
                          "type" => $bankDbInformation->type,
                          "name" => $bankDetailsInformation->account_name,
                          "account_number" => $bankDetailsInformation->account_number,
                          "bank_code" => $bankDbInformation->code,
                          "currency" => $bankDbInformation->currency
                        ];

                        $transferRecipient = $this->createTransferRecipient($data);

                        if ($transferRecipient) {
                            $data = [
                                'auth_id' => $profileInformation->id,
                                'auth_type' => $this->accountAuthType,
                                'bank_name' => $bankDbInformation->name,
                                'acct_name' => $bankDetailsInformation->account_name,
                                'acct_number' => $accountNumber,
                                'sort_code' => $sortCode,
                                'recipient_code' => $transferRecipient->recipient_code,
                            ];

                            $this->bloodBankModel->saveBankAccountDetails($data);

                            $response['status'] = 200;
                            $response['message'] = 'Bank Account Information saved successfully.';
                        }
                    }
                    else {
                        $response['status'] = 401;
                        $response['message'] = 'Invalid Account information. Please ensure the details match with your bank account before trying again.';
                    }
                }
                else {
                    $response['status'] = 401;
                    $response['message'] = 'We could not complete your request. Please try again.';
                }
            }
            else {
                $response = $validation->getErrors();
            }
        }
        
        return $this->response->setJSON($response);
    }
}
