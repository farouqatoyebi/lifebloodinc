<?php 
$data = [];

if (isset($results)) {
    $data = $results;
}

if (!isset($data['page_title'])) {
    $data['page_title'] = 'BetaLife';
}

echo view('admin/nav-menu/admin-header', $data);
echo view('admin/'.$v, $data);
echo view('admin/nav-menu/admin-footer', $data);