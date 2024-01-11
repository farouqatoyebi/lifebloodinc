<?php 
    use App\Controllers\BaseController; 
    $basecontroller = new BaseController();
    $bloodBankInfo = $basecontroller->getUserProfileInformationBasedOnType(session('acct_type'), session('auth_id'));
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (count($allActivities)) { ?>
                    <div class="my-2">
                        <h4 class="text-muted">All Activities</h4>
                    </div>

                    <div class="request-browsing">
                        <div class="row mt-5">
                            <?php foreach ($allActivities as $key => $value) { $canSendOffers = false; ?>
                                <div class="col-12 col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="text-muted text-right font-weight-bold text-uppercase mb-4">Blood Request #<?php echo $value->id; ?></p>
                                            <div class="">
                                                <?php if ($value->auth_type == 'user') { ?>
                                                    <?php if (isset($value->user)) { ?>
                                                        <?php $arrayOfNames = [$value->user->lastname, $value->user->firstname, $value->user->other_names] ?>
                                                        <p class="text-muted">Patient Name:</p> 
                                                        <p class="font-weight-bold"><?php echo implode(" ", $arrayOfNames); ?></p>
                                                    <?php } ?>
                                                <?php } ?>

                                                <p class="text-muted">Hospital Name:</p> 
                                                <p class="font-weight-bold"><?php echo $value->hospital_name; ?></p>
                                                
                                                <p class="text-muted mt-4">Hospital Address:</p> 
                                                <p class="font-weight-bold"><?php echo $value->address; ?></p>
                                            </div>

                                            <div class="mt-4">
                                                <div class="text-right">
                                                    <a href="<?php echo base_url().'/review-activity/blood-request/'.$value->id; ?>" class="btn btn-primary btn-block btn-sm">
                                                        Review Activity
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center">
                        <img src="<?php echo base_url().'/webapp/images/no-request-yet.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;">
                        <p>There are currently no pending activites for you.</p>
                        <p class="h4"> Your activites will appear here once you make some offers. </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>