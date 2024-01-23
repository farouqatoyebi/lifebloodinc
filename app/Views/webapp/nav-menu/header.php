<?php
    use App\Controllers\BaseController;
    use App\Models\Notifications;

    $notifications = new Notifications();
    $basecontroller = new BaseController();

    $auth_type = session('acct_type');
    $auth_id = $basecontroller->getUserProfileInformationBasedOnType($auth_type, session('auth_id'));

    $allNotifications = $notifications->getNotificationsOnly($auth_id->id, $auth_type);
?>
<!DOCTYPE html>
<html lang="en">


<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $page_title; ?></title>
	
	<meta name="description" content="Some description for the page"/>
    <link rel="icon" type="image/png" sizes="16x16" href="#">
	<!-- <link href="<?php echo base_url().'/webapp/vendor/bootstrap-select/dist/css/bootstrap-select.min.css'; ?>" rel="stylesheet" type="text/css"/> -->
	<link href="<?php echo base_url().'/webapp/vendor/owl-carousel/owl.carousel.css'; ?>" rel="stylesheet" type="text/css"/>
	<link href="<?php echo base_url().'/webapp/css/style.css?v=1.0.2'; ?>" rel="stylesheet" type="text/css"/>
</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <div class="main-content">
            <!--**********************************
                Nav header start
            ***********************************-->
            <div class="nav-header">
                <a href="<?php echo base_url(); ?>" class="brand-logo">
                    <!-- <img class="logo-abbr" src="public/images/logo.png" alt=""> -->
                    <!-- <img class="logo-compact" src="public/images/logo-text.png" alt="">
                    <img class="brand-title" src="public/images/logo-text.png" alt=""> -->
                </a>

                <div class="nav-control">
                    <div class="hamburger">
                        <span class="line"></span><span class="line"></span><span class="line"></span>
                    </div>
                </div>
            </div>
            <!--**********************************
                Nav header end
            ***********************************-->
            
            <!--**********************************
                Header start
            ***********************************-->
            <div class="header">
                <div class="header-content">
                    <nav class="navbar navbar-expand">
                        <div class="collapse navbar-collapse justify-content-between">
                            <div class="header-left">
                                <div class="dashboard_bar">
                                    <?php echo $page_title; ?>
                                </div>
                            </div>

                            <ul class="navbar-nav header-right">
                                <li class="nav-item dropdown notification_dropdown">
                                    <a class="nav-link ai-icon" href="javascript:;" role="button" data-toggle="dropdown">
                                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21.75 14.8385V12.0463C21.7471 9.88552 20.9385 7.80353 19.4821 6.20735C18.0258 4.61116 16.0264 3.61555 13.875 3.41516V1.625C13.875 1.39294 13.7828 1.17038 13.6187 1.00628C13.4546 0.842187 13.2321 0.75 13 0.75C12.7679 0.75 12.5454 0.842187 12.3813 1.00628C12.2172 1.17038 12.125 1.39294 12.125 1.625V3.41534C9.97361 3.61572 7.97429 4.61131 6.51794 6.20746C5.06159 7.80361 4.25291 9.88555 4.25 12.0463V14.8383C3.26257 15.0412 2.37529 15.5784 1.73774 16.3593C1.10019 17.1401 0.751339 18.1169 0.75 19.125C0.750764 19.821 1.02757 20.4882 1.51969 20.9803C2.01181 21.4724 2.67904 21.7492 3.375 21.75H8.71346C8.91521 22.738 9.45205 23.6259 10.2331 24.2636C11.0142 24.9013 11.9916 25.2497 13 25.2497C14.0084 25.2497 14.9858 24.9013 15.7669 24.2636C16.548 23.6259 17.0848 22.738 17.2865 21.75H22.625C23.321 21.7492 23.9882 21.4724 24.4803 20.9803C24.9724 20.4882 25.2492 19.821 25.25 19.125C25.2486 18.117 24.8998 17.1402 24.2622 16.3594C23.6247 15.5786 22.7374 15.0414 21.75 14.8385ZM6 12.0463C6.00232 10.2113 6.73226 8.45223 8.02974 7.15474C9.32723 5.85726 11.0863 5.12732 12.9212 5.125H13.0788C14.9137 5.12732 16.6728 5.85726 17.9703 7.15474C19.2677 8.45223 19.9977 10.2113 20 12.0463V14.75H6V12.0463ZM13 23.5C12.4589 23.4983 11.9316 23.3292 11.4905 23.0159C11.0493 22.7026 10.716 22.2604 10.5363 21.75H15.4637C15.284 22.2604 14.9507 22.7026 14.5095 23.0159C14.0684 23.3292 13.5411 23.4983 13 23.5ZM22.625 20H3.375C3.14298 19.9999 2.9205 19.9076 2.75644 19.7436C2.59237 19.5795 2.50014 19.357 2.5 19.125C2.50076 18.429 2.77757 17.7618 3.26969 17.2697C3.76181 16.7776 4.42904 16.5008 5.125 16.5H20.875C21.571 16.5008 22.2382 16.7776 22.7303 17.2697C23.2224 17.7618 23.4992 18.429 23.5 19.125C23.4999 19.357 23.4076 19.5795 23.2436 19.7436C23.0795 19.9076 22.857 19.9999 22.625 20Z" fill="#36C95F"/>
                                        </svg>

                                        <span class="badge light text-white notification-count bg-primary"><?php if (count($allNotifications)) echo '*' ?></span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3"> <?php /* The class height380 gives the notification bar a height of 380 */ ?>
                                            <ul id="notifications-list" class="timeline">
                                                <?php if (count($allNotifications)) { ?>
                                                    <?php foreach ($allNotifications as $value) { ?>
                                                        <li>
                                                            <div class="timeline-panel">
                                                                <div class="media mr-2 media-success">
                                                                    <?php if ($value['type'] == 'request') echo 'RQ'; elseif ($value['type'] == 'offer') echo 'OF'; else echo '<i class="fa fa-home"></i>' ?>
                                                                </div>
                                                                <div class="media-body">
                                                                    <h6 class="mb-1"><?php echo $value['message'] ?></h6>
                                                                    <small class="d-block"><?php echo $value['time']; ?></small>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <li class="no-notification">
                                                        <div class="timeline-panel p-1">
                                                            <div class="media-body text-center">
                                                                <h6 class="mb-1">You current have no new notifications</h6>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <a class="all-notification" href="<?php echo base_url('/notifications') ?>">See all notifications <i class="ti-arrow-right"></i></a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown header-profile">
                                    <a class="nav-link" href="javascript:;" role="button" data-toggle="dropdown">
                                        <?php if (session('logo') && file_exists('uploads/images/logo/'.session('logo'))) { ?>
                                            <img src="<?php echo base_url().'/uploads/images/logo/'.session('logo'); ?>" width="20" alt=""/>
                                        <?php } else { ?>
                                            <img src="<?php echo base_url().'/webapp/images/doctors-image-small.png'; ?>" width="20" alt=""/>
                                        <?php } ?>
                                        <div class="header-info">
                                            <span>Hello, <strong><?php echo session('name'); ?></strong></span>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="<?php echo base_url().'/profile'; ?>" class="dropdown-item ai-icon">
                                            <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            <span class="ml-2">Profile </span>
                                        </a>
                                        <a href="<?php echo base_url().'/logout'; ?>" class="dropdown-item ai-icon">
                                            <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                            <span class="ml-2">Logout </span>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
            
            <!--**********************************
                Header end ti-comment-alt
            ***********************************-->

            <!--**********************************
                Sidebar start
            ***********************************-->
            <div class="deznav">
                <div class="deznav-scroll">
                    <ul class="metismenu" id="menu">
                        <li>
                            <a href="<?php echo base_url().'/dashboard'; ?>" class="ai-icon" aria-expanded="false">
                                <i class="flaticon-381-notification"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                        <?php if (session('acct_type') == 'hospital') { ?>
                            <li>
                                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                                    <i class="flaticon-381-networking"></i>
                                    <span class="nav-text">Make Request</span>
                                </a>
                                <ul aria-expanded="false">
                                    <li><a href="<?php echo base_url().'/request-blood'; ?>">New Blood Request</a></li>
                                    <li><a href="<?php echo base_url().'/accept-donors'; ?>">View Responses</a></li>
                                    <!-- <li><a href="<?php echo base_url().'/my-activities'; ?>">My Activities</a></li> -->
                                </ul>
                            </li>
                            <!-- <li>
                                <a href="<?php echo base_url().'/wallet'; ?>" class="ai-icon" aria-expanded="false">
                                    <i class="flaticon-381-briefcase"></i>
                                    <span class="nav-text">Wallet</span>
                                </a>
                            </li> -->
                            <!-- <li>
                                <a href="#" class="ai-icon" aria-expanded="false">
                                    <i class="flaticon-381-notification"></i>
                                    <span class="nav-text">Notification</span>
                                </a>
                            </li> -->
                            <!-- <li>
                                <a href="#" class="ai-icon" aria-expanded="false">
                                    <i class="flaticon-381-heart"></i>
                                    <span class="nav-text">Health Insurance</span>
                                </a>
                            </li> -->
                            <!-- <li>
                                <a href="<?php echo base_url().'/visitors'; ?>" class="ai-icon" aria-expanded="false">
                                    <i class="fa fa-users"></i>
                                    <span class="nav-text">Visitor's List</span>
                                </a>
                            </li> -->
                        <?php } elseif (session('acct_type') == 'blood-bank') { ?>
                            <li>
                                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                                    <i class="flaticon-381-networking"></i>
                                    <span class="nav-text">Blood Requests</span>
                                </a>
                                <ul aria-expanded="false">
                                    <li><a href="<?php echo base_url().'/browse-blood-requests'; ?>">Browse Requests</a></li>
                                    <li><a href="<?php echo base_url().'/browse-activities'; ?>">Pending Activities</a></li>
                                </ul>
                            </li>
                            
                            <!-- <li>
                                <a href="<?php echo base_url().'/wallet'; ?>" class="ai-icon" aria-expanded="false">
                                <i class="flaticon-381-briefcase"></i>
                                    <span class="nav-text">Wallet</span>
                                </a>
                            </li> -->
                            
                            <li>
                                <a href="<?php echo base_url().'/inventory'; ?>" class="ai-icon" aria-expanded="false">
                                <i class="fa fa-university"></i>
                                    <span class="nav-text">Inventory</span>
                                </a>
                            </li>
                        <?php } ?>
                        
                        <li>
                            <a href="<?php echo base_url().'/settings' ?>" class="ai-icon" aria-expanded="false">
                                <i class="flaticon-381-settings-2"></i>
                                <span class="nav-text">Settings</span>
                            </a>
                        </li>
                    </ul>
                
                    <div class="plus-box bg-white">
                        
                    </div>
                    <div class="copyright">
                        <!-- <p class="fs-14 font-w200"><strong class="font-w400"></strong> <span class="font-weight-bold">&copy; <?php echo date("Y"); ?></span> All Rights Reserved</p> -->
                    </div>
                </div>
            </div>