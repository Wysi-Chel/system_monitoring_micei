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
        "has_ticket_monitoring" => true,
        "table_name" => "micei_system_monitoring",
        "ticket_table_name" => "micei_ticket_monitoring",
        "legacy_table_names" => ["MICEI system monitoring", "micei system monitoring"],
        "legacy_ticket_table_names" => ["MICEI ticket monitoring", "micei ticket monitoring"],
        "legacy_resolved_ticket_table_names" => ["MICEI resolved ticket monitoring", "micei resolved ticket monitoring"],
        "logo_path" => "assets/images/mitsubishi-logo.png",
        "logo_type" => "image/png",
        "logo_alt" => "Mitsubishi Motors Drive your Ambition",
        "export_slug" => "micei",
    ],
    "hyundai" => [
        "key" => "hyundai",
        "company_name" => "Hyundai",
        "system_name" => "NTR System Monitoring",
        "has_ticket_monitoring" => true,
        "table_name" => "ntr_system_monitoring",
        "ticket_table_name" => "ntr_ticket_monitoring",
        "legacy_table_names" => ["NTR system monitoring", "ntr system monitoring"],
        "legacy_ticket_table_names" => ["NTR ticket monitoring", "ntr ticket monitoring"],
        "legacy_resolved_ticket_table_names" => ["NTR resolved ticket monitoring", "ntr resolved ticket monitoring"],
        "logo_path" => "assets/images/hyundai_logo.png",
        "logo_type" => "image/png",
        "logo_alt" => "Hyundai Company",
        "export_slug" => "ntr",
    ],
];

function resolveCompanyConfig(?string $companyKey, array $configs): array
{
    $normalizedKey = strtolower(trim((string) $companyKey));
    return $configs[$normalizedKey] ?? $configs["mitsubishi"];
}

function companySupportsTicketMonitoring(array $company): bool
{
    return (bool) ($company["has_ticket_monitoring"] ?? false);
}

function quoteMysqlIdentifier(string $identifier): string
{
    return "`" . str_replace("`", "``", $identifier) . "`";
}

function mysqlTableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare("SHOW TABLES LIKE :table_name");
    $stmt->execute([":table_name" => $tableName]);

    return $stmt->fetchColumn() !== false;
}

function normalizeLegacyTableNames($legacyTableNames): array
{
    if (is_string($legacyTableNames)) {
        $legacyTableNames = [$legacyTableNames];
    }

    if (!is_array($legacyTableNames)) {
        return [];
    }

    $normalized = [];
    foreach ($legacyTableNames as $legacyTableName) {
        if (!is_string($legacyTableName)) {
            continue;
        }

        $legacyTableName = trim($legacyTableName);
        if ($legacyTableName === "" || in_array($legacyTableName, $normalized, true)) {
            continue;
        }

        $normalized[] = $legacyTableName;
    }

    return $normalized;
}

function countMysqlTableRows(PDO $pdo, string $tableName): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM " . quoteMysqlIdentifier($tableName))->fetchColumn();
}

function fetchMysqlTableColumnNames(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->query("SHOW COLUMNS FROM " . quoteMysqlIdentifier($tableName));
    $columns = [];

    while ($column = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($column["Field"])) {
            continue;
        }

        $columns[] = (string) $column["Field"];
    }

    return $columns;
}

function renameLegacyTableIfNeeded(PDO $pdo, $legacyTableNames, string $targetTableName): void
{
    foreach (normalizeLegacyTableNames($legacyTableNames) as $legacyTableName) {
        if ($legacyTableName === $targetTableName) {
            continue;
        }

        if (mysqlTableExists($pdo, $targetTableName) || !mysqlTableExists($pdo, $legacyTableName)) {
            continue;
        }

        $pdo->exec(
            "RENAME TABLE " . quoteMysqlIdentifier($legacyTableName)
            . " TO " . quoteMysqlIdentifier($targetTableName)
        );
        return;
    }
}

function syncLegacyTableIntoTargetIfNeeded(PDO $pdo, $legacyTableNames, string $targetTableName): void
{
    if (!mysqlTableExists($pdo, $targetTableName)) {
        return;
    }

    $targetRowCount = countMysqlTableRows($pdo, $targetTableName);

    foreach (normalizeLegacyTableNames($legacyTableNames) as $legacyTableName) {
        if ($legacyTableName === $targetTableName || !mysqlTableExists($pdo, $legacyTableName)) {
            continue;
        }

        $legacyRowCount = countMysqlTableRows($pdo, $legacyTableName);
        if ($legacyRowCount === 0) {
            $pdo->exec("DROP TABLE " . quoteMysqlIdentifier($legacyTableName));
            continue;
        }

        if ($targetRowCount === 0) {
            $targetColumns = fetchMysqlTableColumnNames($pdo, $targetTableName);
            $legacyColumns = fetchMysqlTableColumnNames($pdo, $legacyTableName);
            $sharedColumns = array_values(array_intersect($targetColumns, $legacyColumns));

            if ($sharedColumns === []) {
                continue;
            }

            $columnListSql = implode(
                ", ",
                array_map(
                    static fn(string $columnName): string => quoteMysqlIdentifier($columnName),
                    $sharedColumns
                )
            );

            $pdo->exec(
                "INSERT INTO " . quoteMysqlIdentifier($targetTableName)
                . " (" . $columnListSql . ") SELECT " . $columnListSql
                . " FROM " . quoteMysqlIdentifier($legacyTableName)
            );
            $pdo->exec("DROP TABLE " . quoteMysqlIdentifier($legacyTableName));
            $targetRowCount = $legacyRowCount;
        }
    }
}

