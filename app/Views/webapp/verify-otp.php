<!DOCTYPE html>
<html lang="en" class="h-100">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BetaLife | Verify Your Account</title>
    <meta name="description" content="Verify your account"/>
    <link rel="icon" type="image/png" sizes="16x16" href="http://betalifehealth.com/images/logo/logo-500.png">
    <link href="<?php echo base_url().'/webapp/css/style.css'; ?>" rel="stylesheet" type="text/css"/>
    <style>
        .styled-height {
            width: auto;
            height: 250px;
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance:textfield; /* Firefox */
        }
    </style>
</head>

<body class="h-100">
    <nav>
        <img src="<?php echo base_url('/webapp/images/betalife-health-new-logo.png'); ?>" class="nav-header img-fluid bg-transparent" style="width: 100px; height: 100px;" alt="BetaLife Logo" srcset="<?php echo base_url('/webapp/images/betalife-health-new-logo.png'); ?>">
    </nav>
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-7">
                    <?php if ($otpInformation) { ?>
                        <div class="authincation-content">
                            <div class="row no-gutters">
                                <div class="col-xl-12">
                                    <div class="auth-form">
                                        <h4 class="mb-4">Enter OTP</h4>

                                        <div class="p-4">
                                            <p class="mb-0">A 6 digit code has been sent to the email below</p>
                                            <p>Enter it here</p>
                                        </div>

                                        <div class="text-center mb-3">
                                            <img src="<?php echo base_url().'/webapp/images/otp-image.png'; ?>" alt="" class="img-fluid styled-height">
                                        </div>

                                        <form action="<?php echo base_url().'/confirm-user-otp'; ?>" class="verify-otp">
                                            <p class="mt-4 border-bottom ml-5">
                                                @ <span class="ml-4"><?php echo $otpInformation->email; ?></span>
                                            </p>

                                            <?php if (strpos(getenv("app.baseURL"), 'dashboard') === FALSE) { ?>
                                                <p class="text-right">
                                                    Your local OTP: <?php echo $otpInformation->verification_otp ?>
                                                </p>
                                            <?php } ?>

                                            <div class="error_msg"></div>
                                            <p class="text-muted">Enter Here</p>
                                            <div class="row">
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_1" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_2" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_3" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_4" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_5" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                                <div class="col-2 p-2 p-lg-3">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-center" name="wp_number_6" min="0" max="9" maxlength="1" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mt-5">
                                                <div class="row">
                                                    <div class="col-6 mx-auto">
                                                        <button type="submit" class="btn btn-primary btn-block verify-otp-btn">
                                                            Submit
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <div class="new-account mt-4 text-center">
                                            <p>Didn't get a code? <a class="text-primary resend-otp" href="<?php echo base_url().'/resend-user-otp/'.$otpInformation->token; ?>">Resend OTP</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="new-account mt-2 text-center pb-1">
                                <p>Don't have an account? <a class="text-danger" href="<?php echo base_url().'/register'; ?>">Sign Up</a></p>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="shadow p-4">
                            <div class="text-center">
                                <img src="<?php echo base_url().'/webapp/images/invalid-otp-information.png'; ?>" alt="" class="img-fluid">
                            </div>

                            <div class="text-center">
                                <div class="font-weight-bold">
                                    <p class="mb-0">Looks like you may have wandered away from where you intended.</p>
                                    <p class="mb-1">Not to worry, we can get you back on track.</p>
                                    <p class="mt-4">
                                        <a href="<?php echo base_url().'/login'; ?>" class="btn btn-primary">
                                            <i class="fa fa-home"></i> Go Home
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?php echo base_url().'/webapp/vendor/global/global.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/custom.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/deznav-init.js'; ?>" type="text/javascript"></script>

    <?php if ($otpInformation) { ?>
        <script>
            $(function(e){
                $("body").on("keyup", 'input[type="number"]', function(e){
                    $.each($('input[type="number"]'), function(x, y){
                        if ($(this).val().length !== 1) {
                            $(this).focus();
                            $(".error_msg").html("");

                            return false;
                        }
                    });
                });

                setInterval(() => {
                    $.each($('input[type="number"]'), function(x, y){
                        if ($(this).val().length > 1) {
                            let currentChar = $(this).val()
                            $(this).val(currentChar.charAt(0));
                        }
                    });
                }, 500);

                $("body").on("submit", "form.verify-otp", function(e) {
                    e.preventDefault();

                    $(".verify-otp-btn").html("Submit <i class='fa fa-spin fa-spinner'></i>");

                    var first_number = $("input[name='wp_number_1']").val(),
                    second_number = $("input[name='wp_number_2']").val(),
                    third_number = $("input[name='wp_number_3']").val(),
                    fourth_number = $("input[name='wp_number_4']").val(),
                    fifth_number = $("input[name='wp_number_5']").val(),
                    sixth_number = $("input[name='wp_number_6']").val();

                    $.ajax({
                        url : $(this).attr("action"),
                        method : "POST",
                        data : {
                            wp_otp : first_number+''+second_number+''+third_number+''+fourth_number+''+fifth_number+''+sixth_number+'',
                            _token : '<?php echo $otpInformation->token; ?>'
                        },
                        success:function(response) {
                            $('.verify-otp')[0].reset();
                            if (response.status == 200) {
                                $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");

                                loadMyNewPage('body', "<?php echo base_url().'/registration-complete'; ?>", true);
                            }
                            else {
                                $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                            }
                        },
                        error:function(e) {

                        },
                        complete:function() {
                            $(".verify-otp-btn").html("Submit");
                        }
                    });
                });

                $("body").on("click", ".resend-otp", function(e) {
                    e.preventDefault();
                    var resendOTPURL = $(this).attr("href");
                    $(this).html("<i class='fa fa-spin fa-spinner'></i>");
                    $(".error_msg").html("");

                    $.ajax({
                        url : resendOTPURL,
                        method : "POST",
                        success:function(response) {
                            if (response.status == 200) {
                                $('.verify-otp')[0].reset();
                                $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            }
                            else {
                                if (response.message) {
                                    $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                                }
                                else {
                                    $(".error_msg").html("<div class='alert alert-danger'>We encountered a problem while completing your request. Please try again.</div>");
                                }
                            }
                        },
                        complete:function() {
                            $(".resend-otp").html("Resend OTP");
                        }
                    });
                });
            });
        </script>
    <?php } ?>
</body>
</html>