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
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="pl-lg-5">
                            <p class="font-weight-bold">
                                Current Balance <i class="fa fa-eye ml-3"></i>
                            </p>
                            <div class="row">
                                <div class="col-lg-8">
                                    <h1 class="font-weight-bold text-dark">
                                        NGN <?php echo number_format($walletAmount, 2); ?>
                                    </h1>

                                    <hr style="visibility:hidden;">

                                    <p><small class="text-muted">Book Balance: </small> <span class="text-dark font-weight-bold h6">NGN <?php echo number_format($walletBookBalance, 2); ?></span></p>
                                </div>

                                <div class="col-lg-4">
                                    <button class="btn btn-primary btn-block <?php if (!$walletAmount) echo 'disabled'; ?>" <?php if (!$walletAmount) echo 'disabled="disabled"' ?> data-toggle="modal" data-target="#acceptOfferModal">
                                        Withdraw
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="pl-lg-5">
                            <p class="font-weight-bold">
                                Transaction History
                            </p>
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead class="table-default">
                                        <tr>
                                            <th>Name</th>
                                            <th>Ticket Number</th>
                                            <th>Pints</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if ($allTransactions) { ?>
                                            <?php foreach ($allTransactions as $value) { ?>
                                                <tr>
                                                    <td><?php echo $value->name ?></td>
                                                    <td>
                                                        <?php 
                                                            $prependZeros = '';

                                                            if (mb_strlen($value->request_id) == 1) {
                                                                $prependZeros = '000';
                                                            }
                                                            elseif (mb_strlen($value->request_id) == 2) {
                                                                $prependZeros = '00';
                                                            }
                                                            elseif (mb_strlen($value->request_id) == 3) {
                                                                $prependZeros = '0';
                                                            }
                                                        ?>
                                                        <?php echo 'BL_'.$prependZeros.$value->request_id ?>
                                                    </td>
                                                    <td><?php echo number_format($value->no_of_pints_confirmed); ?></td>
                                                    <td><?php echo 'NGN '.number_format($value->no_of_pints_confirmed * $value->amount_per_pint); ?></td>
                                                    <td><?php echo date("jS F Y h:ia", $value->updated_at) ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-danger font-weight-bold">No record found</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($walletAmount) { ?>
                    <!-- Modal -->
                    <div class="modal fade" id="acceptOfferModal" tabindex="-1" aria-labelledby="acceptOfferLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title text-center" id="acceptOfferLabel">Make Withdrawal Request</h3>
                                    <!-- <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" aria-label="Close">&times;</button> -->
                                </div>

                                <div class="modal-body">
                                    <div class="border p-3">
                                        <h4 class="h4">Bank Details</h4>
                                        <hr>

                                        <div class="mt-4">
                                            <div class="row">
                                                <div class="col-4">Bank Name:</div>
                                                <div class="col-8 font-weight-bold"><?php echo $bankDetails->bank_name ? $bankDetails->bank_name : '- - -'; ?></div>
                                                
                                                <div class="col-4">Account Name:</div>
                                                <div class="col-8 font-weight-bold"><?php echo $bankDetails->acct_name ? $bankDetails->acct_name : '- - -'; ?></div>
                                                
                                                <div class="col-4">Account Number:</div>
                                                <div class="col-8 font-weight-bold"><?php echo $bankDetails->acct_number ? $bankDetails->acct_number : '- - -'; ?></div>
                                                
                                                <div class="col-4">Sort Code:</div>
                                                <div class="col-8 font-weight-bold"><?php echo $bankDetails->sort_code ? $bankDetails->sort_code : '- - -'; ?></div>
                                                
                                                <div class="col-4">Amount Withdrawable:</div>
                                                <div class="col-8 font-weight-bold"><?php echo 'NGN '.number_format($walletAmount); ?></div>
                                            </div>
                                        </div>

                                        <form action="<?php echo base_url().'/process-withdrawal-disbursement' ?>" class="withdrawal-form">
                                            <div class="mt-4">
                                                <div class="form-group">
                                                    <label for="">Amount to Withdraw:</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text bg-success text-white" id="basic-addon1">NGN</span>
                                                        <input type="number" name="wp_amount_withdraw" value="<?php echo $walletAmount; ?>" max="<?php echo $walletAmount; ?>" min="1000" id="" class="form-control wp_amount_withdraw">
                                                    </div>
                                                    <small class="wp_amount_withdraw text-danger"></small>
                                                </div>

                                                <div class="form-group mt-4 text-center">
                                                    <button type="submit" class="btn btn-primary make-withdrawal-btn">Make Withdrawal Request</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="pl-lg-5">
                            <p class="font-weight-bold">
                                Withdrawal History
                            </p>
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead class="table-default">
                                        <tr>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if ($allWithdrawalsMade) { ?>
                                            <?php foreach ($allWithdrawalsMade as $value) { ?>
                                                <tr>
                                                    <td><?php echo 'NGN '.number_format($value->amount); ?></td>
                                                    <td>
                                                        <?php if ($value->status == 'approved') { ?>
                                                            <span class="badge badge-primary"><?php echo ($value->status); ?></span>
                                                        <?php } elseif ($value->status == 'rejected') { ?>
                                                            <span class="badge badge-danger"><?php echo ($value->status); ?></span>
                                                        <?php } elseif ($value->status == 'paid') { ?>
                                                            <span class="badge badge-success"><?php echo ($value->status); ?></span>
                                                        <?php } else { ?>
                                                            <span class="badge badge-warning"><?php echo ($value->status); ?></span>
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $value->reason ? (nl2br($value->reason)) : '- - - -'; ?></td>
                                                    <td><?php echo date("jS F Y h:ia", $value->created_at) ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-danger font-weight-bold">No record found</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>