<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$conn->query("DELETE FROM products WHERE id=$id");
header("Location: products.php");
?>