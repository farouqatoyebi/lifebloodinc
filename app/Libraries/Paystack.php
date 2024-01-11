<?php 
namespace App\Libraries;


class Paystack
{
	// TEST KEY
	private $secret_key = 'sk_test_6c7eb277471b04cb659199dcbf570b50a0ce74df';
	private $public_key = 'pk_test_af0aa803a684d0af33616083ed99f445b4b7882a';
	
	// LIVE KEY
	// private $secret_key = 'sk_live_b2a65258f21f8e3378d72e73c483a6472dd9b766';
	// private $public_key = 'pk_live_0d14783b13ee294ac03ec3dc4e561d0469e7bf92 ';


	// Returns the Public Key
	public function get_public_key(){
		return $this->public_key;
	}

	// Returns the Public Key
	public function get_secret_key(){
		return $this->secret_key;
	}


	// Verify Payment
	public function verifyPayment($reference, $amount)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		  "Authorization: Bearer {$this->secret_key}",
		  "Cache-Control: no-cache",
		),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			return false;
		} 

		$response = json_decode($response);
		$paid_amount = $response->data->amount/100;
		

		if ($response->data->status == 'success' && $paid_amount >= $amount ) {
			return true;
		}else{
			return false;
		}
		
	}
}