<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HospitalModel;

class HospitalController extends BaseController
{
    public function index()
    {
        $url = $_SERVER['PATH_INFO'];
        $exploded_url = explode('/', $url);
        $urlSlug = '';
    
        if (isset($exploded_url[2])) {
            $urlSlug = $exploded_url[2];
        }

        $hospitalModel = new HospitalModel();

        $data = [];

        $data['hospital_details'] = $urlSlug ? $hospitalModel->findAccountWithSlug($urlSlug) : '';

        if ($data['hospital_details']) {
            $data['all_blood_type'] = $this->getAllBloodGroups();
        }

        echo view('webapp/visiting-hospital', $data);
    }

    public function recordVistForHospital()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];
        helper(['form']);
        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();

            $url = $_SERVER['PATH_INFO'];
            $exploded_url = explode('/', $url);
            $urlSlug = '';
        
            if (isset($exploded_url[2])) {
                $urlSlug = $exploded_url[2];
            }

            $hospitalInformation = $urlSlug ? $hospitalModel->findAccountWithSlug($urlSlug) : '';
            
            $rules = [
                "wp_phone" => [
                    "label" => "Phone", 
                    "rules" => "required"
                ],
                "wp_name" => [
                    "label" => "Name", 
                    "rules" => "required",
                ],
                "wp_email" => [
                    "label" => "Email", 
                    "rules" => "required|valid_email",
                ],
                "wp_blood_type" => [
                    "label" => "Email", 
                    "rules" => "permit_empty|min_length[2]",
                ],
                "wp_genotype" => [
                    "label" => "Genotype", 
                    "rules" => "permit_empty|min_length[2]",
                ],
                "wp_gender" => [
                    "label" => "Gender", 
                    "rules" => "permit_empty|min_length[2]",
                ],
                "wp_marital_status" => [
                    "label" => "Marital Status", 
                    "rules" => "permit_empty|min_length[2]",
                ],
                "wp_address" => [
                    "label" => "Address", 
                    "rules" => "permit_empty|min_length[5]",
                ],
                "wp_dob" => [
                    "label" => "Email", 
                    "rules" => "required|valid_date",
                ],
            ];

            if ($this->validate($rules)) {
                $arrayOfSubmittedParams = [
                    "fullname" => $this->request->getVar("wp_name"),
                    "phone" => $this->request->getVar("wp_phone"),
                    "email" => $this->request->getVar("wp_email"),
                    "gender" => $this->request->getVar("wp_gender"),
                    "marital_status" => $this->request->getVar("wp_marital_status"),
                    "address" => $this->request->getVar("wp_address"),
                    "blood_group" => $this->request->getVar("wp_blood_type"),
                    "genotype" => $this->request->getVar("wp_genotype"),
                    "dob" => $this->request->getVar("wp_dob"),
                    "hospital_id" => $hospitalInformation->id,
                ];

                $hospitalModel->submitUserVisitingTime($arrayOfSubmittedParams);
                
                $response = [
                    'status' => 200,
                    'message' => $hospitalModel->message,
                ];
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function fetchVisitorsDetails()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];
        helper(['form']);
        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();
            
            $rules = [
                "wp_phone" => [
                    "label" => "Phone", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $getVisitorsDetails = $hospitalModel->getVisitorsDetails($this->request->getVar("wp_phone"));

                $response['status'] = 200;

                if ($getVisitorsDetails) {
                    $response['details'] = [
                        "wp_name" => $getVisitorsDetails->fullname,
                        "wp_email" => $getVisitorsDetails->email,
                        "wp_blood_type" => $getVisitorsDetails->blood_group,
                        "wp_genotype" => $getVisitorsDetails->genotype,
                        "wp_dob" => date("Y-m-d", strtotime($getVisitorsDetails->dob)),
                        "wp_gender" => $getVisitorsDetails->gender,
                        "wp_marital_status" => $getVisitorsDetails->marital_status,
                        "wp_address" => $getVisitorsDetails->address,
                    ];
                }
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function fetchVisitorsDetailsPreview()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];
        helper(['form']);
        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();
            
            $rules = [
                "wp_phone" => [
                    "label" => "Phone", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $getVisitorsDetails = $hospitalModel->getVisitorsDetails($this->request->getVar("wp_phone"));

                $response['status'] = 200;

                if ($getVisitorsDetails) {
                    $response['details'] = [
                        "Name" => $getVisitorsDetails->fullname,
                        "Email Address" => $getVisitorsDetails->email,
                        "Blood Type" => $getVisitorsDetails->blood_group,
                        "Genotype" => $getVisitorsDetails->genotype,
                        "Date of Birth" => date("F jS, Y", strtotime($getVisitorsDetails->dob)),
                        "Gender" => $getVisitorsDetails->gender,
                        "Marital Status" => $getVisitorsDetails->marital_status,
                        "Address" => $getVisitorsDetails->address,
                    ];
                }
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function saveVisitorMedicalRecord()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];

        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();
            
            $rules = [
                "wp_pulse" => [
                    "label" => "Pulse", 
                    "rules" => "required"
                ],
                "wp_blood_pressure" => [
                    "label" => "Blood Pressure", 
                    "rules" => "required"
                ],
                "wp_weight" => [
                    "label" => "Weight", 
                    "rules" => "required"
                ],
                "wp_hbpcv" => [
                    "label" => "HB/PCV", 
                    "rules" => "required"
                ],
                "wp_phone" => [
                    "label" => "Phone", 
                    "rules" => "required"
                ]
            ];

            if ($this->validate($rules)) {
                $getVisitorsDetails = $hospitalModel->getVisitorsDetails($this->request->getVar("wp_phone"));

                if ($getVisitorsDetails) {
                    $accountAuthID = session("auth_id");
                    $accountAuthType = session("acct_type");
                    $getEstablishmentInformation = $this->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);

                    $results = $this->getNumberOfVisitsForVisitor($getVisitorsDetails->id, $getEstablishmentInformation->id, '');

                    if ($results) {
                        if (date('Y-m-d') == date('Y-m-d', $results->visited_on) || true) {
                            $hospitalModel = new HospitalModel();

                            $pulseValue = $this->request->getVar('wp_pulse');
                            $bloodPressureValue = $this->request->getVar('wp_blood_pressure');
                            $weightValue = $this->request->getVar('wp_weight');
                            $hbpcvValue = $this->request->getVar('wp_hbpcv');

                            $data = [
                                'pulse' => $pulseValue,
                                'blood_pressure' => $bloodPressureValue,
                                'weight' => $weightValue,
                                'hbpcv' => $hbpcvValue,
                            ];

                            if ($hospitalModel->updateMedicalCheckForVisitor($results->id, $data)) {
                                $subject = "Medical Check Up Summary - BetaLife";

                                $email_message = "<p>Hello there,</p>";
                                $email_message .= "<p></p>";
                                $email_message .= "<p>See below your medical summary for today ".date('jS F, Y')."</p>";
                                $email_message .= "<p>Pulse Rate: ".$pulseValue."</p>";
                                $email_message .= "<p>Blood Pressure: ".$bloodPressureValue."</p>";
                                $email_message .= "<p>Weight (KG): ".$weightValue."</p>";
                                $email_message .= "<p>HB/PCV: ".$hbpcvValue."</p>";
                                $email_message .= "<p></p> <p>Best Regards,</p><p>BetaLife</p>";

                                if (strpos(getenv("app.baseURL"), 'dashboard') !== FALSE) {
                                    // Send OTP to Email
                                    sendEmail($getVisitorsDetails->email, $subject, $email_message);
                                }

                                $response['status'] = '200';
                                $response['message'] = 'Medical Record saved successfully. A copy has also been sent to the patient\'s mail.';
                            }
                            else {
                                $response['status'] = '401';
                                $response['message'] = 'We could not complete your request at this time. Please try again.';
                            }
                        }
                        else {
                            $response['status'] = '401';
                            $response['message'] = 'You cannot submit a record for an already past date.';
                        }
                    }
                }
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function saveVisitorAdditionalMedicalRecord()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];

        helper(['form']);

        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();
            
            $rules = [
                "wp_type_blood_donor" => [
                    "label" => "Type of Blood Donor", 
                    "rules" => "required"
                ],
                "wp_donor_deferral_reason" => [
                    "label" => "Reason for Donor Deferral", 
                    "rules" => "required"
                ]
            ];

            if ($this->request->getVar('wp_donor_deferral_reason') == 'Others') {
                $rules['wp_others_reason'] = [
                    "label" => "Other Reason", 
                    "rules" => "required"
                ];
            }

            if ($this->validate($rules)) {
                $getVisitorsDetails = $hospitalModel->getVisitorsDetails($this->request->getVar("wp_phone"));

                if ($getVisitorsDetails) {
                    $accountAuthID = session("auth_id");
                    $accountAuthType = session("acct_type");
                    $getEstablishmentInformation = $this->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);

                    $results = $this->getNumberOfVisitsForVisitor($getVisitorsDetails->id, $getEstablishmentInformation->id, '');

                    if ($results) {
                        if (date('Y-m-d') == date('Y-m-d', $results->visited_on) || true) {
                            $hospitalModel = new HospitalModel();

                            $data = [
                                'type_blood_donor' => $this->request->getVar('wp_type_blood_donor'),
                                'donor_deferral_reason' => $this->request->getVar('wp_donor_deferral_reason'),
                                'others_reason' => $this->request->getVar('wp_others_reason'),
                            ];

                            if ($hospitalModel->updateMedicalCheckForVisitor($results->id, $data)) {
                                $response['status'] = '200';
                                $response['message'] = 'Additional Medical Record saved successfully.';
                            }
                            else {
                                $response['status'] = '401';
                                $response['message'] = 'We could not complete your request at this time. Please try again.';
                            }
                        }
                        else {
                            $response['status'] = '401';
                            $response['message'] = 'You cannot submit a record for an already past date.';
                        }
                    }
                }
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }

    public function fetchVisitorsMedicalDetails()
    {
        $response = [
            "status" => 401,
            "message" => "There was an error in your form. Please try again.",
        ];
        helper(['form']);
        // Only post requests are accepted
        if ($this->request->getMethod() == "post") {
            $validation =  \Config\Services::validation();
            $hospitalModel = new HospitalModel();
            
            $rules = [
                "wp_phone" => [
                    "label" => "Phone", 
                    "rules" => "required"
                ],
            ];

            if ($this->validate($rules)) {
                $getVisitorsDetails = $hospitalModel->getVisitorsDetails($this->request->getVar("wp_phone"));

                $response['status'] = 200;

                if ($getVisitorsDetails) {
                    $accountAuthID = session("auth_id");
                    $accountAuthType = session("acct_type");
                    $getEstablishmentInformation = $this->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);
                    $medicalRecords = $this->getNumberOfVisitsForVisitor($getVisitorsDetails->id, $getEstablishmentInformation->id, '');

                    if ($medicalRecords) {
                        if ($medicalRecords->pulse && $medicalRecords->blood_pressure) {
                            $response['details'] = [
                                "Pulse Rate" => $medicalRecords->pulse,
                                "Blood Pressure" => $medicalRecords->blood_pressure,
                                "Weight (KG)" => $medicalRecords->weight,
                                "HB/PCV" => $medicalRecords->hbpcv,
                                "Type of Blood Donor" => $medicalRecords->type_blood_donor,
                                "Reason for Donor Deferral" => $medicalRecords->donor_deferral_reason,
                            ];

                            if (trim($medicalRecords->others_reason)) {
                                $response['details']['Reason for Donor Deferral'] = $medicalRecords->others_reason;
                            }
                        }
                    }
                }
            }
            else {
                $response['validation'] = $validation->getErrors();
            }
        }

        return $this->response->setJSON($response);
    }
}
