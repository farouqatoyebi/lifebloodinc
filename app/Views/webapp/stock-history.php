<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>

        <div class="inventory-history">
            <table class="table table-bordered table-striped">
                <thead class="bg-">
                    <tr>
                        <th>S/N</th>
                        <th>Blood Group</th>
                        <th>Narration</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($inventory_history)) { $counter = 0; ?>
                        <?php foreach ($inventory_history as $key => $value) { $counter++; ?>
                            <tr>
                                <td><?php echo $counter; ?></td>
                                <td><?php echo $value->blood_group; ?></td>
                                <td><?php echo $value->narration; ?></td>
                                <td><?php echo $value->created_at ? date("jS F, Y H:ia", $value->created_at) : '- - -' ; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr class="text-center">
                            <td colspan="5">No record found</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>