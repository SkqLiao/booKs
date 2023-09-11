<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["id"]);

$id = $input_data["id"];

$book_id = queryRawData("SELECT * from reading_record WHERE id = $id")->fetch_assoc()["book_id"];
deleteData("reading_record", "id = $id", false);
$result = queryRawData("SELECT * from reading_record WHERE book_id = $book_id");

if ($result->num_rows == 0) {
    deleteData("reading_status", "book_id = $book_id");
} else {
    printMessage([
        "code" => 200,
        "message" => "删除成功！"
    ]);
}

?>