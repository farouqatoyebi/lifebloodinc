<?php 

	/*
	
	* This helper will handle notification functions such as Email and sms

	*/

use \AfricasTalking\SDK\AfricasTalking;

	
	
	function sendEmail($to, $subject, $message)
	{
		$servermail  = "mailer@betalifehealth.com";
    	$app_name = "Betalife Health Services";

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($servermail, $app_name);
        $email->setSubject($subject);
        $email->setMessage($message);

        if ($email->send()) {
           
            return true;
        
        }else{
            
            return false;
            // $data = $email->printDebugger(['headers']);
            // print_r($data);
            // die();
        }

    } 	// End of sendMail method



    function sendSMS($to, $message)
    {

        $username   = "sandbox";
        $apiKey     = "d68d2715929e9fe57925c4125b4e3b9849c36b63bfe47ea213f0a90300a9ea39";
        	
        $from = "Betalife";

        $AT = new AfricasTalking($username, $apiKey);

        // Get the SMS service
        $sms = $AT->sms();

        try {

            $result = $sms->send([
                'to'      => $to,
                'message' => $message,
                'from'    => $from
            ]);

            if ($result['status'] == 'success') {
            	
            	return true;
            }else{

            	return false;
            }
            

            return true;

        } catch (Exception $e) {
        	return false;
            // echo "Error: ".$e->getMessage();
            // die();
        }

    }