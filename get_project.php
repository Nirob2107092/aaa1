<?php
// filepath: c:\xampp\htdocs\aaa\get_project.php
header('Content-Type: application/json');

// Database connection
$host = "localhost:4308";
$user = "root";
$pass = "";
$db   = "p_db";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM projects WHERE id=$id");

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Project not found']);
    }
} else {
    echo json_encode(['error' => 'No project ID provided']);
}

$conn->close();
