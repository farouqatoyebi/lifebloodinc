<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AccountInformation;

class HealthInsuranceController extends BaseController
{
    public function __construct (){
        $this->isUserSignedIn();
        $this->confirmAccountCompletion();

        $this->accountInformation = new AccountInformation();
        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $this->user_info = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);
    }

    public function index()
    {
        if (!$this->user_info->owners_bvn) {
            session()->setFlashdata('info', 'Complete your profike to continue with health insurance');
            return redirect()->route('profile');
        }

        $isAccountCreated = $this->accountInformation->getAccountInformation($this->accountAuthID, $this->accountAuthType);
        $accountVerified = false;

        if (!$isAccountCreated) {
            $accountVerified = $this->user_info->status;

            if ($this->user_info->status == 'active') {
                $accountVerified = 'pending';
            }
        }
        else {
            $accountVerified = $isAccountCreated->status;
        }

        $data['v'] = 'health-insurance';
        $data['results']['accountVerified'] = $accountVerified;
        $data['results']['page_title'] = 'Health Insurance';

        echo view('webapp/template', $data);
    }
}
