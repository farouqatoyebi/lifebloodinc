<!DOCTYPE html>
<html lang="en" class="h-100">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
    <meta name="description" content="Login to your account"/>
    <link rel="icon" type="image/png" sizes="16x16" href="http://betalifehealth.com/images/logo/logo-500.png">
    <link href="<?php echo base_url().'/webapp/css/style.css'; ?>" rel="stylesheet" type="text/css"/>

    <style>
        @media (min-width: 1300px) {
            .d-xl-block {
                display: block !important;
            }
        }

        .background-image {
            background-color: #c5bdbd5c;
            background-size: cover;
            background-repeat: no-repeat;
        }
    </style>
</head>

<body class="background-image">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-5 mx-auto">
                <div class="d-flex align-items-center justify-content-center vh-100">
                    <div class="w-100">
                        <div class="card">
                            <div class="auth-form">
                                <h4 class="text-center mb-4">Sign in your account</h4>
                                <form action="<?php echo base_url().'/sign-in'; ?>" class="signin" autocomplete="off">
                                    <div class="form-group">
                                        <label class="mb-1"><strong>Email</strong></label>
                                        <input name="wp_email" type="email" class="form-control" placeholder="hello@example.com" required>
                                        <small class="text-danger wp_email"></small>
                                    </div>

                                    <div class="form-group">
                                        <label class="mb-1"><strong>Password</strong></label>
                                        <input name="wp_password" type="password" class="form-control" required>
                                        <small class="text-danger wp_password"></small>
                                    </div>

                                    <div class="form-row mt-4 mb-2">
                                        <div class="form-group col-lg-12">
                                            <div class="custom-control custom-checkbox ml-1">
                                                <input type="checkbox" class="custom-control-input" id="basic_checkbox_1">
                                                <label class="custom-control-label" for="basic_checkbox_1">Remember my preference</label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group col-lg-12 text-right">
                                            <a href="#">Forgot Password?</a>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-block sign-in-btn">Log In</button>
                                    </div>
                                </form>
                                
                                <div class="new-account mt-3">
                                    <p>Don't have an account? <a class="text-primary" href="<?php echo base_url().'/register'; ?>">Sign up</a></p>
                                </div>
                            </div>
                        </div>
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
        $(function(e){
            $("body").on("submit", "form.signin", function(e){
                e.preventDefault();
                $("small.text-danger").addClass("d-none");
                $("input").removeClass("border border-danger");
                $(".sign-in-btn").addClass("disabled");
                $(".sign-in-btn").attr("disabled", "disabled");
                $(".sign-in-btn").html('Log In &nbsp; &nbsp; <i class="fa fa-spin fa-spinner"></i>')

                var there_is_an_error = false;

                var account_email = $("input[name='wp_email']").val();
                var account_password = $("input[name='wp_password']").val();

                if (!account_email) {
                    $("input[name='wp_email']").addClass("border border-danger");
                    $("small.wp_email").text("Required");
                    $("small.wp_email").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_password) {
                    $("input[name='wp_password']").addClass("border border-danger");
                    $("small.wp_password").text("Required");
                    $("small.wp_password").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (there_is_an_error) {
                    return ;
                }

                // var formArray = $(this).serialize();

                var dis_form = document.querySelector('form');
                var formArray = new FormData(dis_form);

                formArray.append("wp_email", account_email);
                formArray.append("wp_password", account_password);

                $.ajax({
                    method : "POST",
                    url : $(this).attr("action"),
                    processData: false,
                    contentType: false,
                    cache: false,
                    data : formArray,
                    success:function(data) {
                        var response = (data);

                        if (response.auth_status == 'success') {
                            if (response.otp_url) {
                                loadMyNewPage('body', response.otp_url, true);
                            }
                            else {
                                $(".sign-in-btn").html('Logging you in &nbsp; <i class="fa fa-spin fa-spinner"></i>');
                                $(".sign-in-btn").attr("disabled", "disabled");

                                setTimeout(() => {
                                    window.location = "<?php echo base_url().'/dashboard'; ?>";
                                }, 2000);
                            }
                        }
                        else {
                            $("input[name='wp_email']").addClass("border border-danger");
                            $("small.wp_email").html('<i class="fa fa-info-circle"></i> '+response.message);
                            $("small.wp_email").removeClass("d-none");
                            $(".sign-in-btn").html('Log In');
                        }
                    },
                    error:function(e) {

                    },
                    complete:function() {
                        setTimeout(() => {
                            $(".sign-in-btn").removeClass("disabled");
                            $(".sign-in-btn").removeAttr("disabled");
                            $(".sign-in-btn").html('Log In');
                        }, 4000);
                    }
                });
            });
        });
    </script>
</body>
</html>