<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop_tps";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');
?>