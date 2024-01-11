<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class RequestModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'requests_tbl';
    protected $table_2          = 'request_donors_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['auth_id', 'blood_group', 'no_of_pints', 'amount', 'paid_amount', 'discount', 'txn_ref', 'urgency', 'diagnosis', 'comments', 'hospital_name', 'address', 'state_id', 'country_id', 'longitude', 'latitude', 'qrc_id', 'qrc_file', 'due_date', 'status', 'old_request_id', 'auth_type'];

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
        'urgency' => 'required',
        'hospital_name' => 'alpha_numeric_punct',
        'address' => 'required',
        'state_id' => 'required|integer',
        'country_id' => 'required|integer',
        'longitude' => 'required|string',
        'latitude' => 'required|string',
        'status' => 'in_list[pending,paid,complete,cancelled]',
        // 'qr_code' => 'alpha_numeric_punct',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = false;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = ['_details'];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected $radius = 500;
    protected $unit = 'mi';    // mi or km
    protected $const_mi = 69.1;
    protected $const_km = 111.111;


    public function _details()
    {
        $this->select('auth_tbl.phone, auth_tbl.email, users_tbl.firstname, users_tbl.lastname, users_tbl.logo, requests_tbl.blood_group, requests_tbl.no_of_pints, requests_tbl.hospital_name, requests_tbl.diagnosis, requests_tbl.comments, requests_tbl.address, requests_tbl.longitude, requests_tbl.latitude')
            ->join('users_tbl', "users_tbl.auth_id = requests_tbl.auth_id")
            ->join('auth_tbl', "auth_tbl.id = requests_tbl.auth_id");
        return;
    }

    /**
     * This returns the details of the request
     * @param id is the request data id to be fetched
     * @param all is used to determine if complete data about a request should be fetched or not
    */
    public function details(int $id, bool $all = false)
    {
        $join = '';

        if ($all){
            $join = ', requests_tbl.qrc_file as qrcode';
        }

        $data['request'] = $this->select("auth_tbl.phone, auth_tbl.email, users_tbl.firstname, users_tbl.lastname, users_tbl.logo, requests_tbl.blood_group, requests_tbl.no_of_pints, requests_tbl.hospital_name, requests_tbl.diagnosis, requests_tbl.comments, requests_tbl.due_date, requests_tbl.created_at, requests_tbl.address, states_tbl.name as state, countries_tbl.name as country, requests_tbl.longitude, requests_tbl.latitude $join")
            ->join('states_tbl', 'states_tbl.id = requests_tbl.state_id')
            ->join('countries_tbl', 'countries_tbl.id = requests_tbl.country_id')
            ->join('users_tbl', "users_tbl.auth_id = requests_tbl.auth_id")
            ->join('auth_tbl', "auth_tbl.id = requests_tbl.auth_id")
            ->find($id);

        // Join List of Donors if All is requested
        if ($all) {
            
            $userModel = new UserModel();

            $donors = $userModel
                ->join('request_donors_tbl', 'users_tbl.auth_id = request_donors_tbl.donor_id')
                ->where('request_donors_tbl.confirmed', 1)
                ->where('request_donors_tbl.request_id', $id)
                ->find();

            $data['donors'] = $donors;
        }
       
        return $data;
    }

    public function betalife_rate($data=false)
    {

        $builder = $this->db->table('betalife_rate_tbl');
        $query = $builder->get();
        $result = $query->getLastRow();

        if ($data) {
            return $result;
        }
        else {
            return $result->amount;
        }
    }

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

        $current_date = time();

        $sql = "SELECT r.id, r.blood_group, r.no_of_pints, r.diagnosis, r.comments, r.hospital_name, r.address, r.latitude, r.longitude, r.urgency, r.created_at, r.due_date, a.email, a.phone, u.firstname, u.lastname, u.logo, $const * DEGREES( ACOS( LEAST( 1.0, COS(RADIANS(r.latitude)) * COS(RADIANS($user_lat)) * COS(RADIANS(r.longitude - $user_lon)) + SIN(RADIANS(r.latitude)) * SIN(RADIANS($user_lat))))) AS radius, '$this->unit' AS unit FROM requests_tbl r INNER JOIN users_tbl u ON r.auth_id = u.auth_id INNER JOIN auth_tbl a ON r.auth_id = a.id WHERE r.status = 'pending' AND r.due_date > {$current_date} HAVING radius < $radius ORDER BY id DESC";

            $query = $this->db->query($sql);
            $requests = $query->getResult();

            return $requests;
    }

    public function get_request_detail($id)
    {

        $result = $this->allowCallbacks()->find($id);

        // $builder = $this->db->table($this->table);
        // $builder = $builder->select('pj_order_details.*, pj_products.name, pj_products.description, pj_product_image.image');
        // $builder = $builder->join('pj_products','pj_order_details.product_id = pj_products.id', 'left');
        // $builder = $builder->join('pj_product_image','pj_products.id = pj_product_image.product_id', 'left');

        // $builder = $builder->where('pj_product_image.main',1);
        // $builder = $builder->where('pj_order_details.order_id',$id);

        // $builder = $builder->get();
        // $result = $builder->getResultArray();
        // return $result;
    }

    public function donorsList($request_id, $donor_id = null)
    {
        $builder = $this->db->table($this->table_2);
        $builder = $builder->select('request_id', $request_id);
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->where('confirmed', 1);

        if (!is_null($donor_id)) {
            $builder = $builder->where('donor_id', $donor_id);
        }

        $builder = $builder->get();
        $donors = $builder->getResult();

        if (empty($donors)) {
           return false;
        }

        return $donors;
    }

    public function fetchAvailableDonors($request_id, $confirmed = 0)
    {
        $table_2 = $this->table_2;

        $builder = $this->db->table('users_tbl');
        $builder = $builder->select('users_tbl.auth_id, users_tbl.firstname, users_tbl.lastname, users_tbl.logo, users_tbl.gender, users_tbl.blood_group, users_tbl.latitude, users_tbl.longitude, users_tbl.address');
        $builder = $builder->join($table_2, "users_tbl.auth_id = $table_2.donor_id");
        $builder->where("$table_2.request_id", $request_id);
        $builder->where("$table_2.donor_type", "user");
        $query = $builder->get();
        // $query = $builder->getCompiledSelect();
        $result = $query->getResult();
        
        if (empty($result)) {
            return null;
        }else{
            return $result;
        }
    }

    public function fetchAvailableBloodBanks($request_id, $confirmed = 0)
    {
        $table_2 = $this->table_2;
        $builder = $this->db->table('blood_banks_tbl');

        $builder = $builder->select("blood_banks_tbl.auth_id, blood_banks_tbl.name, $table_2.blood_group, $table_2.no_of_pints, cities_tbl.name as city, states_tbl.name as state, blood_banks_tbl.address, blood_banks_tbl.logo");
        $builder = $builder->join('cities_tbl', "blood_banks_tbl.city = cities_tbl.id");
        $builder = $builder->join('states_tbl', "blood_banks_tbl.state = states_tbl.id");
        $builder = $builder->join($table_2, "blood_banks_tbl.auth_id = $table_2.donor_id");

        $builder->where("$table_2.request_id", $request_id);
        $builder->where("$table_2.donor_type", "blood-bank");

        $builder = $builder->get();
        $result = $builder->getResult();
        
        if (empty($result)) {
            return null;
        }else{
            return $result;
        }
    }

    public function updateDonorStatus(int $request_id, array $donors){

        $builder = $this->db->table($this->table_2);
        $builder->whereIn('donor_id', $donors);
        $builder->where('request_id', $request_id);
        $builder->set('confirmed', 1);
        $builder->update();
        
        return $this->db->affectedRows();
    }

    public function saveDonorReward(array $data){

        $builder = $this->db->table('donors_reward_history_tbl');
        $builder->insert($data);
        return $this->db->insertID();
    }

    public function checkDonorReward(array $data){

        $builder = $this->db->table('donors_reward_history_tbl');
        $builder = $builder->where($data);
        $builder = $builder->get();
        $result = $builder->getRow();
        return $result;
    }

    public function getReportBreakdownForUser()
    {
        $arrayOfValues = [
            "requests" => [
                "January" => 0,
                "February" => 0,
                "March" => 0,
                "April" => 0,
                "May" => 0,
                "June" => 0,
                "July" => 0,
                "August" => 0,
                "September" => 0,
                "October" => 0,
                "November" => 0,
                "December" => 0,
            ],
            "offers" => [
                "January" => 0,
                "February" => 0,
                "March" => 0,
                "April" => 0,
                "May" => 0,
                "June" => 0,
                "July" => 0,
                "August" => 0,
                "September" => 0,
                "October" => 0,
                "November" => 0,
                "December" => 0,
            ]
        ];
        $basecontroller = new BaseController();
        $accountAuthID = session("auth_id");
        $accountType = session("acct_type");
        $user_info = $basecontroller->getUserProfileInformationBasedOnType($accountType, $accountAuthID);
        $startOfTheYear = strtotime(date("Y-01-01"));
        $endOfTheYear = strtotime(date("Y-12-31"));
        $allOffersResults = $allRequestsResults = [];

        if ($accountType == 'hospital') {
            $sql = "SELECT created_at
                    FROM requests_tbl rtb
                    WHERE created_at BETWEEN '".$startOfTheYear."' AND '".$endOfTheYear."'
                        AND auth_id = '".$user_info->id."'
                        AND auth_type = '".$accountType."'
                        AND deleted_at IS NULL";
            $query = $this->db->query($sql);
            $allRequestsResults = $query->getResult();
        }
        elseif ($accountType == 'blood-bank') {
            $sql = "SELECT rtb.*
                    FROM requests_tbl rtb
                    WHERE rtb.country_id = $user_info->country 
                        AND rtb.deleted_at IS NULL
                        AND created_at BETWEEN '".$startOfTheYear."' AND '".$endOfTheYear."'
                    ORDER BY rtb.created_at DESC";
            $query = $this->db->query($sql);
            $allRequestsResults = $query->getResult();
        }

        if (count($allRequestsResults)) {
            foreach ($allRequestsResults as $value) {
                $valueMonth = date("F", $value->created_at);

                $arrayOfValues['requests'][$valueMonth] += 1;
            }
        }

        if ($accountType == 'hospital') {
            $sql = "SELECT DISTINCT rdtb.donor_id, rdtb.donor_type, rdtb.created_at
                    FROM request_donors_tbl rdtb 
                    JOIN requests_tbl rtb ON rtb.id = rdtb.request_id 
                    WHERE rdtb.created_at BETWEEN '".$startOfTheYear."' AND '".$endOfTheYear."'
                        AND rtb.auth_id = '".$user_info->id."'
                        AND rtb.auth_type = '".$accountType."'
                        AND rdtb.deleted_at IS NULL 
                        AND rtb.deleted_at IS NULL";
            $query = $this->db->query($sql);
            $allOffersResults = $query->getResult();
        }
        elseif ($accountType == 'blood-bank') {
            $sql = "SELECT DISTINCT rdtb.donor_id, rdtb.donor_type, rdtb.created_at
                    FROM request_donors_tbl rdtb
                    JOIN requests_tbl rtb ON rtb.id = rdtb.request_id 
                    WHERE rdtb.request_id = rtb.id 
                        AND rdtb.created_at BETWEEN '".$startOfTheYear."' AND '".$endOfTheYear."'
                        AND rdtb.donor_id = $user_info->id
                        AND rdtb.donor_type = 'blood-bank'
                        AND rdtb.deleted_at IS NULL
                        AND rtb.deleted_at IS NULL";
            $query = $this->db->query($sql);
            $allOffersResults = $query->getResult();
        }

        if (count($allOffersResults)) {
            foreach ($allOffersResults as $value) {
                $valueMonth = date("F", $value->created_at);

                $arrayOfValues['offers'][$valueMonth] += 1;
            }
        }

        return $arrayOfValues;
    }
}