function ensureMonitoringTable(PDO $pdo, array $company): void
{
    if (!isset($company["table_name"]) || !is_string($company["table_name"]) || trim($company["table_name"]) === "") {
        throw new RuntimeException("System monitoring table is not configured for this company.");
    }

    renameLegacyTableIfNeeded($pdo, $company["legacy_table_names"] ?? [], $company["table_name"]);
    $tableNameSql = quoteMysqlIdentifier($company["table_name"]);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS {$tableNameSql} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date_recorded DATE NOT NULL,
            transaction_date DATE NOT NULL,
            branch VARCHAR(100),
            dealer VARCHAR(100),
            department VARCHAR(100),
            module VARCHAR(100),
            user_name VARCHAR(150),
            invoice_reference VARCHAR(150),
            payment_reference VARCHAR(150),
            client_name VARCHAR(200),
            amount DECIMAL(15,2) NULL,
            reason TEXT,
            approved_by VARCHAR(150),
            processed_type VARCHAR(100),
            processed_by VARCHAR(150),
            remarks TEXT,
            classification VARCHAR(100),
            system_admin VARCHAR(150),
            ticket VARCHAR(150),
            status VARCHAR(100),
            offense VARCHAR(150),
            action_taken VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );
    ensureMysqlTableColumn($pdo, $tableNameSql, "dealer", "dealer VARCHAR(100) AFTER branch");
    ensureMysqlTableColumn($pdo, $tableNameSql, "action_taken", "action_taken VARCHAR(100) AFTER offense");
    syncLegacyTableIntoTargetIfNeeded($pdo, $company["legacy_table_names"] ?? [], $company["table_name"]);
    backfillMonitoringDealerValues($pdo, $tableNameSql);
    backfillMonitoringModuleValues($pdo, $tableNameSql);
}

function backfillMonitoringDealerValues(PDO $pdo, string $tableNameSql): void
{
    $pdo->exec(
        "UPDATE {$tableNameSql}
         SET dealer = UPPER(TRIM(branch)),
             branch = 'GSC'
         WHERE COALESCE(TRIM(dealer), '') = ''
           AND UPPER(TRIM(branch)) IN ('MGSC', 'NGSC', 'MKC')"
    );
}

function backfillMonitoringModuleValues(PDO $pdo, string $tableNameSql): void
{
    $pdo->exec(
        "UPDATE {$tableNameSql}
         SET module = 'All Modules'
         WHERE module IS NULL
            OR TRIM(module) = ''
            OR UPPER(TRIM(module)) IN ('ALL MODULE', 'ALL MODULES')"
    );
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
    if (!companySupportsTicketMonitoring($company)) {
        throw new RuntimeException("Ticket monitoring is not enabled for this company.");
    }

    if (!isset($company["ticket_table_name"]) || !is_string($company["ticket_table_name"]) || trim($company["ticket_table_name"]) === "") {
        throw new RuntimeException("Ticket monitoring table is not configured for this company.");
    }

    renameLegacyTableIfNeeded($pdo, $company["legacy_ticket_table_names"] ?? [], $company["ticket_table_name"]);
    $tableNameSql = quoteMysqlIdentifier($company["ticket_table_name"]);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS {$tableNameSql} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            branch VARCHAR(100),
            dealer VARCHAR(100),
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

    ensureMysqlTableColumn($pdo, $tableNameSql, "dealer", "dealer VARCHAR(100) AFTER branch");
    ensureMysqlTableColumn($pdo, $tableNameSql, "module", "module VARCHAR(100) AFTER dealer");
    syncLegacyTableIntoTargetIfNeeded($pdo, $company["legacy_ticket_table_names"] ?? [], $company["ticket_table_name"]);
    backfillTicketMonitoringDealerValues($pdo, $tableNameSql);
    backfillTicketMonitoringModuleValues($pdo, $tableNameSql);
}

function backfillTicketMonitoringDealerValues(PDO $pdo, string $tableNameSql): void
{
    $pdo->exec(
        "UPDATE {$tableNameSql}
         SET dealer = UPPER(TRIM(branch)),
             branch = 'GSC'
         WHERE COALESCE(TRIM(dealer), '') = ''
           AND UPPER(TRIM(branch)) IN ('MGSC', 'NGSC', 'MKC')"
    );
}

function backfillTicketMonitoringModuleValues(PDO $pdo, string $tableNameSql): void
{
    $pdo->exec(
        "UPDATE {$tableNameSql}
         SET module = 'All Modules'
         WHERE module IS NULL
            OR TRIM(module) = ''
            OR UPPER(TRIM(module)) IN ('ALL MODULE', 'ALL MODULES')"
    );
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
