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

$companyConfigs = [
    "mitsubishi" => [
        "key" => "mitsubishi",
        "company_name" => "Mitsubishi",
        "system_name" => "MICEI System Monitoring",
        "table_name" => "MICEI system monitoring",
        "logo_path" => "assets/images/mitsubishi-logo.png",
        "logo_type" => "image/png",
        "logo_alt" => "Mitsubishi Motors Drive your Ambition",
        "export_slug" => "micei",
    ],
    "hyundai" => [
        "key" => "hyundai",
        "company_name" => "Hyundai",
        "system_name" => "NTR System Monitoring",
        "table_name" => "NTR system monitoring",
        "logo_path" => "assets/images/hyundai_logo.png",
        "logo_type" => "image/png",
        "logo_alt" => "Hyundai Company",
        "export_slug" => "ntr",
        "fixed_branch" => "GSC",
    ],
];

function resolveCompanyConfig(?string $companyKey, array $configs): array
{
    $normalizedKey = strtolower(trim((string) $companyKey));
    return $configs[$normalizedKey] ?? $configs["mitsubishi"];
}

function quoteMysqlIdentifier(string $identifier): string
{
    return "`" . str_replace("`", "``", $identifier) . "`";
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
