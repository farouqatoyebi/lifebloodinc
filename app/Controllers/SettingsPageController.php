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
    protected   $bloodBankModel;
    protected   $accountAuthID;
    protected   $accountAuthType;
    protected   $user_info;

    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank'
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
}
