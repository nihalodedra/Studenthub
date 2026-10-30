<?php
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Database connection failed");
}

$sql = "CREATE DATABASE IF NOT EXISTS studenthub_practical13";
mysqli_query($conn, $sql);

mysqli_select_db($conn, "studenthub_practical13");

$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL
)";

mysqli_query($conn, $sql);
?>