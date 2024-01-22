<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Notifications;

class NotificationsController extends BaseController
{
    public function __construct()
    {
        $this->arrayOfAcceptableAccountTypes = [
            'hospital', 'blood-bank'
        ];

        $this->accountAuthID = session("auth_id");
        $this->accountAuthType = session("acct_type");
        $this->user_info = $this->getUserProfileInformationBasedOnType($this->accountAuthType, $this->accountAuthID);

        $this->isUserSignedIn();
        $this->notifiationsModel = new Notifications();
    }

    public function index() 
    {
        $data['v'] = 'notifications';
        $data['results']['page_title'] = 'Notifications';
        $data['results']['allNotifications'] = $this->notifiationsModel->getAllNotifications($this->user_info->id, $this->accountAuthType, false);

        echo view('webapp/template', $data);
    }

    public function setNotifications()
    {
        $this->notifiationsModel->setNotifications($this->user_info->id, $this->accountAuthType);
    }

    public function getNotifications()
    {
        $notifications = $this->notifiationsModel->getNotifications($this->user_info->id, $this->accountAuthType);

        return $this->response->setJSON($notifications);
    }
}
