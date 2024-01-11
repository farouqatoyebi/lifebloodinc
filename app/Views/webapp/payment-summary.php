<?php 
    use App\Controllers\BaseController;
    $basecontroller = new BaseController();
    $serviceChargeFee = $basecontroller->getServiceChargeFee('web-app', 'amount');
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <?php if ($offersLeftToAccept && $request_info->status == 'pending') { ?>
            <div class="row mb-4">
                <div class="col-lg-3 mx-auto">
                    <a href="<?php echo base_url().'/browse-blood-offers/'.$requestID; ?>" class="btn btn-primary btn-block btn-sm">
                        See Offers
                    </a>
                </div>
            </div>
        <?php } ?>
        
        <?php if ($allAcceptedOffers) { ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="h3">Transaction Summary</h3>
                            <div class="row">
                                <?php $currentDonorID = $totalAmountToBePaid = 0; ?>
                                <?php foreach ($allAcceptedOffers as $key => $value) { $donorID = $value->donor_id ?>
                                    <?php if ($donorID != $currentDonorID) { $currentDonorID = $donorID; ?>
                                        <?php $userInfo = $basecontroller->getAccountInformationBasedOnID($value->donor_type, $donorID); ?>
                                        <div class="col-12">
                                            <hr>
                                            <p class="">Name: <span class="font-weight-bold h4"><?php echo $userInfo->name ?></span> </p>
                                        </div>
                                    <?php } ?>

                                    <div class="col-lg-6 rounded border mb-3 p-4">
                                        <?php $subTotal = $value->amount_per_pint * $value->no_of_pints_confirmed; $totalAmountToBePaid += $subTotal; ?>
                                        <p class="">Blood Group: <span class="font-weight-bold"><?php echo $value->blood_group; ?></span></p>
                                        <p class="">Number of Pint: <span class="font-weight-bold"><?php echo $value->no_of_pints_confirmed; ?></span></p>
                                        <p class="">Amount per Pint: <span class="font-weight-bold"><?php echo 'NGN '.number_format($value->amount_per_pint); ?></span></p>
                                        <p class="">Sub Total: <span class="font-weight-bold"><?php echo 'NGN '.number_format($subTotal); ?></span></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <?php if ($offersLeftToAccept && !$basecontroller->hasGeneratedOldRequest($requestID)) { ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="h3 mb-4">What's left to complete your request?</h3>
                            <div class="row">
                                <?php foreach ($offersLeftToAccept as $key => $value) { ?>
                                    <div class="col-lg-6 border-right border-top rounded mb-3 p-3">
                                        <p class="">Blood Group: &nbsp;<span class="font-weight-bold"><?php echo $key; ?></span></p>
                                        <p class="">Number of Pint Left: &nbsp;<span class="font-weight-bold"><?php echo $value['no_of_pints_left']; ?></span></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <?php if ($allAcceptedOffers) { ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-right">
                                <?php $serviceChargeFee = $serviceChargeFee ? $serviceChargeFee : 5000; ?>

                                <p class=""><span class="h6 text-muted">Sub-Total:</span> <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid); ?></span></p>
                                <p class=""><span class="h6 text-muted">Service Charge:</span> <span class="font-weight-bold h4">NGN <?php echo number_format($serviceChargeFee); ?></span></p>
                                <p class=""><span class="h6 text-muted">Total:</span> <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid + $serviceChargeFee); ?></span></p>
                            </div>
                            <hr>

                            <?php if ($request_info->status == 'pending') { ?>
                                <?php if ($allAcceptedOffers) { ?>
                                    <div class="row mt-4">
                                        <div class="col-lg-4">
                                            <a href="<?php echo base_url().'/'; ?>" class="btn btn-outline-primary">
                                                <i class="fa fa-credit-card-alt" aria-hidden="true"></i> &nbsp;Pay with Wallet
                                            </a>
                                        </div>

                                        <div class="col-lg-4 text-center mt-2"></div>

                                        <div class="col-lg-4 text-lg-right">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#transferMoneyModal">
                                                <i class="fa fa-usd" aria-hidden="true"></i> Pay Online
                                            </button>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div class="alert alert-info">
                                        <p class="text-center">
                                            <i class="fa fa-info-circle"></i> Payment is only available after you have accepted some offers.
                                        </p>
                                    </div>
                                <?php } ?>
                            <?php } elseif ($request_info->status == 'paid') { ?>
                                <div class="alert alert-success">
                                    <p class="text-center">
                                        <i class="fa fa-check-circle"></i> Payment has been received for blood request.
                                    </p>
                                </div>
                            <?php } elseif ($request_info->status == 'complete') { ?>
                                <div class="alert alert-success">
                                    <p class="text-center">
                                        <i class="fa fa-times-circle"></i> Blood Request transaction has been completed.
                                    </p>
                                </div>
                            <?php } elseif ($request_info->status == 'cancelled') { ?>
                                <div class="alert alert-danger">
                                    <p class="text-center">
                                        <i class="fa fa-times-circle"></i> Blood Request transaction has been cancelled.
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if ($allAcceptedOffers && $request_info->status == 'pending') { ?>
            <!-- Modal -->
            <div class="modal fade" id="transferMoneyModal" tabindex="-1" aria-labelledby="transferMoneyModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title text-center" id="transferMoneyModalLabel">Make Payment via Transfer</h3>
                        </div>

                        <div class="modal-body">
                            <form action="" method="post" class="make-payment-now">
                                <div class="border rounded p-3">
                                    <h4 class="h4">Payment Breakdown</h4>
                                    <hr>

                                    <div class="">
                                        <div class="row">
                                            <div class="col-3 mb-3">
                                                <span class="h6 text-muted">Sub-Total:</span>
                                            </div> 

                                            <div class="col-9 mb-3">
                                                <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid); ?></span>
                                            </div>

                                            <div class="col-3 mb-3">
                                                <span class="h6 text-muted">Service Charge:</span>
                                            </div>

                                            <div class="col-9 mb-3">
                                                <span class="font-weight-bold h4">NGN <?php echo number_format($serviceChargeFee); ?></span>
                                            </div>

                                            <div class="col-3 mb-3">
                                                <span class="h6 text-muted">Total:</span>
                                            </div> 

                                            <div class="col-9 mb-3">
                                                <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid + $serviceChargeFee); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="request_info" value="<?php echo $requestID; ?>" id="request_info">
                                
                                <?php if ($offersLeftToAccept && !$basecontroller->hasGeneratedOldRequest($requestID)) { ?>
                                    <div class="border rounded p-3 mt-3 add-new-request-segment">
                                        <h4 class="h4">Would you like to create a new request off of what is left to complete your request?</h4>
                                        
                                        <div class="form-group">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input request_radio" type="radio" name="create_new_request" id="yes_to_request" value="yes" required>
                                                <label class="form-check-label" for="yes_to_request">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input request_radio" type="radio" name="create_new_request" id="no_to_request" value="no" required>
                                                <label class="form-check-label" for="no_to_request">No</label>
                                            </div>
                                            <small class="create_new_request text-danger"></small>
                                        </div>

                                        <div class="form-group d-none new_due_date">
                                            <label for=""><sup class="text-danger font-weight-bold">*</sup> Extend Due Date</label>
                                            <input type="date" class="form-control due_date" name="due_date" min="<?php echo date("Y-m-d", strtotime("tomorrow")) ?>" disabled required />
                                            <small class="due_date_error text-danger"></small>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                                <div class="border rounded p-3 mt-3">
                                    <div class="text-center">
                                        <p class="text-info"><i class="fa fa-info-circle"></i> By clicking on pay now, you agree to our terms and conditions with regards to our payment system.</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-block btn-primary btn-pay-now">
                                        Pay Now
                                    </button>
                                </div>

                                <div class="error_msg"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>