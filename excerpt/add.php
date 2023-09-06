<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);  

checkRequiredFields($input_data, ["book_id", "page", "excerpt", "thoughts", "date"]);

addData("reading_excerpt", $input_data);

?>