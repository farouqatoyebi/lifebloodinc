<?php 
    use App\Controllers\BaseController; 
    $allowMultipleBloodGroupsSubmission = true;
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-6">
                <a href="<?php echo base_url().'/profile' ?>" class="">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <p class="font-weight-bold">
                                    <i class="fa fa-user fa-3x"></i>
                                </p>
                                <h4 class="font-weight-bold text-primary">
                                    Profile
                                </h4>
                                <small>Update your profile information</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (session('acct_type') == 'blood-bank') { ?>
                <div class="col-6">
                    <a href="#" class="">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center">
                                    <p class="font-weight-bold">
                                        <i class="fa fa-money fa-3x"></i>
                                    </p>
                                    <h4 class="font-weight-bold text-primary">
                                        Set Rates
                                    </h4>
                                    <small>Update the rates you sell your blood per pint</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>