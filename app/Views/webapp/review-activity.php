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
                <div class="my-2">
                    <h4 class="text-muted">Review Activity</h4>
                </div>

                <div class="request-browsing">
                    <div class="row mt-5">
                        <div class="col-12 col-lg-10 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted text-right font-weight-bold text-uppercase mb-4">Blood Request #<?php echo $allActivities->id; ?></p>
                                    <div class="">
                                        <?php if ($allActivities->auth_type == 'user') { ?>
                                            <?php $arrayOfNames = [$allActivities->user->lastname, $allActivities->user->firstname, $allActivities->user->other_names] ?>
                                            <p class="text-muted">Patient Name:</p> 
                                            <p class="font-weight-bold"><?php echo implode(" ", $arrayOfNames); ?></p>
                                        <?php } ?>

                                        <p class="text-muted">Hospital Name:</p> 
                                        <p class="font-weight-bold"><?php echo $allActivities->hospital_name; ?></p>
                                        
                                        <p class="text-muted mt-4">Hospital Address:</p> 
                                        <p class="font-weight-bold"><?php echo $allActivities->address; ?></p>
                                    </div>

                                    <div class="table-responsive mt-5">
                                        <table class="table table-stripped text-center">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th>Blood Group</th>
                                                    <th>No. of Pint</th>
                                                    <th>Amount per Pint</th>
                                                    <th>Total</th>
                                                    <th>No. of Pint Accepted</th>
                                                    <th>Total Amount Paid</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $currentTotalAmount = $overallTotal = $totalAmountToBePaid = 0;  $confirmedOffer = false ?>
                                                <?php foreach ($allActivities->offer as $value) { ?>
                                                    <?php if (in_array($value->status, ['complete', 'confirmed'])) $confirmedOffer = true; ?>
                                                    <?php 
                                                        $currentTotalAmount = ($value->no_of_pints * $value->amount_per_pint);  
                                                        $overallTotal += $currentTotalAmount;

                                                        if ($value->no_of_pints_confirmed) {
                                                            $totalAmountToBePaid += ($value->no_of_pints_confirmed * $value->amount_per_pint);
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $value->blood_group; ?></td>
                                                        <td><?php echo $value->no_of_pints; ?></td>
                                                        <td><?php echo 'NGN '.number_format($value->amount_per_pint); ?></td>
                                                        <td><?php echo 'NGN '.number_format($currentTotalAmount); ?></td>
                                                        <td><?php echo $value->no_of_pints_confirmed ? $value->no_of_pints_confirmed : '- - -'; ?></td>
                                                        <td><?php echo $value->no_of_pints_confirmed ? 'NGN '.number_format($value->no_of_pints_confirmed * $value->amount_per_pint) : '- - -'; ?></td>
                                                        <td>
                                                            <?php if (in_array($value->status, ['complete', 'confirmed'])) { ?>
                                                                <span class="badge badge-success">Accepted</span>
                                                            <?php } elseif ($value->status == 'declined') { ?>
                                                                <span class="badge badge-danger">Declined</span>
                                                            <?php } else { ?>
                                                                - - -
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <tr class="text-right table-warning font-weight-bold">
                                                    <td colspan="3">Total Expected: </td>
                                                    <td>NGN <?php echo number_format($overallTotal); ?></td>
                                                    <td>Total Paid: </td>
                                                    <td>NGN <?php echo number_format($totalAmountToBePaid); ?></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4">
                                        <?php if ($confirmedOffer) { ?>
                                            <div class="alert alert-success text-center">
                                                <p class="mb-0"><i class="fa fa-check-circle"></i> Your offer has been accepted. See details above.</p>
                                            </div>
                                            
                                            <?php if ($confirmPaymentMade) { ?>
                                                <?php if ($confirmPaymentMade->status == 'pending') { ?>
                                                    <div class="text-center mt-4">
                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#confirm_delivery">
                                                            Confirm Delivery
                                                        </button>
                                                    </div>
                                                <?php } elseif ($confirmPaymentMade->status == 'completed') { ?>
                                                    <div class="alert alert-success text-center">
                                                        <p class="mb-0"><i class="fa fa-check-circle"></i> Delivered and Sealed.</p>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="alert alert-danger text-center">
                                                        <p class="mb-0"><i class="fa fa-times-circle"></i> The delivery has failed.</p>
                                                    </div>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <div class="alert alert-info text-center mt-4">
                                                    <p class="mb-0"><i class="fa fa-info-circle"></i> Delivery information will be available here once payment is confirmed.</p>
                                                </div>
                                            <?php } ?>
                                        <?php } elseif ($allActivities->due_date < time()) { ?>
                                            <div class="alert alert-danger text-center">
                                                <p class="mb-0"><i class="fa fa-info-circle"></i> Offer has expired.</p>
                                            </div>
                                        <?php } elseif ($allActivities->status == 'pending') { ?>
                                            <div class="text-right">
                                                <button role="button" href="" class="btn btn-danger btn-confirm-delete" data-toggle="modal" data-target="#withdrawOffer">
                                                    Withdraw Offer
                                                </button>
                                            </div>
                                        <?php } else { ?>
                                            
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($confirmPaymentMade) { ?>
                        <?php if ($confirmPaymentMade->status == 'pending') { ?>
                            <!-- Modal -->
                            <div class="modal fade" id="confirm_delivery" tabindex="-1" aria-labelledby="confirm_deliveryLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title text-center" id="confirm_deliveryLabel">
                                                Confirm Delivery
                                            </h3>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-12 col-lg-9 mx-auto">
                                                    <div class="alert alert-info mb-4 text-center">
                                                        <p class="mb-0"><i class="fa fa-info-circle"></i> Get the token from the Hospital to complete this delivery</p>
                                                    </div>
                                                    <form action="<?php echo base_url().'/verify-delivery-otp/'.$allActivities->id; ?>" method="POST" class="confirm_delivery_form">
                                                        <p class="font-weight-bold text-center">Enter Delivery OTP: </p>
                                                        <div class="row">
                                                            <div class="col-2 d-none d-lg-block"></div>
                                                            <div class="col-3 col-lg-2">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control text-center verifyOtp" name="wp_number_1" min="0" max="9" maxlength="1" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 col-lg-2">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control text-center verifyOtp" name="wp_number_2" min="0" max="9" maxlength="1" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 col-lg-2">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control text-center verifyOtp" name="wp_number_3" min="0" max="9" maxlength="1" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 col-lg-2">
                                                                <div class="form-group">
                                                                    <input type="number" class="form-control text-center verifyOtp" name="wp_number_4" min="0" max="9" maxlength="1" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-2 d-none d-lg-block"></div>
                                                        </div>
                                                        <small class="error_msg_otp ml-5 text-danger"></small>

                                                        <div class="form-row">
                                                            <div class="col-8 mx-auto">
                                                                <div class="form-group mt-4">
                                                                    <button class="btn btn-success btn-block btn-confirm-delivery" type="submit">
                                                                        Confirm Delivery
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    
                    <?php if ($allActivities->status == 'pending') { ?>
                        <!-- Modal -->
                        <div class="modal fade" id="withdrawOffer" tabindex="-1" aria-labelledby="withdrawOfferLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title text-center" id="withdrawOfferLabel">
                                            Are you sure?
                                        </h3>
                                    </div>

                                    <div class="modal-body">
                                        <?php if ($allActivities->status == 'pending') { ?>
                                            <div class="row">
                                                <div class="col-8 mx-auto text-center">
                                                    <i class="fa fa-question-circle fa-3x text-danger" aria-hidden="true"></i>
                                                    <div class="my-4">
                                                        <h4 class="withdraw-msg">
                                                            Are you sure you want to withdraw your offer sent to <?php echo $allActivities->hospital_name; ?>?
                                                        </h4>

                                                        <small class="text-info"><i class="fa fa-info-circle"></i> Your offer would be removed as a suggestion for the hospital. Withdrawal may fail if hospital has already accepted your offer</small>
                                                    </div>


                                                    <div class="">
                                                        <a href="<?php echo base_url().'/withdraw-offer/'.$allActivities->id; ?>" class="btn btn-danger withdraw-url">
                                                            Yes
                                                        </a>
                                                        <a href="#" class="btn btn-primary withdraw-url" data-dismiss="modal">
                                                            No
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>