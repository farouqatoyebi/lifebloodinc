<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserModel;
use App\Controllers\BaseController; 

class BloodDonationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'requests_tbl';
    protected $table_2          = 'request_donors_tbl';
    protected $table_3          = 'requests_blood_group_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['auth_id', 'blood_group', 'no_of_pints', 'amount', 'txn_ref', 'urgency_id', 'diagnosis', 'comments', 'hospital_name', 'address', 'state_id', 'country_id', 'longitude', 'latitude', 'qr_code', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'auth_id' => 'required|integer',
        'blood_group' => 'required|alpha_numeric_punct',
        'no_of_pints' => 'required|integer',
        'diagnosis' => 'required|alpha_numeric_punct',
        'comments' => 'required|alpha_numeric_punct',
        'urgency_id' => 'required|integer',
        'hospital_name' => 'alpha_numeric_punct',
        'address' => 'alpha_numeric_punct',
        'state_id' => 'required|integer',
        'country_id' => 'required|integer',
        'longitude' => 'required|string',
        'latitude' => 'required|string',
        'status' => 'in_list[pending,processing,complete,cancelled]',
        // 'qr_code' => 'alpha_numeric_punct',
    ];
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

    protected $radius = 500;
    protected $unit = 'mi';    // mi or km
    protected $const_mi = 69.1;
    protected $const_km = 111.111;



     public function findNearbyDonors($hospital_lat, $hospital_lon)
    {
        /*
        
        * The constant 69.1 is to calc. distance in miles (mi)
        * The constant 111.111 is to calc. distance in kilometers (km)
    
        */   
        $radius = $this->radius;

        switch ($this->unit) {
            case 'mi':   
                $const = $this->const_mi;
                break;
            case 'km':
                $const = $this->const_km;
                break;
            
            default:
                die("Unit of measurement can either be mi or km");
                break;
        }


        $sql = "SELECT a.email, a.phone, u.blood_group, u.firstname, u.lastname, u.auth_id, u.longitude, u.latitude, u.address, $const * DEGREES( ACOS( LEAST( 1.0, COS(RADIANS($hospital_lat)) * COS(RADIANS(latitude)) * COS(RADIANS($hospital_lon - longitude)) + SIN(RADIANS($hospital_lat)) * SIN(RADIANS(latitude)) ) ) ) AS radius FROM users_tbl u INNER JOIN auth_tbl a ON u.auth_id = a.id WHERE a.acct_status = 'active' AND u.donor = 'yes' AND u.longitude IS NOT NULL AND u.latitude IS NOT NULL HAVING radius < $radius ORDER BY radius ASC";

        $query = $this->db->query($sql);
        $users = $query->getResult();

        $data= (object) ['radius_range' => "{$radius}{$this->unit}", 'users' => $users];

        return $data;
    }



    public function fetchNearbyRequests($user_lat, $user_lon)
    {
        $radius = $this->radius;

        switch ($this->unit) {
            case 'mi':   
                $const = $this->const_mi;
                break;
            case 'km':
                $const = $this->const_km;
                break;
            
            default:
                die("Unit of measurement can either be mi or km");
                break;
        }



        $sql = "SELECT b.id, b.blood_group, b.no_of_pints, b.diagnosis, b.comments, b.hospital_name, b.address, b.latitude, b.longitude, ur.name AS urgency, a.email, a.phone, u.firstname, u.lastname, $const * DEGREES( ACOS( LEAST( 1.0, COS(RADIANS(b.latitude)) * COS(RADIANS($user_lat)) * COS(RADIANS(b.longitude - $user_lon)) + SIN(RADIANS(b.latitude)) * SIN(RADIANS($user_lat))))) AS radius, '$this->unit' AS unit FROM requests_tbl b INNER JOIN users_tbl u ON b.auth_id = u.auth_id INNER JOIN auth_tbl a ON b.auth_id = a.id INNER JOIN urgency_tbl ur ON b.urgency_id = ur.id WHERE b.status = 'pending' HAVING radius < $radius ORDER BY radius ASC";

            $query = $this->db->query($sql);
            $requests = $query->getResult();

            return $requests;
    }



    public function singleRequest($id)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->select('pj_order_details.*, pj_products.name, pj_products.description, pj_product_image.image');
        $builder = $builder->join('pj_products','pj_order_details.product_id = pj_products.id', 'left');
        $builder = $builder->join('pj_product_image','pj_products.id = pj_product_image.product_id', 'left');

        $builder = $builder->where('pj_product_image.main',1);
        $builder = $builder->where('pj_order_details.order_id',$id);

        $builder = $builder->get();
        $result = $builder->getResultArray();
        return $result;
    }


    public function fetchDonors($request_id)
    {
        $builder = $this->db->table($this->table_2);
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->where('confirmed', 1);
        $builder = $builder->get();
        $donors = $builder->getResult();
        return $donors;
    }

    public function saveRequestToDB($data)
    {
        $builder = $this->db->table($this->table);
        $builder->insert($data);
        $lastInsertId = $this->db->insertID();
        return $lastInsertId;
    }

    public function saveBloodGroupToBloodRequest($request_id, $arrayOfBloodGroupToPint)
    {
        if (!$request_id) {
            return false;
        }

        if (!is_array($arrayOfBloodGroupToPint)) {
            return false;
        }

        if (!count($arrayOfBloodGroupToPint)) {
            return false;
        }
        $builder = $this->db->table($this->table_3);

        foreach ($arrayOfBloodGroupToPint as $key => $value) {
            if ($value > 0) {
                $builder->insert([
                    "request_id" => $request_id,
                    "blood_group" => $key,
                    "no_of_pints" => $value,
                    "created_at" => time(),
                ]);
            }
        }

        return true;
    }

    public function fetchAllDonationRequestsForWeb()
    {
        $account_auth_id = session('auth_id');
        $account_type = session("acct_type");
        $baseController = new BaseController();
        $userModel = new UserModel();

        $accountPersonalInformation =  $baseController->getUserProfileInformationBasedOnType($account_type, $account_auth_id);
        
        $sql = "SELECT rtb.*
                FROM requests_tbl rtb
                WHERE rtb.country_id = $accountPersonalInformation->country
                    AND rtb.status = 'pending' 
                    AND rtb.deleted_at IS NULL
                    AND NOT EXISTS (
                        SELECT id
                        FROM request_donors_tbl rdtb
                        WHERE rdtb.request_id = rtb.id 
                            AND rdtb.donor_id = $accountPersonalInformation->id
                            AND rdtb.donor_type = 'blood-bank'
                            AND rdtb.deleted_at IS NULL
                    )
                    AND due_date > ".time()."
                ORDER BY rtb.created_at DESC";

        $query = $this->db->query($sql);
        $allResults = $query->getResult();

        foreach ($allResults as $key => $value) {
            if ($value->auth_type == 'user') {
                $allResults[$key]->requests = [(object) [
                    "request_id" => $value->id,
                    "blood_group" => $value->blood_group,
                    "no_of_pints" => $value->no_of_pints
                ]];

                $allResults[$key]->user = $userModel->find($value->auth_id);
            }
            else {
                $allResults[$key]->requests = $baseController->getEveryBreakdownUnderBloodRequest($value->id);
            }

            // echo '<pre>'; var_dump($allResults[$key]); echo '</pre>'; 
        

            if (!count($allResults[$key]->requests)) {
                unset($allResults[$key]);
            }
        }
        // exit();

        return $allResults;
    }

    public function didHospitalMakeBloodRequest($request_id, $hospital_id)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('id', $request_id);
        $builder = $builder->where('auth_id', $hospital_id);
        $builder = $builder->where('auth_type', 'hospital');
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->get();

        return $builder->getRow();
    }

    public function getOffersForBloodRequest($request_id)
    {
        $builder = $this->db->table($this->table_2);
        $builder = $builder->select('request_id, donor_id, donor_type');
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->where('status', 'pending');
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->orderBy('created_at', 'DESC');
        $builder = $builder->groupBy('donor_id');
        $builder = $builder->get();
        $allResults = $builder->getResult();

        $basecontroller = new BaseController();
        $arrayOfResults = [];

        // echo '<pre>'; var_dump($allResults); echo '</pre>'; exit();

        if ($allResults) {
            foreach ($allResults as $key => $value) {
                $arrayKey = $value->donor_type.'__'.$value->donor_id;

                $arrayOfResults[$arrayKey] = $value;
                
                $arrayOfResults[$arrayKey]->donor = $basecontroller->getAccountInformationBasedOnID($value->donor_type, $value->donor_id);

                if (!($arrayOfResults[$arrayKey]->donor)) {
                    unset($arrayOfResults[$arrayKey]);
                }
                else {
                    $builder = $this->db->table($this->table_2);
                    $builder = $builder->where('request_id', $request_id);
                    $builder = $builder->where('donor_id', $arrayOfResults[$arrayKey]->donor->id);
                    $builder = $builder->where('donor_type', $value->donor_type);
                    $builder = $builder->where('status', 'pending');
                    $builder = $builder->orderBy('created_at', 'DESC');
                    $builder = $builder->get();
                    $offers = $builder->getResult();

                    if ($offers) {
                        $arrayOfResults[$arrayKey]->donor->offers = $offers;
                    }
                    else {
                        unset($arrayOfResults[$arrayKey]);
                    }
                }
            }
        }

        // echo '<pre>'; var_dump($arrayOfResults); echo '</pre>'; exit();

        return $arrayOfResults;
    }

    public function submitBloodDonationOffers($bloodRequestID, $donorID, $arrayOfValues)
    {
        if (!is_array($arrayOfValues)) {
            return false;
        }
        
        if (!count($arrayOfValues)) {
            return false;
        }

        $basecontroller = new BaseController();
        $allBloodGroups = $basecontroller->getAllBloodGroups();

        foreach ($allBloodGroups as $key => $value) { 
            $arrayOfAllBloodGroups[] = $value->name;
        }
        
        foreach ($arrayOfValues as $key => $value) {
            if (in_array($key, $arrayOfAllBloodGroups)) {
                $bloodRate = $basecontroller->getBloodBankRateForBloodGroup($donorID, $key);

                $builder = $this->db->table($this->table_2);
                $builder = $builder->where('request_id ', $bloodRequestID);
                $builder = $builder->where('donor_id', $donorID);
                $builder = $builder->where('donor_type', 'blood-bank');
                $builder = $builder->where('blood_group', $key);
                $builder = $builder->where('confirmed', '0');
                $builder = $builder->where('status', 'pending');
                $builder = $builder->get();
                $thisResult = $builder->getRow();

                if ($thisResult) {
                    $builder = $this->db->table($this->table_2);
                    $builder = $builder->where('request_id ', $bloodRequestID);
                    $builder = $builder->where('id ', $thisResult->id);
                    $builder->update([
                        "blood_group" => $key,
                        "no_of_pints" => $value,
                        "amount_per_pint" => $bloodRate,
                        "deleted_at" => NULL,
                        "updated_at" => time(),
                    ]);
                }
                else {
                    $builder = $this->db->table($this->table_2);
                    $builder->insert([
                        "request_id" => $bloodRequestID,
                        "donor_id" => $donorID,
                        "donor_type" => 'blood-bank',
                        "blood_group" => $key,
                        "no_of_pints" => $value,
                        "amount_per_pint" => $bloodRate,
                        "created_at" => time(),
                    ]);
                }
            }
        }

        return true;
    }

    public function getOffersForPassedRequest($request_id, $donor_id, $donor_type = 'blood-bank')
    {
        $builder = $this->db->table($this->table_2);
        $builder = $builder->where('request_id ', $request_id);
        $builder = $builder->where('donor_id', $donor_id);
        $builder = $builder->where('donor_type', $donor_type);
        $builder = $builder->get();
        $thisResult = $builder->getResult();

        return $thisResult;
    }

    public function getAllAcceptedOffersForRequest($request_id)
    {
        $builder = $this->db->table($this->table_2);
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->whereIn('status', ['confirmed', 'complete']);
        $builder = $builder->orderBy('created_at', 'DESC');
        $builder = $builder->get();
        $allResults = $builder->getResult();

        return $allResults;
    }

    public function deleteRequest($request_id)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('id', $request_id);
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->update(['deleted_at' => time()]);

        return true;
    }
}