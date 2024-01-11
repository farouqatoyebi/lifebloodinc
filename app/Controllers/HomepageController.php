<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class HomepageController extends BaseController
{
    public function index()
    {
        return view("website/index");
    }
}
