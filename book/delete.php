<?php
require("../func.php");



checkRequiredFields($_GET, ["isbn"]);

$isbn = $_GET["isbn"];

deleteData("basic_info", "isbn = '$isbn'");

?>