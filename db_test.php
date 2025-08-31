<?php
$host = "localhost:4308";
$user = "root";
$pass = "";
$db   = "p_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connected successfully!";
