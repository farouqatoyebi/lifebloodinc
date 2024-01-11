<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Controllers\AuthController;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = new AuthController();

// echo $request->getHeader("Authorization");
// die();

        // if (is_null($request->getHeader("Authorization")))
        // {
        // 	$response = service('response');
        //     $response->setBody('Access denied');
        //     $response->setStatusCode(401);
        //     return $response;
        
        // }

        if(!$auth->verifyAccessToken()) {
        	
        	$response = service('response');
            $response->setBody('Access denied');
            $response->setStatusCode(401);
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}