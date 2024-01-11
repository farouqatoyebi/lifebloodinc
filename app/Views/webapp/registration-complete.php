<!DOCTYPE html>
<html lang="en" class="h-100">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BetaLife | Verify Your Account</title>
    <meta name="description" content="Login to your account"/>
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
        .mt-99 {
            margin-top: 50px;
        }

        input[type=number] {
            -moz-appearance:textfield; /* Firefox */
        }
        .fa-10x {
            font-size: 10em;
        }
    </style>
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-5 mx-auto">
                    <div class="shadow border bg-primary text-white text-center p-5 rounded-top">
                        <i class="fa fa-check-circle-o fa-lg fa-10x"></i>
                        <p class="mt-4">Success</p>
                    </div>
                    <div class="border-top shadow text-center p-5">
                        <p class="">Congratulations, your account has been successfully created.</p>
                        <p class="mt-99">
                            <a href="<?php echo base_url().'/dashboard'; ?>" class="btn btn-primary btn-block rounded-bottom">Continue to your dashboard</a>
                        </p>
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
            $("body").on("keyup", 'input[type="number"]', function(e){
                $.each($('input[type="number"]'), function(x, y){
                    if ($(this).val().length !== 1) {
                        $(this).focus();
                        $(".error_msg").html("");

                        return false;
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
</body>
</html>