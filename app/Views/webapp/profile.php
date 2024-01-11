<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
    $cityName = '';
?>
<div class="content-body">
    <!-- row -->
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Profile</a></li>
            </ol>
        </div>
        
        <?php if (($userProfileInformation->reg_no && $userProfileInformation->reg_no == session('email')) || session('info')) { ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> You must complete your profile information before you proceed.
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="profile card card-body px-3 pt-3 pb-0">
                    <div class="profile-head">
                        <div class="photo-content">
                            <div class="cover-photo"></div>
                        </div>
                        <div class="profile-info">
                            <div class="profile-photo">
                                <?php if (session('logo')) { ?>
                                    <img src="<?php echo base_url().'/uploads/images/logo/'.session('logo'); ?>" class="img-fluid rounded-circle" alt="..." style="height: 100px; width: 100px;">
                                <?php } else { ?>
                                    <img src="<?php echo base_url().'/webapp/images/doctors-image-small.png'; ?>" class="img-fluid rounded-circle" alt="..." style="height: 100px; width: 100px;">
                                <?php } ?>
                            </div>
                            <div class="profile-details"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="profile-statistics mb-5">
                            <div class="text-left">
                                <div class="mt-4">
                                    <p class="">Account Type: &nbsp; <strong><?php echo $account_type = ucwords(str_replace("-", " ", session('acct_type'))); ?></strong></p>
                                    <p class="">Email: &nbsp; <strong><?php echo $thisAccountEmail = session('email') ? session('email') : '- - - - -'; ; ?></strong></p>
                                    <p class="">Phone: &nbsp; <strong><?php $thisAccountPhone = session('phone') ? session('phone') : '- - - - -'; echo '+'.$thisAccountPhone; ?></strong></p>
                                    <p class=""><?php echo $account_type.' Name'; ?>: &nbsp; <strong><?php echo session('name') ?></strong></p>
                                    <p class=""><?php echo $account_type.' City'; ?>: &nbsp; <strong><?php echo isset($userProfileInformation->city) ? $cityName = $baseController->getLocationValueBasedOnType($userProfileInformation->city, 'city') : '- - - - -'; ; ?></strong></p>
                                    <p class=""><?php echo $account_type.' State'; ?>: &nbsp; <strong><?php echo isset($userProfileInformation->state) ? $baseController->getLocationValueBasedOnType($userProfileInformation->state, 'state') : '- - - - -'; ; ?></strong></p>
                                    <p class=""><?php echo $account_type.' Country'; ?>: &nbsp; <strong><?php echo isset($userProfileInformation->country) ? $baseController->getLocationValueBasedOnType($userProfileInformation->country, 'country') : '- - - - -'; ; ?></strong></p>
                                    <p class=""><?php echo $account_type.' Address'; ?>: &nbsp; <strong><?php echo isset($userProfileInformation->Address) ? $userProfileInformation->Address : '- - - - -'; ; ?></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo base_url().'/profile'; ?>" method="POST" class="complete-profile" autocomplete="off">
                            <div class="form-group text-center">
                                <label for="files" style="cursor: pointer;">
                                    <p class="uploaded-logo">
                                        <?php if (session('logo')) { ?>
                                            <img src="<?php echo base_url().'/uploads/images/logo/'.session('logo'); ?>" alt="..." class="img-fluid rounded" id="output" style="width: 50px; height: 50px;">
                                        <?php } else { ?>
                                            <i id="sillohuete" class="fa fa-user-circle-o fa-3x"></i>
                                            <img src="" alt="..." class="img-fluid d-none rounded" id="output" style="width: 50px; height: 50px;">
                                        <?php } ?>
                                    </p>
                                    <span class="text-success file_name h6 font-weight-lighter"></span>
                                    <p class="text-info logo-text">Upload <?php echo ucwords($account_type); ?> Logo</p>
                                    <small class="text-danger wp_logo"></small>
                                </label>
                                <div>
                                    <input type="file" name="wp_logo" id="files" style="visibility:hidden;" accept="image/*">
                                </div>
                            </div>

                            <input type="hidden" class="account_type" value="<?php echo ucwords($account_type); ?>">

                            <div class="form-group">
                                <label class="mb-1"><strong><?php echo ucwords($account_type).' Name'; ?></strong></label>
                                <input name="wp_name" type="text" class="form-control" placeholder="<?php echo ucwords($account_type).' Name'; ?>" value="<?php echo session('name'); ?>" required>
                                <small class="text-danger wp_name"></small>
                            </div>

                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-6">
                                        <label class="mb-1"><strong>Email</strong></label>
                                        <input name="wp_email" type="email" class="form-control disabled" placeholder="hello@example.com" value="<?php echo $thisAccountEmail; ?>" disabled>
                                        <small class="text-danger wp_email"></small>
                                    </div>
                                    
                                    <div class="col-6">
                                        <label class="mb-1"><strong>Phone Number</strong></label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-white border border-dark" id="basic-addon1">+ 234</span>
                                            <?php 
                                                $pos = strpos($thisAccountPhone, '234');
                                                if ($pos !== false) {
                                                    $thisAccountPhone = substr_replace($thisAccountPhone, '', $pos, strlen('234'));
                                                }
                                            ?>
                                            <input name="wp_phone" type="tel" class="form-control" placeholder="e.g. 8123456789" value="<?php echo $thisAccountPhone; ?>">
                                            <small class="text-danger wp_phone"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="mb-1"><strong>Location</strong></label>
                                <input name="wp_location" type="text" class="form-control" value="<?php echo $userProfileInformation->Address; ?>" required>
                                <small class="text-danger wp_location"></small>
                            </div>

                            <div class="form-group mt-4">
                                <div class="form-row">
                                    <div class="col-4">
                                        <label class="mb-1"><strong>Country</strong></label>
                                        <select name="wp_country" id="countries" class="form-control" required>
                                            <option value="">Choose...</option>
                                            <?php foreach ($allCountries as $key => $value) { ?>
                                                <option value="<?php echo $value->id; ?>"  <?php if ($userProfileInformation->country == $value->id) echo 'selected="selected"'; ?>>
                                                    <?php echo $value->name; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <small class="text-danger countries"></small>
                                    </div>
                                    
                                    <div class="col-4">
                                        <label class="mb-1"><strong>State</strong></label>
                                        <select name="wp_state" id="states" class="form-control" required>
                                            <option value="">Choose...</option>
                                            <?php foreach ($allStates as $key => $value) { ?>
                                                <option value="<?php echo $value->id; ?>" <?php if ($userProfileInformation->state == $value->id) echo 'selected="selected"'; ?> country="<?php echo $value->country_id; ?>">
                                                    <?php echo $value->name; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <small class="text-danger states"></small>
                                    </div>

                                    <div class="col-4">
                                        <label for="cities" class="form-label"><strong>City</strong></label>
                                        <input name="wp_city" id="cities" class="form-control" required list="datalistOptions" value="<?php echo $cityName; ?>" placeholder="City...">
                                        <datalist id="datalistOptions">
                                            <?php foreach ($allCities as $key => $value) { ?>
                                                <option value="<?php echo $value->name; ?>" state="<?php echo $value->state_id; ?>">
                                            <?php } ?>
                                        </datalist>
                                        <small class="text-danger cities"></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="mb-1"><strong>Goverment Reg No.</strong></label>
                                <input name="wp_reg_no" type="text" class="form-control" placeholder="Goverment Reg No." value="<?php if ($userProfileInformation->reg_no && $userProfileInformation->reg_no != session('email')) echo $userProfileInformation->reg_no; ?>" required>
                                <small class="text-danger wp_reg_no"></small>
                            </div>
                            
                            <hr>

                            <div class="form-group">
                                <label class="mb-1"><strong>Upload CAC Document</strong></label>
                                <input type="file" name="wp_file_cac" id="cac_doc" class="form-control-file mt-2" accept="image/*, .doc, .docx, .txt, .csv, .pdf" />
                                <small class="text-danger wp_file_cac"></small>
                            </div>

                            <hr>

                            <p>Owner's Information</p>

                            <div class="form-row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" class="form-control" id="owners_first_name" value="<?php echo $userProfileInformation->owners_firstname; ?>" required />
                                        <small class="text-danger owners_first_name"></small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="owners_last_name" value="<?php echo $userProfileInformation->owners_lastname; ?>" required />
                                        <small class="text-danger owners_last_name"></small>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="owners_email" value="<?php echo $userProfileInformation->email ?? session('email'); ?>" required />
                                        <small class="text-danger owners_email"></small>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="mb-1"><strong>Phone</strong></label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-white border border-dark" id="basic-addon1">+ 234</span>
                                            <input type="tel" id="owners_phone" class="form-control" placeholder="e.g. 8123456789" value="<?php echo $userProfileInformation->owners_bvn ?? $thisAccountPhone; ?>">
                                            <small class="text-danger owners_phone"></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="bvn">BVN</label>
                                        <input type="text" class="form-control" id="owners_bvn" value="<?php echo $userProfileInformation->owners_bvn; ?>" required />
                                        <small class="text-danger owners_bvn"></small>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="nin">NIN</label>
                                        <input type="text" class="form-control" id="owners_nin" value="<?php echo $userProfileInformation->owners_nin; ?>" required />
                                        <small class="text-danger owners_nin"></small>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="nin">Registration Date</label>
                                        <input type="date" class="form-control" id="owners_reg_date" value="<?php echo $userProfileInformation->reg_date; ?>" required />
                                        <small class="text-danger owners_reg_date"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <div class="row">
                                    <div class="col-lg-4 mx-auto">
                                        <button type="submit" class="btn btn-primary btn-block profile-btn">
                                            Update Profile
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="error_msg"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>