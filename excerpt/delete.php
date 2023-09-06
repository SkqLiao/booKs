<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["id"]);

$id = $input_data["id"];

deleteData("reading_excerpt", "id = $id");

?>