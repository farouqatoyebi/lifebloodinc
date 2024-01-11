<?php

namespace App\Models;

use CodeIgniter\Model;

class PharmacyModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'pharmacies_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'auth_id', 'logo', 'reg_no'];

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
}
