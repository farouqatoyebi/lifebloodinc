<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <div class="notifications">
            <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3"> <?php /* The class height380 gives the notification bar a height of 380 */ ?>
                <div class="timeline">
                    <?php if (count($allNotifications)) { ?>
                        <?php foreach ($allNotifications as $value) { ?>
                            <div class="card mb-3">
                                <div class="timeline-panel card-body" style="border-bottom: 0px;">
                                    <div class="media mr-2 media-success">
                                        <?php if ($value['type'] == 'request') echo 'RQ'; elseif ($value['type'] == 'offer') echo 'OF'; else echo '<i class="fa fa-home"></i>' ?>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="mb-1"><?php echo $value['message'] ?></h6>
                                        <small class="d-block"><?php echo $value['time']; ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="card mb-3">
                            <div class="timeline-panel card-body" style="border-bottom: 0px;">
                                <div class="media-body text-center">
                                    <p class="text-danger"><i class="fa fa-times-circle fa-3x fa-lg"></i></p>
                                    <h6 class="mb-1">You current have no new notifications</h6>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>