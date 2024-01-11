<!DOCTYPE html>
<html lang="en" class="h-100">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <?php if ($hospital_details) { ?>
        <title>BetaLife | <?php echo $hospital_details->name; ?></title>
    <?php } else { ?>
        <title>BetaLife</title>
    <?php } ?>

    <link rel="icon" type="image/png" sizes="16x16" href="">
    <meta name="description" content="Visiting your hospital"/>
    <link href="<?php echo base_url().'/webapp/css/style.css'; ?>" rel="stylesheet" type="text/css"/>
    <style>
        .background-image {
            background-image: url('<?php echo base_url().'public/webapp/images' ?>');
        }
    </style>
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row">
                <div class="col-lg-7 <?php if (!$hospital_details->slug_qr_code) echo 'mx-auto' ?>">
                    <div class="card mt-4">
                        <div class="card-body">
                            <?php if ($hospital_details) { ?>
                                <div>
                                    <div class="row">
                                        <div class="col-6">
                                            <h3 class="h3 text-primary mt-3"><?php echo $hospital_details->name; ?></h3>
                                        </div>
                                        <div class="col-6 text-right">
                                            <?php if ($hospital_details->logo) { ?>
                                                <img src="<?php echo base_url().'/uploads/images/logo/'.$hospital_details->logo; ?>" alt="..." class="img-fluid rounded" id="output" style="width: 50px; height: 50px;">
                                            <?php } else { ?>
                                                <img src="<?php echo base_url().'/webapp/images/doctors-image-small.png'; ?>" alt="..." class="img-fluid rounded" id="output" style="width: 50px; height: 50px;">
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5 class="h5 font-weight-bold text-center mt-4">Visitor's form</h5>
                                </div>
                                <div class="error_msg"></div>
                                <form action="<?php echo current_url(); ?>" method="post" class="visit">
                                    <div class="form-group">
                                        <label for="">Phone</label>
                                        <input type="tel" class="form-control" id="wp_phone" name="wp_phone" placeholder="Phone" minlength="10" maxlength="15" required>
                                        <small class="text-danger wp_phone"></small>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Name</label>
                                        <input type="text" class="form-control load-info" id="wp_name" name="wp_name" placeholder="Name" required disabled>
                                        <small class="text-danger wp_name"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Email Address</label>
                                        <input type="email" class="form-control load-info" id="wp_email" name="wp_email" placeholder="Phone" required disabled>
                                        <small class="text-danger wp_email"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Blood Type</label>
                                        <select id="wp_blood_type" name="wp_blood_type" class="form-control load-info" disabled>
                                            <option value="">Choose...</option>
                                            <option disabled>- - - - -</option>
                                            <?php foreach ($all_blood_type as $value) { ?>
                                                <option value="<?php echo $value->name; ?>"><?php echo $value->name; ?></option>
                                            <?php } ?>
                                        </select>
                                        <small class="text-danger wp_blood_type"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Gender</label>
                                        <select id="wp_gender" name="wp_gender" class="form-control load-info" disabled>
                                            <option value="">Choose...</option>
                                            <option disabled>- - - - -</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <small class="text-danger wp_gender"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Marital Status</label>
                                        <select id="wp_marital_status" name="wp_marital_status" class="form-control load-info" disabled>
                                            <option value="">Choose...</option>
                                            <option disabled>- - - - -</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Divorced">Divorced</option>
                                            <option value="Widow">Widow</option>
                                            <option value="Widower">Widower</option>
                                            <option value="Widower">Widower</option>
                                        </select>
                                        <small class="text-danger wp_marital_status"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Genotype</label>
                                        <select id="wp_genotype" name="wp_genotype" class="form-control load-info" disabled>
                                            <option value="">Choose...</option>
                                            <option disabled>- - - - -</option>
                                            <option value="AA">AA</option>
                                            <option value="AS">AS</option>
                                            <option value="AC">AC</option>
                                            <option value="SS">SS</option>
                                            <option value="SC">SC</option>
                                        </select>
                                        <small class="text-danger wp_genotype"></small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="">Date of Birth</label>
                                        <input type="date" class="form-control load-info" id="wp_dob" name="wp_dob" placeholder="Phone" required disabled>
                                        <small class="text-danger wp_dob"></small>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Address</label>
                                        <input type="text" name="wp_address" id="wp_address" class="form-control load-info" disabled>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block attendance-btn" disabled>
                                            Submit
                                        </button>
                                    </div>
                                </form>
                            <?php } else { ?>

                            <?php } ?>

                            <div class="text-center">
                                <img src="<?php echo base_url().'/webapp/images/betalife-health-new-logo.png' ?>" alt="" width="100" height="100" class="img-fluid">
                                <p class="font-italic text-cnter">
                                    Powered by <a href="<?php echo base_url(); ?>" class="text-primary">BetaLife</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($hospital_details->slug_qr_code) { ?>
                    <div class="col-lg-5 mt-5 text-right d-none d-lg-block">
                        <p class="text-info">Scan QR Code below: </p>
                        <img src="<?php echo base_url().'/generator/qr/'.$hospital_details->slug_qr_code; ?>" alt="" class="img-fluid" width="100%" height="100%">
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="<?php echo base_url().'/webapp/vendor/global/global.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/custom.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo base_url().'/webapp//js/deznav-init.js'; ?>" type="text/javascript"></script>

    <script>
        $(function(e){
            var storedPhoneNumber;

            $("body").on("keyup", "#wp_phone", function(e) {
                $(".error_msg").html("");

                if ($(this).val().length >= $(this).attr("minlength")) {
                    if (storedPhoneNumber == $(this).val() && $("#wp_name").val()) {
                        return ;
                    }

                    storedPhoneNumber = $(this).val();

                    $(".load-info").attr("placeholder", "Fetching Data...");
                    $(".load-info").attr("disabled", "disabled");
                    $(".attendance-btn").attr("disabled", "disabled");

                    $.ajax({
                        url : '<?php echo base_url().'/visitor/details'; ?>',
                        method : 'POST',
                        data : {
                            wp_phone : $(this).val()
                        },
                        success:function(response) {
                            if (response.details) {
                                $.each(response.details, function(x, y) {
                                    if ($("input[name='"+x+"']").length) {
                                        if (!$("input[name='"+x+"']").val()) {
                                            $("input[name='"+x+"']").val(y);
                                        }
                                    }
                                    else if ($("select[name='"+x+"']").length) {
                                        if (!$("select[name='"+x+"']").val()) {
                                            $("select[name='"+x+"']").val(y);
                                        }
                                    }
                                });
                                $(".load-info").attr("placeholder", "");
                            }
                            else {
                                $(".load-info").val("");
                                $(".load-info").attr("placeholder", "");
                            }
                        },
                        complete:function(){
                            $(".load-info").removeAttr("disabled");
                            $(".attendance-btn").removeAttr("disabled");
                        }
                    });
                }
            });

            $("body").on("submit", ".visit", function(e){
                e.preventDefault();

                var phone = $("#wp_phone").val(), 
                    name = $("#wp_name").val(), 
                    email = $("#wp_email").val(), 
                    dob = $("#wp_dob").val(),
                    form_action = $(this).attr("action"),
                    there_is_an_error = false;

                $("small.text-danger").text("");
                $("input").removeClass("border border-danger");

                $('.attendance-btn').addClass("disabled");
                $('.attendance-btn').attr("disabled", "disabled");
                $('.attendance-btn').html('Submitting... &nbsp; &nbsp; <i class="fa fa-spin fa-spinner"></i>');

                if (!phone) {
                    $("#wp_phone").addClass("border border-danger");
                    $("small.wp_phone").text("Required");
                    there_is_an_error = true;
                }

                if (!name) {
                    $("#wp_name").addClass("border border-danger");
                    $("small.wp_name").text("Required");
                    there_is_an_error = true;
                }

                if (!email) {
                    $("#wp_email").addClass("border border-danger");
                    $("small.wp_email").text("Required");
                    there_is_an_error = true;
                }

                if (!dob) {
                    $("#wp_dob").addClass("border border-danger");
                    $("small.wp_dob").text("Required");
                    there_is_an_error = true;
                }

                if (there_is_an_error) {
                    $(".attendance-btn").removeClass("disabled");
                    $(".attendance-btn").removeAttr("disabled");
                    $(".attendance-btn").html('Submit');

                    return ;
                }

                var formData = $(this).serialize();

                $.ajax({
                    url : form_action,
                    method: 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $('.visit')[0].reset();
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            $(".load-info").attr("disabled", "disabled");
                            setTimeout(() => {
                                $(".attendance-btn").attr("disabled", "disabled");
                            }, 1000);
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
                                });
                            }
                        }
                    },
                    error:function(e) {

                    },
                    complete:function() {
						$(".attendance-btn").removeClass("disabled");
						$(".attendance-btn").removeAttr("disabled");
						$(".attendance-btn").html('Submit');

                        setTimeout(() => {
                            $(".error_msg").html("");
                        }, 3000);
                    }
                });
            });
        });
    </script>
</body>
</html>