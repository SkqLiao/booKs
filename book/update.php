<?php
require("../func.php");

$input_data = json_decode(file_get_contents("php://input"), true);

checkRequiredFields($input_data, ["isbn"]);
$isbn = $input_data["isbn"];
$result = queryRawData("SELECT * FROM basic_info WHERE isbn = '$isbn'");


if ($result->num_rows > 0) {
    if (isset($input_data["cover_base64"])) {
        $cover_base64 = $input_data["cover_base64"];
        $cover_data = base64_decode($cover_base64);
        $cover_file = "../images/$isbn.jpg";
        $cover_success = file_put_contents($cover_file, $cover_data);
        if (!$cover_success) {
            printMessage([
                "code" => $errorMessage["cover_save_failed"]["code"],
                "message" => sprintf($errorMessages["cover_save_failed"]["message"], $isbn)
            ]);
        }
    }
    updateData("basic_info", $input_data, "isbn = '$isbn'");
} else {
    printMessage([
        "code" => $errorMessage["not_exists"]["code"],
        "message" => sprintf($errorMessages["not_exists"]["message"], $isbn)
    ]);
}
?>