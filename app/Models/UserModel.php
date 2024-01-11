<?php

namespace App\Models;

use CodeIgniter\Model;

// $model->allowCallbacks(false)->find(1);

class UserModel extends AuthModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'users_tbl';
    protected $primaryKey       = 'auth_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    // protected $returnType       = \App\Entities\User::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['auth_id','firstname', 'lastname', 'other_names', 'dob', 'gender', 'blood_group', 'genotype', 'occupation', 'address', 'state_id', 'country_id', 'donor', 'logo', 'longitude', 'latitude'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'firstname' => 'required|min_length[3]',
        'lastname' => 'required|min_length[3]',
        // 'other_names' => 'min_length[3]',
        // 'dob' => 'integer',
        // 'gender' => 'in_list[male,female]',
        // 'blood_group' => 'alpha_numeric_punct',
        // 'genotype' => 'alpha_numeric_punct',
        // 'occupation' => 'alpha_numeric_space',
        // 'city_id' => 'integer',
        // 'state_id' => 'integer',
        // 'country_id' => 'integer',
        // 'donor' => 'in_list[yes,no]',
        // 'logo' => 'alpha_numeric_punct',
    ];

    protected $validationMessages = [];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterFind      = [];
    protected $beforeFind     = ['__fullData'];
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];



    public function __fullData() {
        $this->select('users_tbl.firstname, users_tbl.lastname, users_tbl.other_names, users_tbl.dob, users_tbl.gender, users_tbl.blood_group, users_tbl.genotype, users_tbl.occupation, users_tbl.donor, users_tbl.logo, countries_tbl.name as country, states_tbl.name as state, users_tbl.address, users_tbl.latitude, users_tbl.longitude, auth_tbl.phone, auth_tbl.email')
        ->join('auth_tbl', "auth_tbl.id = users_tbl.auth_id")
        ->join('countries_tbl', "countries_tbl.id = users_tbl.country_id", "left")
        ->join('states_tbl', "states_tbl.id = users_tbl.state_id", "left");
        return;
    }

    public function findUser($user_id){

        $builder = $this->db->table('auth_tbl');
        $builder->where('email', $user_id);
        $builder->orWhere('phone', $user_id);
        $query = $builder->get();
        $result = $query->getRowArray();
        return $result;

    }



}
