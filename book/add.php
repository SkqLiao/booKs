<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["isbn"]);

$isbn = $input_data["isbn"];

$result = queryRawData("SELECT * FROM basic_info WHERE isbn = '$isbn'");
if ($result->num_rows > 0) {
    printMessage([
        "code" => $errorMessage["book_exists"]["code"],
        "message" => sprintf($errorMessages["book_exists"]["message"], $isbn)
    ]);
}

addData("basic_info", $input_data);

?>