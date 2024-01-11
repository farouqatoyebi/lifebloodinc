<?php 
    use App\Controllers\BaseController; 
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
                <?php if (count($requestsWithTransactions)) { ?>
                    <div class="my-2">
                        <h4 class="text-muted"><?php echo $page_title; ?></h4>
                    </div>

                    <div class="row mt-5">
                        <?php foreach ($requestsWithTransactions as $key => $value) { ?>
                            <div class="col-12 col-lg-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="">
                                            <p class="text-muted text-right"><?php echo 'Blood Request #'.$value->id; ?></p>
                                        </div>

                                        <div class="request-informattion">
                                            <p class="">Request Information</p>
                                            <hr>
                                            
                                            <small class="text-muted">Due Date: </small>
                                            <p class="font-weight-bold"><?php echo date("F jS, Y", $value->due_date); ?></p>
                                            <hr>
                                            
                                            <small class="text-muted">Status: </small>
                                            <p class="font-weight-bold">
                                                <?php if ($value->status == 'pending') { ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php } elseif ($value->status == 'paid') { ?>
                                                    <span class="badge badge-secondary">Paid</span>
                                                <?php } elseif ($value->status == 'complete') { ?>
                                                    <span class="badge badge-success">Completed</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-danger">Failed</span>
                                                <?php } ?>
                                            </p>
                                            <hr>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4">
                                            <a href="<?php echo base_url().'/view-delivery-information/'.$value->id ?>" class="btn btn-primary btn-sm">
                                                See Delivery Breakdown
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="text-center">
                        <img src="<?php echo base_url().'/webapp/images/no-visitors-yet.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;">
                        <p class="mt-4 font-weight-bold h4">You have made no pending activity to review.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>