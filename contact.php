<?php
// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

// Database credentials
$host = "localhost:4308";
$user = "root";
$pass = "";
$db   = "p_db";

// Gmail credentials
$gmail_username = 'perfeccnirob@gmail.com';
$gmail_password = 'pusp xcft muvz rcbs'; // 16-char App Password from Gmail

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get and sanitize input
    $name    = $conn->real_escape_string($_POST['name']);
    $email   = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // 1️⃣ Insert into database
    $sql = "INSERT INTO contacts (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
    if ($conn->query($sql) !== TRUE) {
        echo "Database error: " . $conn->error;
        exit;
    }

    // 2️⃣ Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmail_username;
        $mail->Password   = $gmail_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($gmail_username, 'Portfolio Website');
        $mail->addAddress($gmail_username); // Send to yourself
        $mail->addReplyTo($email, $name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Contact Form: " . $subject;
        $mail->Body    = "
            <strong>Name:</strong> {$name}<br>
            <strong>Email:</strong> {$email}<br>
            <strong>Subject:</strong> {$subject}<br>
            <strong>Message:</strong><br>{$message}
        ";

        $mail->send();
        echo "Message sent successfully and stored in database!";
    } catch (Exception $e) {
        echo "Message stored but mail not sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request";
}

$conn->close();
