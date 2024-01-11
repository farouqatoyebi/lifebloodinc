<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminPosts extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'posts_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'content', 'image', 'created_at', 'updated_at', 'deleted_at'];

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

    public function allAdminPosts()
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->orderBy('created_at', 'DESC');
        $builder = $builder->get();
        $result = $builder->getResult();

        return $result;
    }

    public function deletePost($postID)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->where('id', $postID);
        $builder->update([
            'deleted_at' => time()
        ]);

        return true;
    }

    public function postDetails($postID)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('deleted_at', NULL);
        $builder = $builder->where('id', $postID);
        $builder = $builder->get();
        $result = $builder->getRow();

        return $result;
    }
}
