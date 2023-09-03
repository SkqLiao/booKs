<?php
require("../func.php");

header('Content-Type: application/json; charset=utf-8');

checkRequiredFields($_GET, ["isbn"]);

$isbn = $_GET["isbn"];

deleteData("basic_info", "isbn = '$isbn'");

?>