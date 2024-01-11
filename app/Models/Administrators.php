<?php

namespace App\Models;
use App\Controllers\BaseController;
use App\Models\WalletModel;
use App\Models\BloodBankModel;

use CodeIgniter\Model;

class Administrators extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'administrators';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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

    public $message = 'Invalid Request';
    public $auth_status = 'failed';

    public function authenticateAdminInformation($data)
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

    public function findUser($user) {
        $builder = $this->db->table($this->table);
        $builder->where('email', $user);
        $query = $builder->get();
        $result = $query->getRow();
        return $result;
    }

    public function countOfUsers()
    {
        $builder = $this->db->table('auth_tbl');
        $noOfUsers = $builder->countAllResults();

        return $noOfUsers;
    }

    public function getAllPendingWithdrawals()
    {
        $builder = $this->db->table('withdrawal_tbl');
        $builder->where('status', 'pending');
        $query = $builder->get();
        $results = $query->getResult();
        $baseController = new BaseController();
        $walletModel = new WalletModel();
        $bloodBankModel = new BloodBankModel();

        if ($results) {
            $infoSaved = [];
            foreach ($results as $key => $value) {
                $thisKeyHere = $value->auth_type.'__'.$value->auth_id;

                if (!in_array($thisKeyHere, array_keys($infoSaved))) {
                    $infoSaved[$thisKeyHere]['acct_info'] = $baseController->getAccountInformationBasedOnID($value->auth_type, $value->auth_id);
                    $infoSaved[$thisKeyHere]['bank_acct_info'] = $bloodBankModel->getBankDetailsInformation($value->auth_id);
                    $data = [
                        'auth_id' => $value->auth_id,
                        'acct_type' => $value->auth_type
                    ];
                    $accountBalnce = $walletModel->getAccountWalletBreakdown($data);
                    $accountBalnce = $accountBalnce ? $accountBalnce->available_balance : 0;
                    $infoSaved[$thisKeyHere]['acct_balance'] = $accountBalnce;
                }

                $value->acct_info = $infoSaved[$thisKeyHere]['acct_info'];
                $value->acct_balance = $infoSaved[$thisKeyHere]['acct_balance'];
                $value->bank_acct_info = $infoSaved[$thisKeyHere]['bank_acct_info'];
            }
        }

        return $results;
    }

    public function getAllApprovedWithdrawals()
    {
        $builder = $this->db->table('withdrawal_tbl');
        $builder->where('status', 'approved');
        $query = $builder->get();
        $results = $query->getResult();
        $baseController = new BaseController();
        $walletModel = new WalletModel();
        $bloodBankModel = new BloodBankModel();

        if ($results) {
            $infoSaved = [];
            foreach ($results as $key => $value) {
                $thisKeyHere = $value->auth_type.'__'.$value->auth_id;

                if (!in_array($thisKeyHere, array_keys($infoSaved))) {
                    $infoSaved[$thisKeyHere]['acct_info'] = $baseController->getAccountInformationBasedOnID($value->auth_type, $value->auth_id);
                    $infoSaved[$thisKeyHere]['bank_acct_info'] = $bloodBankModel->getBankDetailsInformation($value->auth_id);
                    $data = [
                        'auth_id' => $value->auth_id,
                        'acct_type' => $value->auth_type
                    ];
                    $accountBalnce = $walletModel->getAccountWalletBreakdown($data);
                    $accountBalnce = $accountBalnce ? $accountBalnce->available_balance : 0;
                    $infoSaved[$thisKeyHere]['acct_balance'] = $accountBalnce;
                }

                $value->acct_info = $infoSaved[$thisKeyHere]['acct_info'];
                $value->acct_balance = $infoSaved[$thisKeyHere]['acct_balance'];
                $value->bank_acct_info = $infoSaved[$thisKeyHere]['bank_acct_info'];
            }
        }

        return $results;
    }

    public function getAllRejectedWithdrawals()
    {
        $builder = $this->db->table('withdrawal_tbl');
        $builder->where('status', 'rejected');
        $query = $builder->get();
        $results = $query->getResult();
        $baseController = new BaseController();
        $walletModel = new WalletModel();
        $bloodBankModel = new BloodBankModel();

        if ($results) {
            $infoSaved = [];
            foreach ($results as $key => $value) {
                $thisKeyHere = $value->auth_type.'__'.$value->auth_id;

                if (!in_array($thisKeyHere, array_keys($infoSaved))) {
                    $infoSaved[$thisKeyHere]['acct_info'] = $baseController->getAccountInformationBasedOnID($value->auth_type, $value->auth_id);
                    $infoSaved[$thisKeyHere]['bank_acct_info'] = $bloodBankModel->getBankDetailsInformation($value->auth_id);
                    $data = [
                        'auth_id' => $value->auth_id,
                        'acct_type' => $value->auth_type
                    ];
                    $accountBalnce = $walletModel->getAccountWalletBreakdown($data);
                    $accountBalnce = $accountBalnce ? $accountBalnce->available_balance : 0;
                    $infoSaved[$thisKeyHere]['acct_balance'] = $accountBalnce;
                }

                $value->acct_info = $infoSaved[$thisKeyHere]['acct_info'];
                $value->acct_balance = $infoSaved[$thisKeyHere]['acct_balance'];
                $value->bank_acct_info = $infoSaved[$thisKeyHere]['bank_acct_info'];
            }
        }

        return $results;
    }

    public function getWithdrawalInfo($withdrawalID)
    {
        $builder = $this->db->table('withdrawal_tbl');
        $builder->where('id', $withdrawalID);
        $query = $builder->get();
        $result = $query->getRow();

        return $result;
    }

    public function updateWithdrawalRequest($id, $data)
    {
        $builder = $this->db->table('withdrawal_tbl');
        $builder->where('id', $id);
        $builder->update($data);

        return true;
    }


    public function getAllAccountsInDB($account_type = 'user')
    {
        $results = [];
        $walletModel = new WalletModel();

        if ($account_type == 'hospital') {
            $builder = $this->db->table('hospitals_tbl');
            $builder = $builder->orderBy('created_at', 'DESC');
            $query = $builder->get();
            $results = $query->getResult();
        }
        elseif ($account_type == 'blood-bank') {
            $builder = $this->db->table('blood_banks_tbl');
            $builder = $builder->orderBy('created_at', 'DESC');
            $query = $builder->get();
            $results = $query->getResult();
        }
        elseif ($account_type == 'pharmacy') {
            $builder = $this->db->table('pharmacies_tbl');
            $builder = $builder->orderBy('created_at', 'DESC');
            $query = $builder->get();
            $results = $query->getResult();
        }
        else {
            $builder = $this->db->table('users_tbl');
            $builder = $builder->orderBy('created_at', 'DESC');
            $query = $builder->get();
            $results = $query->getResult();
        }
        
        if ($results) {
            foreach ($results as $key => $value) {
                $data = [
                    'auth_id' => $value->id,
                    'acct_type' => $account_type
                ];
                $accountBalnce = $walletModel->getAccountWalletBreakdown($data);
                $value->amount = $accountBalnce ? $accountBalnce->balance : 0;
                $value->acct_balance = $accountBalnce ? $accountBalnce->available_balance : 0;
            }
        }

        return $results;
    }

    public function getProfileInformation($email)
    {
        $builder = $this->db->table($this->table);
        $builder->where('email', $email);
        $builder = $builder->get();
        $result = $builder->getRow();

        return $result;
    }

    public function updateAdminProfileInformation($email, $data)
    {
        $builder = $this->db->table($this->table);
        $builder->where('email', $email);
        $builder->update($data);

        $result = $this->getProfileInformation($email);

        return $result;
    }
}
