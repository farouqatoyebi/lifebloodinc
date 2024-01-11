<?php namespace App\Models;

use CodeIgniter\Model;

class CouponModel extends Model
{
    protected $table      = 'coupon_tbl';
    protected $table_2      = 'coupon_users_tbl';
    protected $primaryKey = 'id';

    protected $returnType     = 'object';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['code', 'worth', 'type', 'frequency', 'start_date', 'end_date', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat = 'int';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [
        'code' => 'required|is_unique[coupon_tbl.code,id,{id}]|min_length[4]', 
        'worth' => 'required', 
        'type' => 'required|in_list[fixed,percentage]', 
        'frequency' => 'required|in_list[one-time,one-time-per-user,unlimited]', 
        'start_date' => 'required|integer', 
        'end_date' => 'required|integer'
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;




    public function checkCoupon($coupon_id, $user_id = null)
    {
        $builder = $this->db->table($this->table_2);
        $builder->where('coupon_id',$coupon_id);

        if (!is_null($user_id)) {
            $builder->where('user_id',$user_id);
        }

        $builder = $builder->get();
        $result = $builder->getRowArray(); 
        
        if (!empty($result)) {
            return false;
        }else{
            return true;
        }

    }


    public function findUserRecord(array $data){

        $builder = $this->db->table('coupon_users_tbl');
        $builder = $builder->where($data);
        $builder = $builder->get();
        $result = $builder->getRow();
        return $result;
    }

}