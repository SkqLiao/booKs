<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);  

checkRequiredFields($input_data, ["book_id", "time_length", "start_page", "end_page", "date", "finished"]);

$finished = $input_data["finished"] ? 1 : 0;
unset($input_data["finished"]);

addData("reading_record", $input_data, false);

$result = queryRawData("SELECT * from reading_status WHERE book_id = {$input_data["book_id"]}");
if ($result->num_rows > 0) {
    updateData("reading_status", 
    [
        "finished" => $finished
    ],
    "book_id = {$input_data["book_id"]}");
} else {
    addData("reading_status", [
        "book_id" => $input_data["book_id"],
        "finished" => $finished
    ]);
}



?>