<?php 
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CouponModel;


class Coupon extends BaseController
{

    protected $error_message = '';

	public function __construct()
	{
		// Load Helpers
		helper(['form','url','date']);
		
		// Load services
		$this->session = \Config\Services::session();

		$this->couponModel = new CouponModel();
	
	}


	public function index()
	{
        $couponModel = new CouponModel();

		$coupons = $couponModel->findAll();

		foreach ($coupons as $key => $coupon) {
		
			$coupons[$key]->start_date = date("Y-m-d", $coupon->start_date);
			$coupons[$key]->end_date = date("Y-m-d", $coupon->end_date);
		}
		
		$data = [
			"site_title" => "Admin AA",
			"app_name" => "Eccomerce",
			"page_title" => "Coupons",
			"coupons" => $coupon,
			];

		return view('admin/coupon-view', $data);
	}


	public function new()
    {

        if (!$this->request->getPost()) {
        	die("no access");

        }

        $couponModel = new CouponModel();

        $data = $this->request->getPost();
        $data = (object) $data;
        $data->start_date = strtotime($data->start_date);
        $data->end_date = strtotime($data->end_date);

        if(!$couponModel->insert($data)){
        	print_r($couponModel->errors());
        	die();
        }

        $msg = "Coupon saved";
        $this->session->setFlashdata('success_message', $msg);

        echo json_encode(["status"=>true]);
        die;
    }




    public function edit()
    {

        if (!$this->request->getPost()) {
        	die("no access");
        }
        
        $couponModel = new CouponModel();

        $id = $this->request->getVar("coupon-id");

        $data = [
            'code' => $this->request->getVar("code"),
            'type' => $this->request->getVar("type"),
            'amount' => $this->request->getVar("amount"),
            'frequency' => $this->request->getVar("frequency"),
            'excluded_products' => $this->request->getVar("excluded-products"),
            'start_date' => strtotime($this->request->getVar("start-date")),
            'end_date' => strtotime($this->request->getVar("end-date")),
            'created_at' => time(),
        ];

        $couponModel->update($id, $data);
        $msg = "Coupon Edited";
        $this->session->setFlashdata('success_message', $msg);

        return redirect()->back();
    }

    public function delete($id)
    {
        $couponModel = new CouponModel();

        $couponModel->delete($id);

        $msg = "Coupon deleted";
        $this->session->setFlashdata('success_message', $msg);
         return redirect()->back();
    }


    public function error(){
        return $this->error_message;
    }


    public function validateCoupon($code)
    {
        // return false if no code passed to method
        if (empty($code)) {

            return false;
        }

        $couponModel = new CouponModel();

        $coupon_data = $couponModel->where('code',$code)->first();

        // if coupon not found return false
        if (empty($coupon_data)) {
            $this->error_message = "Invalid Coupon";
            return false;
        }

        // if coupon expired or not active yet
        if ($coupon_data->start_date > time() || $coupon_data->end_date < time()) {
            $this->error_message = "Coupon expired";
            return false;
        }


        // if coupon is a one-time usage 
        if ($coupon_data->frequency == 'one-time') {

            // if coupon found used by any user
            if(!$couponModel->checkCoupon($coupon_data->id))
            {
                $this->error_message = "Coupon used already";
                return false;
            }
        }

        // if coupon is used by the current user
        if ($coupon_data->frequency == 'one-time-per-user') {

            // if coupon found used by user return false 
            if(!$couponModel->checkCoupon($coupon_data->id, auth_id()))
            {
                $this->error_message = "Coupon used by you";
                return false;
            }
        }

        return $coupon_data;
    }



    public function addUser(array $data)
    {
        $couponModel = new CouponModel();

        $couponModel->saveToTable($couponModel->table_2, $data);

        return true;
    }


    public function isUsed(int $user_id, int $request_id)
    {
        $data = ['user_id' => $user_id, 'request_id' => $request_id];
        $couponModel = new CouponModel();

        $result = $couponModel->findUserRecord($data);
        return $result;
    }

    public function worth($coupon_id)
    {
        $couponModel = new CouponModel();
        
        if(!$result = $couponModel->find($coupon_id)){
            return 0;
        }

        if ($result->type == 'percentage' && $result->worth == 100) {
            return 100;
        }

        return 0;

    }
}
