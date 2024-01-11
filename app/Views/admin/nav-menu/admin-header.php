<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" sizes="16x16" href="">
    <title>
        <?php echo $page_title; ?> | BetaLife
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <!-- Nucleo Icons -->
    <link href="<?php echo base_url('/admin/css/nucleo-icons.css') ?>" rel="stylesheet" />
    <!-- CSS Files -->
    <link href="<?php echo base_url('/admin/css/black-dashboard.css?v=1.0.2') ?>" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="<?php echo base_url('/admin/demo/demo.css') ?>" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
</head>
<body class="">
    <div class="wrapper">
        <div class="sidebar">
            <?php /* Tip 1: You can change the color of the sidebar using: data-color="blue | green | orange | red" */ ?>
            <div class="sidebar-wrapper">
                <div class="logo">
                    <a href="javascript:void(0)" class="simple-text logo-mini">
                        BL
                    </a>
                    <a href="javascript:void(0)" class="simple-text logo-normal">
                        BetaLife
                    </a>
                </div>
                <ul class="nav">
                    <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'dashboard') !== FALSE) echo'active'; ?>">
                        <a href="<?php echo base_url('/admin/dashboard'); ?>">
                            <i class="tim-icons icon-chart-pie-36"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'withdrawals') !== FALSE) echo'active' ?>">
                        <a href="<?php echo base_url('/admin/pending-withdrawals'); ?>">
                            <i class="tim-icons icon-atom"></i>
                            <p>Withdrawals</p>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="tim-icons icon-pin"></i>
                            <p>Accounts</p>
                        </a>

                        <ul>
                            <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'view-hospitals') !== FALSE) echo'active' ?>">
                                <a href="<?php echo base_url('/admin/view-hospitals'); ?>">
                                    <i class="tim-icons icon-heart-2"></i>
                                    Hospitals
                                </a>
                            </li>
                            
                            <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'blood-banks') !== FALSE) echo'active' ?>">
                                <a href="<?php echo base_url('/admin/view-blood-banks'); ?>">
                                    <i class="tim-icons icon-bank"></i>
                                    Blood Banks
                                </a>
                            </li>
                            
                            <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'view-phamarcies') !== FALSE) echo'active' ?>">
                                <a href="<?php echo base_url('/admin/view-phamarcies'); ?>">
                                    <i class="tim-icons icon-istanbul"></i>
                                    Pharmacies
                                </a>
                            </li>
                            
                            <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'view-users') !== FALSE) echo'active' ?>">
                                <a href="<?php echo base_url('/admin/view-users'); ?>">
                                    <i class="tim-icons icon-single-02"></i>
                                    Users
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'manage-posts') !== FALSE) echo'active' ?>">
                        <a href="<?php echo base_url('/admin/manage-posts'); ?>">
                            <i class="tim-icons icon-notes"></i>
                            <p>Posts</p>
                        </a>
                    </li>
                    <li class="<?php if (strpos($_SERVER['PATH_INFO'], 'manage-users') !== FALSE) echo'active' ?>">
                        <a href="<?php echo base_url('/admin/manage-users'); ?>">
                            <i class="tim-icons icon-settings"></i>
                            <p>Admins</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle d-inline">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="<?php echo base_url('/admin/dashboard'); ?>">Dashboard</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navigation">
                        <ul class="navbar-nav ml-auto">
                            <li class="search-bar input-group">
                                <button class="btn btn-link" id="search-button" data-toggle="modal" data-target="#searchModal"><i class="tim-icons icon-zoom-split" ></i>
                                    <span class="d-lg-none d-md-block">Search</span>
                                </button>
                            </li>
                            <li class="dropdown nav-item">
                                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                    <div class="photo">
                                        <?php if (session('profile_photo')) { ?>
                                            <img src="<?php echo base_url().'/uploads/images/profile_photo/'.session('profile_photo'); ?>" alt="Profile Photo">
                                        <?php } else { ?>
                                            <img src="<?php echo base_url().'/webapp/images/doctors-image-small.png'; ?>" alt="Profile Photo">
                                        <?php } ?>
                                    </div>
                                    <b class="caret d-none d-lg-block d-xl-block"></b>
                                    <p class="d-lg-none">
                                        Menu
                                    </p>
                                </a>
                                <ul class="dropdown-menu dropdown-navbar">
                                    <li class="nav-link"><a href="<?php echo base_url('/admin/profile'); ?>" class="nav-item dropdown-item">Profile</a></li>
                                    <li class="nav-link"><a href="javascript:void(0)" class="nav-item dropdown-item">Settings</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li class="nav-link"><a href="<?php echo base_url('/admin/logout') ?>" class="nav-item dropdown-item">Log out</a></li>
                                </ul>
                            </li>
                            <li class="separator d-lg-none"></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="modal modal-search fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="SEARCH">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i class="tim-icons icon-simple-remove"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>