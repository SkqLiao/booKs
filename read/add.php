<?php
require("../func.php");

header('Content-Type: application/json; charset=utf-8');

$input_data = json_decode(file_get_contents("php://input"), true);  

checkRequiredFields($input_data, ["book_id", "time_length", "start_page", "end_page", "date"]);

addData("reading_record", $input_data);

?>