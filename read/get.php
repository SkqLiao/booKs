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
$sql = "SELECT * FROM reading_record WHERE YEAR(date) = $year";
$result = $conn->query($sql);
$response['data'] = array();


$fieldTypes = [];
while ($fieldInfo = $result->fetch_field()) {
    $fieldTypes[$fieldInfo->name] = $fieldInfo->type;
}

$response = ['data' => []];

while ($row = $result->fetch_assoc()) {
    foreach ($row as $fieldName => &$fieldValue) {
        // 根据字段类型进行自动转换
        switch ($fieldTypes[$fieldName]) {
            case MYSQLI_TYPE_TINY:
            case MYSQLI_TYPE_SHORT:
            case MYSQLI_TYPE_INT24:
            case MYSQLI_TYPE_LONG:
                $fieldValue = intval($fieldValue);
                break;
            case MYSQLI_TYPE_FLOAT:
            case MYSQLI_TYPE_DOUBLE:
            case MYSQLI_TYPE_DECIMAL:
                $fieldValue = floatval($fieldValue);
                break;
            // 其他类型转换
            case MYSQLI_TYPE_DATE:
                $fieldValue = date('Y-m-d', strtotime($fieldValue));
                break;
            case MYSQLI_TYPE_DATETIME:
                $fieldValue = date('Y-m-d H:i:s', strtotime($fieldValue));
                break;
            // 添加其他需要转换的字段类型
        }
    }
    $response['data'][] = $row;
}
$response['code'] = 200;
echo json_encode($response);
?>