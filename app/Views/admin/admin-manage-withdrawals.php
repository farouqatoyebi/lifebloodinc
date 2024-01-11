<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="row mb-4">
                <div class="offset-lg-8"></div>
                <?php if ($page_type != 'approved') { ?>
                    <div class="col-lg-2">
                        <a href="<?php echo base_url('/admin/approved-withdrawals'); ?>" class="btn btn-success btn-block">
                            Approved Withdrawals
                        </a>
                    </div>
                <?php } ?>
                <?php if ($page_type != 'pending') { ?>
                    <div class="col-lg-2">
                        <a href="<?php echo base_url('/admin/pending-withdrawals'); ?>" class="btn btn-secondary btn-block">
                            Pending Withdrawals
                        </a>
                    </div>
                <?php } ?>
                
                <?php if ($page_type != 'rejected') { ?>
                    <div class="col-lg-2">
                        <a href="<?php echo base_url('/admin/rejected-withdrawals'); ?>" class="btn btn-danger btn-block">
                            Rejected Withdrawals
                        </a>
                    </div>
                <?php } ?>
            </div>
            
            <div class="modal modal-search fade" id="rejectRequestModal" tabindex="-1" role="dialog" aria-labelledby="rejectRequestModal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body rejectRequestBody">
                            <form action="" method="post" class="reject-request-form">
                                <div class="form-group">
                                    <label for="">Rejection Reason</label>
                                    <textarea name="reason" id="reason" cols="30" rows="10" style="max-height: 150px;" required class="form-control border border-primary p-3 rounded"></textarea>
                                    <div class="text-danger reason_error"></div>
                                </div>

                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-danger confirm-reject" value="reject_only">
                                            Reject Only
                                        </button>
                                    </div>

                                    <div class="col-lg-6"></div>

                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary btn-block confirm-reject" value="reject_and_refund">
                                            Reject & Refund
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> Pending Withdrawals</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table tablesorter" id="list-accounts">
                            <thead class="text-primary">
                                <tr>
                                    <th>
                                        S/N
                                    </th>
                                    <th>
                                        Institution Name
                                    </th>
                                    <th>
                                        Institution Type
                                    </th>
                                    <th>
                                        Bank Details
                                    </th>
                                    <th class="text-center">
                                        Amount
                                    </th>
                                    <th class="text-center">
                                        Balance
                                    </th>
                                    <th class="text-center">
                                        <?php if ($page_type == 'pending') { ?>
                                            Action
                                        <?php } else { ?>
                                            Status
                                        <?php } ?>
                                    </th>
                                    <?php if ($page_type == 'rejected') { ?>
                                        <th>
                                            Reason
                                        </th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($withdrawals) { $counter = 0; ?>
                                    <?php foreach ($withdrawals as $value) { ?>
                                        <tr>
                                            <td>
                                                <?php echo ++$counter; ?>
                                            </td>
                                            <td>
                                            <?php echo isset($value->acct_info->name) ? $value->acct_info->name : $value->acct_info->first_name.' '.$value->acct_info->last_name; ?>
                                            </td>
                                            <td>
                                                <?php echo ucwords(str_replace('-', ' ', $value->auth_type)); ?>
                                            </td>
                                            <td>
                                                <?php if ($value->bank_acct_info) { ?>
                                                    <p><?php echo $value->bank_acct_info->bank_name; ?></p>
                                                    <p><?php echo $value->bank_acct_info->acct_name; ?></p>
                                                    <p><?php echo $value->bank_acct_info->acct_number; ?></p>
                                                <?php } else echo '- - - - -'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo 'NGN '.number_format($value->amount, 2); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo 'NGN '.number_format($value->acct_balance, 2); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($page_type == 'pending') { ?>
                                                    <a href="<?php echo base_url('/admin/approve-withdrawal-request/'.$value->id); ?>" class="btn btn-primary btn-sm mr-1 mb-2 confirm-withdrawal-approval">
                                                        Approve
                                                    </a>

                                                    <a href="<?php echo base_url('/admin/reject-withdrawal-request/'.$value->id); ?>" class="btn btn-secondary btn-sm mr-1 mb-2 reject-withdrawal-request" data-toggle="modal" data-target="#rejectRequestModal">
                                                        Disapprove
                                                    </a>
                                                <?php } else { ?>
                                                    <?php echo ucwords($value->status); ?>
                                                <?php } ?>
                                            </td>
                                            <?php if ($page_type == 'rejected') { ?>
                                                <td><?php echo $value->reason ? (nl2br($value->reason)) : '- - - -'; ?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <p>No records found</p>
                                        </td>
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