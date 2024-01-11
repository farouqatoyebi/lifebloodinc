<?php 
    $url = $_SERVER['PATH_INFO'];
    $exploded_url = explode('/', $url);
    $accountType = '';

    if (isset($exploded_url[2])) {
        $display_form = true;
        $accountType = str_replace('-', ' ', urldecode($exploded_url[2]));
    }
    else {
        $display_form = false;
    }

    if ($accountType == 'hospital') {
        $imageUrl = base_url('/webapp/images/hospital-register-bg-1.png');
    }
    elseif ($accountType == 'pharmacy') {
        $imageUrl = base_url('/webapp/images/pharmacy-register-bg-1.png');
    }
    else {
        $imageUrl = base_url('/webapp/images/blood-bank-register-bg-1.png');
    }
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register</title>
    <meta name="description" content="Register your account"/>
    <link rel="icon" type="image/png" sizes="16x16" href="http://betalifehealth.com/images/logo/logo-500.png">
    <link href="<?php echo base_url().'/webapp/css/style.css'; ?>" rel="stylesheet" type="text/css"/>
    
        <style>
            .background-image {
                background-color: #c5bdbd5c;
                background-size: cover;
                background-repeat: no-repeat;
            }

            @media (min-width: 1300px) {
                .d-xl-block {
                    display: block !important;
                }
            }
            
        </style>
    
</head>

