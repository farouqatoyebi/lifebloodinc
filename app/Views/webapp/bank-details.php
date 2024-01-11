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

        <div class="title">
            <p class="font-weight-bold mb-4">
                <small class="text-info">
                    <i class="fa fa-info-circle"></i> Bank Account for Payment Disbursement
                </small>
            </p>
            <hr>
        </div>

        <form action="<?php echo base_url().'/settings/bank-information'; ?>" method="POST" class="bank-information" autocomplete="off">
            <!-- row -->
            <div class="form-row">
                <div class="col-lg-9 mx-auto">
                    <?php if ($displayWarning) { ?>
                        <div class="alert alert-info">
                            <p class="mb-0 font-weight-bold text-center">
                                <i class="fa fa-info-circle"></i> You must provide your bank details before you can make a withdrawal.
                            </p>
                        </div>
                    <?php } ?>

                    <div class="error_msg"></div>
                    <div class="row">
                        <div class="form-group col-lg-12 mb-3">
                            <label for="" class="font-weight-bold">Bank Name: </label>
                            <select name="wp_bank_name" id="" class="form-control rounded wp-bank-deets" required>
                                <option value="">Choose Bank</option>
                                <option value="" disabled>- - - -</option>

                                <?php foreach ($availableBanks as $value) { ?>
                                    <option value="<?php echo $value->code; ?>" <?php if ($bankDetails) { if ($bankDetails->bank_name == $value->name) echo 'selected="selected"';}; ?>>
                                        <?php echo $value->name; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <small class="text-danger error"></small>
                        </div>
                        
                        <div class="form-group col-lg-12 mb-3">
                            <label for="" class="font-weight-bold">Account Name: </label>
                            <input type="text" class="form-control rounded wp-bank-deets" value="<?php echo $bankDetails ? $bankDetails->acct_name : ''; ?>" placeholder="Account Name" name="wp_acct_name" min="0" required>
                            <small class="text-danger error"></small>
                        </div>
                        
                        <div class="form-group col-lg-12 mb-3">
                            <label for="" class="font-weight-bold">Account Number: </label>
                            <input type="tel" class="form-control rounded wp-bank-deets" value="<?php echo $bankDetails ? $bankDetails->acct_number : ''; ?>" placeholder="Account Number" name="wp_account_number" min="0" required>
                            <small class="text-danger error"></small>
                        </div>
                        
                        <div class="form-group col-lg-12 mb-3">
                            <label for="" class="font-weight-bold">Sort Code: </label>
                            <input type="text" class="form-control rounded wp-bank-deets" value="<?php echo $bankDetails ? $bankDetails->sort_code : ''; ?>" placeholder="Sort Code" name="wp_sort_code" min="0">
                            <small class="text-danger error"></small>
                        </div>
                    </div>

                    <div class="form-group mt-4 text-right">
                        <button type="submit" class="btn btn-primary rounded bank-info-btn">
                            Save Bank Information
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>