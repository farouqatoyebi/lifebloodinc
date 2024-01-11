<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
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
                <?php if (count($deliveryInformationBreakdown)) { ?>
                    <div class="my-2">
                        <h4 class="text-muted"><?php echo $page_title; ?></h4>
                    </div>

                    <div class="row mt-5">
                        <div class="col-12 col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <table class="table table bordered table-stripped">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>S/N</th>
                                                <th>Donor Name</th>
                                                <th>Donor Type</th>
                                                <th>Total Amount</th>
                                                <th>Delivery Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $counter = 0; ?>
                                            <?php foreach ($deliveryInformationBreakdown as $key => $value) { $counter++; ?>
                                                <?php 
                                                    $donorInformation = $baseController->getAccountInformationBasedOnID($value->donor_type, $value->donor_id); 
                                                    $donorType = ucwords(str_replace('-', ' ', $value->donor_type));
                                                ?>
                                                <tr>
                                                    <td><?php echo $counter; ?></td>
                                                    <td><?php echo ucwords($donorInformation->name); ?></td>
                                                    <td><?php echo $donorType; ?></td>
                                                    <td><?php echo 'NGN '.number_format($value->total_amount); ?></td>
                                                    <td>
                                                        <?php 
                                                            if ($value->status == 'pending') {
                                                                $badgeClass = 'badge badge-warning';
                                                            }
                                                            elseif ($value->status == 'completed') {
                                                                $badgeClass = 'badge badge-success';
                                                            }
                                                            else {
                                                                $badgeClass = 'badge badge-danger';
                                                            }
                                                        ?>
                                                        <span class="<?php echo $badgeClass; ?>"><?php echo $value->status; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($value->status == 'pending') { ?>
                                                            <button class="btn btn-primary btn-block btn-sm" data-toggle="modal" otp="<?php echo $value->otp_code; ?>" donor_name="<?php echo $donorInformation->name; ?>" donor_type="<?php echo $donorType; ?>" data-target="#acceptOfferModal">
                                                                Confirm Delivery
                                                            </button>
                                                        <?php } else { ?>
                                                            - - -
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="acceptOfferModal" tabindex="-1" aria-labelledby="acceptOfferLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title text-center" id="acceptOfferLabel">Confirm Delivery</h3>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-10 mx-auto text-center">
                                                    <p class="text-info mb-5">
                                                        <i class="fa fa-info-circle"></i> Provide the OTP below to the <span class="donor_type"></span> to confirm their blood delivery.
                                                    </p>

                                                    <h3 class="h3 donor_name mb-4 font-weight-bold text-secondary"></h3>

                                                    <h3 class="h3 otpCode"></h3>

                                                    <p class="mt-5">
                                                        <a href="" class="btn btn-outline-secondary btn-sm">
                                                            Verify Delivery
                                                        </a>
                                                    </p>
                                                </div>
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
    </div>
</div>