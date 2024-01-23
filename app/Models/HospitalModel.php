<?php

namespace App\Models;

use App\Controllers\BaseController;
use App\Controllers\SendOutgoingEmailController;
use App\Models\BloodDonationModel;
use CodeIgniter\Model;

class HospitalModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'hospitals_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'auth_id', 'logo', 'reg_no'];
    protected $message    = "";

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    
    public function findAccount($accountAuthID)
    {
        $builder = $this->db->table($this->table);
        $builder->where('auth_id ', $accountAuthID);
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

    public function confirmURLSlugInformation($hospital_acct_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id ', $hospital_acct_id);
        $query = $builder->get();
        $result = $query->getRow();
        $baseController = new BaseController();
        $hospitalModel = new HospitalModel();

        if ($result) {
            if (!$result->url_slug) {
                $generatedURLSlug = $baseController->createSlugFromName($result->name);
                $arrayUpdates = array(
                    "url_slug" => $generatedURLSlug
                );
                
                $builder = $this->db->table($this->table);
                $builder->where('id ', $hospital_acct_id);
                $builder->update($arrayUpdates);

                session()->set(['slug' => $generatedURLSlug]);
            }
        }

        return true;
    }

    public function findAccountWithSlug($url_slug)
    {
        $builder = $this->db->table($this->table);
        $builder->where('url_slug ', $url_slug);
        $query = $builder->get();
        return $query->getRow();
    }

    public function getVisitorsDetails($visitors_phone)
    {
        $builder = $this->db->table('visitors_tbl');
        $builder->where('phone', $visitors_phone);
        $query = $builder->get();
        $visitorsResult = $query->getRow();

        return $visitorsResult;
    }

    public function submitUserVisitingTime($data)
    {
        $visitorsResult = $this->getVisitorsDetails($data['phone']);
        $hospital_id = $data['hospital_id'];

        // It is not needed the insert so it should be removed
        unset($data['hospital_id']);

        if (!$visitorsResult) {
            $data['created_at'] = time();
            $builder = $this->db->table('visitors_tbl');
            $builder->insert($data);
            $vistors_id = $this->db->insertID();
        }
        else {
            $vistors_id = $visitorsResult->id;

            $builder = $this->db->table('visiting_date_tbl');
            $builder->where('visitors_id', $visitorsResult->id);
            $builder->where('hospital_id', $hospital_id);
            $builder->orderBy('visited_on', 'DESC');
            $query = $builder->get();
            $visitorsDateResult = $query->getRow();

            $data['updated_at'] = time();
            $builder = $this->db->table('visitors_tbl');
            $builder->where('id', $visitorsResult->id);
            $builder->update($data);

            if ($visitorsDateResult) {
                if ($visitorsDateResult) {
                    if (date("Y-m-d") == date("Y-m-d", $visitorsDateResult->visited_on)) {
                        $this->message = "A record has already been saved for you today.";
                        return false;
                    }
                }
            }
        }

        $visitingDateInfoArray = [
            "visitors_id" => $vistors_id,
            "hospital_id" => $hospital_id,
            "visited_on" => time(),
        ];
        
        $builder = $this->db->table('visiting_date_tbl');
        $builder->insert($visitingDateInfoArray);
        $this->message = "Your visit for the current day has been successfully recorded.";
        return true;
    }

    public function getAllVisitors($hospital_id, $startDateToRender = '', $endDateToRender = '')
    {
        $builder = $this->db->table('visiting_date_tbl');
        $builder->join('visitors_tbl', 'visitors_tbl.id = visiting_date_tbl.visitors_id');
        $builder->where('hospital_id', $hospital_id);

        if ($startDateToRender && $endDateToRender) {
            $builder->where('visiting_date_tbl.visited_on BETWEEN '.$startDateToRender.' AND '.$endDateToRender);
        }

        $builder->orderBy('visited_on', 'DESC');
        $builder->groupBy('visitors_tbl.id');
        $query = $builder->get();
        $allVisitorsReport = $query->getResult();

        return $allVisitorsReport;
    }

    public function getLastFiveVisitors($hospital_id)
    {
        $builder = $this->db->table('visiting_date_tbl');
        $builder->join('visitors_tbl', 'visitors_tbl.id = visiting_date_tbl.visitors_id');
        $builder->where('hospital_id', $hospital_id);

        $builder->orderBy('visited_on', 'DESC');
        $builder->groupBy('visitors_tbl.id');
        $builder->limit(5);
        $query = $builder->get();
        $allVisitorsReport = $query->getResult();

        return $allVisitorsReport;
    }

    public function fetchAllHospitalPendingRequests($hospitalID)
    {
        $builder = $this->db->table('requests_tbl');
        $builder->where('auth_id', $hospitalID);
        $builder->where('auth_type', 'hospital');
        $builder->where('status', 'pending');
        $builder->where('deleted_at', NULL);
        $builder->orderBy('due_date', 'DESC');
        $builder->orderBy('created_at', 'DESC');
        $query = $builder->get();
        $allPendingRequestsSentOut = $query->getResult();

        return $allPendingRequestsSentOut;
    }

    public function fetchRequestInformation($requestID)
    {
        $builder = $this->db->table('requests_tbl');
        $builder->where('id', $requestID);
        $builder->where('deleted_at', NULL);
        $query = $builder->get();
        $thisRequest = $query->getRow();

        return $thisRequest;
    }

    public function getTotalNumberOfRequestsMade($hospitalID)
    {
        $builder = $this->db->table('requests_tbl');
        $builder->where('auth_id', $hospitalID);
        $builder->where('auth_type', 'hospital');
        $builder->where('deleted_at', NULL);
        $builder->where('status', 'pending');
        $allPendingRequestsSentOut = $builder->countAllResults();

        return $allPendingRequestsSentOut;
    }

    public function updateMedicalCheckForVisitor($visitor_tbl_id, $data)
    {
        if (!$visitor_tbl_id) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        if (!count($data)) {
            return false;
        }

        $builder = $this->db->table('visiting_date_tbl');
        $builder->where('id', $visitor_tbl_id);
        $builder->update($data);

        return true;
    }

    public function fetchOfferBreakDownForHospital($requestID, $donorID, $donorType)
    {
        $baseController = new BaseController();
        $bloodaDonationModel = new BloodDonationModel();
        $arrayOfBreakdown = [];

        $requestsBreakdown = $baseController->getEveryBreakdownUnderBloodRequest($requestID);
        $offerBreakdown = $bloodaDonationModel->getOffersForPassedRequest($requestID, $donorID, $donorType);
        $acceptedOffersBreakdown = $bloodaDonationModel->getAllAcceptedOffersForRequest($requestID);

        if (!empty($requestsBreakdown) && !empty($offerBreakdown)) {
            foreach ($requestsBreakdown as $key => $value) {
                $arrayOfBreakdown[$value->blood_group]['no_of_pints_req'] = $value->no_of_pints;
                $arrayOfBreakdown[$value->blood_group]['no_of_pints_left'] = $value->no_of_pints;
            }

            if (count($offerBreakdown)) {
                foreach ($offerBreakdown as $key => $value) {
                    $arrayOfBreakdown[$value->blood_group]['no_of_pints_offered'] = $value->no_of_pints;
                    $arrayOfBreakdown[$value->blood_group]['amount_per_pint'] = $value->amount_per_pint;
                    $arrayOfBreakdown[$value->blood_group]['amount_per_pint_formatted'] = number_format($value->amount_per_pint);
                    $arrayOfBreakdown[$value->blood_group]['currency'] = 'NGN';
                }
            }

            if (count($acceptedOffersBreakdown)) {
                foreach ($acceptedOffersBreakdown as $key => $value) {
                    $arrayOfBreakdown[$value->blood_group]['no_of_pints_accepted'] = $value->no_of_pints;
                    $arrayOfBreakdown[$value->blood_group]['amount_per_pint_accepted'] = $value->amount_per_pint;
                    $arrayOfBreakdown[$value->blood_group]['no_of_pints_left'] = $arrayOfBreakdown[$value->blood_group]['no_of_pints_req'] - $value->no_of_pints_confirmed;
                }
            }
        }
        
        return $arrayOfBreakdown;
    }

    public function recordOfferAcceptedByHospital($requestID, $donorID, $donorType, $bloodGroup, $data)
    {
        $builder = $this->db->table('request_donors_tbl');
        $builder = $builder->where('request_id ', $requestID);
        $builder = $builder->where('donor_id', $donorID);
        $builder = $builder->where('donor_type', $donorType);
        $builder = $builder->where('blood_group', $bloodGroup);
        $builder->update($data);

        return true;
    }

    public function getRequestLeftAfterAccepted($requestID)
    {
        $baseController = new BaseController();
        $bloodaDonationModel = new BloodDonationModel();

        $requestsBreakdown = $baseController->getEveryBreakdownUnderBloodRequest($requestID);
        $acceptedOffersBreakdown = $bloodaDonationModel->getAllAcceptedOffersForRequest($requestID);

        $arrayOfBreakdown = [];

        foreach ($requestsBreakdown as $key => $value) {
            $arrayOfBreakdown[$value->blood_group]['no_of_pints_left'] = $value->no_of_pints;
        }

        // echo '<pre>'; var_dump($acceptedOffersBreakdown); exit();

        if (count($acceptedOffersBreakdown)) {
            foreach ($acceptedOffersBreakdown as $key => $value) {
                $arrayOfBreakdown[$value->blood_group]['no_of_pints_left'] = $arrayOfBreakdown[$value->blood_group]['no_of_pints_left'] - $value->no_of_pints_confirmed;

                if (!$arrayOfBreakdown[$value->blood_group]['no_of_pints_left']) {
                    unset($arrayOfBreakdown[$value->blood_group]);
                }
            }
        }
        
        return $arrayOfBreakdown;
    }

    public function createNewRequestFromThisRequest($oldRequestID, $data, $due_date) 
    {
        $bloodRequestModel = new BloodDonationModel();
        if (!is_array($data)) {
            return false;
        }

        if (!count($data)) {
            return false;
        }

        if (!$oldRequestID) {
            return false;
        }

        if (!$due_date) {
            return false;
        }

        $builder = $this->db->table('requests_tbl');
        $builder->where('id', $oldRequestID);
        $builder->where('status', 'pending');
        $builder->where('deleted_at', NULL);
        $result = $builder->get();
        $result = $result->getRow();

        if ($result) {
            $builder = $this->db->table('requests_tbl');
            $builder->insert(
                [
                    'auth_id' => $result->auth_id,
                    'auth_type' => $result->auth_type,
                    'urgency' => $result->urgency,
                    'diagnosis' => $result->diagnosis,
                    'comments' => $result->comments,
                    'due_date' => strtotime($due_date),
                    'hospital_name' => $result->hospital_name,
                    'address' => $result->address,
                    'state_id' => $result->state_id,
                    'city_id' => $result->city_id,
                    'country_id' => $result->country_id,
                    'longitude' => $result->longitude,
                    'latitude' => $result->latitude,
                    'qrc_id' => $result->qrc_id,
                    'qrc_file' => $result->qrc_file,
                    'created_at' => time(),
                    'status' => 'pending',
                    'old_request_id' => $oldRequestID,
                ]
            );

            $requestID = $this->db->insertID();

            if ($requestID) {
                $bloodRequestModel->saveBloodGroupToBloodRequest($requestID, $data);

                return true;
            }
        }

        return false;
    }

    public function recordTransactionPayment($requestID, $amount, $tx_ref, $status = 'pending')
    {
        $builder = $this->db->table('transaction_history_tbl');
        $builder->where('request_id', $requestID);
        $result = $builder->get();
        $result = $result->getRow();

        if ($result) {
            if ($result->status == 'pending') {
                $builder = $this->db->table('transaction_history_tbl');
                $builder->where('request_id', $requestID);
                $builder->update([
                    'currency' => 'NGN',
                    'amount' => $amount,
                    'transaction_ref' => $tx_ref,
                    'status' => $status,
                    'updated_at' => time(),
                ]);
            }
        }
        else {
            $builder = $this->db->table('transaction_history_tbl');
            $builder->insert([
                'request_id' => $requestID,
                'currency' => 'NGN',
                'amount' => $amount,
                'transaction_ref' => $tx_ref,
                'status' => $status,
                'created_at' => time(),
            ]);
        }

        $builder = $this->db->table('requests_tbl');
        $builder->where('id', $requestID);
        $builder->where('deleted_at', NULL);
        $builder->update([
            'paid_amount' => $amount,
            'txn_ref' => $tx_ref,
            'status' => ($status == 'success') ? 'paid' : 'pending',
            'updated_at' => time(),
        ]);

        return true;
    }

    public function fetchAllHospitalNonPendingRequests()
    {
        $user_id = session()->get('auth_id');
        $user_type = session()->get('acct_type');

        $baseController = new BaseController();
        $userProfileInformation = $baseController->getUserProfileInformationBasedOnType($user_type, $user_id);

        $builder = $this->db->table('requests_tbl');
        $builder->where('auth_id', $userProfileInformation->id);
        $builder->where('auth_type', 'hospital');
        $builder->whereIn('status', ['paid', 'complete', 'cancelled']);
        $builder->where('deleted_at', NULL);
        $builder->orderBy('due_date', 'DESC');
        $builder->orderBy('created_at', 'DESC');
        $query = $builder->get();
        $allNonPendingRequests = $query->getResult();

        return $allNonPendingRequests;
    }

    public function deliveryInformationBreakdown($requestID, $status = '')
    {
        if (!$requestID) {
            return false;
        }

        $user_id = session()->get('auth_id');
        $user_type = session()->get('acct_type');

        $baseController = new BaseController();
        $bloodaDonationModel = new BloodDonationModel();
        $userProfileInformation = $baseController->getUserProfileInformationBasedOnType($user_type, $user_id);

        $builder = $this->db->table('requests_tbl');
        $builder->where('auth_id', $userProfileInformation->id);
        $builder->where('auth_type', 'hospital');
        $builder->where('deleted_at', NULL);
        $builder->where('id', $requestID);
        $query = $builder->get();
        $requestInformation = $query->getRow();

        if ($requestInformation) {
            $builder = $this->db->table('blood_request_delivery_tbl');
            $builder->where('request_id', $requestID);

            if ($status) {
                $builder->where('status', $status);
            }

            $query = $builder->get();
            $allDeliveryInfo = $query->getResult();

            if ($allDeliveryInfo) {
                return $allDeliveryInfo;
            }
            else {
                $acceptedOffersBreakdown = $bloodaDonationModel->getAllAcceptedOffersForRequest($requestID);
                $arrayOfPaymentToRecord = [];

                if ($acceptedOffersBreakdown) {
                    foreach ($acceptedOffersBreakdown as $key => $value) {
                        $arrayKey = $value->donor_id.'_'.$value->donor_type;
                        $totalAmount = ($value->no_of_pints_confirmed * $value->amount_per_pint);
                        if (!array_key_exists($arrayKey, $arrayOfPaymentToRecord)) {
                            $arrayOfPaymentToRecord[$arrayKey] = [
                                'total_amount' => $totalAmount,
                                'donor_type' => $value->donor_type,
                                'donor_id' => $value->donor_id,
                            ];
                        }
                        else {
                            $arrayOfPaymentToRecord[$arrayKey]['total_amount'] += $totalAmount;
                        }
                    }
                }

                if (count($arrayOfPaymentToRecord)) {
                    $outgoingEmails = new SendOutgoingEmailController();

                    foreach ($arrayOfPaymentToRecord as $key => $value) {
                        $data = [
                            'request_id' => $requestID,
                            'donor_id' => $value['donor_id'],
                            'donor_type' => $value['donor_type'],
                            'total_amount' => $value['total_amount'],
                            'otp_code' => $baseController->generateOTP(4),
                            'status' => 'pending',
                            'created_at' => time(),
                        ];

                        $baseController->saveDeliveryInformationIntoDB($data);
                    }

                    $outgoingEmails->sendEmailForBloodDonationPaymentDone($requestID);

                    return $this->deliveryInformationBreakdown($requestID);
                }
            }
        }

        return false;
    }

    public function getBloodBanksHospitalHasDoneWithBusinessWith($hospitalID, $return_type = 'count')
    {
        $builder = $this->db->table('blood_request_delivery_tbl');
        $builder = $builder->select('blood_request_delivery_tbl.donor_id, blood_request_delivery_tbl.donor_type');
        $builder = $builder->join('requests_tbl', 'requests_tbl.id = blood_request_delivery_tbl.request_id');
        $builder  = $builder->where('requests_tbl.auth_id', $hospitalID);
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

    public function areThereNewOffersSinceLastChecked($requestID, $timeCheck)
    {
        $sql = "SELECT request_id
                FROM request_donors_tbl
                WHERE request_id = ".$requestID."
                    AND (
                        created_at > ".$timeCheck."
                        OR 
                        updated_at > ".$timeCheck."
                    )
                    AND status = 'pending'
                    AND deleted_at IS NULL
                ORDER BY created_at DESC";

        $query = $this->db->query($sql);
        $result = $query->getRow();

        // var_dump($sql, $timeCheck); exit();

        return $result;
    }
}
