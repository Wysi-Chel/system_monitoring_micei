<?php
// Database settings
// For XAMPP default setup, username is usually "root" and password is usually blank.
$host = "localhost";
$dbname = "system_monitoring_db";
$username = "chel";
$password = "Wysiwyg1721!";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
