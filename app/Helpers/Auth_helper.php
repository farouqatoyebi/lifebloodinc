<?php 

/**

* This helper will be used to access some auth controller global functions

*/

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\AuthController;

if (! function_exists('auth_id')) {
	
	/*
	* This function will return user's id if logged in or null if not logged in
	*/

	function auth_id()
	{
		$auth = new AuthController();

		if($decoded = $auth->verifyAccessToken()) {

            return $decoded->aid;

        }else{

        	return null;
        }

	}
}