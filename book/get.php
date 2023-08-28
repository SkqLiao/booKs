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

// 根据查询参数添加条件

function getCondition($key) {
    if (!isset($_GET[$key])) return null;
    $value = $_GET[$key];
    if (is_array($value)) {
        $condition = "$key IN (";
        foreach ($value as $v) {
            $condition .= "'$v',";
        }
        $condition = substr($condition, 0, -1);
        $condition .= ")";
    } else {
        $condition = "$key = '$value'";
    }
    return $condition;
}

$keys = array('isbn', 'publish', 'buy_pos', 'series', 'producer', 'author');

$where_conditions = array();

foreach ($keys as $key) {
    $condition = getCondition($key);
    if ($condition) {
        $where_conditions[] = $condition;
    }
}

if (isset($_GET['title'])) {
    $where_conditions[] = "CONCAT(title, subtitle, original_title) LIKE '%" . $_GET['title'] . "%'";
}

if (isset($_GET['author'])) {
    foreach ($_GET['author'] as $author) {
        $where_conditions[] = "author LIKE '%" . $author . "%'";
    }
}

if (isset($_GET['buy_date_from'])) {
    $buy_date_from = $_GET['buy_date_from'];
    $where_conditions[] = "buy_date >= '$buy_date_from'";
}
if (isset($_GET['buy_date_to'])) {
    $buy_date_to = $_GET['buy_date_to'];
    $where_conditions[] = "buy_date <= '$buy_date_to'";
}

if (isset($_GET['buy_price_from'])) {
    $buy_price_from = $_GET['buy_price_from'];
    $where_conditions[] = "real_price >= $buy_price_from";
}
if (isset($_GET['buy_price_to'])) {
    $buy_price_to = $_GET['buy_price_to'];
    $where_conditions[] = "real_price <= $buy_price_to";
}

// 构建查询语句
$sql = "SELECT * FROM basic_info WHERE 1=1";

// 判断是否有查询条件
if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

if (isset($_GET['real_price'])) {
    $sql .= " AND real_price >= $value[0] AND real_price <= $value[1]";
}

if (isset($_GET['n'])) {
    $n = $_GET['n'];
    $sql .= " LIMIT 1 OFFSET ". ($n - 1);
}

// 查询数据库获取图书信息
$result = $conn->query($sql);

// 构建响应数组
$response = array();

function str2List($str) {
    // 删除首尾第一个字符]
    if ($str[0] == '[' && $str[strlen($str) - 1] == ']') {
        $str = substr($str, 1, strlen($str) - 2);
    }
    $array = explode(", ", $str);

    // 去掉单引号和空格
    $cleanedArray = array_map(function($str) {
        return trim(trim($str, "'"), " ");
    }, $array);
    return $cleanedArray;
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $isbn = $row['isbn'];
        $imagePath = '../images/'. $isbn .'.jpg';
        echo $image_path."\n";
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $imageBase64 = base64_encode($imageData);
            $row['cover_base64'] = $imageBase64;
        }

        $row['author'] = str2List($row['author']);
        $row['translator'] = str2List($row['translator']);
        $row['catalog'] = str2List($row['catalog']);
        $row['rating']['count'] = intval($row['rating_count']);
        $row['rating']['value'] = floatval($row['rating_value']);
        $row['rating']['percent'][] = floatval($row['rating_one_star_per']);
        $row['rating']['percent'][] = floatval($row['rating_two_star_per']);
        $row['rating']['percent'][] = floatval($row['rating_three_star_per']);
        $row['rating']['percent'][] = floatval($row['rating_four_star_per']);
        $row['rating']['percent'][] = floatval($row['rating_five_star_per']);
        // erase old values
        unset($row['rating_count']);
        unset($row['rating_value']);
        unset($row['rating_five_star_per']);
        unset($row['rating_four_star_per']);
        unset($row['rating_three_star_per']);
        unset($row['rating_two_star_per']);
        unset($row['rating_one_star_per']);

        $response['data'][] = $row;
    }
    $response['response'] = "查询成功！";
    $response['code'] = 200;
} else {
    $response['response'] = "没有找到匹配的图书信息。";
    $response['code'] = 404;
}

// 输出响应数据
echo json_encode($response);

// 关闭数据库连接
$conn->close();

?>
