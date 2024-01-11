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
                <?php if (count($allRequests)) { ?>
                    <div class="my-2">
                        <h4 class="text-muted">Browse Blood Requests</h4>
                    </div>

                    <div class="request-browsing">
                        <div class="row mt-5 allRequests">
                            <?php foreach ($allRequests as $key => $value) { $canSendOffers = $canShowButton = false; ?>
                                <div class="col-12 col-lg-4 request-breakdown">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="">
                                                <?php if ($value->auth_type == 'user') { ?>
                                                    <?php $arrayOfNames = [$value->user->lastname, $value->user->firstname, $value->user->other_names] ?>
                                                    <p class="text-muted">Patient Name:</p> 
                                                    <p class="font-weight-bold"><?php echo implode(" ", $arrayOfNames); ?></p>
                                                <?php } ?>

                                                <p class="text-muted">Hospital Name:</p> 
                                                <p class="font-weight-bold"><?php echo $value->hospital_name ?></p>
                                                
                                                <p class="text-muted mt-4">Hospital Address:</p> 
                                                <p class="font-weight-bold"><?php echo $value->address ?></p>
                                            </div>

                                            <div class="request-informattion">
                                                <hr/>
                                                <p class="text-muted text-right mb-3">Request Breakdown</p> 
                                                <div class="row">
                                                    <?php foreach ($value->requests as $requests) { ?>
                                                        <div class="col-6 mb-2">
                                                            <div class="row">
                                                                <div class="col text-center">
                                                                    <span class="text-danger"><?php echo $requests->blood_group; ?></span> 
                                                                </div>
                                                                <div class="col text-center">
                                                                    <span class="font-weight-bold px-3"><?php echo $requests->no_of_pints ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <hr/>
                                            </div>

                                            <div class="d-flex justify-content-between mt-4">
                                                <button class="btn btn-danger btn-sm remove-this-request">
                                                    Dismiss
                                                </button>
                                                <div class="btn-group dropup">
                                                    <button class="btn btn-primary btn-sm" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                                                        Send Offer
                                                    </button>
                                                    
                                                    <div class="dropdown-menu p-4" style="width: 600px;" onclick="event.stopPropagation();">
                                                        <div class="offer-form">
                                                            <h5 class="h5 text-right text-muted font-weight-bold">Number of Pint</h5>
                                                            <form action="<?php echo base_url().'/send-request-offer/'.$requests->request_id; ?>" hospital="<?php echo $value->hospital_name; ?>" class="send-offer" method="post">
                                                                <div class="form-row">
                                                                    <?php foreach ($value->requests as $requests) { $bloodRate = $basecontroller->getBloodBankRateForBloodGroup($bloodBankInfo->id, $requests->blood_group); ?>
                                                                        <?php if ($bloodRate > 0) { $bloodInventoryDetails = $basecontroller->bloodInventoryDetails($bloodBankInfo->id, session('acct_type'), $requests->blood_group); $canSendOffers = true; ?>
                                                                            <div class="col-lg-6">
                                                                                <div class="form-group">
                                                                                    <label class="font-weight-bold" for="<?php echo $requests->blood_group ?>"><?php echo $requests->blood_group ?></label>
                                                                                    <div class="input-group mb-3 <?php if ($bloodInventoryDetails->amount_available <= 0) echo 'd-flex justify-content-center align-items-center p-2 border border-danger'; ?>">
                                                                                        <?php if ($bloodInventoryDetails->amount_available > 0) { $canShowButton = true; ?>
                                                                                            <?php $htmlMax = ($requests->no_of_pints > $bloodInventoryDetails->amount_available) ? $bloodInventoryDetails->amount_available : $requests->no_of_pints; ?>
                                                                                            <span class="input-group-text bg-white border border-dark" id="basic-addon1">Pint</span>
                                                                                            <input type="number" name="wp_offer[<?php echo $requests->blood_group ?>]" value="<?php echo $htmlMax ?>" currency="NGN" rate-val="<?php echo $bloodRate; ?>" class="form-control wp_offer" max="<?php echo $htmlMax ?>" group="<?php echo $requests->blood_group ?>" min="0">
                                                                                        <?php } else { ?>
                                                                                            <p class="font-weight-bold text-center mb-0 text-danger font-italic">Out of Stock</p>
                                                                                        <?php } ?>
                                                                                    </div>

                                                                                    <div class="text-right mt-0">
                                                                                        <small class="text-info font-weight-bold"><?php echo 'Current price per pint: NGN '.number_format($bloodRate); ?></small>
                                                                                    </div>
                                                                                    <small class="text-danger wp_offer"></small>
                                                                                </div>
                                                                            </div>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </div>

                                                                <?php if ($canShowButton) { ?>
                                                                    <button class="btn btn-outline-primary btn-block">
                                                                        Send Offer
                                                                    </button>
                                                                <?php } ?>

                                                                <?php if (!$canSendOffers) { ?>
                                                                    <div class="text-center p-4">
                                                                        <i class="fa fa-times-circle mb-3 text-danger fa-3x"></i>
                                                                        <h5 class="h5">
                                                                            You currently cannot send an offer for this request as you do not have a price set for the blood requested.
                                                                        </h5>
                                                                    </div>
                                                                <?php } ?>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Modal -->
                    <div class="modal fade" id="sendOffer" tabindex="-1" aria-labelledby="sendOfferLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title text-center" id="sendOfferLabel"></h3>
                                    <!-- <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" aria-label="Close">&times;</button> -->
                                </div>

                                <div class="modal-body">
                                    
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary send-offer-btn">Send Offer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center">
                        <img src="<?php echo base_url().'/webapp/images/no-request-yet.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;">
                        <p>There are currently no new requests.</p>
                        <p class="h4"> We will be sure to update you once there is a new request. </p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>