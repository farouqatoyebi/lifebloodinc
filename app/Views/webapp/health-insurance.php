<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <div class="health-insurance">
            <?php if ($accountVerified == 'active') { ?>

            <?php } elseif ($accountVerified == 'inactive') { ?>

            <?php } elseif ($accountVerified == 'rejected') { ?>

            <?php } elseif ($accountVerified == 'pending') { ?>
                <div class="row">
                    <div class="col-lg-9 mx-auto">
                        <div class="card">
                            <div class="card-body text-center">
                                <p class="mb-4"><img src="<?php echo base_url('/webapp/images/under-review.jpg') ?>" alt="" class="img-fluid"></p>
                                <p class="font-weight-bolder">We are currently reviewing your profile. Please wait while we confirm your details.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>

            <?php } ?>
        </div>
    </div>
</div>