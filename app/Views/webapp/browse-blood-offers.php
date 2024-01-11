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
            <div class="card-body" request="<?php echo $request_info->id ?>">
                <style>
                    .load-new-offers {
                        position: fixed;
                        display: none;
                        right: 35%;
                        bottom: 15px;
                        z-index: 99999;
                        transition: 0.5s;
                    }
                    @media screen and (max-width: 992px) {
                        .load-new-offers {
                            right: 30%;
                        }
                    }
                </style>

                <a href="#" class="btn btn-secondary load-new-offers d-none btn-sm" style="display: inline;">
                    Load More Offers
                </a>

                <?php if ($hasRequestLeft) { ?>
                    <div class="row mb-4">
                        <div class="col-lg-9"></div>
                        <div class="col-lg-3">
                            <a href="<?php echo base_url().'/payment-summary/'.$request_info->id; ?>" class="btn btn-primary btn-block btn-sm">
                                See Summary
                            </a>
                        </div>
                    </div>
                <?php } ?>

                <?php if (count($allOffers) && $hasRequestLeft) { ?>
                    <div class="my-2">
                        <h4 class="text-muted"><?php echo $page_title; ?></h4>
                    </div>

                    <div class="row mt-5 allOffers">
                        <?php foreach ($allOffers as $key => $value) { ?>
                            <div class="col-12 col-lg-6 offer-breakdown">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="">
                                            <span class="text-muted"><?php echo ucwords(str_replace('-', ' ', $value->donor_type)).' Name'; ?></span>
                                            <p class="font-weight-bold"><?php echo isset($value->donor->firstname) ? implode(" ", [$value->donor->lastname, $value->donor->firstname, $value->donor->other_names]) : $value->donor->name ?></p>

                                            <span class="text-muted">Address</span>
                                            <p class="font-weight-bold"><?php echo isset($value->donor->address) ? $value->donor->address : $value->donor->Address ?></p>
                                        </div>

                                        <div class="request-informattion">
                                            <p class="">Offer Breakdown</p>
                                            <hr>
                                            <?php $totalAmount = 0; ?>
                                            <div class="row">
                                                <?php foreach ($value->donor->offers as $offersBreakDown) { ?>
                                                    <div class="col-lg-4">
                                                        <small class="text-muted">Blood Group: </small>
                                                        <p class="font-weight-bold"><?php echo $offersBreakDown->blood_group; ?></p>
                                                        
                                                        <small class="text-muted">No. of Pint: </small>
                                                        <p class="font-weight-bold"><?php echo $offersBreakDown->no_of_pints; ?></p>
                                                        
                                                        <small class="text-muted">Amount per Pint: </small>
                                                        <p class="font-weight-bold"><?php echo 'NGN '.number_format($offersBreakDown->amount_per_pint); ?></p>
                                                        <hr>
                                                        <p class="font-weight-bold mb-0"><?php echo 'NGN '.number_format($offersBreakDown->amount_per_pint * $offersBreakDown->no_of_pints); ?></p>
                                                    </div>
                                                    <?php $totalAmount += $offersBreakDown->amount_per_pint * $offersBreakDown->no_of_pints; ?>
                                                <?php } ?>
                                            </div>
                                            <hr>
                                            <div class="text-right">
                                                <small class="text-muted">Total: </small>
                                                <p class="font-weight-bold"><?php echo 'NGN '.number_format($totalAmount); ?></p>
                                                <hr>
                                            </div>
                                        </div>

                                        <?php if ($request_info->due_date > time()) { ?>
                                            <div class="d-flex justify-content-between mt-4">
                                                <button class="btn btn-danger btn-sm remove-this">
                                                    Dismiss
                                                </button>
                                                <button class="btn btn-primary btn-sm accept-offer-modal" data-toggle="modal" request="<?php echo $request_info->id ?>" offer="<?php echo $value->donor->id ?>" offerType="<?php echo $value->donor_type ?>" data-target="#acceptOfferModal">
                                                    Accept Offer
                                                </button>
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-danger text-center">
                                                <p class="mb-0"><i class="fa fa-info-circle"></i> Request has expired.</p>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="acceptOfferModal" tabindex="-1" aria-labelledby="acceptOfferLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title text-center" id="acceptOfferLabel"></h3>
                                    <!-- <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" aria-label="Close">&times;</button> -->
                                </div>

                                <div class="modal-body">
                                    
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary accept-offer-btn disabled" disabled>Accept Offer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } elseif (!$hasRequestLeft) { ?>
                    <h3 class="h3 text-muted">Quota Achieved</h3>
                    <hr class="mb-4">
                    <div class="text-center">
                        <p><i class="fa fa-check-circle text-success fa-3x"></i></p>
                        <p class="font-weight-bold">You have successfully accepted and met the quota of your blood request.</p>
                        <p class="font-weight-bold">Please click button below to see summary.</p>
                        <p class="mt-4">
                            <a href="<?php echo base_url().'/payment-summary/'.$request_info->id; ?>" class="btn btn-primary btn-lg">
                                See Summary
                            </a>
                        </p>
                    </div>
                <?php } else { ?>
                    <div class="text-center">
                        <img src="<?php echo base_url().'/webapp/images/searching-for-offers.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;">
                        <p class="mt-4 finding-offers font-weight-bold h4">Searching for Offers</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>