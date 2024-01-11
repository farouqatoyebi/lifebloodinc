<?php 
$data = [];

if (isset($results)) {
    $data = $results;
}

if (!isset($data['page_title'])) {
    $data['page_title'] = 'BetaLife';
}

echo view('webapp/nav-menu/header', $data);
echo view('webapp/'.$v, $data);
echo view('webapp/nav-menu/footer', $data);