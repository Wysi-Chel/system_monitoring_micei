<?php
// Database settings
// For XAMPP default setup, username is usually "root" and password is usually blank.
$host = "localhost";
$dbname = "system_monitoring_db";
$username = "chel";
$password = "Wysiwyg1721!";

// Excel export helper Python executable.
// Using the absolute path avoids PATH differences between the terminal and Apache/PHP.
$pythonCommand = "C:\\Users\\IT TechSupport 3\\AppData\\Local\\Python\\pythoncore-3.14-64\\python.exe";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
