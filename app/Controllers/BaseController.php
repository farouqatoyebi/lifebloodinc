<?php

namespace App\Controllers;

use App\Models\BloodBankModel;
use App\Models\HospitalModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use Phpqrcode\QRcode;
use App\Libraries\Paystack;

use CodeIgniter\API\ResponseTrait;
use DateTime;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */

    use ResponseTrait;

    protected $request;

    protected $webmail  = "mailer@betalifehealth.com";
    protected $app_name = "Betalife Health Services";

    // Defines the image path
    protected $img_path = "./uploads/user/img/";
    protected $thumb_path = "./uploads/user/img/thumb/";

    protected $bloodbank_img_path = "./uploads/bloodbanks/img/";
    protected $bloodbank_thumb_path = "./uploads/bloodbanks/img/thumb/";
    
    protected $qrcode_path  = '/generator/qr/';
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['myfunctions','notification'];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function getAllCountriesList()
    {
        $db = Database::connect();
        $builder = $db->table('countries_tbl')->get();

        return $builder->getResult();
    }

    public function getAllStatesList()
    {
        $db = Database::connect();
        $builder = $db->table('states_tbl')->get();

        return $builder->getResult();
    }

    public function getAllCitiesList()
    {
        $db = Database::connect();
        $builder = $db->table('cities_tbl')->get();

        return $builder->getResult();
    }

    public function getPassedCityID($cityName, $stateID, $countryID)
    {
        $db = Database::connect();
        $builder = $db->table('cities_tbl');
        $builder = $builder->where('name', trim($cityName));
        $builder = $builder->limit(1);
        $builder = $builder->get();
        $result = $builder->getRow();

        if ($result) {
            return $result->id;
        }
        else {
            $builder = $db->table('cities_tbl');
            $builder->insert([
                'name' => $cityName,
                'state_id' => $stateID,
                'country_id' => $countryID,
                'created_at' => time(),
            ]);
            $lastInsertId = $db->insertID();

            return $lastInsertId;
        }

        return 0;
    }

    public function isUserSignedIn()
    {
        if (!session('isLoggedIn')) {
            header('Location: '.base_url().'/login');
            exit();
        }
    }

    public function webAppSetLoginInformation($data)
    {
        $session = session();
        $arrayOfSignedInValue = [];

        if (isset($data->id)) {
            if ($data->status && $data->acct_status == 'active') {
                $accountName = $this->getUserProfileInformationBasedOnType($data->acct_type, $data->id);

                $arrayOfSignedInValue['isLoggedIn'] = true;
                $arrayOfSignedInValue['auth_id'] = $data->id;
                $arrayOfSignedInValue['name'] = $accountName->name;
                $arrayOfSignedInValue['email'] = $data->email;
                $arrayOfSignedInValue['phone'] = $data->phone;
                $arrayOfSignedInValue['acct_type'] = $data->acct_type;
                $arrayOfSignedInValue['status'] = $data->status;
                $arrayOfSignedInValue['logo'] = $accountName->logo;

                if ($data->acct_type == 'hospital') {
                    $arrayOfSignedInValue['slug'] = $accountName->url_slug;
                    $arrayOfSignedInValue['slugQR'] = $accountName->slug_qr_code;
                }
            }
        }

        $session->set($arrayOfSignedInValue);

        return true;
    }

    public function webAppAdminSetLoginInformation($data)
    {
        $session = session();
        $arrayOfSignedInValue = [];

        if (isset($data->id)) {
            $arrayOfSignedInValue['adminIsLoggedIn'] = true;
            $arrayOfSignedInValue['auth_admin_id'] = $data->id;
            $arrayOfSignedInValue['admin_name'] = $data->first_name.' '.$data->last_name;
            $arrayOfSignedInValue['admin_first_name'] = $data->last_name;
            $arrayOfSignedInValue['admin_last_name'] = $data->first_name;
            $arrayOfSignedInValue['admin_email'] = $data->email;
            $arrayOfSignedInValue['profile_photo'] = $data->profile_photo;
        }

        $session->set($arrayOfSignedInValue);

        return true;
    }

    public function confirmAccountCompletion()
    {
        $user_id = session()->get('auth_id');
        $user_type = session()->get('acct_type');
        $profileInformation = $this->getUserProfileInformationBasedOnType($user_type, $user_id);

        $redirectToProfilePage = false;

        if ($profileInformation) {
            if ($profileInformation->reg_no == session('email')) {
                $redirectToProfilePage = true;
            }

            $user = new UserModel();
            $userInfo = (object) $user->findUser(session('email'));

            if ($userInfo->status == '1' && $userInfo->acct_status == 'inactive') {
                $db = Database::connect();
                $builder = $db->table('auth_tbl');
                $builder = $builder->where('id', $user_id);
                $builder->update([
                    "acct_status" => "active",
                ]);
            }
        }
        
        if ($redirectToProfilePage) {
            header('Location: '.base_url().'/profile');
            exit();
        }

        if ($user_type == 'hospital') {
            $hospitalModel = new HospitalModel();

            $hospitalModel->confirmURLSlugInformation($profileInformation->id);
        }
        elseif ($user_type == 'blood-bank') {
            $bloodBankModel = new BloodBankModel();

            $bloodBankRates = $bloodBankModel->bloodBankBloodGroupRates($profileInformation->id);

            if (!$bloodBankRates) {
                header('Location: '.base_url().'/settings/set-rates');
                exit();
            }
        }
    }

    public function getUserProfileInformationBasedOnType($accountType, $accountAuthID)
    {
        $userProfileMoreInformation = '';

        // Check if user has completed their profile upload.
        if ($accountType == 'hospital') {
            $hospitalModel = new HospitalModel();

            $userProfileMoreInformation = $hospitalModel->findAccount($accountAuthID);
        }
        elseif ($accountType == 'blood-bank') {
            $bloodBankModel = new BloodBankModel();

            $userProfileMoreInformation = $bloodBankModel->findAccount($accountAuthID);
        }
        elseif ($accountType == 'user') {
            $userModel = new UserModel();
            
            $userProfileMoreInformation = $userModel->findUser($accountAuthID);
        }

        return $userProfileMoreInformation;
    }

    public function getAccountInformationBasedOnID($accountType, $accountID)
    {
        $userProfileMoreInformation = '';

        // Check if user has completed their profile upload.
        if ($accountType == 'hospital') {
            $hospitalModel = new HospitalModel();

            $userProfileMoreInformation = $hospitalModel->find($accountID);
        }
        elseif ($accountType == 'blood-bank') {
            $bloodBankModel = new BloodBankModel();

            $userProfileMoreInformation = $bloodBankModel->find($accountID);
        }
        elseif ($accountType == 'user') {
            $userModel = new UserModel();
            
            $userProfileMoreInformation = $userModel->find($accountID);
        }

        return $userProfileMoreInformation;
    }

    public function moveUploadedFileToDestination($field_name, $image_type = 'logo', $is_image = true)
    {
        if($file = $this->request->getFile($field_name)) {
            if ($file->isValid() && ! $file->hasMoved()) {
                $newName = date('YmdHis__').rand(10000, 9999999).'.'.$file->getExtension();
                if ($is_image) {
                    $file->move(ROOTPATH . 'public/uploads/images/'.$image_type, $newName);
                }
                else {
                    $file->move(ROOTPATH . 'public/uploads/'.$image_type, $newName);
                }

                // if ($is_image) {
                //     $imageService = \Config\Services::image();
                //     $this->img_path = './uploads/images/'.$image_type.'/';
                //     $this->thumb_path = $this->img_path.'thumbnail/';
                //     if($file->move($this->img_path, $newName)) {
                //         // echo'<pre>'; var_dump($this->thumb_path); echo'</pre>'; exit();
                //         $imageService->withFile($this->img_path.$newName)
                //             ->resize(350, 350)
                //             ->save($this->thumb_path.$newName);
                //             // echo'<pre>'; var_dump($this->img_path.$newName, $this->thumb_path.$newName); echo'</pre>'; exit();
                //     }
                // }

                return $newName;
            }
            elseif (session($image_type)) {
                return session($image_type);
            }
        }

        return false;
    }

    public function getLocationValueBasedOnType($location_id, $location_type, $column_to_return = 'name')
    {
        // To reference this inside other controllers, you use SELF not $this
        $db = Database::connect();
        $returnedValue = '';

        if ($location_type == 'country') {
            $builder = $db->table('countries_tbl')->where('id', $location_id)->get();

            $returnedValue = $builder->getRow();
        }
        elseif ($location_type == 'state') {
            $builder = $db->table('states_tbl')->where('id', $location_id)->get();

            $returnedValue = $builder->getRow();
        }
        elseif ($location_type == 'city') {
            $builder = $db->table('cities_tbl')->where('id', $location_id)->get();

            $returnedValue = $builder->getRow();
        }

        if ($returnedValue) {
            return $returnedValue->$column_to_return;
        }

        return '';
    }

    public function getAllBloodGroups()
    {
        $db = Database::connect();
        $builder = $db->table('blood_group_tbl')->get();

        return $builder->getResult();
    }

    public function getAllUrgencyLevels()
    {
        $db = Database::connect();
        $builder = $db->table('urgency_tbl')->get();

        return $builder->getResult();
    }

    public function getEveryBreakdownUnderBloodRequest($request_id)
    {
        $db = Database::connect();
        $builder = $db->table('requests_blood_group_tbl');
        $builder = $builder->where('request_id', $request_id);
        $builder = $builder->get();

        return $builder->getResult();
    }
    
    public function createSlugFromName($string){
        $string = $this->removeStringAccents($string);
        $string = $this->convertSymbolsToWords($string);
        $string = strtolower($string); // Force lowercase
        $space_chars = array(
            " ", // space
            "…", // ellipsis
            "–", // en dash
            "—", // em dash
            "/", // slash
            "\\", // backslash
            ":", // colon
            ";", // semi-colon
            ".", // period
            "+", // plus sign
            "#", // pound sign
            "~", // tilde
            "_", // underscore
            "|", // pipe
        );

        foreach($space_chars as $char){
            $string = str_replace($char, '-', $string); // Change spaces to dashes
        }

        // Only allow letters, numbers, and dashes
        $string = preg_replace('/([^a-zA-Z0-9\-]+)/', '', $string);
        $string = preg_replace('/-+/', '-', $string); // Clean up extra dashes

        if (substr($string, -1)==='-'){ // Remove - from end
            $string = substr($string, 0, -1);
        }

        if (substr($string, 0, 1) === '-'){ // Remove - from start
            $string = substr($string, 1);
        }

        return $string;
    }

    public function removeStringAccents($string) {
        if(!preg_match('/[\x80-\xff]/', $string)){
        return $string;
        }
        if($this->seems_utf8($string)){
            $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
            // Euro Sign
            chr(226).chr(130).chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194).chr(163) => '');
            $string = strtr($string, $chars);
        } 
        else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
            .chr(252).chr(253).chr(255);
            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }
        return $string;
    }

    public function convertSymbolsToWords($output){
        $output = str_replace('@', ' at ', $output);
        $output = str_replace('%', ' percent ', $output);
        $output = str_replace('&', ' and ', $output);
        return $output;
    }

    public function seems_utf8($str) {
        $length = strlen($str);

        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; # 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }

        return true;
    }

    public function generateHospitalLinkQRCode($qr_url)
    {
        $Qr = new Qrcode();
    
        $qr_id = base64_encode(uniqid().time());


        $filename = "qr_".time().".png";

        $path = FCPATH.$this->qrcode_path.$filename;

        $Qr->png($qr_url, $path, 'H', 20);

        $data = (object) ['id' => $qr_id, 'file' => $filename, 'path' => $path];
        
        return $data;
    }

    public function getBloodBankRateForBloodGroup($bloodBankID, $bloodGroupName)
    {
        $db = Database::connect();
        $builder = $db->table('blood_banks_blood_group_rate_tbl');
        $builder->where('blood_bank_id', $bloodBankID);
        $builder->where('blood_group', $bloodGroupName);
        $query = $builder->get();
        $result = $query->getRow();

        if ($result) {
            return $result->rate;
        }

        return 0;
    }

    public function getNumberOfVisitsForVisitor($visitor_id, $hospital_id, $result_type = 'count')
    {
        $db = Database::connect();
        $builder = $db->table('visiting_date_tbl');
        $builder->where('hospital_id', $hospital_id);
        $builder->where('visitors_id', $visitor_id);
        if ($result_type == 'count') {
            $allPendingRequestsSentOut = $builder->countAllResults();
        }
        else {
            $builder = $builder->orderby('visited_on', 'desc');
            $builder = $builder->limit(1);
            $builder = $builder->get();
            $allPendingRequestsSentOut = $builder->getRow();
        }

        return $allPendingRequestsSentOut;
    }

    public function getNumberOfDailyVisitsForHospital($hospital_id)
    {
        $db = Database::connect();
        $builder = $db->table('visiting_date_tbl');
        $builder->where('hospital_id', $hospital_id);
        $builder->where('visiting_date_tbl.visited_on BETWEEN '.strtotime(date('Y-m-d 00:00:00')).' AND '.strtotime(date('Y-m-d 23:59:59')));
        $allPendingRequestsSentOut = $builder->countAllResults();

        return $allPendingRequestsSentOut;
    }

    public function getServiceChargeFee($app_type, $return_column = 'amount')
    {
        $db = Database::connect();
        $builder = $db->table('betalife_rate_tbl');
        $builder->where('service_type', $app_type);
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(1);
        $result = $builder->get();
        $result = $result->getRow();

        if ($result && $return_column) {
            return $result->$return_column;
        }

        return $result;
    }
    
    public function generateOTP($length = 0)
    {
        $otpVal = random_int(100000, 999999);

        if ($length) {
            if (mb_strlen($otpVal) > $length) {
                $otpVal = substr($otpVal, 0, $length);
            }
        }

       return $otpVal;
    }

    public function hasGeneratedOldRequest($requestID)
    {
        $db = Database::connect();
        $builder = $db->table('requests_tbl');
        $builder->where('old_request_id', $requestID);
        $builder->where('deleted_at', NULL);
        $builder->limit(1);
        $result = $builder->get();
        $result = $result->getRow();

        return $result;
    }

    public function saveDeliveryInformationIntoDB($data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (!count($data)) {
            return false;
        }

        $db = Database::connect();
        $builder = $db->table('blood_request_delivery_tbl');
        $builder->insert($data);

        return false;
    }

    public function getAvailableBanks()
    {
        $db = Database::connect();
        $paystack = new Paystack();
        $builder = $db->table('available_banks_tbl');
        $result = $builder->get();
        $result = $result->getResult();

        if (!$result) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.paystack.co/bank',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$paystack->get_public_key()
                ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response);
            curl_close($curl);

            foreach ($response->data as $key => $value) {
                $builder = $db->table('available_banks_tbl');
                $builder->where('bank_id', $value->id);
                $result = $builder->get();
                $result = $result->getRow();
                
                if (!$result) {
                    $builder = $db->table('available_banks_tbl');
                    $builder->insert([
                        'name' => $value->name,
                        'code' => $value->code,
                        'country' => $value->country,
                        'currency' => $value->currency,
                        'type' => $value->type,
                        'bank_id' => $value->id,
                        'created_at' => time(),
                    ]);
                }
            }

            return $this->getAvailableBanks();
        }
        
        return $result;
    }

    public function verifyBankAccountInformation($accountNumber, $accountCode)
    {
        $paystack = new Paystack();

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.paystack.co/bank/resolve?account_number='.$accountNumber.'&bank_code='.$accountCode,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$paystack->get_secret_key()
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);

        if ($response) {
            if ($response->status) {
                return $response->data;
            }
        }

        return false;
    }

    public function createTransferRecipient($data)
    {
        $paystack = new Paystack();

        $url = "https://api.paystack.co/transferrecipient";
        $fields =$data;

        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Authorization: Bearer ".$paystack->get_secret_key(),
          "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);
        $response = json_decode($result);

        if ($response) {
            if ($response->status) {
                return $response->data;
            }
        }
        
        
        return false;
    }

    public function getBankInformationFromDB($bankCode)
    {
        $db = Database::connect();
        $builder = $db->table('available_banks_tbl');
        $builder->where('code', $bankCode);
        $result = $builder->get();
        $result = $result->getRow();

        return $result;
    }

    public function initiateBulkTransfer($recipientAccountCode, $amount)
    {
        $paystack = new Paystack();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.paystack.co/transfer/bulk',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "currency": "NGN",
            "source": "balance",
            "transfers": [
                {
                    "amount": '.$amount.',
                    "recipient": "'.$recipientAccountCode.'"
                }
            ]
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$paystack->get_secret_key(),
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);

        if ($response) {
            if ($response->status) {
                return $response->data;
            }
        }
        
        return false;
    }

    public function isAccountInformationComplete()
    {
        # code...
    }

    public function bloodInventoryDetails($auth_id, $auth_type, $blood_group)
    {
        $db = Database::connect();
        $builder = $db->table('blood_group_stock');
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->where('blood_group', $blood_group);
        $builder = $builder->get();
        $result = $builder->getRow();

        return $result;
    }

    public function timespan($datetime)
    {
        // $this->load->helper('date');

        //client created date get from database
        $date = $datetime;

        // Declare timestamps
        $last = new DateTime($date);
        $now = new DateTime( date( 'Y-m-d h:i:s', time() )) ; 

        // Find difference
        $interval = $last->diff($now);

        // Store in variable to be used for calculation etc
        $years = (int)$interval->format('%Y');
        $months = (int)$interval->format('%m');
        $days = (int)$interval->format('%d');
        $hours = (int)$interval->format('%H');
        $minutes = (int)$interval->format('%i');

        $timeSpan = '';

        if($years > 0) {
            $timeSpan .= $years.' Years ';
        }
        if($months > 0) {
            $timeSpan .= $months.' Months ';
        }
        if($days > 0) {
            $timeSpan .= $days.' Days ';
        }
        if($hours > 0) {
            $timeSpan .=  $hours.' Hours ';
        }
        if ($minutes > 0) {
            $timeSpan .= $minutes.' minutes ';
        }

        if (!$timeSpan) {
            $timeSpan = "just now";
        }
        else {
            $timeSpan .= 'ago';
        }

        return trim($timeSpan);
    }

    public function topRatedInstitutions($auth_id, $type)
    {
        $arrayOfResponseValues = $response = [];

        if ($type == 'hospital') {
            $hospitalModel = new HospitalModel();
            $response = $hospitalModel->getBloodBanksHospitalHasDoneWithBusinessWith($auth_id, '');
        }
        elseif ($type == 'blood-bank') {
            $bloodBankModel = new BloodBankModel();
            $response = $bloodBankModel->getHospitalsBloodBankHasDoneWithBusinessWith($auth_id, '');
        }

        if (count($response)) {
            foreach ($response as $key => $value) {
                if (isset($value->donor_type)) {
                    $value->auth_type = $value->donor_type;
                    $value->auth_id = $value->donor_id;
                }
                $information = (object) $this->getAccountInformationBasedOnID($value->auth_type, $value->auth_id);

                $arrayOfResponseValues[] = [
                    'name' => $information->name,
                    'address' => $information->Address,
                    'logo' => (file_exists('uploads/images/logo/'.$information->logo) && $information->logo) ? base_url().'/uploads/images/logo/'.$information->logo : base_url().'/webapp/images/doctors-image-small.png',
                ];
            }
        }

        return $arrayOfResponseValues;
    }

    public function getLastFiveHospitalVisitors($hospitalID)
    {
        $hospitalModel = new HospitalModel();
        $visitors = $hospitalModel->getLastFiveVisitors($hospitalID);

        return $visitors;
    }
}