<body class="background-image">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="d-flex align-items-center justify-content-center vh-100">
                    <div class="w-100">
                        <?php if ($display_form) { ?>
                            <div class="card">
                                <div class="auth-form card-body">
                                    <h4 class="mb-4">Sign up as <?php echo ucwords($accountType); ?></h4>
                                    <div class="error_msg"></div>

                                    <form action="<?php echo base_url().'/register/account'; ?>" enctype="multipart/form-data" method="POST" class="signup">
                                        <input type="hidden" name="wp_acct_type" value="<?php echo $accountType; ?>">
                                        <div class="form-group">
                                            <label class="mb-1"><strong><?php echo ucwords($accountType).' Name'; ?></strong></label>
                                            <input name="wp_name" type="text" class="form-control" placeholder="<?php echo ucwords($accountType).' Name'; ?>" required>
                                            <small class="text-danger wp_name"></small>
                                        </div>

                                        <div class="form-group">
                                            <div class="form-row">
                                                <div class="col-6">
                                                    <label class="mb-1"><strong>Email</strong></label>
                                                    <input name="wp_email" type="email" class="form-control" placeholder="hello@example.com" required>
                                                    <small class="text-danger wp_email"></small>
                                                </div>
                                                
                                                <div class="col-6">
                                                    <label class="mb-1"><strong>Phone Number</strong></label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text bg-white border border-dark" id="basic-addon1">+ 234</span>
                                                        <input name="wp_phone" type="tel" class="form-control" placeholder="e.g. 8123456789" required>
                                                        <small class="text-danger wp_phone"></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="col-6">
                                                <div class="form-group mt-4">
                                                    <label class="mb-1"><strong>Password</strong></label>
                                                    <input name="wp_password" type="password" class="form-control" required>
                                                    <small class="text-danger wp_password"></small>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="form-group mt-4">
                                                    <label class="mb-1"><strong>Confirm Password</strong></label>
                                                    <input name="wp_confirm_password" type="password" class="form-control" required>
                                                    <small class="text-danger wp_confirm_password"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary btn-block sign-up-btn">Sign up</button>
                                        </div>
                                    </form>

                                    <div class="new-account mt-4 text-center">
                                        <p>Already have an account? <a class="text-primary" href="<?php echo base_url().'/login'; ?>">Sign in</a></p>
                                    </div>

                                    <p class="text-right mt-4 mb-0">
                                        <a href="<?php echo base_url('register'); ?>"><i class="fa fa-arrow-left"></i> Start Over</a>
                                    </p>
                                </div>
                            </div>
                        <?php } else { ?>
                            <p class="text-center h2 mb-5">Register As</p>

                            <div class="rounded mb-3">
                                <a href="<?php echo base_url().'/register/hospital'; ?>">
                                    <div class="d-flex justify-content-between border border-dark bg-white shadow p-5 rounded">
                                        <span>
                                            <h5 class="text-dark">Hospital</h5>
                                            <small class="text-muted">Register as a hospital to get closer to blood banks, donors etc</small>
                                        </span>
                                        <i class="fa fa-arrow-right mt-1"></i>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="rounded mb-3">
                                <a href="<?php echo base_url().'/register/blood-bank'; ?>">
                                    <div class="d-flex justify-content-between border border-dark bg-white shadow p-5 rounded">
                                        <span>
                                            <h5 class="text-dark">Blood Bank</h5>
                                            <small class="text-muted">Register as a hospital to deliver blood to hospitals, patients etc</small>
                                        </span>
                                        <i class="fa fa-arrow-right mt-1"></i>
                                    </div>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?php echo base_url().'/webapp/vendor/global/global.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/custom.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/deznav-init.js'; ?>" type="text/javascript"></script>

    <script>
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };

        $(function(e){
            $("body").on("submit", "form.signup", function(e){
                e.preventDefault();
                $("small.text-danger").addClass("d-none");
                $("input").removeClass("border border-danger");
                $('.sign-up-btn').addClass("disabled");
                $('.sign-up-btn').attr("disabled", "disabled");
                $('.sign-up-btn').html('Sign Up &nbsp; &nbsp; <i class="fa fa-spin fa-spinner"></i>');
                $(".error_msg").html("");

                var there_is_an_error = false;

                var account_name = $("input[name='wp_name']").val();
                var account_email = $("input[name='wp_email']").val();
                var account_phone = $("input[name='wp_phone']").val();
                var account_password = $("input[name='wp_password']").val();
                var account_cpassword = $("input[name='wp_confirm_password']").val();
                var account_type = $("input[name='wp_acct_type']").val();

                if (!account_name) {
                    $("input[name='wp_name']").addClass("border border-danger");
                    $("small.wp_name").text("Required");
                    $("small.wp_name").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_email) {
                    $("input[name='wp_email']").addClass("border border-danger");
                    $("small.wp_email").text("Required");
                    $("small.wp_email").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_phone) {
                    $("input[name='wp_phone']").addClass("border border-danger");
                    $("small.wp_phone").text("Required");
                    $("small.wp_phone").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_password) {
                    $("input[name='wp_password']").addClass("border border-danger");
                    $("small.wp_password").text("Required");
                    $("small.wp_password").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_cpassword) {
                    $("input[name='wp_confirm_password']").addClass("border border-danger");
                    $("small.wp_confirm_password").text("Required");
                    $("small.wp_confirm_password").removeClass("d-none");
                    there_is_an_error = true;
                }
                else if (account_cpassword != account_password) {
                    $("input[name='wp_confirm_password']").addClass("border border-danger");
                    $("small.wp_confirm_password").text("Passwords do not match");
                    $("small.wp_confirm_password").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (there_is_an_error) {
                    $(".sign-up-btn").removeClass("disabled");
                    $(".sign-up-btn").removeAttr("disabled");
                    $(".sign-up-btn").html('Sign Up');

                    return ;
                }

                var formArray = $(this).serialize();

                $.ajax({
                    method : "POST",
                    url : $(this).attr("action"),
                    data : formArray,
                    success:function(response) {
                        if (response.status == 200) {
                            $('.signup')[0].reset();
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            
                            loadMyNewPage('body', "<?php echo base_url().'/verify-otp/'; ?>"+response.otp_token, true);
                        }
                        else {
                            if (response.message) {
                                $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                            }

                            if (response.validation) {
                                $.each(response.validation, function(x, y) {
                                    if ($('small.'+x).length) {
                                        $("input[name='"+x+"']").addClass("border border-danger");
                                        $("small."+x+"").text(y);
                                        $("small."+x+"").removeClass("d-none");
                                    }
                                })
                            }
                        }
                    },
                    error:function(e) {

                    },
                    complete:function() {
                        $(".sign-up-btn").removeClass("disabled");
                        $(".sign-up-btn").removeAttr("disabled");
                        $(".sign-up-btn").html('Sign Up');
                    }
                });
            });
        })
    </script>
</body>
</html>