<?php 
    use App\Controllers\BaseController; 
    $allowMultipleBloodGroupsSubmission = true;
    $baseController = new BaseController();
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>
        <form action="POST" class="make-blood-request">
            <!-- row -->
            <div class="row">
                <div class="col-xl-6 d-none col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><?php echo $acccontTypeName = ucwords(str_replace("-", " ", session('acct_type'))); ?> Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <div class="form-group">
                                    <label for=""><?php echo $acccontTypeName.' Name'; ?></label>
                                    <input type="text" name="wp_name" class="form-control input-default disabled " placeholder="..." value="<?php echo session('name'); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $acccontTypeName.' Location'; ?></label>
                                    <div class="row p-2">
                                        <div class="col-3 px-1">
                                            <input type="text" name="wp_location" class="form-control disabled" value="<?php echo $userProfileInformation->Address; ?>" placeholder="<?php echo $acccontTypeName.' Location'; ?>" disabled>
                                        </div>
                                        
                                        <div class="col-3 px-1">
                                            <input type="text" name="wp_city" class="form-control disabled" value="<?php echo $baseController->getLocationValueBasedOnType($userProfileInformation->city, 'city'); ?>" placeholder="<?php echo $acccontTypeName.' City'; ?>" disabled>
                                        </div>
                                        
                                        <div class="col-3 px-1">
                                            <input type="text" name="wp_state" class="form-control disabled" value="<?php echo $baseController->getLocationValueBasedOnType($userProfileInformation->state, 'state'); ?>" placeholder="<?php echo $acccontTypeName.' State'; ?>" disabled>
                                        </div>
                                        
                                        <div class="col-3 px-1">
                                            <input type="text" name="wp_country" class="form-control disabled" value="<?php echo $baseController->getLocationValueBasedOnType($userProfileInformation->country, 'country'); ?>" placeholder="<?php echo $acccontTypeName.' Country'; ?>" disabled>
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="form-group text-right">
                                    <button type="button" class="btn btn-outline-primary btn-sm submit-details">
                                        Continue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12">
                    <div class="card other-details">
                        <div class="card-header">
                            <h4 class="card-title">Request Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="basic-form">
                                <div class="form-group">
                                    <label for="">Short Description</label>
                                    <textarea name="wp_short_desc" class="form-control rounded p-3 h-50" id="short_desc"></textarea>
                                    <small class="text-danger short_desc"></small>
                                </div>

                                <div class="form-group">
                                    <?php $allUrgencyLevels = $baseController->getAllUrgencyLevels(); $increment = 0; ?>

                                    <label for="">Urgency level</label>
                                    <p class="text-danger range-level text-center font-weight-bold d-block">Urgency Level: <?php echo $allUrgencyLevels[0]->name; ?></p>
                                    <input type="range" name="wp_urgency_level" class="form-control multi-range" list="markers" value="1" min="1" max="<?php echo count($allUrgencyLevels); ?>" step="1" id="urgency_level">
                                    <datalist id="markers">
                                        <?php foreach ($allUrgencyLevels as $key => $value) { $increment++; ?>
                                            <option value="<?php echo $increment; ?>" level-name="<?php echo $value->name; ?>"></option>
                                        <?php } ?>
                                    </datalist>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-5 mt-4">
                                            <label for="">Due Date</label>
                                            <input type="date" name="wp_due_date" id="due_date" class="form-control rounded" required min="<?php echo date("Y-m-d"); ?>">
                                            <small class="text-danger due_date"></small>
                                        </div>

                                        <div class="col-md-7 mt-4">
                                            <label for="">Number of Pints</label>
                                            <div class="row">
                                                <?php $allBloodGroups = $baseController->getAllBloodGroups(); ?>
                                                <?php if ($allBloodGroups) { ?>
                                                    <?php if (count($allBloodGroups)) { ?>
                                                        <?php if ($allowMultipleBloodGroupsSubmission) {?>
                                                            <?php foreach ($allBloodGroups as $key => $value) { ?>
                                                                <div class="input-group mb-2 col-6">
                                                                    <span class="input-group-text bg-danger text-white border border-danger"><?php echo $value->name; ?></span>
                                                                    <input name="wp_blood_pint[<?php echo $value->name; ?>]" type="number" class="form-control rounded blood_pint_input text-center" group="<?php echo $value->name; ?>" placeholder="e.g. <?php echo rand(1, 30); ?>">
                                                                </div>
                                                            <?php } ?>
                                                            <small class="text-danger blood_pint"></small>
                                                        <?php } else { ?>
                                                            <div class="input-group mb-2 col-12">
                                                                <div class="input-group-prepend">
                                                                    <select name="wp_blood_group" class="form-control text-danger" id="blood_group">
                                                                        <option value="" selected disabled>Choose...</option>
                                                                        <option disabled>- - - - -</option>
                                                                        <?php foreach ($allBloodGroups as $key => $value) { ?>
                                                                            <option value="<?php echo $value->name; ?>" group="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <input name="wp_blood_pint" id="blood_pint" type="number" class="form-control rounded text-center" placeholder="e.g. 7">
                                                            </div>
                                                            <div class="col-12">
                                                                <small class="text-danger blood_error"></small>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <small class="text-danger pint_error"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <div class="row">
                                        <div class="col-md-6 col-12 ml-auto">
                                            <button type="button" class="btn btn-primary btn-block btn-sm generate-request">
                                                Generate Request
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card request-summary d-none">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5 col-12">
                                    <p class="text-muted">Blood Request #<?php echo rand(1000, 9999); ?></p>
                                </div>

                                <div class="col-md-7 col-12">
                                    <p class="font-weight-bold"><?php echo session('name'); ?></p>
                                </div>

                                <div class="col-12">
                                    <label for="" class="font-weight-bold text-muted">Address</label>
                                    <input type="text" name="" id="" class="form-control disabled px-5" value="<?php echo $userProfileInformation->Address; ?>" disabled>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="font-weight-bold text-muted" for="">Number of Pints</label>
                                        </div>

                                        <div class="col-md-8 col-12">
                                            <div class="row">
                                                <?php if ($allBloodGroups) { ?>
                                                    <?php if (count($allBloodGroups)) { ?>
                                                        <?php foreach ($allBloodGroups as $key => $value) { ?>
                                                            <div class="col-md-3 mt-2 col-6 req_pint_a d-none mt-4" target="<?php echo $value->name; ?>">
                                                                <div class="input-group">
                                                                    <span class="input-group-text text-white no-border-radius bg-danger"><?php echo $value->name; ?></span>
                                                                    <input type="text" name="" class="form-control text-center final_pint disabled border border-danger" target="<?php echo $value->name; ?>"  disabled>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 mt-4">
                                            <div class="input-group">
                                                <span class="input-group-text text-white border border-primary bg-primary">Urgency Level:</span>
                                                <input type="text" name="" id="final_urgency_level" class="form-control disabled" value="" disabled>
                                            </div>
                                        </div>

                                        <div class="col-12 d-none short_desc_div">
                                            <div class="form-group mt-5">
                                                <div class="form-row">
                                                    <div class="col-!2 col-md-8">
                                                        <div class="card">
                                                            <div class="card-body shadow rounded">
                                                                <h6 class="text-muted h6">Short Description</h6>
                                                                <hr/>
                                                                <p id="short_desc_text"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-5 col-md-6">
                                            <div class="form-group">
                                                <p><span class="text-muted">Due Date: </span> <span class="font-weight-bold" id="due_date_display"></span></p>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-5 col-md-6">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <button type="button" class="btn btn-primary btn-block edit-request-btn">
                                                            Edit Request
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-outline-danger btn-block make-request-btn">
                                                            Make Request
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="error_msg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>