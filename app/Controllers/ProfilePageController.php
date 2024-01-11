<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AuthModel;
use App\Models\BloodBankModel;
use App\Models\HospitalModel;
use App\Models\PharmacyModel;

class ProfilePageController extends BaseController
{
    protected $arrayOfAcceptableAccountTypes;
    protected $authModel;
    protected $accountAuthID;
    protected $accountAuthType;
    protected $user_info;
    
    public function __construct (){
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank', 'pharmacy'
        ];

        $this->isUserSignedIn();
        
        $this->authModel = new AuthModel();

        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $userProfileInformation = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);

        $this->user_info = $userProfileInformation;
    }

    public function index()
    {
        //
    }

    public function profileInformationPage()
    {
        $data['v'] = 'profile';

        $userProfileInformation = $this->user_info;
        $data['results']['allCountries'] = $this->getAllCountriesList();
        $data['results']['allStates'] = $this->getAllStatesList();
        $data['results']['allCities'] = $this->getAllCitiesList();
        $data['results']['page_title'] = 'Profile';

        $data['results']['userProfileInformation']= $userProfileInformation;

        echo view('webapp/template', $data);
    }

    public function processProfileCompletion()
    {
        $response = [];
        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $authModel = new AuthModel();
            $tableUnique = "auth_tbl";

            if (session('acct_type') == 'blood-bank') {
                $profileToUpdate = new BloodBankModel();
                $tableUnique = "blood_banks_tbl";
            }
            elseif (session('acct_type') == 'hospital') {
                $profileToUpdate = new HospitalModel();
                $tableUnique = "hospitals_tbl";
            }
            elseif (session('acct_type') == 'pharmacy') {
                $profileToUpdate = new PharmacyModel();
                $tableUnique = "pharmacies_tbl";
            }
            
            $rules = [
                "wp_name" => [
                    "label" => "name", 
                    "rules" => "required"
                ],
                "wp_location" => [
                    "label" => "Location", 
                    "rules" => "required"
                ],
                "wp_country" => [
                    "label" => "Country", 
                    "rules" => "required"
                ],
                "wp_city" => [
                    "label" => "City", 
                    "rules" => "required"
                ],
                "wp_state" => [
                    "label" => "State", 
                    "rules" => "required"
                ],
                "owners_first_name" => [
                    "label" => "First Name", 
                    "rules" => "required"
                ],
                "owners_last_name" => [
                    "label" => "Last Name", 
                    "rules" => "required"
                ],
                "owners_email" => [
                    "label" => "Owner's Email", 
                    "rules" => "required|valid_email|is_unique[auth_tbl.email]"
                ],
                "owners_phone" => [
                    "label" => "Phone", 
                    "rules" => "required|min_length[10]"
                ],
                "owners_bvn" => [
                    "label" => "BVN", 
                    "rules" => "required|min_length[10]"
                ],
                "owners_nin" => [
                    "label" => "NIN", 
                    "rules" => "required|min_length[10]"
                ],
                "owners_reg_date" => [
                    "label" => "Registration Date", 
                    "rules" => "required|date"
                ],
            ];

            if (!$this->user_info->logo) {
                $rules['wp_logo'] = [
                    "label" => "Logo", 
                    "rules" => "uploaded[wp_logo]"
                ];
            }

            if (!$this->user_info->cac_document) {
                $rules['wp_file_cac'] = [
                    "label" => "CAC Document", 
                    "rules" => "uploaded[wp_file_cac]"
                ];
            }

            $regNoExists = $profileToUpdate->where('reg_no', $this->request->getVar("wp_reg_no"))->get();
            $regNoExists = $regNoExists->getRow();

            if ($regNoExists) {
                if ($regNoExists->id != $this->user_info->id) {
                    return $this->response->setJSON(['validation' => ['wp_reg_no' => 'This registration number already belongs to an institution.']]);
                }
            }

            $phoneNoExists = $authModel->where('id', '<> '.session('auth_id'))->where('phone', '234'.$this->request->getVar("wp_phone"))->get();
            $phoneNoExists = $phoneNoExists->getRow();

            if ($phoneNoExists) {
                return $this->response->setJSON(['validation' => ['wp_phone' => 'This phone number already belongs to an institution.']]);
            }
            
            if ($this->validate($rules)) {
                $uploadedFileName = $this->moveUploadedFileToDestination('wp_logo');
                $uploadedCACDocument = $this->moveUploadedFileToDestination('wp_file_cac', 'cac_document', false);
                $cityValue = $this->request->getVar("wp_city");
                $countryID = $this->request->getVar("wp_country");
                $stateID = $this->request->getVar("wp_state");

                $getCityInformationID = $this->getPassedCityID($cityValue, $stateID, $countryID);

                if ($uploadedFileName) {
                    $accountAuthRegData = [
                        "name" => $this->request->getVar("wp_name"),
                        "city" => $getCityInformationID,
                        "state" => $stateID,
                        "country" => $countryID,
                        "address" => $this->request->getVar("wp_location"),
                        "logo" => $uploadedFileName,
                        "reg_no" =>  $this->request->getVar("wp_reg_no"),
                        "owners_firstname" =>  $this->request->getVar("owners_first_name"),
                        "owners_lastname" =>  $this->request->getVar("owners_last_name"),
                        "email" =>  $this->request->getVar("owners_email"),
                        "phone" =>  $this->request->getVar("owners_phone"),
                        "owners_bvn" =>  $this->request->getVar("owners_bvn"),
                        "owners_nin" =>  $this->request->getVar("owners_nin"),
                        "reg_date" =>  $this->request->getVar("owners_reg_date"),
                        "updated_at" => time(),
                    ];

                    if ($uploadedCACDocument) {
                        $accountAuthRegData['cac_document'] = $uploadedCACDocument;
                    }

                    session()->set([
                        'logo' => $uploadedFileName,
                        'name' => $this->request->getVar("wp_name"),
                    ]);

                    $this->authModel->update(session('auth_id'), [
                        "phone" => '234'.$this->request->getVar("wp_phone"),
                    ]);

                    $profileUpdated = $profileToUpdate->updateTableInformation($this->user_info->id, $accountAuthRegData);

                    $response["status"] = 200;
                    $response["message"] = "Account information has been updated successfully.";
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
}
