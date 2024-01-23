            <div class="footer">
                <div class="copyright text-center">
                    <?php echo date("Y"); ?> &copy; Copyright 
                    <strong><span>LifelineBlood</span></strong>. 
                    A dissertation project by Boluwatife Folorunso
                </div>
            </div>
        </div>
    </div>

    <style>
        .end-0 {
            position: fixed;
            bottom: 105px;
            right: 40px;
            z-index: 99;
            border: none;
            outline: none;
        }

        .toast-container {
            left: 75vw;
        }

        @media screen and (max-width: 992px) {
            .toast-container {
                left: 25%;
            }
        }
    </style>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
	<script src="<?php echo base_url().'/webapp/vendor/global/global.min.js'; ?>" type="text/javascript"></script>
	<!-- <script src="<?php echo base_url().'/webapp/vendor/bootstrap-select/dist/js/bootstrap-select.min.js'; ?>" type="text/javascript"></script> -->
	<script src="<?php echo base_url().'/webapp/vendor/chart.js/Chart.bundle.min.js'; ?>" type="text/javascript"></script>
	<script src="<?php echo base_url().'/webapp/vendor/owl-carousel/owl.carousel.js'; ?>" type="text/javascript"></script>
	<script src="<?php echo base_url().'/webapp/vendor/apexchart/apexchart.js'; ?>" type="text/javascript"></script>
	<script src="<?php echo base_url().'/webapp/js/dashboard/dashboard-1.js?ver=1.1'; ?>" type="text/javascript"></script>
	<script src="<?php echo base_url().'/webapp/js/custom.min.js?ver=1.3'; ?>" type="text/javascript"></script>
	<script src="<?php echo base_url().'/webapp/js/deznav-init.js'; ?>" type="text/javascript"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script> 

	<script>
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };

        var alreadyInSync = false;
        var currentTime = <?php echo time(); ?>;

        function searchingForOffers(check_now = false) {
            if ($(".finding-offers").length) {
                if (!alreadyInSync) {
                    var numberOfDots = 1;
                    var offerText = $(".finding-offers").text();

                    setInterval(() => {
                        if (numberOfDots == 1) {
                            $(".finding-offers").text(offerText+".");
                        }
                        else if (numberOfDots == 2) {
                            $(".finding-offers").text(offerText+"..");
                        }
                        else if (numberOfDots == 3) {
                            $(".finding-offers").text(offerText+"...");
                        }
                        else if (numberOfDots == 4) {
                            $(".finding-offers").text(offerText+"....");
                        }
                        else {
                            $(".finding-offers").text(offerText+"......");
                            numberOfDots = 0;
                        }
                        numberOfDots++
                    }, 1000);

                    alreadyInSync = true;
                }

                var requestID = $("div[request]").attr('request');
                if (requestID && check_now) {
                    checkForNewRequests(requestID, 'update-page');
                }
            }
        }

        function beginPaymentProcessing(paymentID) {
            $.ajax({
                url : '<?php echo base_url().'/begin-payment-process/'; ?>'+paymentID,
                method : 'POST',
                success:function(response) {
                    if (response) {
                        payWithPaystack(paymentID, response.s_key, response.email, response.amount);
                    }
                }
            });
        }

        function recordPaymentInformation(paymentID, tx_ref, action = '') {
            $.ajax({
                url : '<?php echo base_url().'/record-payment-information/'; ?>'+paymentID,
                method : 'POST',
                data : {
                    tx_ref : tx_ref,
                    action : action
                },
                success:function(response) {
                    if (action == 'validate') {
                        window.location = '<?php echo base_url().'/view-delivery-information/'; ?>'+paymentID;
                    }
                }
            });
        }

        function checkForNewRequests(requestID, action) {
            $.ajax({
                url : '<?php echo base_url('/get-new-offers-for-request') ?>/' +requestID,
                method : 'POST',
                data : {
                    check_time : currentTime,
                },
                success:function(response) {
                    if (response == 'true') {
                        if (action == 'update-page') {
                            $("div[request]").html('<div class="col-12 text-center"><p><i class="fa fa-spin fa-spinner fa-3x"></i></p><p>Fetching new offers</p></div>');
                            setTimeout(() => {
                                loadMyNewPage('.main-content', "<?php echo base_url().'/browse-blood-offers/'; ?>"+requestID);
                                $(this).addClass("d-none");
                            }, 1500);

                            getServerTime();
                        }
                        else if (action == 'show-sticky-btn') {
                            $(".load-new-offers").html("Load More Offers");
                            $(".load-new-offers").removeAttr("disabled");
                            $(".load-new-offers").removeClass("disabled");
                            $(".load-new-offers").removeClass("d-none");
                        }
                    }
                },
                complete:function() {

                }
            });
        }

        function getServerTime() {
            $.ajax({
                url : '<?php echo base_url('/get-time') ?>',
                method : 'POST',
                success:function(response) {
                    currentTime = response;
                },
                complete:function() {

                }
            });
        }

        function checkForNewRequestsForBtnOnly() {
            if ($(".allOffers").length) {
                var requestID = $("div[request]").attr('request');
                if (requestID) {
                    checkForNewRequests(requestID, 'show-sticky-btn');
                }
            }
        }

        function setNotifications() {
            $.ajax({
                url : "<?php echo base_url('/set-notifications') ?>",
                method : "GET",
                success:function(response) {
                    
                }
            })
        }

        function getNotifications() {
            $.ajax({
                url : "<?php echo base_url('/get-notifications') ?>",
                method : "GET",
                success:function(response) {
                    if (response.length) {
                        $(".toast-container").remove();
                        $(".content-body .container-fluid").append(`<div class="toast-container fixed-bottom"></div>`);
                        $(".toast-container").append(`<button onclick="playSound();" id="play-sound" style="visibility: hidden;">Play</button>`);
                        $(".notification-count").text("*");
                        $(".no-notification").remove();

                        $.each(response, function(index, message){
                            $(".toast-container").append(`
                                <div class="toast" data-autohide="false">
                                    <div class="toast-header">
                                        <strong class="mr-auto text-primary">Notification</strong>
                                        <small class="text-muted">${message.time}</small>
                                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                                    </div>
                                    <div class="toast-body">
                                        ${message.message}
                                    </div>
                                </div>
                            `);
                            $('.toast').toast('show');
                            performSound();

                            if ($('ul#notifications-list li').length >= 3) {
                                $('ul#notifications-list li:last-child').remove();
                            }

                            var messageTypeSent = `<i class="fa fa-home"></i>`;

                            if (message.type == 'request') {
                                messageTypeSent = `RQ`;
                            }
                            else if (message.type == 'pffer') {
                                messageTypeSent = `OF`;
                            }

                            $('ul#notifications-list').prepend(`
                            <li>
                                <div class="timeline-panel">
                                    <div class="media mr-2 media-success">
                                        ${messageTypeSent}
                                    </div>
                                    <div class="media-body">
                                        <h6 class="mb-1">${message.message}</h6>
                                        <small class="d-block">${message.time}</small>
                                    </div>
                                </div>
                            </li>`);
                        });
                    }
                }
            })
        }

        function performSound(){
            var soundButton = document.getElementById("play-sound");
            soundButton.click();
        }

        function playSound() {
            const audio = new Audio("<?php echo base_url().'/webapp/sounds/betalife-notification-bell-sound.wav'; ?>");
            audio.play();
            setTimeout(() => { 
                audio.pause(); 
            }, 3500);
        }

		$(function(e){
            $("body").on("change", "#countries", function(e) {
                var currVal = $(this).val();
                $("#states").val("");
                $("#cities").val("");

                $("option[country]").hide();
                $("option[country='"+currVal+"']").show();
            });

            setInterval(() => {
                setNotifications();
            }, 5000);

            setInterval(() => {
                getNotifications();
            }, 3000);

            $("body").on("click", ".load-new-offers", function(e){
                $(this).html("Loading... <i class='fa fa-spin fa-spinner'></i>");
                $(this).attr("disabled", "disabled");
                $(this).addClass("disabled");

                var requestID = $("div[request]").attr('request');

                checkForNewRequests(requestID, 'update-page');
            });
            
            $("body").on("change", "#states", function(e) {
                var currVal = $(this).val();
                $("#cities").val("");

                $("option[state]").hide();
                $("option[state='"+currVal+"']").show();
            });

            $("body").on("keyup", '.verifyOtp', function(e){
                $.each($('.verifyOtp'), function(x, y){
                    if ($(this).val().length !== 1) {
                        $(this).focus();
                        $(".error_msg").html("");

                        return false;
                    }
                });

                setInterval(() => {
                    $.each($('.verifyOtp'), function(x, y){
                        if ($(this).val().length > 1) {
                            let currentChar = $(this).val()
                            $(this).val(currentChar.charAt(0));
                        }
                    });
                }, 500);
            });

            $("body").on("submit", ".confirm_delivery_form", function(e){
                e.preventDefault();

                var there_is_an_error = false;
                $(".error_msg_otp").html("");
                $(".btn-confirm-delivery").html('Confirming OTP... <i class="fa fa-spin fa-spinner"></i>');
                $(".btn-confirm-delivery").addClass("disabled");
                $(".btn-confirm-delivery").attr("disabled", "disabled");

                $.each($('.verifyOtp'), function(x, y){
                    if ($(this).val().length != 1) {
                        $(this).addClass("border border-danger");
                        there_is_an_error = true;
                    }
                    else if ($(this).val() < 0) {
                        $(this).addClass("border border-danger");
                        there_is_an_error = true;
                    }
                    else if ($(this).val() > 9) {
                        $(this).addClass("border border-danger");
                        there_is_an_error = true;
                    }
                });

                if (there_is_an_error) {
                    $(".error_msg_otp").html("<i class='fa fa-times-circle'></i> There is an error in the submitted fields");
                    $(".btn-confirm-delivery").html('Confirm Delivery');
                    $(".btn-confirm-delivery").removeClass("disabled");
                    $(".btn-confirm-delivery").removeAttr("disabled");

                    return;
                }

                var otpValue = '';

                $.each($('.verifyOtp'), function(x, y){
                    otpValue += ''+$(this).val()+'';
                });

                $.ajax({
                    url : $(this).attr('action'),
                    method : $(this).attr('method'),
                    data : {
                        otp : otpValue
                    },
                    success:function(response) {
                        if (response.status == 200) {
                            $(".modal-body").html('<div class="text-center text-success"><p><i class="fa fa-check-circle fa-3x"></i></p><p>'+response.message+'</p></div>');

                            setTimeout(() => {
                                $('div#confirm_delivery').modal("hide");
                            }, 2000);

                            setTimeout(() => {
                                window.location.href = window.location.href;
                            }, 3000);
                        }
                        else {
                            $(".error_msg_otp").html('<i class="fa fa-times-circle"></i> '+response.message);
                        }
                    },
                    complete:function() {
                        $(".btn-confirm-delivery").html('Confirm Delivery');
                        $(".btn-confirm-delivery").removeClass("disabled");
                        $(".btn-confirm-delivery").removeAttr("disabled");
                        $("form.confirm_delivery_form")[0].reset();
                    }
                })
            });
            
            $("body").on("change", "input[name='wp_logo']", function(e){
                var filename = this.files[0].name;
                $("#sillohuete").addClass("d-none");
                $("#output").removeClass("d-none");
				var account_type = $(".account_type").val();
                loadFile(e);

                $("span.file_name").text(filename);
                $("p.logo-text").text("Change "+account_type+" logo")
            });
			
            $("body").on("submit", "form.complete-profile", function(e){
                e.preventDefault();
                $("small.text-danger").addClass("d-none");
                $("input").removeClass("border border-danger");

                $('.profile-btn').addClass("disabled");
                $('.profile-btn').attr("disabled", "disabled");
                $('.profile-btn').html('Updating Profile... &nbsp; &nbsp; <i class="fa fa-spin fa-spinner"></i>');
                $('.error_msg').html("");

                var there_is_an_error = false;

                var account_name = $("input[name='wp_name']").val();
                var account_location = $("input[name='wp_location']").val();
                var account_country = $("#countries").val();
                var account_state = $("#states").val();
                var account_city = $("#cities").val();
                var account_logo = $("input[name='wp_logo']").val();
                var reg_number = $("input[name='wp_reg_no']").val();
                var phone_number = $("input[name='wp_phone']").val();


                var owners_first_name = $("#owners_first_name").val();
                var owners_last_name = $("#owners_last_name").val();
                var owners_email = $("#owners_email").val();
                var owners_phone = $("#owners_phone").val();
                var owners_bvn = $("#owners_bvn").val();
                var owners_nin = $("#owners_nin").val();
                var owners_reg_date = $("#owners_reg_date").val();

                <?php if (!session('logo')) { ?>
                    if (!account_logo) {
                        $("input[name='wp_logo']").addClass("border border-danger");
                        $("small.wp_logo").text("A file must be uploaded to continue");
                        $("small.wp_logo").removeClass("d-none");
                        there_is_an_error = true;
                    }
                <?php } ?>

                if (!account_name) {
                    $("input[name='wp_name']").addClass("border border-danger");
                    $("small.wp_name").text("Required");
                    $("small.wp_name").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_location) {
                    $("input[name='wp_location']").addClass("border border-danger");
                    $("small.wp_location").text("Required");
                    $("small.wp_location").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_country) {
                    $("#countries").addClass("border border-danger");
                    $("small.countries").text("Required");
                    $("small.countries").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_state) {
                    $("#states").addClass("border border-danger");
                    $("small.states").text("Required");
                    $("small.states").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!account_city) {
                    $("#cities").addClass("border border-danger");
                    $("small.cities").text("Required");
                    $("small.cities").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!reg_number) {
                    $("input[name='wp_reg_no']").addClass("border border-danger");
                    $("small.wp_reg_no").text("Required");
                    $("small.wp_reg_no").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!phone_number) {
                    $("input[name='wp_phone']").addClass("border border-danger");
                    $("small.wp_phone").text("Required");
                    $("small.wp_phone").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_first_name) {
                    $("#owners_first_name").addClass("border border-danger");
                    $("small.owners_first_name").text("Required");
                    $("small.owners_first_name").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_last_name) {
                    $("#owners_last_name").addClass("border border-danger");
                    $("small.owners_last_name").text("Required");
                    $("small.owners_last_name").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_email) {
                    $("#owners_email").addClass("border border-danger");
                    $("small.owners_email").text("Required");
                    $("small.owners_email").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_phone) {
                    $("#owners_phone").addClass("border border-danger");
                    $("small.owners_phone").text("Required");
                    $("small.owners_phone").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_bvn) {
                    $("#owners_bvn").addClass("border border-danger");
                    $("small.owners_bvn").text("Required");
                    $("small.owners_bvn").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_nin) {
                    $("#owners_nin").addClass("border border-danger");
                    $("small.owners_nin").text("Required");
                    $("small.owners_nin").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (!owners_reg_date) {
                    $("#owners_nin").addClass("border border-danger");
                    $("small.owners_nin").text("Required");
                    $("small.owners_nin").removeClass("d-none");
                    there_is_an_error = true;
                }

                if (there_is_an_error) {
                    $(".profile-btn").removeClass("disabled");
                    $(".profile-btn").removeAttr("disabled");
                    $(".profile-btn").html('Update Profile');
                    $(".error_msg").html("<div class='alert alert-danger'>You have an error in your form.</div>");

                    return ;
                }

                // var formArray = $(this).serialize();

                var photo = document.getElementById("files").files[0];
                var cac_doc = document.getElementById("cac_doc").files[0];
                var dis_form = document.querySelector('form');
                var formArray = new FormData(dis_form);

                formArray.append("wp_logo", photo);
                formArray.append("wp_file_cac", cac_doc);
                formArray.append("wp_name", account_name);
                formArray.append("wp_location", account_location);
                formArray.append("wp_country", account_country);
                formArray.append("wp_state", account_state);
                formArray.append("wp_city", account_city);
                formArray.append("wp_reg_no", reg_number);
                formArray.append("wp_phone", phone_number);
                formArray.append("owners_first_name", owners_first_name);
                formArray.append("owners_last_name", owners_last_name);
                formArray.append("owners_email", owners_email);
                formArray.append("owners_phone", owners_phone);
                formArray.append("owners_bvn", owners_bvn);
                formArray.append("owners_nin", owners_nin);
                formArray.append("owners_reg_date", owners_reg_date);

                $.ajax({
                    method : "POST",
                    url : $(this).attr("action"),
                    processData: false,
                    contentType: false,
                    cache: false,
                    enctype: "multipart/form-data",
                    data : formArray,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            loadMyNewPage('.main-content', "<?php echo base_url().'/profile'; ?>");
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
						$(".profile-btn").removeClass("disabled");
						$(".profile-btn").removeAttr("disabled");
						$(".profile-btn").html('Update Profile');
                    }
                });
            });

            $("body").on("click", ".submit-details", function(e){
                e.preventDefault();
                $(".other-details").removeClass("d-none");
                $(this).addClass("d-none");
            });

            $("body").on("keyup", "#short_desc", function(e){
                $("#short_desc").removeClass("border border-danger");
                $(".short_desc").text("");
            });

            $("body").on("keyup, change", "#due_date", function(e){
                $(this).removeClass("border border-danger");
                $(".due_date").text("");
            });

            $("body").on("keyup", ".blood_pint_input", function(e){
                $(".blood_pint_input").removeClass("border border-danger");
                $(".blood_pint").text("");
            });

            $("body").on("click", ".generate-request", function(e){
                e.preventDefault();

                var short_desc = $("#short_desc").val(),
                    urgency_level = $("#urgency_level").val(),
                    due_date = $("#due_date").val(),
                    there_is_an_error = false,
                    has_at_least_one_value = false;

                if (!due_date) {
                    $("#due_date").addClass("border border-danger");
                    $(".due_date").text("* Select a valid date.");
                    
                    there_is_an_error = true;
                }
                else {
                    var calcDueDateSubmitted = new Date(due_date); //dd-mm-YYYY
                    var currentDateValue = new Date();
                    currentDateValue.setHours(0,0,0,0);

                    if(calcDueDateSubmitted < currentDateValue) {
                        $("#due_date").addClass("border border-danger");
                        $(".due_date").text("* Please select a current or future due date.");
                        
                        there_is_an_error = true;
                    }
                }

                $.each($(".blood_pint_input"), function(x, y){
                    if ($(this).val()) {
                        has_at_least_one_value = true;
                    }
                });

                if (!has_at_least_one_value) {
                    $(".blood_pint_input").addClass("border border-danger");
                    $(".blood_pint").text("* You must enter number of pint for at least one blood group.");
                    
                    there_is_an_error = true;
                }
                else {
                    $.each($(".blood_pint_input"), function(x, y){
                        if ($(this).val()) {
                            if ($(this).val() <= 0) {
                                $(this).addClass("border border-danger");
                                $(".blood_pint").text("* Number of pint requested must be greater than 0.");
                                
                                there_is_an_error = true;
                            }
                        }
                    });
                }

                if (there_is_an_error) {
                    return ;
                }

                $.each($(".blood_pint_input"), function(x, y){
                    if (y.value) {
                        $("div[target='"+$(this).attr("group")+"']").removeClass("d-none");
                        $("input[target='"+$(this).attr("group")+"']").val(y.value);
                    }
                });

                $("input[group]").addClass("disabled");
                $(".multi-range").attr("disabled", "disabled");
                $("input[group]").attr("disabled", "disabled");
                $("#due_date").attr("disabled", "disabled");
                $("#short_desc").attr("disabled", "disabled");
                $("#blood_group").attr("disabled", "disabled");
                $("#blood_pint").attr("disabled", "disabled");

                var urgencyLevelTitle = $("option[level-name][value='"+urgency_level+"']").attr("level-name");

                $("#final_urgency_level").val(urgencyLevelTitle);

                if (short_desc) {
                    $(".short_desc_div").removeClass("d-none");
                    $("#short_desc_text").text(short_desc);
                }
                else {
                    $(".short_desc_div").addClass("d-none");
                    $("#short_desc_text").text("");
                }

                $("#due_date_display").text(convertDateToWords(due_date));

                $('.request-summary').removeClass("d-none");
                $(this).addClass("d-none");
            });

            $("body").on("click", ".edit-request-btn", function(e){
                e.preventDefault();
                e.stopPropagation();

                $("input[group]").removeAttr("disabled");
                $("#due_date").removeAttr("disabled");
                $("#short_desc").removeAttr("disabled");
                $(".multi-range").removeAttr("disabled");
                $("#blood_group").removeAttr("disabled");
                $("#blood_pint").removeAttr("disabled");
                $(".generate-request").removeClass("d-none");
                $('.request-summary').addClass("d-none");
            });

            searchingForOffers();

            setInterval(() => {
                searchingForOffers(true);
                checkForNewRequestsForBtnOnly();
            }, 5000);

            $("body").on("click", ".confirm-request-delete", function(e){
                if (!confirm("Are you sure you want to delete this request?")) e.preventDefault();
            })

            $("body").on("submit", ".make-blood-request", function(e){
                e.preventDefault();
                $(".make-request-btn").html('Making Request <i class="fa fa-spin fa-spinner"></i>')
                $(".make-request-btn").attr('disabled', 'disabled');
                $(".make-request-btn").addClass('disabled');
                
                $("input[group]").removeAttr("disabled");
                $("#due_date").removeAttr("disabled");
                $("#short_desc").removeAttr("disabled");
                $(".multi-range").removeAttr("disabled");
                $("#blood_group").removeAttr("disabled");
                $("#blood_pint").removeAttr("disabled");

                var formSubmittedValues = $(this).serialize();

                $.ajax({
                    url : '<?php echo base_url().'/submit-blood-request'; ?>',
                    method: 'POST',
                    data : formSubmittedValues,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            loadMyNewPage('.main-content', response.accept_donor_url);
                        }
                        else {
                            $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                        }
                    },
                    complete:function(e){
                        setTimeout(() => {
                            $(".make-request-btn").html('Make Request');
                            $(".make-request-btn").removeAttr('disabled');
                            $(".make-request-btn").removeClass('disabled');
                        }, 2000);
                    }
                });
                
                $(".multi-range").attr("disabled", "disabled");
                $("input[group]").attr("disabled", "disabled");
                $("#due_date").attr("disabled", "disabled");
                $("#short_desc").attr("disabled", "disabled");
                $("#blood_group").attr("disabled", "disabled");
                $("#blood_pint").attr("disabled", "disabled");
            });

            $("body").on("submit", ".set-rates", function(e) {
                e.preventDefault();
                
                var there_is_an_error = false;
                $(".rates-btn").html("Setting Rates <i class='fa fa-spin fa-spinner'></i>");
                $.each($(".wp-set-rates"), function(x, y){
                    if (y.value) {
                        if (y.value < 0) {
                            $(this).addClass("border border-danger");

                            there_is_an_error = true;
                        } 
                    }
                    else {
                        $(this).addClass("border border-danger");

                        there_is_an_error = true;
                    }
                });

                if (there_is_an_error) {
                $(".rates-btn").html("Set Rates");
                    return ;
                }

                var formData = $(this).serialize(),
                    form_action = $(this).attr("action");

                $.ajax({
                    url : form_action,
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            // loadMyNewPage('.main-content', response.accept_donor_url);
                        }
                        else {
                            $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                        }
                    },
                    complete:function(){
                        $(".rates-btn").html("Set Rates");
                    }
                });
            });

            $("body").on("submit", ".bank-information", function(e) {
                e.preventDefault();
                
                var there_is_an_error = false;
                $(".bank-info-btn").html("Saving Bank Information <i class='fa fa-spin fa-spinner'></i>");
                $(".bank-info-btn").attr("disabled", "disabled");
                $(".wp-bank-deets").removeClass("border border-danger");
                $(".error").text("");

                $.each($(".wp-bank-deets"), function(x, y) {
                    if ($(this).attr('name') != 'wp_sort_code') {
                        if (!y.value) {
                            $(this).addClass("border border-danger");
                            $(this).closest(".form-group").find(".error").text("* Required");

                            there_is_an_error = true;
                        }
                    }
                });

                if (there_is_an_error) {
                    $(".bank-info-btn").html("Save Bank Information");
                    $(".bank-info-btn").removeAttr("disabled");

                    return ;
                }

                var formData = $(this).serialize(),
                    form_action = $(this).attr("action");

                $.ajax({
                    url : form_action,
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                        }
                        else {
                            $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                        }
                    },
                    complete:function(){
                        $(".bank-info-btn").html("Save Bank Information");
                        $(".bank-info-btn").removeAttr("disabled");
                    }
                });
            });

            $("body").on("submit", ".send-offer", function(e) {
                e.preventDefault();

                $("input.wp_offer").removeClass("border border-danger");
                $("small.wp_offer").text("");

                var submittedForm = $(this).find("input.wp_offer"), 
                    there_is_an_error = false,
                    hospitalName = $(this).attr("hospital");

                $.each(submittedForm, function(x, y) {
                    if (y.value < 0) {
                        $(this).addClass("border border-danger");
                        $(this).closest(".form-group").find("small.wp_offer").text("Number of pint cannot be less than 1");

                        there_is_an_error = true;
                    }
                });

                if (there_is_an_error) {
                    return ;
                }
                
                var outputHtml = '<form action="'+$(this).attr("action")+'" id="send-out-offer" hospital-name="'+hospitalName+'">';
                outputHtml += '<div class="row">';
                var totalInvoiceAmount = 0;
                var currency = '';

                $.each(submittedForm, function(x, y) {
                    let bloodGroup = $(this).attr("group");
                    let no_of_pint = $(this).val();
                    let rateValue = customNumberFormat($(this).attr("rate-val"));
                    let totalAmountDisplayed = customNumberFormat(parseInt($(this).attr("rate-val")) * parseInt(y.value));
                    let totalAmount = parseInt($(this).attr("rate-val")) * parseInt(y.value);

                    if (no_of_pint > 0) {
                        totalInvoiceAmount += totalAmount;
                        currency = $(this).attr("currency");

                        outputHtml += '<div class="col-lg-6 border-right">';
                            outputHtml += '<input type="hidden" name="wp_blood_offer['+bloodGroup+']" value="'+no_of_pint+'">';
                            outputHtml += '<span class="text-muted">Blood Group:</span><p class="text-muted">'+bloodGroup+'</p>';
                            outputHtml += '<span class="text-muted">Number of Pint:</span><p class="font-weight-bold">'+no_of_pint+'</p>';
                            outputHtml += '<span class="text-muted">Amount per pint:</span><p class="font-weight-bold">'+currency+' '+rateValue+'</p>';
                            outputHtml += '<span class="text-muted">Sub-Total:</span><p class="font-weight-bold">'+currency+' '+totalAmountDisplayed+'</p>';
                            outputHtml += '<hr>';
                        outputHtml += '</div>';
                    }
                });

                outputHtml += '<div class="col-lg-12">'
                outputHtml += '<p class="text-right font-weight-bold">Total: '+currency+' '+ customNumberFormat(totalInvoiceAmount)+'</p></div>';
                outputHtml += '</div>'

                $(".modal-title").html("Send offer to "+hospitalName);
                $("div.modal-body").html(outputHtml);

                $(this).closest(".dropdown-menu").removeClass("show");

                $('div#sendOffer').modal("show");
                $(".send-offer-btn").removeAttr("disabled");
            });

            $("body").on("click", "button.send-offer-btn", function(e) {
                $("#send-out-offer").submit();
            });

            $("body").on("submit", "form#send-out-offer", function(e) {
                e.preventDefault();

                var formData = $(this).serialize(),
                    dis_form = $(this);
                $(".send-offer-btn").html("Sending Offer... <i class='fa fa-spin fa-spinner'></i>");

                $.ajax({
                    url : $(this).attr("action"),
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $("div.modal-body").html('<div class="text-center"><p><i class="fa fa-check-circle text-success fa-3x"></i></p><p>Offer sent to '+dis_form.attr("hospital-name")+' successfully.</div>');
                            $(".request-browsing").html("<div class='card mt-5'><div class='card-body'><div></div class='text-center'><p class='text-center'><i class='fa fa-spin fa-spinner fa-3x'></i></p><p class='text-center'>Loading Data...</p></div></div>");
                            $('div#sendOffer').modal("hide");

                            setTimeout(() => {
                                loadMyNewPage('.main-content', "<?php echo base_url().'/browse-blood-requests'; ?>");
                            }, 2000);
                        }
                        else {
                            $("div.modal-body").html('<div class="text-center"><p><i class="fa fa-times-circle text-danger fa-3x"></i></p><p>We could not send out the offer. Please try again.</div>');
                        }
                    },
                    complete:function() {
                        $(".send-offer-btn").html("Send Offer");
                        $(".send-offer-btn").attr("disabled", "disabled");
                    }
                });
            });

            $("body").on("click", "button[info-visit]", function(e) {
                var visitor_info = $(this).attr("info-visit");

                $("div.modal-body").html("<div class='text-center'><p><i class='fa fa-spin fa-spinner fa-3x'></i></p>Fetching data...<p></p></div>")
                $.ajax({
                    url : '<?php echo base_url().'/view-visitor-details'; ?>',
                    method : 'POST',
                    data : {
                        wp_phone : visitor_info 
                    },
                    success:function(response) {
                        if (response.details) {
                            var outputHtml = '<div class="row">';
                            var visitorsName = '';

                            $.each(response.details, function(x, y) {
                                if (!y) {
                                    y = '- - - - -';
                                }

                                if (x == 'Name') {
                                    visitorsName = y;
                                }

                                outputHtml += '<div class="col-lg-6 border-right">';
                                outputHtml += '<p class="text-muted">'+x+'</p>';
                                outputHtml += '<p class="font-weight-bold">'+y+'</p>';
                                outputHtml += '<hr>';
                                outputHtml += '</div>';
                            });
                            
                            outputHtml += '</div>';

                            if (visitorsName.charAt(visitorsName.length-1) == 's') {
                                addition = '\'';
                            } 
                            else {
                                addition = '\'s';
                            }

                            $.ajax({
                                url : 'get-visitor-medical-details',
                                method : 'POST',
                                data : {
                                    wp_phone : visitor_info 
                                },
                                success:function(response) {
                                    var displayAdditionalForm = true;

                                    // HTML for additional form information
                                    if (response.details) {
                                        outputHtml += '<div class="row">';

                                        $.each(response.details, function(x, y) {
                                            let displayInfo = true;

                                            if (x == 'Type of Blood Donor') {
                                                if (!y) {
                                                    displayInfo = false;
                                                }
                                                else {
                                                    displayAdditionalForm = false;
                                                }
                                            }

                                            if (x == 'Reason for Donor Deferral') {
                                                if (!y) {
                                                    displayInfo = false;
                                                }
                                            }

                                            if (displayInfo) {
                                                if (!y) {
                                                    y = '- - - - -';
                                                }

                                                outputHtml += '<div class="col-lg-6 border-right">';
                                                outputHtml += '<p class="text-muted">'+x+'</p>';
                                                outputHtml += '<p class="font-weight-bold">'+y+'</p>';
                                                outputHtml += '<hr>';
                                                outputHtml += '</div>';
                                            }
                                        });
                                        
                                        outputHtml += '</div>';
                                    }

                                    if (!response.details || displayAdditionalForm) {
                                        outputHtml += '<div>';
                                            outputHtml += '<div>';
                                                if (!response.details) {
                                                    outputHtml += '<form method="POST" class="medical-record" autocomplete="off">';
                                                        outputHtml += '<p class="font-weight-bold">Medical Information</p>';

                                                        outputHtml += '<p class="font-weight-bold">Basic Medical Check</p>';
                                                        outputHtml += '<div class="form-group">';
                                                            outputHtml += '<label>Pulse Rate</label>';
                                                            outputHtml += '<input class="form-control med-check" name="wp_pulse" type="text" placeholder="Pulse Rate" required />';
                                                        outputHtml += '</div>';
                                                        
                                                        outputHtml += '<div class="form-group">';
                                                            outputHtml += '<label>Blood Pressure</label>';
                                                            outputHtml += '<input class="form-control med-check" name="wp_blood_pressure" type="text" placeholder="Blood Pressure" required />';
                                                        outputHtml += '</div>';
                                                        
                                                        outputHtml += '<div class="form-group">';
                                                            outputHtml += '<label>Weight (KG)</label>';
                                                            outputHtml += '<input class="form-control med-check" name="wp_weight" type="text" placeholder="Weight (KG)" required />';
                                                        outputHtml += '</div>';
                                                        
                                                        outputHtml += '<div class="form-group">';
                                                            outputHtml += '<label>HB/PCV</label>';
                                                            outputHtml += '<input class="form-control med-check" name="wp_hbpcv" type="text" placeholder="HB/PCV" required />';
                                                        outputHtml += '</div>';

                                                        outputHtml += '<input name="wp_phone" type="hidden" value="'+visitor_info+'" />';
                                                        
                                                        outputHtml += '<div class="form-group">';
                                                            outputHtml += '<button type="submit" class="btn btn-primary btn-block save-record">Save Rcord</button>';
                                                        outputHtml += '</div> <div class="error_msg"></div>';

                                                    outputHtml += '</form>';
                                                }

                                                var displayNone = 'd-none';

                                                if (response.details) {
                                                    displayNone = '';
                                                }

                                                if (displayAdditionalForm) {
                                                    outputHtml += '<div class="more-medical-info mt-4 '+displayNone+'"><hr>';
                                                        outputHtml += '<form method="POST" class="additional-medical-record" autocomplete="off">';
                                                            outputHtml += '<p class="font-weight-bold">Additional Medical Information</p>';

                                                            outputHtml += '<div class="form-group">';
                                                                outputHtml += '<label>Type of Blood Donor</label>';
                                                                outputHtml += '<select class="form-control med-add-check" name="wp_type_blood_donor" required>';
                                                                    outputHtml += '<option value="" selected disabled>Choose</option>'
                                                                    outputHtml += '<option value="" disabled>- - - -</option>'
                                                                    outputHtml += '<option value="Voluntary">Voluntary</option>'
                                                                    outputHtml += '<option value="Family Replacement">Family Replacement</option>'
                                                                    outputHtml += '<option value="Autologous">Autologous</option>'
                                                                outputHtml += '</select>';
                                                            outputHtml += '</div>';

                                                            outputHtml += '<div class="form-group">';
                                                                outputHtml += '<label>Reason for Donor Deferral</label>';
                                                                outputHtml += '<select class="form-control med-add-check" name="wp_donor_deferral_reason" required>';
                                                                    outputHtml += '<option value="" selected disabled>Choose</option>'
                                                                    outputHtml += '<option value="" disabled>- - - -</option>'
                                                                    outputHtml += '<option value="Low Hb/PCV">Low Hb/PCV</option>'
                                                                    outputHtml += '<option value="Medical Conditions">Medical Conditions</option>'
                                                                    outputHtml += '<option value="Risky Behavior">Risky Behavior</option>'
                                                                    outputHtml += '<option value="Low Weight">Low Weight</option>'
                                                                    outputHtml += '<option value="Self Differal">Self Differal</option>'
                                                                    outputHtml += '<option value="Not Differed">Not Differed</option>'
                                                                    outputHtml += '<option value="Others">Others</option>'
                                                                outputHtml += '</select>';
                                                            outputHtml += '</div>';

                                                            outputHtml += '<input name="wp_phone" type="hidden" value="'+visitor_info+'" />';
                                                        
                                                            outputHtml += '<div class="form-group others d-none">';
                                                                outputHtml += '<label>Others (Reason)</label>';
                                                                outputHtml += '<input class="form-control med-add-check" name="wp_others_reason" type="text" disabled="disabled" placeholder="Others (Reason)" required />';
                                                            outputHtml += '</div>';
                                                        
                                                            outputHtml += '<div class="form-group">';
                                                                outputHtml += '<button type="submit" class="btn btn-secondary btn-block save-additional-record">Save Additional Record</button>';
                                                            outputHtml += '</div> <div class="error_additional_msg"></div>';

                                                        outputHtml += '</form>';
                                                    outputHtml += '</div>';
                                                }

                                            outputHtml += '</div>';
                                        outputHtml += '</div>';
                                    }
                                    $("div.modal-body").html(outputHtml);
                                }
                            });

                            $(".modal-title").html(visitorsName+addition+' Medical Information');
                        }
                        else {
                            $("div.modal-body").html("<div class='text-center'><p><i class='fa fa-times-circle text-danger fa-3x'></i></p>Invalid request made.<p></p></div>");
                        }
                    },
                    complete:function(){
                        
                    }
                });
            });

            $("body").on("submit", ".medical-record", function(e){
                e.preventDefault();
                $(".save-record").html("Saving Record <i class='fa fa-spin fa-spinner'></i>");
                $(".save-record").attr("disabled", "disabled");
                $(".save-record").addClass("disabled");

                var formData = $(this).serialize();

                $.ajax({
                    url : '<?php echo base_url().'/save-visitor-record'; ?>',
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".save-record").addClass("d-none"); 
                            $(".med-check").attr("disabled", "disabled");
                            $(".error_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                            $('.more-medical-info').removeClass("d-none");
                        }
                        else {
                            $(".error_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                        }
                    },
                    complete:function(){
                        $(".save-record").html("Save Rcord");
                        $(".save-record").removeAttr("disabled");
                        $(".save-record").removeClass("disabled");
                    }
                })
            });

            $("body").on("submit", ".additional-medical-record", function(e){
                e.preventDefault();
                $(".save-additional-record").html("Saving Additional Record <i class='fa fa-spin fa-spinner'></i>");
                $(".save-additional-record").attr("disabled", "disabled");
                $(".save-additional-record").addClass("disabled");

                var formData = $(this).serialize();

                $.ajax({
                    url : '<?php echo base_url().'/save-visitor-additional-record'; ?>',
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".save-additional-record").addClass("d-none"); 
                            $(".med-add-check").attr("disabled", "disabled");
                            $(".error_additional_msg").html("<div class='alert alert-success'>"+response.message+"</div>");
                        }
                        else {
                            $(".error_additional_msg").html("<div class='alert alert-danger'>"+response.message+"</div>");
                        }
                    },
                    complete:function(){
                        $(".save-additional-record").html("Save Additional Record");
                        $(".save-additional-record").removeAttr("disabled");
                        $(".save-additional-record").removeClass("disabled");
                    }
                })
            });

            $("body").on("change", "select[name='wp_donor_deferral_reason']", function(e){
                if ($(this).val() == 'Others') {
                    $(".others").removeClass("d-none");
                    $(".others").find("input").removeAttr("disabled");
                }
                else {
                    $(".others").addClass("d-none");
                    $(".others").find("input").attr("disabled", "disabled");
                }
            });

            $("body").on("click", ".remove-this", function (e) {
                e.preventDefault();

                $(this).html('Dismissing... <i class="fa fa-spin fa-spinner"></i>');
                $(this).attr("disabled", "disabled");
                $(this).addClass("disabled");

                setTimeout(() => {
                    $(this).closest(".offer-breakdown").remove();

                    if (!$(".allOffers").children().length) {
                        $(".allOffers").html('<div class="col-12"> <div class="text-center"><img src="<?php echo base_url().'/webapp/images/searching-for-offers.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;"><p class="mt-4 finding-offers font-weight-bold h4">Searching for Offers</p></div> </div>');
                    }
                }, 2000);
            });

            $("body").on("click", ".remove-this-request", function (e) {
                e.preventDefault();

                $(this).html('Dismissing... <i class="fa fa-spin fa-spinner"></i>');
                $(this).attr("disabled", "disabled");
                $(this).addClass("disabled");

                setTimeout(() => {
                    $(this).closest(".request-breakdown").remove();

                    if (!$(".allRequests").children().length) {
                        $(".allRequests").html('<div class="col-12"> <div class="text-center"><img src="<?php echo base_url().'/webapp/images/no-request-yet.jpg'; ?>" alt="" class="img-fluid" style="height: 500px;"><p>There are currently no new requests.</p><p class="h4"> We will be sure to update you once there is a new request. </p></div> </div>');
                    }
                }, 2000);
            });

            $("body").on("click", ".accept-offer-modal", function(e){
                $("div.modal-body").html('<div class="text-center"><i class="fa fa-spin fa-spinner fa-3x"></i> <p class="mt-3">Fetching Information...</p></div>');

                var request = $(this).attr("request"), offer = $(this).attr("offer"), offerType = $(this).attr("offerType");

                if (!request || !offer || !offerType) {
                    $("div.modal-body").html('<div class="text-center"><i class="fa fa-times-circle fa-3x text-danger"></i> <p class="mt-3">You have made an invalid request. Please try again.</p></div>');
                }

                $.ajax({
                    url : '<?php echo base_url().'/accept-request-breakdown/' ?>'+request,
                    method : 'POST',
                    data : {
                        wp_offer : offer,
                        wp_request : request,
                        wp_offer_type : offerType
                    },
                    success:function(response) {
                        if (response.status == 200) {
                            $(".modal-title").html("Accept Offer");
                            var outputHtml = '<form class="submit-accepted-offer" request-info="'+request+'">', enableOfferButton = false, overallTotal = 0, currencyUsed = '';
                            
                            outputHtml += '<div class="alert alert-info mb-3"><p class="mb-0 font-weight-bold text-center"><i class="fa fa-info-circle"></i> You can leave the fields you do not intend to accept blank.</p></div>';
                            outputHtml += '<div class="row">';
                            outputHtml += '<input type="hidden" name="wp_request" value="'+request+'">';
                            outputHtml += '<input type="hidden" name="wp_offer" value="'+offer+'">';
                            outputHtml += '<input type="hidden" name="wp_offer_type" value="'+offerType+'">';
                            $.each(response.breakdown, function(x, y){
                                y.no_of_pints_offered = parseInt(y.no_of_pints_offered);
                                y.no_of_pints_left = parseInt(y.no_of_pints_left);

                                if (y.no_of_pints_left && y.no_of_pints_offered) {
                                    enableOfferButton = true;

                                    let calculatedMax = y.no_of_pints_offered;
                                    if (y.no_of_pints_offered > y.no_of_pints_left) {
                                        calculatedMax = y.no_of_pints_left;
                                    }

                                    let calculatedTotal = calculatedMax * parseInt(y.amount_per_pint);

                                    overallTotal += calculatedTotal;
                                    currencyUsed = y.currency;

                                    outputHtml += '<div class="col-lg-6">';
                                        outputHtml += '<div class="card">';
                                            outputHtml += '<div class="card-body">';
                                                outputHtml += '<p class="font-weight-bold h4">'+x+'</p>';
                                                outputHtml += '<p> <span class="text-muted">Requested Amount:</span> <span class="font-weight-bold">'+y.no_of_pints_req+'</span></p>';
                                                outputHtml += '<p> <span class="text-muted">Amount Offered:</span> <span class="font-weight-bold">'+y.no_of_pints_offered+'</span></p>';
                                                outputHtml += '<p> <span class="text-muted">Amount left to accept:</span> <span class="font-weight-bold">'+y.no_of_pints_left+'</span></p>';
                                                outputHtml += '<p> <span class="text-muted">Amount Per Pint:</span> <span class="font-weight-bold">'+y.currency+' '+y.amount_per_pint_formatted+'</span></p>';
                                                outputHtml += '<hr/>';
                                                outputHtml += '<div class="form-group"><label>Number to Accept</label><input type="number" placeholder="Number to Accept" min="0" max="'+calculatedMax+'" amt="'+y.amount_per_pint+'" total="'+calculatedTotal+'" currency="'+y.currency+'" name="wp_confirmed_data['+x+']" value="'+calculatedMax+'" class="form-control accepted_number" required /></div>';
                                                outputHtml += '<small class="text-info text-right calculatedAmt">'+y.currency+' '+customNumberFormat(calculatedTotal)+'</small>';
                                                outputHtml += '<small class="error_msg d-block"></small>';
                                            outputHtml += '</div>';
                                        outputHtml += '</div>';
                                    outputHtml += '</div>';
                                }
                            });

                            outputHtml += '</div>';

                            if (enableOfferButton) {
                            outputHtml += '<div class="text-right">';
                            outputHtml += '<hr>';
                            outputHtml += '<small class="text-muted">Total: &nbsp;</small> <span class="font-weight-bold h4 totalAmt">'+currencyUsed+' '+customNumberFormat(overallTotal)+'</span>';
                            outputHtml += '</div>';

                                $('.accept-offer-btn').removeClass('disabled');
                                $('.accept-offer-btn').removeAttr('disabled');
                            }
                            else {
                                $('.accept-offer-btn').addClass('disabled');
                                $('.accept-offer-btn').attr('disabled', 'disabled');
                            }

                            outputHtml += '</form>';

                            $("div.modal-body").html(outputHtml);
                        }
                        else {
                            $(".modal-title").html("Oops! Something went wrong.");
                            $("div.modal-body").html('<div class="text-center"><i class="fa fa-times-circle fa-3x text-danger"></i> <p class="mt-3">'+response.message+'</p></div>');

                            $('.accept-offer-btn').addClass('disabled');
                            $('.accept-offer-btn').attr('disabled', 'disabled');
                        }
                    },
                    complete:function() {

                    }
                });
            });

            $("body").on("click", ".accept-offer-btn", function(e){
                $('.submit-accepted-offer').submit();
            });

            $("body").on("keyup", ".accepted_number", function(e){
                $(".error_msg").html("");
                $(this).val(parseInt($(this).val()));

                if ($(this).val()) {
                    let currentAmount = parseInt($(this).attr('amt'));
                    let currentValue = parseInt($(this).val());

                    let newCalcAmount = currentAmount * currentValue;
                    var currency = $(this).attr('currency');
                    $(this).closest(".card-body").find(".calculatedAmt").html($(this).attr('currency')+' '+customNumberFormat(newCalcAmount));

                    $(this).attr("total", newCalcAmount);

                    let newTotal = 0;

                    $.each($(".accepted_number"), function(index){
                        if ($(this).attr("total") && $(this).val()) {
                            let valueNow = parseInt($(this).attr("total"));
                            newTotal += valueNow;
                        }
                    });

                    if (newTotal) {
                        $(".totalAmt").text(currency+' '+customNumberFormat(newTotal));
                    }

                }
            });

            $("body").on("submit", ".submit-accepted-offer", function(e){
                e.preventDefault();
                var hasAValue = false, there_is_an_error = false;
                $(".accept-offer-btn").html("Accepting Offer...<i class='fa fa-spin fa-spinner'></i>");

                $.each($(".accepted_number"), function(index){
                    if ($(this).val() > 0) {
                        hasAValue = true;
                        let currentValue = parseInt($(this).val());
                        let maxValue = parseInt($(this).attr("max"));

                        if (currentValue > maxValue) {
                            there_is_an_error = true;
                            let targeted = $(this).closest(".card-body").find(".error_msg");

                            targeted.addClass("text-danger");
                            targeted.html('<i class="fa fa-times-circle"></i> Accepted amount cannot be more than '+$(this).attr("max"));
                        }
                    }
                });

                if (!hasAValue) {
                    $(".accept-offer-btn").html("Accept Offer");
                    $(".error_msg").html('<i class="fa fa-times-circle"></i> You must accept at least one offer to continue');
                    $(".error_msg").addClass("text-danger");
                    return ; 
                }

                if (there_is_an_error) {
                    $(".accept-offer-btn").html("Accept Offer");
                    return ; 
                }

                var formData = $(this).serialize(), request_info = $(this).attr('request-info');

                $.ajax({
                    url : '<?php echo base_url().'/record-accepted-request'; ?>',
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response.status == 200) {
                            $(".modal-title").html("Success!");
                            $("div.modal-body").html('<div class="text-center"><i class="fa fa-check-circle fa-3x text-success"></i> <p class="mt-3">'+response.message+'</p></div>');
                            $(".allOffers").html('<div class="col-12 text-center"><p><i class="fa fa-spin fa-spinner fa-3x"></i></p><p>Refreshing... Please wait</p></div>');
                            $(".accept-offer-btn").html("Offer Accepted <i class='fa fa-check-circle'></i>");

                            setTimeout(() => {
                                loadMyNewPage('.main-content', "<?php echo base_url().'/browse-blood-offers/'; ?>"+request_info);
                            }, 3500);
                        }
                        else {
                            $(".modal-title").html("Error!");
                            $("div.modal-body").html('<div class="text-center"><i class="fa fa-times-circle fa-3x text-danger"></i> <p class="mt-3">'+response.message+'</p></div>');
                            $(".accept-offer-btn").html("Accept Offer");
                        }

                        setTimeout(() => {
                            $('div#acceptOfferModal').modal("hide");
                        }, 2000);

                        $(".accept-offer-btn").attr("disabled", "disabled");
                        $(".accept-offer-btn").addClass("disabled");
                    },
                    complete:function() {

                    }
                });
            });

            $("body").on("change", ".request_radio", function(e){
                var new_request_val = $("input[name='create_new_request']:checked").val();
                $(".due_date_error").text("");

                if (new_request_val == 'yes') {
                    $(".new_due_date").removeClass("d-none");
                    $(".due_date").removeAttr("disabled");
                }
                else {
                    $(".new_due_date").addClass("d-none");
                    $(".due_date").attr("disabled", "disabled");
                }
            });

            $("body").on("click", "button[otp]", function(e){
                var otpCode = $(this).attr('otp'),
                    donorName = $(this).attr('donor_name'),
                    donorType = $(this).attr('donor_type'),
                    finalText = '';

                $(".donor_type").text(donorType);
                $(".donor_name").text(donorName);

                for (var i = 0; i < otpCode.length; i++) {
                    finalText += '<span class="border border-primary px-3 py-2 text-primary mr-2">'+otpCode.charAt(i)+'</span>';
                }

                $(".otpCode").html(finalText);
            });

            $("body").on("submit", ".withdrawal-form", function(e){
                e.preventDefault();
                $(".make-withdrawal-btn").html('Making withdrawal... <i class="fa fa-spin fa-spinner"></i>');
                $(".make-withdrawal-btn").addClass("disabled");
                $(".make-withdrawal-btn").attr("disabled", "disabled");

                var formData = $(this).serialize();

                $.ajax({
                    url : $(this).attr('action'),
                    method : 'POST',
                    data : formData,
                    success:function(response) {
                        if (response) {
                            if (response.status == 200) {
                                $(".modal-body").html('<div class="text-center text-success"><p><i class="fa fa-check-circle fa-3x"></i></p> <p>'+response.message+'</p></div>');

                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            }
                            else {
                                if (response.validation) {
                                    $.each(response.validation, function(a, b){
                                        $("small."+a).text(b);
                                    });
                                }
                                else {
                                    $("small.text-danger").text(response.message);
                                }
                            }
                        }
                    },
                    complete:function(){
                        $(".make-withdrawal-btn").html('Make Withdrawal');
                        $(".make-withdrawal-btn").removeClass("disabled");
                        $(".make-withdrawal-btn").removeAttr("disabled");
                    }
                });
            });

            $("body").on("click", ".add-new-inventory", function(e){
                e.preventDefault(); e.stopPropagation();
                var inventory_to_add = $(this).attr("inventory");
                $(".error_msg").html("");
                $("#inventoryModal").modal("show");
                $("#blood_group").val(inventory_to_add);
                $(".blood_group_text").text(inventory_to_add);
            });

            $("body").on("submit", ".inventory-form", function(e){
                e.preventDefault(); e.stopPropagation();
                $(".btn-add-inventory").html(`Adding to Inventory... <i class="fa fa-spin fa-spinner"></i>`);
                $(".btn-add-inventory").attr(`disabled`, `disabled`);
                $(".btn-add-inventory").addClass(`disabled`);
                $(".error_msg").html("");

                var inventory_blood = $("#blood_group").val();
                var amount_avail = $("#amount_avail").val();

                $.ajax({
                    url : "<?php echo base_url('/add-to-inventory') ?>",
                    method : "POST",
                    data : {
                        inventory_blood : inventory_blood,
                        amount_avail : amount_avail
                    },
                    success:function(response){
                        if (response) {
                            if (response.status == 200) {
                                $(".alert-msg").html('<div class="alert alert-success"><p>'+response.message+'</p></div>');
                                $(".add-new-inventory").attr(`disabled`, `disabled`);
                                $(".add-new-inventory").html(`Loading...`);
                                $("#inventoryModal").modal("hide");

                                setTimeout(() => {
                                    loadMyNewPage('.main-content', "<?php echo base_url().'/inventory'; ?>");
                                }, 2000);
                            }
                            else {
                                if (response.validation) {
                                    $.each(response.validation, function(a, b){
                                        $("small."+a).text(b);
                                    });
                                }
                                else {
                                    $("small.text-danger").text(response.message);
                                }
                            }
                        }
                    },
                    complete:function() {
                        $(".btn-add-inventory").html(`Add to Inventory`);
                        $(".btn-add-inventory").removeAttr(`disabled`);
                        $(".btn-add-inventory").removeClass(`disabled`);
                    }
                })
            });
		});
	</script>