<?php
require_once('config.php');

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}

// 设置 JSON 格式响应
header('Content-Type: application/json');

// 获取查询参数
$queryParam = isset($_GET['query']) ? $_GET['query'] : '';

// 构建查询语句
$sql = "SELECT ";

if ($queryParam === 'publish') {
    $sql .= "publish, COUNT(*) as count";
} elseif ($queryParam === 'producer') {
    $sql .= "producer, COUNT(*) as count";
} elseif ($queryParam === 'series') {
    $sql .= "series, COUNT(*) as count";
} else {
    $sql .= "*" . $sql .= ", COUNT(*) as count";
}

$sql .= " FROM basic_info GROUP BY $queryParam";

// 查询数据库获取图书信息
$result = $conn->query($sql);

// 构建响应数组
$response = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row[$queryParam] === '')
            continue;
        $row['value'] = $row[$queryParam];
        unset($row[$queryParam]);
        $response['data'][] = $row;
    }
    $response['response'] = "查询成功！";
    $response['code'] = 200;
} else {
    $response['response'] = "没有找到匹配的信息。";
    $response['code'] = 404;
}

// 输出响应数据
echo json_encode($response);

// 关闭数据库连接
$conn->close();
