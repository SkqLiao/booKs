<?php

header('Content-Type: application/json charset=utf-8');

if (!isset($_GET['isbn'])) {
    $response = array(
        'code' => 404,
        'response' => '缺少isbn',
    );
    echo json_encode($response);
    exit;
}

$imagePath = '../images/'. $_GET['isbn'] .'.jpg';
if (file_exists($imagePath)) {
    $imageData = file_get_contents($imagePath);
    $imageBase64 = base64_encode($imageData);
}

$response = array(
    'code' => 200,
    'data' => $imageBase64,
    'response' => '查询成功',
);

echo json_encode($response);

?>