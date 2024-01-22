<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;
use DateTime;

class Notifications extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'notifications_tbl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function setNotifications($auth_id, $auth_type)
    {
        $notificationTypes = [];
        $action_type = "";
        $basecontroller = new BaseController();

        if ($auth_type == 'blood-bank') {
            $sql = "SELECT rtb.auth_id, rtb.auth_type, rtb.id as request_id
                FROM requests_tbl rtb
                WHERE NOT EXISTS (
                    SELECT id 
                    FROM request_donors_tbl rdtb
                    WHERE rdtb.request_id = rtb.id
                        AND rdtb.donor_id = ".$auth_id."
                        AND rdtb.donor_type = '".$auth_type."'
                )
                AND NOT EXISTS (
                    SELECT id 
                    FROM notifications_tbl ntb
                    WHERE ntb.action_id = rtb.id
                        AND ntb.action_type = 'request'
                        AND ntb.auth_id = ".$auth_id."
                        AND ntb.auth_type = '".$auth_type."'
                )
                AND rtb.due_date > ".time()."";
            $action_type = "request";
            $crafted_msg = "A new request has been made by account_type_here - account_name_here. Click <a href=\"".base_url('/browse-blood-requests')."\" class=\"text-decoration-none text-primary\">here</a> to view requests.";
        }
        elseif ($auth_type == 'hospital') {
            $sql = "SELECT DISTINCT donor_id as auth_id, donor_type as auth_type, request_id
                FROM request_donors_tbl rdtb
                JOIN requests_tbl rtb ON rdtb.request_id = rtb.id 
                WHERE rtb.auth_id = ".$auth_id."
                    AND rtb.auth_type = '".$auth_type."'
                    AND rdtb.status = 'pending'
                    AND NOT EXISTS (
                        SELECT id 
                        FROM notifications_tbl ntb
                        WHERE ntb.action_id = rdtb.request_id
                            AND ntb.action_type = 'offer'
                            AND ntb.auth_id = ".$auth_id."
                            AND ntb.auth_type = '".$auth_type."'
                    )
                    AND rtb.due_date > ".time()."";
            $action_type = "offer";
            $crafted_msg = "A new offer has been sent by account_type_here - account_name_here. Click <a href=\"request_offer_url_here\" class=\"text-decoration-none text-primary\">here</a> to view offers.";
        }

        if ($sql) {
            $query = $this->db->query($sql);
            $allResults = $query->getResult();

            if (count($allResults)) {
                foreach ($allResults as $key => $value) {
                    $userInfo = (object) $basecontroller->getAccountInformationBasedOnID($value->auth_type, $value->auth_id);
                    if ($value->auth_type == 'individual') {
                        $account_type = "an Individual";
                        $account_name = $userInfo->firstname.' '.$userInfo->lastname;
                        $request_offer_url = "";
                    }
                    elseif ($value->auth_type == 'hospital') {
                        $account_type = "a Hospital";
                        $account_name = $userInfo->name;
                        $request_offer_url = base_url('/browse-blood-offers/'.$value->request_id);
                    }
                    elseif ($value->auth_type == 'blood-bank') {
                        $account_type = "a Blood Bank";
                        $account_name = $userInfo->name;
                        $request_offer_url = "";
                    }

                    $message = str_replace('account_type_here', $account_type, $crafted_msg);
                    $message = str_replace('account_name_here', $account_name, $message);
                    $message = str_replace('request_offer_url_here', $request_offer_url, $message);

                    $builder = $this->db->table($this->table);
                    $builder->insert([
                        'auth_id' => $auth_id,
                        'auth_type' => $auth_type,
                        'action_id' => $value->request_id,
                        'action_type' => $action_type,
                        'message' => $message,
                        'status' => 'pending',
                        'created_at' => time(),
                    ]);
                }
            }
        }
    }

    public function getNotifications($auth_id, $auth_type)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->where('status', 'pending');
        $builder = $builder->get();
        $result = $builder->getResult();
        $basecontroller = new BaseController();

        $messages = [];

        if (count($result)) {
            foreach ($result as $key => $value) {
                $builder = $this->db->table($this->table);
                $builder = $builder->where('auth_id', $auth_id);
                $builder = $builder->where('auth_type', $auth_type);
                $builder = $builder->where('id', $value->id);
                $builder->update([
                    'status' => 'sent',
                    'updated_at' => time(),
                ]);

                $messages[] = [
                    'time' => $basecontroller->timespan(date("Y-m-d H:i:s", $value->created_at)),
                    'message' => $value->message,
                    'type' => $value->action_type,
                ];
            }
        }

        return $messages;
    }

    public function getNotificationsOnly($auth_id, $auth_type)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->whereIn('status', ['pending', 'sent']);
        $builder = $builder->orderBy('created_at', 'DESC');
        $builder = $builder->limit(3);
        $builder = $builder->get();
        $result = $builder->getResult();

        $messages = [];

        if (count($result)) {
            foreach ($result as $key => $value) {
                $messages[] = [
                    'time' => date("jS M Y - h:i A", $value->created_at),
                    'message' => $value->message,
                    'type' => $value->action_type,
                ];
            }
        }

        return $messages;
    }

    public function getAllNotifications($auth_id, $auth_type, $limit = true)
    {
        $builder = $this->db->table($this->table);
        $builder = $builder->where('auth_id', $auth_id);
        $builder = $builder->where('auth_type', $auth_type);
        $builder = $builder->orderBy('created_at', 'DESC');
        if ($limit) {
            $builder = $builder->limit(3);
        }
        $builder = $builder->get();
        $result = $builder->getResult();

        $messages = [];

        if (count($result)) {
            foreach ($result as $key => $value) {
                $builder = $this->db->table($this->table);
                $builder = $builder->where('auth_id', $auth_id);
                $builder = $builder->where('auth_type', $auth_type);
                $builder = $builder->where('id', $value->id);
                $builder->update([
                    'status' => 'viewed',
                    'updated_at' => time(),
                ]);

                $messages[] = [
                    'time' => date("jS M Y - h:i A", $value->created_at),
                    'message' => $value->message,
                    'type' => $value->action_type,
                ];
            }
        }

        return $messages;
    }
}
