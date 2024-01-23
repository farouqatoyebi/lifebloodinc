<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Controllers\BaseController;
use App\Models\WalletModel;
use App\Models\HospitalModel;
use App\Models\UserModel;

class BloodBankModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'blood_banks_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['auth_id', 'name', 'city', 'state', 'country', 'Address', 'reg_no', 'logo'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = false;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = ['__fullData'];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    
    /**

    * This Method can be called by setting the allowCallBack to true
    * It will return the full data of blood banks with selected field from the auth_tbl

    */

    public function __fullData() {
        $this->select('blood_banks_tbl.name, blood_banks_tbl.address, countries_tbl.name as country, states_tbl.name as state, blood_banks_tbl.reg_no, blood_banks_tbl.logo, auth_tbl.phone, auth_tbl.email')
        ->join('auth_tbl', "auth_tbl.id = blood_banks_tbl.auth_id")
        ->join('countries_tbl', "countries_tbl.id = blood_banks_tbl.country", "left")
        ->join('states_tbl', "states_tbl.id = blood_banks_tbl.state", "left");
        return;
    }


    public function findAccount($account_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('auth_id ', $account_id);
        $query = $builder->get();
        return $query->getRow();
    }

    public function updateTableInformation($id, $data)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id', $id);
        $builder->update($data);

        return true;
    }

    public function bloodBankBloodGroupRates($bloodBankID)
    {
        $builder = $this->db->table($this->table);
        $builder->select('blood_banks_blood_group_rate_tbl.*');
        $builder->join('blood_banks_blood_group_rate_tbl', 'blood_banks_blood_group_rate_tbl.blood_bank_id = '.$this->table.'.id');
        $builder->where('blood_banks_blood_group_rate_tbl.blood_bank_id ', $bloodBankID);
        $query = $builder->get();
        return $query->getResult();
    }

    public function setBloodBankBloodGroupRates($bloodBankID, $bloodGroupToPriceArray)
    {
        if (!$bloodBankID) {
            return false;
        }

        if (!is_array($bloodGroupToPriceArray)) {
            return false;
        }

        if (!count($bloodGroupToPriceArray)) {
            return false;
        }
        $builder = $this->db->table('blood_banks_blood_group_rate_tbl');

        foreach ($bloodGroupToPriceArray as $key => $value) {
            $builder->where('blood_bank_id', $bloodBankID);
            $builder->where('blood_group', $key);
            $query = $builder->get();
            $result = $query->getRow();

            if ($value >= 0) {
                if ($result) {
                    $builder->where('blood_bank_id', $bloodBankID);
                    $builder->where('blood_group', $key);
                    $builder->update([
                        "blood_group" => $key,
                        "rate" => $value,
                        "updated_at" => time(),
                    ]);
                }
                else {
                    $builder->insert([
                        "blood_bank_id" => $bloodBankID,
                        "blood_group" => $key,
                        "rate" => $value,
                        "created_at" => time(),
                    ]);
                }
            }
        }

        return true;
    }

    public function fetchActivities($bloodBankID, $request_id = 0)
    {
        $additional_sql = '';
        $userModel = new UserModel();

        if ($request_id) {
            $additional_sql = " AND rtb.id = ".$request_id." ";
        }

        $sql = "SELECT rtb.*
                FROM requests_tbl rtb 
                WHERE EXISTS (
                    SELECT id
                    FROM request_donors_tbl rdtb
                    WHERE rdtb.request_id = rtb.id 
                        AND rdtb.donor_id = $bloodBankID
                        AND rdtb.donor_type = 'blood-bank' 
                        AND deleted_at IS NULL
                )
                AND deleted_at IS NULL
                ".$additional_sql."
                ORDER BY rtb.created_at DESC";

        $query = $this->db->query($sql);
        
        if ($request_id) {
            $allResults = $query->getRow();
            if ($allResults->auth_type == 'user') {
                $allResults->user = $userModel->find($allResults->auth_id);
            }
        }
        else {
            $allResults = $query->getResult();

            foreach ($allResults as $key => $value) {
                if ($value->auth_type == 'user') {
                    $allResults[$key]->user = $userModel->find($value->auth_id);
                }
            }
        }

        return $allResults;
    }

    public function getTotalNumberOfPendingActivities($bloodBankID)
    {
        $numberOfRequests = $this->fetchActivities($bloodBankID);

        if ($numberOfRequests) {
            return count($numberOfRequests);
        }

        return 0;
    }

    public function withdrawOfferSent($request_id, $bloodBankID)
    {
        $builder = $this->db->table('request_donors_tbl');
        $builder = $builder->where('request_id ', $request_id);
        $builder = $builder->where('donor_id', $bloodBankID);
        $builder = $builder->where('donor_type', 'blood-bank');
        $builder = $builder->whereIn('status', ['confirmed', 'complete']);
        $builder = $builder->get();

        $hasBeenAccepted = $builder->getRow();

        if (!$hasBeenAccepted) {
            $builder = $this->db->table('request_donors_tbl');
            $builder = $builder->where('request_id ', $request_id);
            $builder = $builder->where('donor_id', $bloodBankID);
            $builder = $builder->where('donor_type', 'blood-bank');
            $builder = $builder->where('status', 'pending');
            $builder->update([
                "deleted_at" => time(),
                'updated_at' => time(),
            ]);

            return [
                'status' => 200,
                'message' => 'Offer has been withdrawn successfully'
            ];
        }
        else {
            return [
                'status' => 401,
                'message' => 'Offer has already been accepted by Hospital and offer cannot be withdrawn'
            ];
        }
    }

    public function confirmRequestPaymentMade($request_id)
    {
        $baseController = new BaseController();

        $accountAuthID = session('auth_id');
        $accountAuthType = session('acct_type');
        $bloodBankInformation = $baseController->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);

        $builder = $this->db->table('blood_request_delivery_tbl');
        $builder = $builder->select('status, total_amount');
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->where('donor_id', $bloodBankInformation->id);
        $builder = $builder->where('donor_type', $accountAuthType);
        $builder = $builder->get();

        $awaitingDelivery = $builder->getRow();
        return $awaitingDelivery;
    }

    public function confirmDeliveryMade($request_id, $otpCode)
    {
        $baseController = new BaseController();
        $walletModel = new WalletModel();
        $hospitalModel = new HospitalModel();

        $accountAuthID = session('auth_id');
        $accountAuthType = session('acct_type');
        $bloodBankInformation = $baseController->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);

        $builder = $this->db->table('blood_request_delivery_tbl');
        $builder = $builder->select('status, total_amount');
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->where('donor_id', $bloodBankInformation->id);
        $builder = $builder->where('donor_type', $accountAuthType);
        $builder = $builder->where('otp_code', $otpCode);
        $builder = $builder->get();

        $confirmDeliveryInfo = $builder->getRow();

        if ($confirmDeliveryInfo) {
            if ($walletModel->creditBloodBankWallet($bloodBankInformation->id, $confirmDeliveryInfo->total_amount)) {
                $builder = $this->db->table('blood_request_delivery_tbl');
                $builder = $builder->where('request_id', $request_id);
                $builder = $builder->where('donor_id', $bloodBankInformation->id);
                $builder = $builder->where('donor_type', $accountAuthType);
                $builder = $builder->where('otp_code', $otpCode);
                $builder->update([
                    'status' => 'completed'
                ]);

                $builder = $this->db->table('request_donors_tbl');
                $builder = $builder->where('request_id', $request_id);
                $builder = $builder->where('donor_id', $bloodBankInformation->id);
                $builder = $builder->where('donor_type', $accountAuthType);
                $builder = $builder->where('status', 'confirmed');
                $builder = $builder->where('confirmed', '1');
                $builder->update([
                    'status' => 'complete'
                ]);

                if (!$hospitalModel->deliveryInformationBreakdown($request_id, 'pending')) {
                    $builder = $this->db->table('requests_tbl');
                    $builder = $builder->where('id', $request_id);
                    $builder->where('deleted_at', NULL);
                    $builder->update([
                        'status' => 'complete'
                    ]);
                }

                return true;
            }
        }

        return false;
    }

    public function getAllHospitalThatHasConfirmedBloodBanktTransactions()
    {
        $baseController = new BaseController();
        $hospitalModel = new HospitalModel();

        $accountAuthID = session('auth_id');
        $accountAuthType = session('acct_type');
        $bloodBankInformation = $baseController->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);

        if ($bloodBankInformation) {
            $builder = $this->db->table('request_donors_tbl');
            $builder = $builder->whereIn('status', ['confirmed', 'complete']);
            $builder = $builder->where('donor_id', $bloodBankInformation->id);
            $builder = $builder->where('donor_type', $accountAuthType);
            $builder = $builder->get();
            $allAcceptedPints = $builder->getResult();

            if ($allAcceptedPints) {
                foreach ($allAcceptedPints as $key => $value) {
                    $foundResults = false;

                    $builder = $this->db->table('requests_tbl');
                    $builder = $builder->where('id', $value->request_id);
                    $builder->where('deleted_at', NULL);
                    $builder = $builder->get();
                    $currentRequest = $builder->getRow();

                    if ($currentRequest) {
                        if ($currentRequest->auth_type == 'hospital') {
                            $currentInformation = $hospitalModel->find($currentRequest->auth_id);

                            if ($currentInformation) {
                                $allAcceptedPints[$key]->name = $currentInformation['name'];
                                $foundResults = true;
                            }
                        }
                    }

                    if (!$foundResults) {
                        unset($allAcceptedPints[$key]);
                    }
                }

                return $allAcceptedPints;
            }
        }

        return false;
    }

    public function getHospitalsBloodBankHasDoneWithBusinessWith($bloodBankID, $return_type = 'count')
    {
        $builder = $this->db->table('blood_request_delivery_tbl');
        $builder = $builder->select('requests_tbl.auth_id, requests_tbl.auth_type');
        $builder = $builder->join('requests_tbl', 'requests_tbl.id = blood_request_delivery_tbl.request_id');
        $builder  = $builder->where('blood_request_delivery_tbl.donor_id', $bloodBankID);
        $builder  = $builder->where('blood_request_delivery_tbl.donor_type', 'blood-bank');
        $builder  = $builder->where('requests_tbl.auth_type', 'hospital');
        $builder  = $builder->where('requests_tbl.deleted_at', NULL);
        $builder  = $builder->distinct();

        if ($return_type == 'count') {
            $result = $builder->countAllResults();
        }
        else {
            $query = $builder->get();
            $result = $query->getResult();
        }

        return $result;
    }
}
