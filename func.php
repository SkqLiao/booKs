<?php
$errorMessages = [
    "db_connection_error" => ["code" => 501, "message" => "数据库连接失败"],
    "db_query_error" => ["code" => 500, "message" => "数据库查询失败"],
    "db_insert_error" => ["code" => 501, "message" => "数据库插入失败"],
    "db_update_error" => ["code" => 502, "message" => "数据库更新失败"],
    "db_delete_error" => ["code" => 503, "message" => "数据库删除失败"],
    "missing_fields" => ["code" => 504, "message" => "字段：%s 的数据未传入"],
    "data_type_mismatch" => ["code" => 505, "message" => "字段：%s 的数据类型应为：%s，实际为：%s"],
    "unknown_field" => ["code" => 506, "message" => "未知字段：%s"],
    "missing_required_fields" => ["code" => 507, "message" => "缺少必填字段 %s"],
    "exists" => ["code" => 508, "message" => "%s 已存在"],
    "not_exists" => ["code" => 509, "message" => "%s 不存在"],
    "cover_save_failed" => ["code" => 510, "message" => "封面保存失败：%s"],
    // 在这里添加其他错误情况...
];

header('Content-Type: application/json; charset=utf-8');

function printMessage($message) {
    echo json_encode($message);
    exit;
}

$host = "mysql";
$username = "root";
$password = "123456";
$database = "book";
$conn = new mysqli('p:'.$host, $username, $password, $database);

if ($conn->connect_error) {
    printMessage([
        "code" => $errorMessages["db_connection_error"]["code"],
        "message" => $errorMessages["db_connection_error"]["message"],
        "error" => $conn->connect_error
    ]);
}

function mapMysqlTypeToDataType($mysqlType) {
    switch ($mysqlType) {
        case MYSQLI_TYPE_INT24:
        case MYSQLI_TYPE_LONG:
        case MYSQLI_TYPE_LONGLONG:
        case MYSQLI_TYPE_SHORT:
        case MYSQLI_TYPE_TINY:
            return "integer";

        case MYSQLI_TYPE_FLOAT:
        case MYSQLI_TYPE_DOUBLE:
            return "float";

        case MYSQLI_TYPE_BIT:
            return "boolean";

        case MYSQLI_TYPE_JSON:
            return "json";

        default:
            return "string"; // 默认为字符串
    }
}

function convertToDataType($tableName, $field, $value, $dataType) {
    global $json_convert_field;
    
    switch ($dataType) {
        case 'json':
            return json_decode($value, true);

        case 'integer':
            return (int)$value;

        case 'float':
            return (float)$value;

        case 'boolean':
            return (bool)$value;

        default:
            return $value;
    }
}

function checkRequiredFields($inputData, $requiredFields) {
    global $errorMessages;
    foreach ($requiredFields as $field) {
        if (!isset($inputData[$field])) {
            printMessage([
                "code" => $errorMessages["missing_required_fields"]["code"],
                "message" => sprintf($errorMessages["missing_required_fields"]["message"], $field)
            ]);
        }
    }
}

function queryRawData($sql) {
    global $conn, $errorMessages;
    $result = $conn->query($sql);
    if ($result === FALSE) {
        printMessage([
            "code" => $errorMessages["db_query_error"]["code"],
            "message" => $errorMessages["db_query_error"]["message"],
            "error" => $conn->error
        ]);
    }
    return $result;
}

function addData($tableName, $input_data) {
    global $errorMessages, $conn;
    $result = queryRawData("SHOW COLUMNS FROM $tableName");
    $columns_in_table = array();
    while ($row = $result->fetch_assoc()) {
        $columns_in_table[] = $row['Field'];
    }
    $valid_columns = array_intersect(array_keys($input_data), $columns_in_table);
    $columns = implode(", ", $valid_columns);
    $values = array();
    foreach ($valid_columns as $column) {
        if (is_array($input_data[$column])) {
            $values[] = "'" . json_encode($input_data[$column], JSON_UNESCAPED_UNICODE) . "'";
        } else {
            $values[] = "'" . $input_data[$column] . "'";
        }
    }
    $values = implode(", ", $values);
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";

    if ($conn->query($sql) === TRUE) {
        printMessage([
            "code" => 200,
            "message" => "增加成功！"
        ]);
    } else {
        printMessage([
            "code" => $errorMessages["db_insert_error"]["code"],
            "message" => $errorMessages["db_insert_error"]["message"],
            "sql" => $sql,
            "error" => $conn->error
        ]);
    }
}

function updateData($tableName, $input_data, $condition) {
    global $conn, $errorMessages;
    $valid_columns = array();
    
    $result = queryRawData("SHOW COLUMNS FROM $tableName");
    $columns_in_table = array();
    while ($row = $result->fetch_assoc()) {
        $columns_in_table[] = $row['Field'];
    }

    foreach ($input_data as $key => $value) {
        if (in_array($key, $columns_in_table)) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $valid_columns[] = "$key = '" . $value . "'";
        }
    }
    
    $updates = implode(", ", $valid_columns);
    
    $sql = "UPDATE $tableName SET $updates WHERE $condition";
    
    if ($conn->query($sql) === TRUE) {
        printMessage([
            "code" => 200,
            "message" => "更新成功！"
        ]);
    } else {
        printMessage([
            "code" => $errorMessages["db_update_error"]["code"],
            "message" => $errorMessages["db_update_error"]["message"],
            "sql" => $sql,
            "error" => $conn->error
        ]);
    }
}

function deleteData($tableName, $condition) {
    global $conn, $errorMessages;
    $sql = "DELETE FROM $tableName WHERE $condition";
    if ($conn->query($sql) === TRUE) {
        printMessage([
            "code" => 200,
            "message" => "删除成功！",
            "sql" => $sql
        ]);
    } else {
        printMessage([
            "code" => $errorMessages["db_delete_error"]["code"],
            "message" => $errorMessages["db_delete_error"]["message"],
            "sql" => $sql,
            "error" => $conn->error
        ]);
    }
}

?>