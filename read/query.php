<?php
// 创建数据库连接
$conn = new mysqli("mysql", "root", "123456", "book");

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
}

// 设置 JSON 格式响应
header('Content-Type: application/json');

if (!isset($_GET['bookid'])) {
    $response = array(
        'code' => 404,
        'response' => '缺少图书 id',
    );
    echo json_encode($response);
    exit;
}

$sql = 'SELECT * FROM reading_record WHERE book_id = ' . $_GET['bookid'];

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
    'response' => '查询成功',
    'data' => array(),
);

$fieldTypes = [];
while ($fieldInfo = $result->fetch_field()) {
    $fieldTypes[$fieldInfo->name] = $fieldInfo->type;
}

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

echo json_encode($response);


?>