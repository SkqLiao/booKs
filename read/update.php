<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["id"]);
$id = $input_data["id"];
$result = queryRawData("SELECT * FROM reading_record WHERE id = $id");

if ($result->num_rows > 0) {
    updateData("reading_record", $input_data, "id = $id");
} else {
    printMessage([
        "code" => $errorMessage["record_not_exists"]["code"],
        "message" => sprintf($errorMessages["record_not_exists"]["message"], $id)
    ]);
}
?>
