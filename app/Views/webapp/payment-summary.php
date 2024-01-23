<?php 
    use App\Controllers\BaseController;
    $basecontroller = new BaseController();
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
                                <p class=""><span class="h6 text-muted">Sub-Total:</span> <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid); ?></span></p>
                                <p class=""><span class="h6 text-muted">Total:</span> <span class="font-weight-bold h4">NGN <?php echo number_format($totalAmountToBePaid); ?></span></p>
                            </div>
                            <hr>

                            <?php if ($request_info->status == 'pending') { ?>
                                <?php if ($allAcceptedOffers) { ?>
                                    <div class="row mt-4">
                                        <div class="col-lg-4">
                                            <a href="#" class="btn btn-outline-primary">
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
    </div>
</div>