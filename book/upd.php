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
    $response['response'] = "传入的数据为空。";
    echo json_encode($response);
    exit;
}

// 检查是否传入了必要的 ISBN 参数
if (!isset($input_data['isbn'])) {
    $response['response'] = "必须传入 ISBN 参数。";
    echo json_encode($response);
    exit;
}

// 获取传入的 ISBN 参数
$isbn = $input_data['isbn'];

// 检查图书是否存在
$check_sql = "SELECT * FROM basic_info WHERE isbn = '$isbn'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows === 0) {
    $response['response'] = "指定的 ISBN 不存在。";
    echo json_encode($response);
    exit;
}

if (isset($input_data['cover_base64'])) {
    $image_data = str_replace('data:image/jpeg;base64,', '', $input_data['cover_base64']);
    $image_data = base64_decode($image_data);
    $isbn = $input_data['isbn'];
    $image_path = "../images/{$isbn}.jpg";
    
    if (file_put_contents($image_path, $image_data) === false) {
        $response['response'] = "图书信息更新失败：" . "图像保存失败";
        $response['code'] = 500;
        echo json_encode($response);
        exit;
    }
}

// 构建 SQL 更新语句
$sql = "UPDATE basic_info SET ";
$update_fields = array();

$allowed_fields = array(
    'title', 'subtitle', 'original_title', 'author', 'translator', 'publish',
    'producer', 'publishDate', 'price', 'binding', 'series', 'buy_pos', 'real_price'
);

foreach ($allowed_fields as $field) {
    if (isset($input_data[$field])) {
        if ($field === 'publishDate' || $field === 'buy_date') {
            // 验证并转换日期字段
            $date_value = validateAndFormatDate($input_data[$field]);
            if ($date_value !== false) {
                $update_fields[] = "$field = STR_TO_DATE('" . $conn->real_escape_string($date_value) . "', '%Y-%m-%d')";
            }
        } elseif ($field === 'real_price' || $field === 'price') {
            // 验证并转换价格字段
            $price_value = validateAndFormatPrice($input_data[$field]);
            if ($price_value !== false) {
                $update_fields[] = "$field = " . floatval($price_value);
            }
        } else {
            // 过滤所有空格
            $input_data[$field] = preg_replace('/\s+/', '', $input_data[$field]);
            $update_fields[] = "$field = '" . $conn->real_escape_string($input_data[$field]) . "'";
        }
    }
}

if (empty($update_fields)) {
    $response['response'] = "没有提供需要更新的字段。";
    echo json_encode($response);
    exit;
}

$sql .= implode(", ", $update_fields);
$sql .= " WHERE isbn = '$isbn'";

// 执行更新操作
if ($conn->query($sql) === TRUE) {
    $response['response'] = "图书信息更新成功。";
    $response['code'] = 200;
} else {
    $response['response'] = "图书信息更新失败：" . $conn->error;
    $response['code'] = 500;
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
