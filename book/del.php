<?php

// 读取数据库连接配置信息
require_once('config.php');

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $response['code'] = 405;
    $response['response'] = "不支持的请求方法。";
    echo json_encode($response);
    exit;
}

// 设置 JSON 格式响应
header('Content-Type: application/json');

// 获取传入的 ISBN 参数
$isbn = isset($_GET['isbn']) ? $_GET['isbn'] : '';

// 检查是否传入了必要的 ISBN 参数
if (empty($isbn)) {
    $response['code'] = 400;
    $response['response'] = "必须传入 ISBN 参数。";
    echo json_encode($response);
    exit;
}

// 检查图书是否存在
$check_sql = "SELECT * FROM basic_info WHERE isbn = '$isbn'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows === 0) {
    $response['response'] = "指定的 ISBN 不存在。";
    echo json_encode($response);
    exit;
}

// 构建 SQL 删除语句
$sql = "DELETE FROM basic_info WHERE isbn = '$isbn'";

// 执行删除操作
if ($conn->query($sql) === TRUE) {
    $response['response'] = "图书信息删除成功。";
    $response['code'] = 200;
} else {
    $response['response'] = "图书信息删除失败：" . $conn->error;
    $response['code'] = 500;
}

// 输出响应数据
echo json_encode($response);

// 关闭数据库连接
$conn->close();

?>
