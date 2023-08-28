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

if (!isset($_GET['id'])) {
    $response = array(
        'code' => 404,
        'response' => '缺少图书 id',
    );
    echo json_encode($response);
    exit;
}

$col = array();

if (isset($_GET['columns'])) {
    foreach ($_GET['columns'] as $columns) {
        $col[] = $columns;
    }
}

if (count($col) == 0) {
    $col[] = '*';
}

$sql = 'SELECT ' . implode(',', $col) . ' FROM basic_info WHERE id = ' . $_GET['id'];

$result = $conn->query($sql);

if ($conn->error) {
    $response = array(
        'code' => 500,
        'response' => '数据库查询失败: ' . $conn->error,
    );
    echo json_encode($response);
    exit;
}

if ($result->num_rows == 0) {
    $response = array(
        'code' => 404,
        'response' => '找不到图书',
    );
    echo json_encode($response);
    exit;
}

$response = array(
    'code' => 200,
    'data' => $result->fetch_assoc(),
    'response' => '查询成功',
);

// 输出响应数据
echo json_encode($response);

// 关闭数据库连接
$conn->close();

?>
