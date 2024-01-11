<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountInformation extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'account_information';
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

    public function getAccountInformation($auth_id, $auth_type)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->get();
        $result = $builder->getRow();

        return $result;
    }
}
