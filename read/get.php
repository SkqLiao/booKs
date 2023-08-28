<?php

// 读取数据库连接配置信息
require_once('config.php');

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}

// 设置 JSON 格式响应
header('Content-Type: application/json');
$response = array();
if (!isset($_GET['year'])) {
    $response['response'] = "必须传入 年份 参数。";
    $response['code'] = 404;
    echo json_encode($response);
    exit;
}
$year = $_GET['year'];
$sql = "SELECT * FROM reading_record WHERE YEAR(start_time) = $year";
$result = $conn->query($sql);
$response['data'] = array();
while ($row = $result->fetch_assoc()) {
    $response['data'][] = $row;
}
$response['code'] = 200;
echo json_encode($response);
?>