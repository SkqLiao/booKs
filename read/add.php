<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);  

checkRequiredFields($input_data, ["book_id", "time_length", "start_page", "end_page", "start_time", "finished"]);

addData("reading_record", $input_data);
?>