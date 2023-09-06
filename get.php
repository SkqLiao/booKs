<?php
require("func.php");

header('Content-Type: application/json charset=utf-8');

checkRequiredFields($_GET, ["table", "fields"]);

$tableName = $_GET["table"];
$query_fields = $_GET["fields"];

// 构建查询语句
$selectFields = implode(", ", $query_fields);
$sql = "SELECT $selectFields FROM $tableName";

if (isset($_GET["conditions"])) {
    $conditionsClause = implode(" AND ", $_GET["conditions"]);
    $sql .= " WHERE $conditionsClause";
}

if (isset($_GET["order_by"])) {
    $sql .= " ORDER BY " . $_GET["order_by"];
}

if (isset($_GET["limit"])) {
    $sql .= " LIMIT " . $_GET["limit"];
}

if (isset($_GET["offset"])) {
    $sql .= " OFFSET " . $_GET["offset"];
}

if (isset($_GET["group_by"])) {
    $sql .= " GROUP BY " . $_GET["group_by"];
}

// 执行查询
$result = $conn->query($sql);

if (!$result) {
    printMessage([
        "code" => $errorMessages["db_query_error"]["code"],
        "message" => sprintf($errorMessages["db_query_error"]["message"]),
        "sql" => $sql,
        "error" => $conn->error
    ]);
}

$data = [];

// 获取查询结果字段的数据类型
$fieldInfo = $result->fetch_fields();
$dataTypes = [];
foreach ($fieldInfo as $field) {
    $dataTypes[$field->name] = mapMysqlTypeToDataType($field->type);
}

while ($row = $result->fetch_assoc()) {
    foreach ($fieldInfo as $field_) {
        $field = $field_->name;
        // if (!isset($row[$field])) {
        //     printMessage([
        //         "code" => $errorMessages["unknown_field"]["code"],
        //         "message" => sprintf($errorMessages["unknown_field"]["message"], $field)
        //     ]);
        // }
        $row[$field] = convertToDataType($tableName, $filed, $row[$field], $dataTypes[$field]);
    }
    $data[] = $row;
}

printMessage([
    "code" => 200,
    "data" => $data,
    "response" => "查询成功"
]);

?>