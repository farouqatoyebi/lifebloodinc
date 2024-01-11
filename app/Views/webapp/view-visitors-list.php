<?php
    use App\Controllers\BaseController;
    $baseController = new BaseController();
    $accountAuthID = session("auth_id");
    $accountAuthType = session("acct_type");
    $getEstablishmentInformation = $baseController->getUserProfileInformationBasedOnType($accountAuthType, $accountAuthID);
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title ?></a></li>
            </ol>

            <div class="text-right">
                Daily Visits <small class="font-weight-bold mt-5">(<?php echo date("jS F, Y"); ?>)</small>: 
                <span class="font-weight-bold"><?php echo $baseController->getNumberOfDailyVisitsForHospital($getEstablishmentInformation->id); ?></span>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <form action="<?php echo base_url().'/visitors'; ?>" method="get">
                        <div class="form-row">
                            <div class="col-lg-4">
                                <label for="">Start Date</label>
                                <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" id="" class="form-control" required>
                            </div>
                            
                            <div class="col-lg-4">
                                <label for="">End Date</label>
                                <input type="date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date("Y-m-d"); ?>" name="end_date" id="" class="form-control">
                            </div>
                            
                            <div class="col-lg-4">
                                <label for="" style="visibility: hidden;">Start Date</label><br/>
                                <input type="submit" value="Go" name="" id="" class="btn btn-primary">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Visitor's List</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($allVisitorsRendered)) { ?>
                            <div class="table-responsive text-center">
                                <table class="table header-border table-responsive-sm">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Phone</th>
                                            <th>Blood Group</th>
                                            <th>Number of Visits</th>
                                            <th>Last Date Visited</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allVisitorsRendered as $value) { ?>
                                            <?php 
                                                $visitorsInfoVisit = $baseController->getNumberOfVisitsForVisitor($value->id, $getEstablishmentInformation->id, ''); 
                                                if (!$visitorsInfoVisit) $visitorsInfoVisit = $value;
                                            ?>
                                            <tr>
                                                <td><?php echo $value->fullname; ?></td>
                                                <td><?php echo $value->phone; ?></td>
                                                <td><?php echo $value->blood_group; ?></td>
                                                <td><?php echo $baseController->getNumberOfVisitsForVisitor($value->id, $getEstablishmentInformation->id); ?></td>
                                                <td><?php echo date("jS F, Y", $visitorsInfoVisit->visited_on); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" info-visit="<?php echo $value->phone; ?>" data-target="#exampleModal">
                                                        <i class="fa fa-eye"></i> View Information
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center">
                                <img src="<?php echo base_url().'/webapp/images/no-visitors-yet.jpg' ?>" alt="" class="img-fluid" style="height: 600px;">
                                <p class="mt-4">
                                    There are currently no visitors for the current day. You can use the filter to view previous records of your visitors.
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>