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
                    <i class="fa fa-info-circle"></i> If the blood type currently isn't available in your bank, you can just set a zero price for it.
                </small>
            </p>
            <p class="text-right">
                <small class="text-danger font-weight-bold mt-4">NOTE: Prices are stored in Nigerian NGN.</small>
            </p>
            <hr>
        </div>

        <form action="<?php echo base_url().'/settings/set-rates'; ?>" method="POST" class="set-rates">
            <!-- row -->
            <div class="form-row">
                <div class="col-lg-9 mx-auto">
                    <div class="error_msg"></div>
                    <div class="row">
                        <?php foreach ($bloodBankRates as $value) { $bloodGroupName = isset($value->name) ? $value->name : $value->blood_group; ?>
                            <div class="form-group col-lg-6 mb-3">
                                <label for="" class="font-weight-bold"><?php echo 'Price for '.$bloodGroupName ?></label>
                                <input type="number" class="form-control rounded wp-set-rates" value="<?php echo isset($value->rate) ? $value->rate : '0'; ?>" placeholder="Price per pint for <?php echo $bloodGroupName ?>" name="<?php echo 'wp_blood_price['.$bloodGroupName.']' ?>" min="0" required>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="form-group mt-4 text-right">
                        <button type="submit" class="btn btn-primary rounded rates-btn">
                            Set Rates
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>