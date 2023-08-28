<?php

require_once('config.php');
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

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

$keys = array('isbn', 'publish', 'buy_pos', 'series', 'producer');

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

$sql = "SELECT COUNT(*) as total FROM basic_info WHERE 1=1";

if (!empty($where_conditions)) {
    $sql .= " AND " . implode(" AND ", $where_conditions);
}

$result = $conn->query($sql);
$response = array();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $count = $row['total'];
    $response['data'] = intval($count);
} else {
    $response['data'] = 0;
}
$response["response"] = "查询成功";
$response["code"] = 200;
echo json_encode($response);

$conn->close();
?>
