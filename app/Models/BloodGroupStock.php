<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class BloodGroupStock extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'blood_group_stock';
    protected $table2            = 'blood_group_stock_history';
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

    public function getUsersStock($auth_id, $auth_type)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->get();
        $result = $builder->getResult();

        if (!count($result)) {
            $basecontroller = new BaseController();
            $allBloodGroups = $basecontroller->getAllBloodGroups();

            if (count($allBloodGroups)) {
                foreach ($allBloodGroups as $value) {
                    $builder = $this->db->table($this->table);
                    $builder->insert([
                        'auth_id' => $auth_id,
                        'auth_type' => $auth_type,
                        'blood_group' => $value->name,
                        'amount_available' => 0,
                        'created_at' => time(),
                    ]);
                }

                return $this->getUsersStock($auth_id, $auth_type);
            }
        }

        return $result;
    }

    public function updateStockInformation($auth_id, $auth_type, $data)
    {
        $blood_group = $data['bloodGroup'];
        $amount_available = $data['amount_avail'];

        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->where('blood_group', $data['bloodGroup']);
        $builder = $builder->get();
        $result = $builder->getRow();

        $updateStock = $this->db->table($this->table);
        $updateStock = $updateStock->where('auth_id', $auth_id);
        $updateStock = $updateStock->where('auth_type', $auth_type);
        $updateStock = $updateStock->where('blood_group', $data['bloodGroup']);
        $updateStock->update([
            'amount_available' => $result->amount_available + $amount_available,
            'updated_at' => time(),
        ]);

        $insertHistory = $this->db->table($this->table2);
        $insertHistory->insert([
            'auth_id' => $auth_id,
            'auth_type' => $auth_type,
            'blood_group' => $blood_group,
            'amount_updated' => $amount_available,
            'narration' => "Added ".$amount_available." pints of ".$blood_group." blood to the inventory",
            'status' => "added-to-inventory",
            'created_at' => time(),
        ]);
    }

    public function getBloodStockHistory($auth_id, $auth_type, $blood_group)
    {
        $builder = $this->db->table($this->table2);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->where('blood_group', $blood_group);
        $builder = $builder->orderBy('created_at', 'DESC');
        $builder = $builder->get();
        $result = $builder->getResult();

        return $result;
    }
}
