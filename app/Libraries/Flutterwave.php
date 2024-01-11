<?php 
namespace App\Libraries;

class Flutterwave
{

	// LIVE KEYS
	// private $secret_key = 'FLWSECK-861342224daec9dee5c9773175edaae9-X';
	// private public_key = 'FLWPUBK-6f55663a3b636e3bf537ffc085ee41e3-X';

	// TEST KEYS FOR POJICT SYSTEM
	private $secret_key = 'FLWSECK_TEST-f135ec9d408502f1b6d8ba6e5e29d9de-X';
	private $public_key = 'FLWPUBK_TEST-69194a2a0f68782083a27d3f7794bd76-X';
	

	public function __construct()
	{
		$this->session = \Config\Services::session();
	}


	/*
	* This is used to get access the public key
	*/

	public function get_public_key(){
		return $this->public_key;
	}


	/**

	*	This method is used to verify payments
	
	*/
	public function verifyPayment($reference, $amount)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$reference}/verify",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "Authorization: Bearer {$this->secret_key}"
		  ),

		));

		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		if ($error) {
			return false;
		} 

		$response = json_decode($response);

		// if ($response->status == 'error') {
		// 	return false;
		// }

		$paid_amount = $response->data->amount;
		
		if ($response->data->status == 'successful' && $paid_amount >= $amount && $response->data->currency == 'NGN') {
			
			return true;

		}else{

			return false;
		}
			
	}
	
}