    
            <footer class="footer">
                <div class="container-fluid">
                    <ul class="nav">
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link">
                                BetaLife
                            </a>
                        </li>
                    </ul>
                    <div class="copyright">
                        &copy;
                        <script> document.write(new Date().getFullYear()) </script> BetaLife
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!--   Core JS Files   -->
    <script src="<?php echo base_url('/admin/js/core/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('/admin/js/core/popper.min.js'); ?>"></script>
    <script src="<?php echo base_url('/admin/js/core/bootstrap.min.js'); ?>"></script>
    <script src="<?php echo base_url('/admin/js/plugins/perfect-scrollbar.jquery.min.js'); ?>"></script>

    <!-- Chart JS -->
    <!-- <script src="<?php echo base_url('/admin/js/plugins/chartjs.min.js'); ?>"></script> -->
    <!--  Notifications Plugin    -->
    <script src="<?php echo base_url('/admin/js/plugins/bootstrap-notify.js'); ?>"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js"></script>

    <!-- Control Center for Black Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="<?php echo base_url('/admin/js/black-dashboard.min.js?v=1.0.0'); ?>"></script>
    <script src="<?php echo base_url('/admin/demo/demo.js?v=1.02'); ?>"></script>
    <script>
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            }
        };

        $(document).ready(function() {
            $().ready(function() {
                $sidebar = $('.sidebar');
                $navbar = $('.navbar');
                $main_panel = $('.main-panel');

                $full_page = $('.full-page');

                $sidebar_responsive = $('body > .navbar-collapse');
                sidebar_mini_active = true;
                white_color = false;

                window_width = $(window).width();

                fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();
                var btnClicked = '';

                $('#list-accounts').DataTable();
            
                $("body").on("change", "input[name='wp_profile_photo']", function(e){
                    var filename = this.files[0].name;
                    $("#sillohuete").addClass("d-none");
                    $("#output").removeClass("d-none");
                    loadFile(e);

                    $("span.file_name").text(filename);
                    $("p.logo-text").text("Change profile photo");
                });

                $("body").on("change", "input[name='wp_image']", function(e){
                    var filename = this.files[0].name;
                    $("#sillohuete").addClass("d-none");
                    $("#output").removeClass("d-none");
                    loadFile(e);

                    $("span.file_name").text(filename);
                    $("p.logo-text").text("Change profile photo");
                });

                $('.fixed-plugin a').click(function(event) {
                    if ($(this).hasClass('switch-trigger')) {
                        if (event.stopPropagation) {
                            event.stopPropagation();
                        } else if (window.event) {
                            window.event.cancelBubble = true;
                        }
                    }
                });

                $('.fixed-plugin .background-color span').click(function() {
                    $(this).siblings().removeClass('active');
                    $(this).addClass('active');

                    var new_color = $(this).data('color');

                    if ($sidebar.length != 0) {
                        $sidebar.attr('data', new_color);
                    }

                    if ($main_panel.length != 0) {
                        $main_panel.attr('data', new_color);
                    }

                    if ($full_page.length != 0) {
                        $full_page.attr('filter-color', new_color);
                    }

                    if ($sidebar_responsive.length != 0) {
                        $sidebar_responsive.attr('data', new_color);
                    }
                });

                $('.switch-sidebar-mini input').on("switchChange.bootstrapSwitch", function() {
                    var $btn = $(this);

                    if (sidebar_mini_active == true) {
                        $('body').removeClass('sidebar-mini');
                        sidebar_mini_active = false;
                        blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
                    } 
                    else {
                        $('body').addClass('sidebar-mini');
                        sidebar_mini_active = true;
                        blackDashboard.showSidebarMessage('Sidebar mini activated...');
                    }

                    // we simulate the window Resize so the charts will get updated in realtime.
                    var simulateWindowResize = setInterval(function() {
                        window.dispatchEvent(new Event('resize'));
                    }, 180);

                    // we stop the simulation of Window Resize after the animations are completed
                    setTimeout(function() {
                        clearInterval(simulateWindowResize);
                    }, 1000);
                });

                $('.switch-change-color input').on("switchChange.bootstrapSwitch", function() {
                    var $btn = $(this);

                    if (white_color == true) {

                        $('body').addClass('change-background');
                        setTimeout(function() {
                        $('body').removeClass('change-background');
                        $('body').removeClass('white-content');
                        }, 900);
                        white_color = false;
                    }
                    else {
                        $('body').addClass('change-background');
                        setTimeout(function() {
                        $('body').removeClass('change-background');
                        $('body').addClass('white-content');
                        }, 900);

                        white_color = true;
                    }
                });

                $('.light-badge').click(function() {
                    $('body').addClass('white-content');
                });

                $('.dark-badge').click(function() {
                    $('body').removeClass('white-content');
                });

                $("body").on("click", ".confirm-withdrawal-approval", function(e){
                    if (!confirm("Are you sure you want to approve this withdrawal request?")) e.preventDefault();
                });

                $("body").on("click", ".reject-withdrawal-request", function(e) {
                    $("form.reject-request-form").attr("action", $(this).attr("href"));
                    e.preventDefault();
                });

                $("body").on("submit", "form.reject-request-form", function(e) {
                    e.preventDefault();
                    $(".reason_error").html("<i class='fa fa-spin fa-spinner'></i>");
                    $(".confirm-reject").attr("disabled", "disabled");
                    $("#reason").attr("disabled", "disabled");
                    $(".confirm-reject").addClass("disabled");

                    if (btnClicked == 'reject_only') {
                        if (!confirm("This will NOT REFUND the institution. Are you sure you want to reject this request without refunding?")) return false;
                    }

                    var reason = $("#reason").val();
                    var reason_type = btnClicked;

                    $.ajax({
                        url : $(this).attr('action'),
                        method : 'POST',
                        data : {
                            wp_reason : reason,
                            wp_reject_type : reason_type
                        },
                        success:function(response) {
                            if (response) {
                                if (response.status == 200) {
                                    $(".rejectRequestBody").html('<div class="text-center text-success"><p><i class="fa fa-check-circle fa-3x"></i></p><p>'+response.message+'</p></div>');

                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                }
                                else if (response.validation) {
                                    $(".reason_error").html(response.validation.wp_reason);
                                }
                                else {
                                    $(".rejectRequestBody").html('<div class="text-center text-error"><p><i class="fa fa-times-circle fa-3x"></i></p><p>'+response.message+'</p></div>');
                                }
                            }
                        },
                        complete:function(e) {
                            $(".confirm-reject").removeAttr("disabled");
                            $(".confirm-reject").removeClass("disabled");
                            $("#reason").removeAttr("disabled");
                        }
                    });
                });

                $("body").on("click", ".confirm-reject", function(e) {
                    btnClicked = $(this).val();
                });

                $("body").on("change", "#countries", function(e) {
                    var currVal = $(this).val();
                    $("#states").val("");
                    $("#cities").val("");

                    $("option[country]").hide();
                    $("option[country='"+currVal+"']").show();
                });

                $("body").on("change", "#states", function(e) {
                    $("#cities").val("");
                });

                $("body").on("submit", ".save-profile-form", function(e) {
                    e.preventDefault();
                    console.log("submitted!");

                    $(".save-profile").html('Saving Profile... <i class="fa fa-spin fa-spinner"></i>');
                    $(".save-profile").addClass("disabled");
                    $(".save-profile").attr("disabled", "disabled");

                    var first_name = $("input[name='wp_first_name']").val();
                    var last_name = $("input[name='wp_last_name']").val();
                    var address = $("input[name='wp_address']").val();
                    var facebook = $("input[name='wp_facebook']").val();
                    var twitter = $("input[name='wp_twitter']").val();
                    var whatsapp = $("input[name='wp_whatsapp']").val();
                    var country = $("#countries").val();
                    var state = $("#states").val();
                    var city = $("#cities").val();
                    var profile_photo = $("input[name='wp_profile_photo']").val();
                    var about_me = $("textarea[name='wp_about_me']").val();

                    var photo = document.getElementById("profile_photo").files[0];
                    var dis_form = document.querySelector('form');
                    var formArray = new FormData(dis_form);

                    formArray.append("wp_profile_photo", photo);
                    formArray.append("wp_first_name", first_name);
                    formArray.append("wp_last_name", last_name);
                    formArray.append("wp_country", country);
                    formArray.append("wp_state", state);
                    formArray.append("wp_city", city);
                    formArray.append("wp_address", address);
                    formArray.append("wp_facebook", facebook);
                    formArray.append("wp_twitter", twitter);
                    formArray.append("wp_whatsapp", whatsapp);
                    formArray.append("wp_about_me", about_me);

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

                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
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
                            $(".save-profile").removeClass("disabled");
                            $(".save-profile").removeAttr("disabled");
                            $(".save-profile").html('Save Profile');
                        }
                    });
                });

                $("body").on("submit", ".submit-post-form", function(e){
                    e.preventDefault();
                    $(".error_msg_small").html("");
                    var btnText = $(".btn-submit-post").text();

                    $(".btn-submit-post").addClass("disabled");
                    $(".btn-submit-post").attr("disabled", "disabled");
                    $(".btn-submit-post").html('Submitting...<i class="fa fa-spin fa-spinner"></i>');

                    var title = $("#title").val();
                    var body = $("#body").val();
                    var image = $("input[name='wp_image']").val();

                    var image = document.getElementById("image").files[0];
                    var dis_form = document.querySelector('form');
                    var formArray = new FormData(dis_form);

                    formArray.append("wp_title", title);
                    formArray.append("wp_body", body);
                    formArray.append("wp_image", image);

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
                                window.location = "<?php echo base_url('/admin/manage-posts') ?>";
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
                            $(".btn-submit-post").removeClass("disabled");
                            $(".btn-submit-post").removeAttr("disabled");
                            $(".btn-submit-post").html(btnText);
                        }
                    });
                });

                $("body").on("click", ".confirm-post-delete", function(e){
                    if (!confirm("Are you sure you want to delete this post?")) e.preventDefault();
                })
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Javascript method's body can be found in assets/js/demos.js
            demo.initDashboardPageCharts();
        });
    </script>
    <script src="https://cdn.trackjs.com/agent/v3/latest/t.js"></script>
    <script>
        window.TrackJS &&
        TrackJS.install({
            token: "ee6fab19c5a04ac1a32a645abde4613a",
            application: "black-dashboard-free"
        });
    </script>
</body>
</html>