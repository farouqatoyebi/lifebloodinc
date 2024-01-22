<?php

namespace App\Models;

use CodeIgniter\Model;

// $model->allowCallbacks(false)->find(1);

class AuthModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'auth_tbl';
    protected $table_2          = 'users_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    // protected $returnType       = \App\Entities\User::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'phone', 'password', 'token', 'token_date', 'token_expiry', 'otp', 'otp_expiry', 'otp_status', 'status', 'acct_type', 'acct_status', 'verification_otp'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'email' => 'required|valid_email|is_unique[auth_tbl.email,id,{id}]',
        'phone' => 'required|min_length[11]',
        'acct_type' => 'required|in_list[user,hospital,blood-bank]',

    ];

    protected $validationMessages   = [
         'email'        => [
            'is_unique' => 'Email already exists',
            'required' => 'Email is required',
        ],

        'phone'        => [
            'is_unique' => 'Phone number already exists',
            'required' => 'Phone number is required',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = false;
    protected $afterFind      = [];
    protected $beforeFind     = ['__fullData'];
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public $message = 'Invalid Request';
    public $auth_status = 'failed';


    protected function __fullData() {
        $this->join($this->table_2, "$this->table_2.auth_id = auth_tbl.id");
        return;
    } 


    public function findUser($user){

        $builder = $this->db->table('auth_tbl');
        $builder->where('email', $user);
        $builder->orWhere('phone', $user);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;

    }

    public function saveToTable($table, $data){
        $builder = $this->db->table($table);
        $builder->insert($data);
        $lastInsertId = $this->db->insertID();
        return $lastInsertId;
    }

    public function authenticateUserInformation($data)
    {
        if (!$data['email'] || !$data['password']) {
            $this->message = 'The submitted data do not match our records';
            return false;
        }

        if ($authRowValue = $this->findUser($data['email'])) {
            if (password_verify($data['password'], $authRowValue->password)) {
                $this->message = 'User signed in successfully.';
                $this->auth_status = 'success';

                return $authRowValue;
            }
        }
        
        $this->message = 'Invalid Username/Password passed.';
    }

    public function verifyOtpToken($uniq_token)
    {
        $builder = $this->db->table('auth_tbl');
        $builder->where('token ', $uniq_token);
        $query = $builder->get();
        $result = $query->getRow();

        if ($result) {
            return $result;
        }

        return false;
    }

    public function verifyUserOTPToken($data)
    {
        $builder = $this->db->table('auth_tbl');
        $builder->where('token ', $data['token']);
        $builder->where('verification_otp ', $data['otp']);
        $query = $builder->get();
        $result = $query->getRow();

        if ($result) {
            return $result;
        }

        return false;
    }
}
