<?php

/**

* This helper should be loaded through the helper() function to access alll available functions in this helper
* To load this helper simply load the function by the below code in your controllers method or controller
***	helper('myfunctions')	****

*/


function getAge($dob){
	
	$diff = time() - $dob;
	$age = $diff / 60 / 60 / 24 / 365;
	return floor($age);
}


function assignLogoUrl($data, string $img_path)
{
	if (is_object($data)) {
	
		if (property_exists($data, "logo")) {

			$data->logo = base_url($img_path.$data->logo);

		}

	}else{

		foreach ($data as $key => $value) {

	        if (property_exists($data[$key], "logo")) {   
	        
	        	$data[$key]->logo = base_url($img_path.$data[$key]->logo);
	        }
	    }
	}

    return $data;
}