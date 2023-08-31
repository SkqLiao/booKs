<?php

require_once('config.php');

$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}

// 设置 JSON 格式响应
header('Content-Type: application/json');

// 获取传入的 JSON 数据
$input_data = json_decode(file_get_contents("php://input"), true);

// 检查传入的数据是否为空
if (empty($input_data)) {
    $response['response'] = "传入的数据为空。";
    echo json_encode($response);
    exit;
}

$params = array('book_id', 'time_length', 'start_page', 'end_page', 'date');

foreach ($params as $param) {
    if (!isset($input_data[$param])) {
        $response['response'] = "必须传入 $param 参数。";
        echo json_encode($response);
        exit;
    }
}

$sql = "INSERT INTO reading_record (book_id, time_length, start_page, end_page, date) VALUES (";
$sql .= "'" . $input_data['book_id'] . "',";
$sql .= "'" . $input_data['time_length'] . "',";
$sql .= "'" . $input_data['start_page'] . "',";
$sql .= "'" . $input_data['end_page'] . "',";
$sql .= "'" . $input_data['date'] . "'";
$sql .= ")";

$result = $conn->query($sql);

if ($conn->error) {
    $response = array(
        'code' => 500,
        'response' => '数据库查询失败: ' . $conn->error,
    );
    echo json_encode($response);
    exit;
}

$response = array(
    'code' => 200,
    'response' => '添加成功',
);
echo json_encode($response);
?>