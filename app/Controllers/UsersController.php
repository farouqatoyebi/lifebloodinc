<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Files\File;

use App\Models\UserModel;


class UsersController extends AuthController
{

	public function __construct (){
        
        helper(['Auth', 'form', 'filesystem']);

        $this->userModel = new UserModel();
    }


    public function show()
    {

        $user_data = $this->userModel->find(auth_id());

        $user_data->logo = base_url($this->thumb_path.$user_data->logo);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'success',
            'data' => $user_data,
        ];
             
        return $this->respond($response, 200);

    }



    public function update()
    {
    	$post_data = $this->request->getJSON();

    	if(!$this->userModel->update(auth_id(), $post_data))
    	{

    		$response = [
	            'status' => 500,
	            'error' => true,
	            'message' => 'unable to update data',
	            'data' => '',
	        ];
             
        	return $this->respond($response);
    	}


    	$user_data = $this->userModel->find(auth_id());

    	$response = [
            'status' => 200,
            'error' => false,
            'message' => 'success',
            'data' => $user_data,
        ];
             
        return $this->respondCreated($response);

    }



    public function updateLogo(){

    	$status = false;

    	$validationRule = [
            'logo' => [
                'label' => 'Image File',
                'rules' => 'uploaded[logo]'
                    . '|is_image[logo]'
                    . '|mime_in[logo,image/jpg,image/jpeg,image/png]'
                    . '|max_size[logo,5000]'
                    // . '|max_dims[logo,1024,768]',
            ],
        ];

        if (! $this->validate($validationRule)) {
            
            $message = $this->validator->getErrors();

            $response = [
		            'status' => 500,
		            'error' => true,
		            'message' => $message,
		            'data' => null,
		        ];
		             
		    return $this->respond($response);
        }


    	$file = $this->request->getfile('logo');

    	$imageService = \Config\Services::image();

    	// Generate Image name
    	$img_name = "user_".time().".".$file->getExtension();

    	// Validate file and upload
        if($file->isvalid() && !$file->hasMoved()){

            if($file->move($this->img_path, $img_name))
            {
                $imageService->withFile($this->img_path.$img_name)
                    ->resize(100, 100)
                    ->save($this->thumb_path.$img_name);

                $status = true;
            }
        }


        if ($status) {

        	$this->userModel->update(auth_id(), ['logo' => $img_name]);
        	
        	$response = [
	            'status' => 200,
	            'error' => false,
	            'message' => 'success',
	            'data' => ['url' => base_url($this->thumb_path.$img_name)],
	        ];
	             
	        return $this->respondCreated($response);

        }else{

        	$response = [
	            'status' => 500,
	            'error' => true,
	            'message' => 'Something went wrong',
	            'data' => null,
	        ];
	             
	        return $this->respondCreated($response);

        }

    }	// End of function



    public function updateLocation()
    {
    $post_data = $this->request->getJSON();

        if (!isset($post_data->latitude) || empty($post_data->latitude) ||
            !isset($post_data->longitude) || empty($post_data->longitude)
        ){

            return $this->failValidationError();

        }


        $this->userModel->update(auth_id(), $post_data);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'success',
            'data' => null
        ];

        return $this->respondCreated($response);
    }



    public function getCountries()
    {
        $countries = $this->userModel->fetchTable('countries_tbl');

        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'success',
            'data' => $countries
        ];
                 
        return $this->respond($response);

    }


    public function getStates($country_id)
    {
        $states = $this->userModel->fetchTable('states_tbl', ['country_id' => $country_id]);

        $response = [
            'status' => 200,
            'error' => false,
            'message' => 'success',
            'data' => $states
        ];
                 
        return $this->respond($response);

    }

}	// End of Class
