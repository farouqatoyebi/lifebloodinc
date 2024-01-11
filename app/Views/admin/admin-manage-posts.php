<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> Posts</h4>
                </div>
                <div class="card-body">
                    <div class="text-right">
                        <a href="<?php echo base_url('admin/add-post') ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Add Post
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table tablesorter" id="list-accounts">
                            <thead class="text-primary">
                                <tr>
                                    <th>
                                        S/N
                                    </th>
                                    <th>
                                        Title
                                    </th>
                                    <th>
                                        Content
                                    </th>
                                    <th>
                                        Image
                                    </th>
                                    <th>
                                        Date Created
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($posts) { $counter = 0; ?>
                                    <?php foreach ($posts as $post) { ?>
                                        <tr>
                                            <td>
                                                <?php echo ++$counter; ?>
                                            </td>
                                            <td>
                                                <?php echo $post->title; ?>
                                            </td>
                                            <td>
                                                <?php echo $post->content; ?>
                                            </td>
                                            <td>
                                                <img src="<?php echo base_url().'/uploads/images/post/'.$post->image; ?>" alt="" class="img-fluid" style="width: 70px; height: 70px;">
                                            </td>
                                            <td>
                                                <?php echo $post->created_at ? date("F jS, Y", $post->created_at) : '- - -'; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo base_url('admin/edit-post/'.$post->id); ?>" class="btn btn-primary btn-sm m-1">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <a href="<?php echo base_url('admin/delete-post/'.$post->id); ?>" class="btn btn-danger btn-sm m-1 confirm-post-delete">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <p>No record found</p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>