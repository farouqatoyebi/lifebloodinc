<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Controllers\BaseController;

class WalletModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'donation_wallet_tbl';
    protected $primaryKey       = 'auth_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['auth_id', 'balance'];

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
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];



    public function updateDonationWallet($auth_id, $amount, $type = 'individual'){
        
        $sql = "UPDATE donation_wallet_tbl SET balance = balance + $amount, available_balance = available_balance + $amount WHERE auth_id = $auth_id AND auth_type = '$type'";
        $query = $this->db->query($sql);

        return $this->db->affectedRows();
    }

    public function creditBloodBankWallet($bloodBankID, $amount, $creditBalance = true)
    {
        if (!$bloodBankID) {
            return false;
        }

        if (!$amount) {
            return false;
        }

        if ($bloodBankID < 0) {
            return false;
        }

        if ($amount < 0) {
            return false;
        }

        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $bloodBankID);
        $builder = $builder->where('auth_type', 'blood-bank');
        $result = $builder->get();
        $result = $result->getRow();

        if ($result) {
            $data = [
                'balance' => $amount + $result->balance,
                'updated_at' => time(),
            ];

            if ($creditBalance) {
                $data['available_balance'] = $amount + $result->available_balance;
            }

            $builder = $this->db->table($this->table);
            $builder = $builder->where('auth_id', $bloodBankID);
            $builder = $builder->where('auth_type', 'blood-bank');
            $builder->update($data);
        }
        else {
            $data = [
                'auth_id' => $bloodBankID,
                'auth_type' => 'blood-bank',
                'balance' => $amount,
                'created_at' => time(),
            ];
            
            if ($creditBalance) {
                $data['available_balance'] = $amount;
            }

            $builder = $this->db->table($this->table);
            $builder->insert($data);
        }

        return true;
    }

    public function debitBloodBankWallet($bloodBankID, $amount)
    {
        if (!$bloodBankID) {
            return false;
        }

        if (!$amount) {
            return false;
        }

        if ($bloodBankID < 0) {
            return false;
        }

        if ($amount < 0) {
            return false;
        }

        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $bloodBankID);
        $builder = $builder->where('auth_type', 'blood-bank');
        $result = $builder->get();
        $result = $result->getRow();

        if ($result) {
            $amountLeft = $result->balance - $amount;

            if ($amountLeft <= 0) {
                $amountLeft = 0;
            }

            $builder = $this->db->table($this->table);
            $builder = $builder->where('auth_id', $bloodBankID);
            $builder = $builder->where('auth_type', 'blood-bank');
            $builder->update([
                'balance' => $amountLeft,
                'updated_at' => time(),
            ]);

            return true;
        }

        return false;
    }

    public function debitBloodBankAccountBalance($bloodBankID, $amount)
    {
        if (!$bloodBankID) {
            return false;
        }

        if (!$amount) {
            return false;
        }

        if ($bloodBankID < 0) {
            return false;
        }

        if ($amount < 0) {
            return false;
        }

        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $bloodBankID);
        $builder = $builder->where('auth_type', 'blood-bank');
        $result = $builder->get();
        $result = $result->getRow();

        if ($result) {
            $amountLeft = $result->available_balance - $amount;

            if ($amountLeft <= 0) {
                $amountLeft = 0;
            }

            $builder = $this->db->table($this->table);
            $builder = $builder->where('auth_id', $bloodBankID);
            $builder = $builder->where('auth_type', 'blood-bank');
            $builder->update([
                'available_balance' => $amountLeft,
                'updated_at' => time(),
            ]);

            return true;
        }

        return false;
    }

    public function getAccountWalletBreakdown($data = [])
    {
        if (empty($data)) {
            $accountAuthID = session()->get('auth_id');
            $accountType = session()->get('acct_type');

            $baseController = new BaseController();
            $userProfileInformation = $baseController->getUserProfileInformationBasedOnType($accountType, $accountAuthID);
            $auth_id = $userProfileInformation->id;
        }
        else {
            $auth_id = $data['auth_id'];
            $accountType = $data['acct_type'];
        }

        if ($auth_id && $accountType) {
            $builder = $this->db->table($this->table);
            $builder = $builder->where('auth_id', $auth_id);
            $builder = $builder->where('auth_type', $accountType);
            $result = $builder->get();
            $result = $result->getRow();

            return $result;
        }

        return false;
    }

    public function getAllTransactionsBreakdown()
    {
        $accountAuthID = session()->get('auth_id');
        $accountType = session()->get('acct_type');

        $baseController = new BaseController();
        $userProfileInformation = $baseController->getUserProfileInformationBasedOnType($accountType, $accountAuthID);

        if ($userProfileInformation) {
            $builder = $this->db->table($this->table);
            $builder = $builder->where('auth_id', $userProfileInformation->id);
            $builder = $builder->where('auth_type', $accountType);
            $result = $builder->get();
            $result = $result->getRow();

            return $result;
        }

        return false;
    }
}
