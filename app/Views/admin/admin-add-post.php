<?php 
    use App\Controllers\BaseController; 
    $baseController = new BaseController();
?>
<div class="content">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"> Add New Post</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo base_url('admin/add-post') ?>" method="post" class="submit-post-form" enctype="multipart/form-data" autocomplete="off">
                        <div class="form-group">
                            <label for="title"><sup class="text-danger font-weight-bold">*</sup> Title</label>
                            <input type="text" name="wp_title" id="title" class="form-control" required />
                            <small class="wp_title text-danger error_msg_small"></small>
                        </div>

                        <div class="form-group">
                            <label for="body"><sup class="text-danger font-weight-bold">*</sup> Body</label>
                            <textarea name="wp_body" id="body" cols="30" rows="10" class="form-control" required></textarea>
                            <small class="wp_body text-danger error_msg_small"></small>
                        </div>

                        <div class="form-group">
                            <label class="btn btn-outline-secondary btn-block cursor-pointer" for="image"> Choose an Image to Upload</label>
                            <input type="file" name="wp_image" id="image" class="form-control-file" accept="image/*">
                            <img src="#" alt="" class="ing-fluid d-none" id="output" style="width: 70px; height: 70px;">
                            <small class="wp_image text-danger error_msg_small"></small>
                        </div>

                        <div class="form-group mt-5">
                            <button type="submit" class="btn btn-primary btn-block btn-submit-post">
                                Submit
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="error_msg"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>