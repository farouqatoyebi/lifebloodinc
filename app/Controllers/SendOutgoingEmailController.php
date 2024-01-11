<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class SendOutgoingEmailController extends BaseController
{
    public function __construct()
    {
        $this->db = Database::connect();
        $this->subject = 'New Blood Request';
        $this->bannerImage = 'sending-request.png';

        $this->htmlBody = '<!DOCTYPE html>
        <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
           <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width">
              <meta http-equiv="X-UA-Compatible" content="IE=edge">
              <meta name="x-apple-disable-message-reformatting">
              <title></title>
              <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
              <style>
                 /* What it does: Remove spaces around the email design added by some email clients. */
                 /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
                 html,
                 body {
                    margin: 0 auto !important;
                    padding: 0 !important;
                    height: 100% !important;
                    width: 100% !important;
                    background: #f1f1f1;
                 }
                 /* What it does: Stops email clients resizing small text. */
                 * {
                 -ms-text-size-adjust: 100%;
                 -webkit-text-size-adjust: 100%;
                 }
                 /* What it does: Centers email on Android 4.4 */
                 div[style*="margin: 16px 0"] {
                    margin: 0 !important;
                 }
                 /* What it does: Stops Outlook from adding extra spacing to tables. */
                 table,
                 td {
                    mso-table-lspace: 0pt !important;
                    mso-table-rspace: 0pt !important;
                 }
                 /* What it does: Fixes webkit padding issue. */
                 table {
                    border-spacing: 0 !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                    margin: 0 auto !important;
                 }
                 /* What it does: Uses a better rendering method when resizing images in IE. */
                 img {
                    -ms-interpolation-mode:bicubic;
                 }
                 /* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
                 a {
                    text-decoration: none;
                 }
                 /* What it does: A work-around for email clients meddling in triggered links. */
                 *[x-apple-data-detectors],  /* iOS */
                 .unstyle-auto-detected-links *,
                 .aBn {
                    border-bottom: 0 !important;
                    cursor: default !important;
                    color: inherit !important;
                    text-decoration: none !important;
                    font-size: inherit !important;
                    font-family: inherit !important;
                    font-weight: inherit !important;
                    line-height: inherit !important;
                 }
                 /* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
                 .a6S {
                 display: none !important;
                    opacity: 0.01 !important;
                 }
                 /* What it does: Prevents Gmail from changing the text color in conversation threads. */
                 .im {
                    color: inherit !important;
                 }
                 img.g-img + div {
                    display: none !important;
                 }
                 /* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
                 @media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
                    u ~ div .email-container {
                        min-width: 320px !important;
                    }
                 }
                 /* iPhone 6, 6S, 7, 8, and X */
                 @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
                    u ~ div .email-container {
                        min-width: 375px !important;
                    }
                 }
                 /* iPhone 6+, 7+, and 8+ */
                 @media only screen and (min-device-width: 414px) {
                    u ~ div .email-container {
                        min-width: 414px !important;
                    }
                 }
              </style>
              <!-- CSS Reset : END -->
              <!-- Progressive Enhancements : BEGIN -->
              <style>
                 .primary{
                    background: #30e3ca;
                 }
                 .bg_white{
                    background: #ffffff;
                 }
                 .bg_light{
                    background: #fafafa;
                 }
                 .bg_black{
                    background: #000000;
                 }
                 .bg_dark{
                    background: rgba(0,0,0,.8);
                 }
                 .email-section{
                    padding:2.5em;
                 }
                 /*BUTTON*/
                 .btn{
                    padding: 10px 15px;
                    display: inline-block;
                 }
                 .btn.btn-primary{
                    border-radius: 5px;
                    background: #30e3ca;
                    color: #ffffff;
                 }
                 .btn.btn-white{
                    border-radius: 5px;
                    background: #ffffff;
                    color: #000000;
                 }
                 .btn.btn-white-outline{
                    border-radius: 5px;
                    background: transparent;
                    border: 1px solid #fff;
                    color: #fff;
                 }
                 .btn.btn-black-outline{
                    border-radius: 0px;
                    background: transparent;
                    border: 2px solid #000;
                    color: #000;
                    font-weight: 700;
                 }
                 h1,h2,h3,h4,h5,h6{
                    font-family: \'Lato\', sans-serif;
                    color: #000000;
                    margin-top: 0;
                    font-weight: 400;
                 }
                 body{
                    font-family: \'Lato\', sans-serif;
                    font-weight: 400;
                    font-size: 15px;
                    line-height: 1.8;
                    color: rgba(0,0,0,.4);
                 }
                 a{
                    color: #30e3ca;
                 }
                 table{
                 }
                 /*LOGO*/
                 .logo h1{
                    margin: 0;
                 }
                 .logo h1 a{
                    color: #30e3ca;
                    font-size: 24px;
                    font-weight: 700;
                    font-family: \'Lato\', sans-serif;
                 }
                 /*HERO*/
                 .hero{
                    position: relative;
                    z-index: 0;
                 }
                 .hero .text{
                    color: rgba(0,0,0,.3);
                 }
                 .hero .text h2{
                    color: #000;
                    font-size: 40px;
                    margin-bottom: 0;
                    font-weight: 400;
                    line-height: 1.4;
                 }
                 .hero .text h3{
                    font-size: 24px;
                    font-weight: 300;
                 }
                 .hero .text h2 span{
                    font-weight: 600;
                    color: #30e3ca;
                 }
                 /*HEADING SECTION*/
                 .heading-section{
                 }
                 .heading-section h2{
                    color: #000000;
                    font-size: 28px;
                    margin-top: 0;
                    line-height: 1.4;
                    font-weight: 400;
                 }
                 .heading-section .subheading{
                    margin-bottom: 20px !important;
                    display: inline-block;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    color: rgba(0,0,0,.4);
                    position: relative;
                 }
                 .heading-section .subheading::after{
                    position: absolute;
                    left: 0;
                    right: 0;
                    bottom: -10px;
                    content: \'\';
                    width: 100%;
                    height: 2px;
                    background: #30e3ca;
                    margin: 0 auto;
                 }
                 .heading-section-white{
                    color: rgba(255,255,255,.8);
                 }
                 .heading-section-white h2{
                    font-family: 
                    line-height: 1;
                    padding-bottom: 0;
                 }
                 .heading-section-white h2{
                    color: #ffffff;
                 }
                 .heading-section-white .subheading{
                    margin-bottom: 0;
                    display: inline-block;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 2px;
                    color: rgba(255,255,255,.4);
                 }
                 ul.social{
                    padding: 0;
                 }
                 ul.social li{
                    display: inline-block;
                    margin-right: 10px;
                 }
                 /*FOOTER*/
                 .footer{
                    border-top: 1px solid rgba(0,0,0,.05);
                    color: rgba(0,0,0,.5);
                 }
                 .footer .heading{
                    color: #000;
                    font-size: 20px;
                 }
                 .footer ul{
                    margin: 0;
                    padding: 0;
                 }
                 .footer ul li{
                    list-style: none;
                    margin-bottom: 10px;
                 }
                 .footer ul li a{
                    color: rgba(0,0,0,1);
                 }
                 @media screen and (max-width: 500px) {
                 }
              </style>
           </head>
           <body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f1f1f1;">
              <center style="width: 100%; background-color: #f1f1f1;">
                 <div style="display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
                    &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
                 </div>
                 <div style="max-width: 600px; margin: 0 auto;" class="email-container">
                    <!-- BEGIN BODY -->
                    <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                       <tr>
                          <td valign="top" class="bg_white" style="padding: 1em 2.5em 0 2.5em;">
                             <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                   <td class="logo" style="text-align: center;">
                                      <h1><img class="" src="" /></h1>
                                   </td>
                                </tr>
                             </table>
                          </td>
                       </tr>
                       <!-- end tr -->
                       <tr>
                          <td valign="middle" class="hero bg_white" style="padding: 3em 0 2em 0;">
                             <img src="'.base_url('/webapp/images/{{ banner_image_here }}').'" alt="" style="width: 300px; max-width: 600px; height: auto; margin: auto; display: block;">
                          </td>
                       </tr>
                       <!-- end tr -->
                       <tr>
                          <td valign="middle" class="hero bg_white" style="padding: 2em 0 4em 0;">
                             <table>
                                <tr>
                                   <td>
                                      {{ body_here }}
                                   </td>
                                </tr>
                             </table>
                          </td>
                       </tr>
                    </table>
                    <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                       <tr>
                          <td class="bg_light" style="text-align: center;">
                             <p>No longer want to receive these email? You can <a href="#" style="color: rgba(0,0,0,.8);">Unsubscribe here</a></p>
                          </td>
                       </tr>
                    </table>
                 </div>
              </center>
           </body>
        </html>';
    }

    public function index()
    {
        
    }

    public function sendOutNewBloodRequestEmail()
    {
        $body = '<div class="text" style="padding: 0 2.5em; text-align: center;">
           <h2>New Blood Request</h2>
           <p style="text-align: left; font-size: 18px;">A new blood request has been sent out. Kindly log on to your dashboard to send an offer and save a life today.</p>
           <!-- <p><a href="'.base_url('login').'" class="btn btn-primary">Go to Dashboard</a></p> -->
        </div>';

        $htmlMsg = preg_replace('/{{ body_here }}/', $body, $this->htmlBody);
        $htmlMsg = str_replace('%7B%7B%20banner_image_here%20%7D%7D', $this->bannerImage, $htmlMsg);

        // Get all eligible users for the email
        $builder = $this->db->table('blood_banks_tbl');
        $builder = $builder->select('auth_tbl.email');
        $builder = $builder->join('auth_tbl', 'auth_tbl.id = blood_banks_tbl.auth_id');
        $builder = $builder->where('acct_type', 'blood-bank');
        $builder = $builder->distinct();
        $builder = $builder->get();
        $result = $builder->getResult();

        $eligibleEmails = [];

        if ($result) {
            foreach ($result as $key => $value) {
                $eligibleEmails[] = $value->email;
            }
        }

        if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
            if (count($eligibleEmails)) {
                foreach ($eligibleEmails as $value) {
                    sendEmail($value, $this->subject, $htmlMsg);
                }
            }
        }
    }

    public function sentOutEmailForOfferSentByBloodBank($bloodBankID, $requestID)
    {
        // Get Blood Bank Information
        $builder = $this->db->table('blood_banks_tbl');
        $builder = $builder->where('id', $bloodBankID);
        $builder = $builder->get();
        $result = $builder->getRow();
        $this->subject = 'New Blood Donation Offer';
        $this->bannerImage = 'received-blood-offer.png';

        if ($result) {
            // Get request information
            $builder = $this->db->table('requests_tbl');
            $builder = $builder->select('auth_tbl.email');
            $builder = $builder->join('auth_tbl', 'auth_tbl.id = requests_tbl.auth_id');
            $builder = $builder->where('requests_tbl.id', $requestID);
            $builder = $builder->get();
            $resultRequest = $builder->getRow();

            if ($resultRequest) {
                $body = '<div class="text" style="padding: 0 2.5em; text-align: center;">
                   <h2>New Blood Donation Offer</h2>
                   <p style="text-align: left; font-size: 18px;">A new blood donation offer has been sent by a blood bank for your blood request with ID #'.$requestID.'. Click the button below to see the offer sent.</p>
                   <p><a href="'.base_url('login').'" class="btn btn-primary">Go to Dashboard</a></p>
                </div>';
        
                $htmlMsg = preg_replace('/{{ body_here }}/', $body, $this->htmlBody);
                $htmlMsg = str_replace('%7B%7B%20banner_image_here%20%7D%7D', $this->bannerImage, $htmlMsg);
        
                if ($resultRequest->email) {
                    if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                        sendEmail($resultRequest->email, $this->subject, $htmlMsg);
                    }
                }
            }
        }
    }

    public function sentOutEmailForAcceptedOfferByHospitalToBloodBank($bloodBankID, $requestID)
    {
        $this->subject = 'Blood Donation Offer Accepted!';
        $this->bannerImage = 'accepted-blood-offer.png';

        // Get Blood Bank Email
        $builder = $this->db->table('blood_banks_tbl');
        $builder = $builder->select('auth_tbl.email');
        $builder = $builder->join('auth_tbl', 'auth_tbl.id = blood_banks_tbl.auth_id');
        $builder = $builder->where('auth_tbl.acct_type', 'blood-bank');
        $builder = $builder->where('blood_banks_tbl.id', $bloodBankID);
        $builder = $builder->distinct();
        $builder = $builder->get();
        $result = $builder->getRow();

        if ($result) {
            $body = '<div class="text" style="padding: 0 2.5em; text-align: center;">
                <h2>Blood Donation Offer Accepted</h2>
                <p style="text-align: left; font-size: 18px;">Your offer sent for blood request with ID #'.$requestID.' has been accepeted. Click the button below to see the breakdown of the accepted .</p>
                <p><a href="'.base_url('login').'" class="btn btn-primary">Go to Dashboard</a></p>
            </div>';
    
            $htmlMsg = preg_replace('/{{ body_here }}/', $body, $this->htmlBody);
            $htmlMsg = str_replace('%7B%7B%20banner_image_here%20%7D%7D', $this->bannerImage, $htmlMsg);
    
            if ($result->email) {
                if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                    sendEmail($result->email, $this->subject, $htmlMsg);
                }
            }
        }
    }

    public function sendOutPaymentReceiptForPaymentMade($email = '', $amount = 1000, $currency = 'NGN')
    {
        $this->subject = 'Payment Received by BetaLife!';

        $receiptHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <meta name="x-apple-disable-message-reformatting" />
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="color-scheme" content="light dark" />
                <meta name="supported-color-schemes" content="light dark" />
                <title></title>
                <style type="text/css" rel="stylesheet" media="all">
                /* Base ------------------------------ */
                
                @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap");
                body {
                    width: 100% !important;
                    height: 100%;
                    margin: 0;
                    -webkit-text-size-adjust: none;
                }
                
                a {
                    color: #3869D4;
                }
                
                a img {
                    border: none;
                }
                
                td {
                    word-break: break-word;
                }
                
                .preheader {
                    display: none !important;
                    visibility: hidden;
                    mso-hide: all;
                    font-size: 1px;
                    line-height: 1px;
                    max-height: 0;
                    max-width: 0;
                    opacity: 0;
                    overflow: hidden;
                }
                /* Type ------------------------------ */
                
                body,
                td,
                th {
                    font-family: "Nunito Sans", Helvetica, Arial, sans-serif;
                }
                
                h1 {
                    margin-top: 0;
                    color: #333333;
                    font-size: 22px;
                    font-weight: bold;
                    text-align: left;
                }
                
                h2 {
                    margin-top: 0;
                    color: #333333;
                    font-size: 16px;
                    font-weight: bold;
                    text-align: left;
                }
                
                h3 {
                    margin-top: 0;
                    color: #333333;
                    font-size: 14px;
                    font-weight: bold;
                    text-align: left;
                }
                
                td,
                th {
                    font-size: 16px;
                }
                
                p,
                ul,
                ol,
                blockquote {
                    margin: .4em 0 1.1875em;
                    font-size: 16px;
                    line-height: 1.625;
                }
                
                p.sub {
                    font-size: 13px;
                }
                /* Utilities ------------------------------ */
                
                .align-right {
                    text-align: right;
                }
                
                .align-left {
                    text-align: left;
                }
                
                .align-center {
                    text-align: center;
                }
                
                .u-margin-bottom-none {
                    margin-bottom: 0;
                }
                /* Buttons ------------------------------ */
                
                .button {
                    background-color: #3869D4;
                    border-top: 10px solid #3869D4;
                    border-right: 18px solid #3869D4;
                    border-bottom: 10px solid #3869D4;
                    border-left: 18px solid #3869D4;
                    display: inline-block;
                    color: #FFF;
                    text-decoration: none;
                    border-radius: 3px;
                    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
                    -webkit-text-size-adjust: none;
                    box-sizing: border-box;
                }
                
                .button--green {
                    background-color: #22BC66;
                    border-top: 10px solid #22BC66;
                    border-right: 18px solid #22BC66;
                    border-bottom: 10px solid #22BC66;
                    border-left: 18px solid #22BC66;
                }
                
                .button--red {
                    background-color: #FF6136;
                    border-top: 10px solid #FF6136;
                    border-right: 18px solid #FF6136;
                    border-bottom: 10px solid #FF6136;
                    border-left: 18px solid #FF6136;
                }
                
                @media only screen and (max-width: 500px) {
                    .button {
                    width: 100% !important;
                    text-align: center !important;
                    }
                }
                /* Attribute list ------------------------------ */
                
                .attributes {
                    margin: 0 0 21px;
                }
                
                .attributes_content {
                    background-color: #F4F4F7;
                    padding: 16px;
                }
                
                .attributes_item {
                    padding: 0;
                }
                /* Related Items ------------------------------ */
                
                .related {
                    width: 100%;
                    margin: 0;
                    padding: 25px 0 0 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                }
                
                .related_item {
                    padding: 10px 0;
                    color: #CBCCCF;
                    font-size: 15px;
                    line-height: 18px;
                }
                
                .related_item-title {
                    display: block;
                    margin: .5em 0 0;
                }
                
                .related_item-thumb {
                    display: block;
                    padding-bottom: 10px;
                }
                
                .related_heading {
                    border-top: 1px solid #CBCCCF;
                    text-align: center;
                    padding: 25px 0 10px;
                }
                /* Discount Code ------------------------------ */
                
                .discount {
                    width: 100%;
                    margin: 0;
                    padding: 24px;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                    background-color: #F4F4F7;
                    border: 2px dashed #CBCCCF;
                }
                
                .discount_heading {
                    text-align: center;
                }
                
                .discount_body {
                    text-align: center;
                    font-size: 15px;
                }
                /* Social Icons ------------------------------ */
                
                .social {
                    width: auto;
                }
                
                .social td {
                    padding: 0;
                    width: auto;
                }
                
                .social_icon {
                    height: 20px;
                    margin: 0 8px 10px 8px;
                    padding: 0;
                }
                /* Data table ------------------------------ */
                
                .purchase {
                    width: 100%;
                    margin: 0;
                    padding: 35px 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                }
                
                .purchase_content {
                    width: 100%;
                    margin: 0;
                    padding: 25px 0 0 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                }
                
                .purchase_item {
                    padding: 10px 0;
                    color: #51545E;
                    font-size: 15px;
                    line-height: 18px;
                }
                
                .purchase_heading {
                    padding-bottom: 8px;
                    border-bottom: 1px solid #EAEAEC;
                }
                
                .purchase_heading p {
                    margin: 0;
                    color: #85878E;
                    font-size: 12px;
                }
                
                .purchase_footer {
                    padding-top: 15px;
                    border-top: 1px solid #EAEAEC;
                }
                
                .purchase_total {
                    margin: 0;
                    text-align: right;
                    font-weight: bold;
                    color: #333333;
                }
                
                .purchase_total--label {
                    padding: 0 15px 0 0;
                }
                
                body {
                    background-color: #F2F4F6;
                    color: #51545E;
                }
                
                p {
                    color: #51545E;
                }
                
                .email-wrapper {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                    background-color: #F2F4F6;
                }
                
                .email-content {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                }
                /* Masthead ----------------------- */
                
                .email-masthead {
                    padding: 25px 0;
                    text-align: center;
                }
                
                .email-masthead_logo {
                    width: 94px;
                }
                
                .email-masthead_name {
                    font-size: 16px;
                    font-weight: bold;
                    color: #A8AAAF;
                    text-decoration: none;
                    text-shadow: 0 1px 0 white;
                }
                /* Body ------------------------------ */
                
                .email-body {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                }
                
                .email-body_inner {
                    width: 570px;
                    margin: 0 auto;
                    padding: 0;
                    -premailer-width: 570px;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                    background-color: #FFFFFF;
                }
                
                .email-footer {
                    width: 570px;
                    margin: 0 auto;
                    padding: 0;
                    -premailer-width: 570px;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                    text-align: center;
                }
                
                .email-footer p {
                    color: #A8AAAF;
                }
                
                .body-action {
                    width: 100%;
                    margin: 30px auto;
                    padding: 0;
                    -premailer-width: 100%;
                    -premailer-cellpadding: 0;
                    -premailer-cellspacing: 0;
                    text-align: center;
                }
                
                .body-sub {
                    margin-top: 25px;
                    padding-top: 25px;
                    border-top: 1px solid #EAEAEC;
                }
                
                .content-cell {
                    padding-left: 45px;
                    padding-right: 45px;
                    padding-top: 20px;
                    padding-bottom: 20px;
                }
                /*Media Queries ------------------------------ */
                
                @media only screen and (max-width: 600px) {
                    .email-body_inner,
                    .email-footer {
                    width: 100% !important;
                    }
                }
                
                @media (prefers-color-scheme: dark) {
                    body,
                    .email-body,
                    .email-body_inner,
                    .email-content,
                    .email-wrapper,
                    .email-masthead,
                    .email-footer {
                    background-color: #333333 !important;
                    color: #FFF !important;
                    }
                    p,
                    ul,
                    ol,
                    blockquote,
                    h1,
                    h2,
                    h3,
                    span,
                    .purchase_item {
                    color: #FFF !important;
                    }
                    .attributes_content,
                    .discount {
                    background-color: #222 !important;
                    }
                    .email-masthead_name {
                    text-shadow: none !important;
                    }
                }
                
                :root {
                    color-scheme: light dark;
                    supported-color-schemes: light dark;
                }
                </style>
                <style type="text/css">
                    .f-fallback  {
                    font-family: Arial, sans-serif;
                    }
                </style>
            </head>
            <body>
                <span class="preheader">This is a receipt for your recent payment on BetaLife. No payment is due with this receipt.</span>
                <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                    <td align="center">
                        <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <tr>
                            <td class="email-masthead">
                            <a href="'.base_url('').'" class="f-fallback email-masthead_name">
                                <img src="'.base_url('/webapp/images/betalife-health-new-logo.png').'" height="80px" />
                            </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="email-body" width="570" cellpadding="0" cellspacing="0">
                            <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                <td class="content-cell">
                                    <div class="f-fallback">
                                    <h1>Hello '.session('name').',</h1>
                                    <p>Thank you for using BetaLife Health Services. This email is the receipt for your payment made.</p>
                                    <p>This purchase will appear with a "betalife__" prefix on the transaction reference.</p>
                                    <table class="purchase" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                        <tr>
                                        <td></td>
                                        <td>
                                            <h3 class="align-right">'.date("F jS, Y").'</h3></td>
                                        </tr>
                                        <tr>
                                        <td colspan="2">
                                            <table class="purchase_content" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <th class="purchase_heading" align="left">
                                                <p class="f-fallback">Description</p>
                                                </th>
                                                <th class="purchase_heading" align="right">
                                                <p class="f-fallback">Amount</p>
                                                </th>
                                            </tr>
                                            
                                            <tr>
                                                <td width="50%" class="purchase_item"><span class="f-fallback">Payment</span></td>
                                                <td class="align-right" width="50%" class="purchase_item"><span class="f-fallback">'.$currency.' '.number_format($amount, 2).'</span></td>
                                            </tr>
                                            </table>
                                        </td>
                                        </tr>
                                    </table>
                                    <p>If you have any questions about this receipt, simply an email to <a href="mailto:hello@betalifehealth.com">hello@betalifehealth.com</a> or reach out to our <a href="https://betalifehealth.com/contact-us">support team</a> for help.</p>
                                    <p>Cheers,
                                        <br>The BetaLife team</p>
                                    </div>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                <td class="content-cell" align="center">
                                    <p class="f-fallback sub align-center">
                                    BetaLife Health Services
                                    <br>No. 6 Otunba Ogungbe Crescent, 
                                    <br>Lekki Phase 1, Lagos, Nigeria.
                                    </p>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                </table>
            </body>
        </html>';

        if ($email) {
            if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                sendEmail($email, $this->subject, $receiptHtml);
            }
        }
    }

    public function sendEmailForBloodDonationPaymentDone($requestID)
    {
        $this->subject = 'Pending Delivery!';
        $this->bannerImage = 'pending-delivery.png';

        $builder = $this->db->table('blood_request_delivery_tbl');
        $builder->where('request_id', $requestID);
        $query = $builder->get();
        $eligibleDonors = $query->getResult();

        if ($eligibleDonors) {
            $eligibleEmails = [];

            foreach ($eligibleDonors as $key => $value) {
                if ($value->donor_type == 'hospital') {
                    $table_to_use = 'hospitals_tbl';
                }
                elseif ($value->donor_type == 'blood-bank') {
                    $table_to_use = 'blood_banks_tbl';
                }
                elseif ($value->donor_type == 'pharmacy') {
                    $table_to_use = 'pharmacies_tbl';
                }
                else {
                    $table_to_use = 'users_tbl';
                }

                // Get Email
                $builder = $this->db->table($table_to_use);
                $builder = $builder->select('auth_tbl.email');
                $builder = $builder->join('auth_tbl', 'auth_tbl.id = '.$table_to_use.'.auth_id');
                $builder = $builder->where('auth_tbl.acct_type', $value->donor_type);
                $builder = $builder->where($table_to_use.'.id', $value->donor_id);
                $builder = $builder->distinct();
                $builder = $builder->get();
                $result = $builder->getRow();

                if ($result) {
                    if (!in_array($result->email, $eligibleEmails)) {
                        $eligibleEmails[] = $result->email;
                    }
                }
            }

            if (count($eligibleEmails)) {
                $body = '<div class="text" style="padding: 0 2.5em; text-align: center;">
                    <h2>Pending Delivery</h2>
                    <p style="text-align: left; font-size: 18px;">Payment has been confirmed for blood request #'.$requestID.'. Delivery is now pending. Kindly log on to your dashboard to attend to this request\'s delivery accordingly.</p>
                    <p><a href="'.base_url('login').'" class="btn btn-primary">Go to Dashboard</a></p>
                </div>';
        
                $htmlMsg = preg_replace('/{{ body_here }}/', $body, $this->htmlBody);
                $htmlMsg = str_replace('%7B%7B%20banner_image_here%20%7D%7D', $this->bannerImage, $htmlMsg);

                foreach ($eligibleEmails as $value) {
                    if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                        sendEmail($result->email, $this->subject, $htmlMsg);
                    }
                }
            }
        }
    }
}
