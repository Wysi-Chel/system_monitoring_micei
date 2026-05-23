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
        "ticket_table_name" => "MICEI ticket monitoring",
        "resolved_ticket_table_name" => "MICEI resolved ticket monitoring",
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
        "ticket_table_name" => "NTR ticket monitoring",
        "resolved_ticket_table_name" => "NTR resolved ticket monitoring",
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

function ensureMysqlTableColumn(PDO $pdo, string $tableNameSql, string $columnName, string $definitionSql): void
{
    $columnNameSql = $pdo->quote($columnName);
    $columnExists = (bool) $pdo->query("SHOW COLUMNS FROM {$tableNameSql} LIKE {$columnNameSql}")->fetch(PDO::FETCH_ASSOC);

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE {$tableNameSql} ADD COLUMN {$definitionSql}");
    }
}

function ensureTicketMonitoringTable(PDO $pdo, array $company): void
{
    if (!isset($company["ticket_table_name"]) || !is_string($company["ticket_table_name"]) || trim($company["ticket_table_name"]) === "") {
        throw new RuntimeException("Ticket monitoring table is not configured for this company.");
    }

    $tableNameSql = quoteMysqlIdentifier($company["ticket_table_name"]);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS {$tableNameSql} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            branch VARCHAR(100),
            module VARCHAR(100),
            ticket_number VARCHAR(150) NOT NULL,
            ticket_description TEXT,
            date_created DATE NOT NULL,
            created_by VARCHAR(150),
            ticket_status VARCHAR(100) NOT NULL,
            resolved_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    ensureMysqlTableColumn($pdo, $tableNameSql, "module", "module VARCHAR(100) AFTER branch");
}

function ensureResolvedTicketMonitoringTable(PDO $pdo, array $company): void
{
    if (!isset($company["resolved_ticket_table_name"]) || !is_string($company["resolved_ticket_table_name"]) || trim($company["resolved_ticket_table_name"]) === "") {
        throw new RuntimeException("Resolved ticket monitoring table is not configured for this company.");
    }

    $tableNameSql = quoteMysqlIdentifier($company["resolved_ticket_table_name"]);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS {$tableNameSql} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            source_ticket_id INT NOT NULL,
            branch VARCHAR(100),
            module VARCHAR(100),
            ticket_number VARCHAR(150) NOT NULL,
            ticket_description TEXT,
            date_created DATE NOT NULL,
            created_by VARCHAR(150),
            ticket_status VARCHAR(100) NOT NULL,
            resolved_at DATETIME NOT NULL,
            ticket_age_days INT NOT NULL DEFAULT 0,
            archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_source_ticket_id (source_ticket_id)
        )"
    );

    ensureMysqlTableColumn($pdo, $tableNameSql, "module", "module VARCHAR(100) AFTER branch");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
