<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BloodGroupStock;

class BloodGroupStockController extends BaseController
{
    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank', 'pharmacy'
        ];

        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $this->user_info = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);

        $this->isUserSignedIn();
        $this->confirmAccountCompletion();
        $this->stockModel = new BloodGroupStock();
    }

    public function index()
    {
        $data['v'] = 'stock-information';
        $data['results']['inventory_info'] = $this->stockModel->getUsersStock($this->user_info->id, $this->accountAuthType);
        $data['results']['page_title'] = 'inventory Information';

        echo view('webapp/template', $data);
    }

    public function addToStock()
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
                "inventory_blood" => [
                    "label" => "Blood Group", 
                    "rules" => "required"
                ],
                "amount_avail" => [
                    "label" => "Amount Available",
                    "rules" => "required|numeric",
                ],
            ];

            if ($this->validate($rules)) {
                $bloodGroup = $this->request->getVar('inventory_blood');
                $amount_avail = $this->request->getVar('amount_avail');
                $data = [
                    'bloodGroup' => $bloodGroup,
                    'amount_avail' => $amount_avail,
                ];

                $this->stockModel->updateStockInformation($this->user_info->id, $this->accountAuthType, $data);

                $response["status"] = 200;
                $response["message"] = "Inventory list has been updated successfully.";
            }
            else {
                $response["status"] = 401;
                $response["validation"] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function stockHistory()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $bloodGroup = "A";
    
        if (isset($exploded_url[2])) {
            $bloodGroup = $exploded_url[2];
        }

        $data['v'] = 'stock-history';
        $data['results']['inventory_history'] = $this->stockModel->getBloodStockHistory($this->user_info->id, $this->accountAuthType, $bloodGroup);
        $data['results']['page_title'] = 'inventory History';

        echo view('webapp/template', $data);
    }
}
