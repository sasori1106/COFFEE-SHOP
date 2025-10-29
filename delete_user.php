<?php
session_start();
include "db.php";

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

// Prevent deleting self
$user = $conn->query("SELECT username FROM users WHERE id=$id")->fetch_assoc();
if ($user['username'] == $_SESSION['username']) {
    header("Location: users.php");
    exit();
}

$conn->query("DELETE FROM users WHERE id=$id");
header("Location: users.php");
?>