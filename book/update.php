<?php
require("../func.php");

header('Content-Type: application/json; charset=utf-8');

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["isbn"]);
$isbn = $input_data["isbn"];
$result = queryRawData("SELECT * FROM basic_info WHERE isbn = '$isbn'");

if ($result->num_rows > 0) {
    updateData("basic_info", $input_data, "isbn = '$isbn'");
} else {
    printMessage([
        "code" => $errorMessage["book_not_exists"]["code"],
        "message" => sprintf($errorMessages["book_not_exists"]["message"], $isbn)
    ]);
}
?>
