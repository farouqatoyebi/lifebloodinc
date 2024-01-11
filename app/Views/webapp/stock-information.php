<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <div class="inventory-information">
            <table class="table table-bordered table-striped">
                <thead class="bg-">
                    <tr>
                        <th>S/N</th>
                        <th>Blood Group</th>
                        <th>Amount Available</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($inventory_info)) { $counter = 0; ?>
                        <?php foreach ($inventory_info as $key => $value) { $counter++; ?>
                            <tr>
                                <td><?php echo $counter; ?></td>
                                <td><?php echo $value->blood_group; ?></td>
                                <td><?php echo number_format($value->amount_available); ?></td>
                                <td><?php echo $value->updated_at ? date("jS F, Y", $value->updated_at) : '- - -' ; ?></td>
                                <td>
                                    <a href="#" class="btn btn-dark m-1 btn-sm add-new-inventory" inventory="<?php echo $value->blood_group; ?>">
                                        <i class="fa fa-plus-circle"></i> Add New
                                    </a>

                                    <a href="<?php echo base_url('/inventory-history/'.urlencode($value->blood_group)) ?>" class="btn btn-info m-1 btn-sm">
                                        <i class="fa fa-history"></i> See History
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr class="text-center">
                            <td colspan="5">No record found</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            

            <div class="modal fade" id="inventoryModal" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="inventoryModalLabel">Add to Inventory</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="" method="post" class="inventory-form">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="">Blood Group</label>
                                    <p class="font-weight-bolder text-danger blood_group_text"></p>
                                    <input type="hidden" name="" id="blood_group" class="form-control" />
                                    <small class="text-danger error_msg inventory_blood"></small>
                                </div>

                                <div class="form-group">
                                    <label for="">Amount to Add</label>
                                    <input type="number" name="" id="amount_avail" class="form-control" min="1" required />
                                    <small class="text-danger error_msg amount_avail"></small>
                                </div>

                                <div class="alert-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-sm btn-primary btn-add-inventory">Add to Inventory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>