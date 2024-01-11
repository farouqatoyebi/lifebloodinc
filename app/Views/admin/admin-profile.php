<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
?>
<div class="content">
    <div class="row">
        <div class="col-md-8">
            <form action="<?php echo base_url('/admin/submit-profile'); ?>" class="save-profile-form" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header">
                        <h5 class="title">Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 pl-md-1">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email address</label>
                                    <input type="email" class="form-control text-white" placeholder="mike@email.com" value="<?php echo isset($myProfile->email) ? $myProfile->email : ''; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pr-md-1">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" placeholder="First Name" name="wp_first_name" value="<?php echo isset($myProfile->first_name) ? $myProfile->first_name : ''; ?>" required>
                                    <small id="wp_first_name" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-6 pl-md-1">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" placeholder="Last Name" name="wp_last_name" value="<?php echo isset($myProfile->last_name) ? $myProfile->last_name : ''; ?>" required>
                                    <small id="wp_last_name" class="text-danger d-none"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" placeholder="Home Address" name="wp_address" value="<?php echo isset($myProfile->address) ? $myProfile->address : ''; ?>" required>
                                    <small id="wp_address" class="text-danger d-none"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 px-md-1">
                                <div class="form-group">
                                    <label>Country</label>
                                    <select name="wp_country" id="countries" class="form-control text-white" required>
                                        <option value="" selected disabled>--Choose--</option>
                                        <?php foreach ($allCountries as $key => $value) { ?>
                                            <option value="<?php echo $value->id; ?>" class="text-dark" <?php if (isset($myProfile->country) && $myProfile->country == $value->id) echo 'selected="selected"'; ?>>
                                                <?php echo $value->name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small id="wp_country" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-4 pr-md-1">
                                <div class="form-group">
                                    <label>State</label>
                                    <select name="wp_state" id="states" class="form-control text-white" required>
                                        <option value="" selected disabled>--Choose--</option>
                                        <?php foreach ($allStates as $key => $value) { ?>
                                            <option value="<?php echo $value->id; ?>" country="<?php echo $value->country_id; ?>" <?php if (isset($myProfile->state) && $myProfile->state == $value->id) echo 'selected="selected"'; ?>>
                                                <?php echo $value->name; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <small id="wp_state" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-4 pr-md-1">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" class="form-control" list="datalistOptions" placeholder="City" name="wp_city" id="cities" value="<?php echo isset($myProfile->city) ? $cityName = $baseController->getLocationValueBasedOnType($myProfile->city, 'city') : ''; ; ?>" required>
                                    <datalist id="datalistOptions">
                                        <?php foreach ($allCities as $key => $value) { ?>
                                            <option value="<?php echo $value->name; ?>" state="<?php echo $value->state_id; ?>">
                                        <?php } ?>
                                    </datalist>
                                    <small id="wp_city" class="text-danger d-none"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 pr-md-1">
                                <hr>
                                <p class="font-weight-bold">Socials</p>
                            </div>

                            <div class="col-md-4 pr-md-1">
                                <div class="form-group">
                                    <label>Facebook</label>
                                    <input type="text" class="form-control" name="wp_facebook" placeholder="Facebook" value="<?php echo isset($myProfile->facebook) ? $myProfile->facebook : ''; ?>">
                                    <small id="wp_facebook" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-4 px-md-1">
                                <div class="form-group">
                                    <label>Twitter</label>
                                    <input type="text" class="form-control" name="wp_twitter" placeholder="Twitter" value="<?php echo isset($myProfile->twitter) ? $myProfile->twitter : ''; ?>">
                                    <small id="wp_twitter" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-4 px-md-1">
                                <div class="form-group">
                                    <label>WhatsApp</label>
                                    <input type="text" class="form-control" name="wp_whatsapp" placeholder="WhatsApp" value="<?php echo isset($myProfile->whatsapp) ? $myProfile->whatsapp : ''; ?>">
                                    <small id="wp_whatsapp" class="text-danger d-none"></small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    
                                    <label for="profile_photo" style="cursor: pointer;">
                                        <p class="uploaded-logo">
                                            <?php if (session('profile_photo')) { ?>
                                                <img src="<?php echo base_url().'/uploads/images/profile_photo/'.session('profile_photo'); ?>" alt="..." class="img-fluid rounded" id="output" style="width: 50px; height: 50px;">
                                            <?php } else { ?>
                                                <i id="sillohuete" class="fa fa-user-circle-o fa-3x"></i>
                                                <img src="" alt="..." class="img-fluid d-none rounded" id="output" style="width: 50px; height: 50px;">
                                            <?php } ?>
                                        </p>
                                        <span class="text-success file_name h6 font-weight-lighter"></span>
                                        <p class="text-info logo-text btn btn-primary btn-sm mt-4 text-white">Upload Profile Photo</p>
                                        <small class="text-danger wp_logo"></small>
                                    </label>
                                    <input type="file" name="wp_profile_photo" id="profile_photo" class="form-control-file" accept="image/*">
                                    <small id="wp_profile_photo" class="text-danger d-none"></small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>About Me</label>
                                    <textarea rows="4" cols="80" class="form-control" name="wp_about_me" placeholder="Tell us a little about you"><?php echo isset($myProfile->about_me) ? $myProfile->about_me : ''; ?></textarea>
                                    <small id="wp_about_me" class="text-danger d-none"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary save-profile">Save Profile</button>

                        <div class="mt-4">
                            <div class="error_msg"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card card-user">
                <div class="card-body">
                    <p class="card-text">
                        <div class="author">
                        <div class="block block-one"></div>
                        <div class="block block-two"></div>
                        <div class="block block-three"></div>
                        <div class="block block-four"></div>
                        <a href="javascript:void(0)">
                            <?php if (session('profile_photo')) { ?>
                                <img src="<?php echo base_url().'/uploads/images/profile_photo/'.session('profile_photo'); ?>" alt="Profile Photo" class="avatar">
                            <?php } else { ?>
                                <img src="<?php echo base_url().'/webapp/images/doctors-image-small.png'; ?>" alt="Profile Photo" class="avatar">
                            <?php } ?>
                            <h5 class="title"><?php echo session('admin_name'); ?></h5>
                        </a>
                        <p class="description">
                            Ceo/Co-Founder
                        </p>
                        </div>
                    </p>
                    <div class="card-description">
                        <?php echo isset($myProfile->about_me) ? nl2br($myProfile->about_me) : ''; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="button-container">
                        <button href="javascript:void(0)" onclick="window.location.href='<?php echo base_url(); ?>'" class="btn btn-icon btn-round btn-facebook">
                            <i class="fab fa-facebook"></i>
                        </button>
                        <button href="javascript:void(0)" onclick="window.location.href='<?php echo base_url(); ?>'" class="btn btn-icon btn-round btn-twitter">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button href="javascript:void(0)" onclick="window.location.href='<?php echo base_url(); ?>'" class="btn btn-icon btn-round btn-google">
                            <i class="fab fa-google-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>