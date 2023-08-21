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

// 获取传入的 JSON 数据
$input_data = json_decode(file_get_contents("php://input"), true);

// 检查传入的数据是否为空
if (empty($input_data)) {
    $response['message'] = "传入的数据为空。";
    echo json_encode($response);
    exit;
}

// 检查是否传入了必要的 ISBN 参数
if (!isset($input_data['isbn'])) {
    $response['message'] = "必须传入 ISBN 参数。";
    echo json_encode($response);
    exit;
}

// 检查图书是否存在
$isbn = $input_data['isbn'];
$check_sql = "SELECT * FROM basic_info WHERE isbn = '$isbn'";
$check_result = $conn->query($check_sql);
if ($check_result->num_rows > 0) {
    $response['message'] = "指定的 ISBN 已存在。";
    echo json_encode($response);
    exit;
}

// 初始化允许添加的字段列表
$allowed_fields = array(
    'title', 'subtitle', 'original_title', 'douban_id', 'isbn', 'author',
    'translator', 'publish', 'producer', 'publishDate', 'pages', 'price',
    'binding', 'series', 'book_intro', 'cover_url', 'url', 'rating_count',
    'rating_value', 'rating_five_star_per', 'rating_four_star_per',
    'rating_three_star_per', 'rating_two_star_per', 'rating_one_star_per',
    'catalog', 'real_price', 'buy_date', 'buy_pos'
);

// 初始化要插入的字段数组
$insert_fields = array();
$insert_values = array();

// 遍历允许添加的字段
foreach ($allowed_fields as $field) {
    if (isset($input_data[$field])) {
        if ($field === 'publishDate' || $field === 'buy_date') {
            // 验证并转换日期字段
            $date_value = validateAndFormatDate($input_data[$field]);
            if ($date_value !== false) {
                $insert_fields[] = $field;
                $insert_values[] = "STR_TO_DATE('" . $conn->real_escape_string($date_value) . "', '%Y-%m-%d')";
            } else {
                $response['message'] = "日期格式不正确。";
                echo json_encode($response);
                exit;
            }
        } elseif ($field === 'real_price' || $field === 'price') {
            // 验证并转换价格字段
            $price_value = validateAndFormatPrice($input_data[$field]);
            if ($price_value !== false) {
                $insert_fields[] = $field;
                $insert_values[] = floatval($price_value);
            } else {
                $response['message'] = "价格格式不正确。";
                echo json_encode($response);
                exit;
            }
        } else {
            $insert_fields[] = $field;
            $insert_values[] = "'" . $conn->real_escape_string($input_data[$field]) . "'";
        }
    }
}

// 检查是否提供了需要插入的字段
if (empty($insert_fields) || empty($insert_values)) {
    $response['message'] = "没有提供需要插入的字段。";
    echo json_encode($response);
    exit;
}

// 构建插入语句
$sql = "INSERT INTO basic_info (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $insert_values) . ")";

// 执行插入操作
if ($conn->query($sql) === TRUE) {
    $response['message'] = "图书信息增加成功。";
} else {
    $response['message'] = "图书信息增加失败：" . $conn->error;
}

// 输出响应数据
echo json_encode($response);

// 关闭数据库连接
$conn->close();

// 验证并转换日期字段
function validateAndFormatDate($date) {
    $date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
    if (preg_match($date_pattern, $date)) {
        return $date;
    }
    return false;
}

// 验证并转换价格字段
function validateAndFormatPrice($price) {
    if (is_numeric($price)) {
        return $price;
    }
    return false;
}

?>
