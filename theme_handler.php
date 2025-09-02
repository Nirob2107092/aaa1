<?php
// filepath: c:\xampp\htdocs\aaa\theme_handler.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('portfolio_theme', $theme, time() + (365 * 24 * 60 * 60), '/');
    echo json_encode(['success' => true, 'theme' => $theme]);
    exit;
}
