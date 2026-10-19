<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "bcp_library_db"; // Make sure this matches your phpMyAdmin database name exactly!

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>