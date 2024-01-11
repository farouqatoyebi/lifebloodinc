<?php 
    use App\Controllers\BaseController; 
?>
<div class="content-body">
    <div class="container-fluid">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="javascript:void(0)"><?php echo $page_title; ?></a></li>
            </ol>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="my-2">
                    <h4 class="text-muted">Blood Request</h4>
                    <p class="font-weight-bold"><?php echo session('name'); ?></p>
                </div>

                <div class="my-3">
                    <div class="shadow">
                        <label for="">Address</label>
                        <p></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>