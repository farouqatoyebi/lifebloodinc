<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use Phpqrcode\QRcode;


class Home extends BaseController
{
    use ResponseTrait;

    public function index()
    {
    	$requestModel = new \App\Models\RequestModel();

    	print_r($requestModel->betalife_rate(true));

    }
}
