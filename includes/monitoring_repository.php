<?php
function normalizeSearchFilter($value): string
{
    $value = trim((string) $value);
    if ($value === "") {
        return "";
    }

    return preg_replace('/\s+/u', ' ', $value) ?? $value;
}

function normalizeDateFilter($value): string
{
    $value = trim((string) $value);
    if ($value === "") {
        return "";
    }

    $date = DateTimeImmutable::createFromFormat("Y-m-d", $value);
    return $date && $date->format("Y-m-d") === $value ? $value : "";
}

function normalizeAllowedFilter($value, array $allowedOptions): string
{
    $value = trim((string) $value);
    return in_array($value, $allowedOptions, true) ? $value : "";
}

function normalizePositiveInt($value, int $default): int
{
    if (is_numeric($value) && (int) $value > 0) {
        return (int) $value;
    }

    return $default;
}

function escapeLikeTerm(string $value): string
{
    return str_replace(["\\", "%", "_"], ["\\\\", "\\%", "\\_"], $value);
}

function buildMonitoringFilters(array $input, array $company, array $filterOptions): array
{
    $dateFrom = normalizeDateFilter($input["date_from"] ?? "");
    $dateTo = normalizeDateFilter($input["date_to"] ?? "");

    if ($dateFrom !== "" && $dateTo !== "" && $dateFrom > $dateTo) {
        [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

    $filters = [
        "search" => "",
        "date_from" => "",
        "date_to" => "",
        "branch" => "",
        "department" => "",
        "module" => "",
        "status" => normalizeAllowedFilter($input["status"] ?? "", $filterOptions["status"] ?? []),
        "page" => normalizePositiveInt($input["page"] ?? 1, 1),
        "per_page" => 25,
    ];

    if (($company["fixed_branch"] ?? null) === null) {
        $filters["branch"] = normalizeAllowedFilter($input["branch"] ?? "", $filterOptions["branch"] ?? []);
    }

    return $filters;
}

function buildTicketMonitoringFilters(array $input, array $company, array $filterOptions): array
{
    $filters = [
        "search" => normalizeSearchFilter($input["q"] ?? ""),
        "branch" => "",
        "ticket_status" => normalizeAllowedFilter($input["status"] ?? "", $filterOptions["status"] ?? []),
        "page" => normalizePositiveInt($input["page"] ?? 1, 1),
        "per_page" => normalizePositiveInt($input["per_page"] ?? 25, 25),
    ];

    if (($company["fixed_branch"] ?? null) === null) {
        $filters["branch"] = normalizeAllowedFilter($input["branch"] ?? "", $filterOptions["branch"] ?? []);
    }

    if (!in_array($filters["per_page"], $filterOptions["per_page"] ?? [25], true)) {
        $filters["per_page"] = 25;
    }

    return $filters;
}

function buildMonitoringWhereClause(array $filters, array &$bindings): string
{
    $conditions = [];
    $bindings = [];

    if ($filters["search"] !== "") {
        $searchValue = "%" . escapeLikeTerm($filters["search"]) . "%";
        $searchColumns = [
            "branch",
            "department",
            "module",
            "user_name",
            "invoice_reference",
            "payment_reference",
            "client_name",
            "CAST(amount AS CHAR)",
            "reason",
            "approved_by",
            "processed_type",
            "processed_by",
            "remarks",
            "classification",
            "system_admin",
            "ticket",
            "status",
            "offense",
            "DATE_FORMAT(date_recorded, '%Y-%m-%d')",
            "DATE_FORMAT(transaction_date, '%Y-%m-%d')",
            "DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s')",
        ];

        $searchParts = [];
        foreach ($searchColumns as $index => $columnExpression) {
            $paramKey = "search_" . $index;
            $searchParts[] = $columnExpression . " LIKE :" . $paramKey . " ESCAPE '\\\\'";
            $bindings[$paramKey] = $searchValue;
        }

        $conditions[] = "(" . implode(" OR ", $searchParts) . ")";
    }

    if ($filters["date_from"] !== "") {
        $conditions[] = "date_recorded >= :date_from";
        $bindings["date_from"] = $filters["date_from"];
    }

    if ($filters["date_to"] !== "") {
        $conditions[] = "date_recorded <= :date_to";
        $bindings["date_to"] = $filters["date_to"];
    }

    if ($filters["branch"] !== "") {
        $conditions[] = "branch = :branch";
        $bindings["branch"] = $filters["branch"];
    }

    if ($filters["department"] !== "") {
        $conditions[] = "department = :department";
        $bindings["department"] = $filters["department"];
    }

    if ($filters["module"] !== "") {
        $conditions[] = "module = :module";
        $bindings["module"] = $filters["module"];
    }

    if ($filters["status"] !== "") {
        $conditions[] = "CONCAT(',', REPLACE(COALESCE(status, ''), ', ', ','), ',') LIKE :status ESCAPE '\\\\'";
        $bindings["status"] = "%," . escapeLikeTerm($filters["status"]) . ",%";
    }

    return $conditions === [] ? "" : " WHERE " . implode(" AND ", $conditions);
}

function buildTicketMonitoringWhereClause(array $filters, array &$bindings): string
{
    $conditions = [
        "COALESCE(TRIM(ticket_number), '') <> ''",
    ];
    $bindings = [];

    if (($filters["search"] ?? "") !== "") {
        $searchValue = "%" . escapeLikeTerm($filters["search"]) . "%";
        $searchColumns = [
            "ticket_number",
            "module",
            "ticket_description",
            "created_by",
            "ticket_status",
        ];

        $searchParts = [];
        foreach ($searchColumns as $index => $columnExpression) {
            $paramKey = "ticket_search_" . $index;
            $searchParts[] = $columnExpression . " LIKE :" . $paramKey . " ESCAPE '\\\\'";
            $bindings[$paramKey] = $searchValue;
        }

        $conditions[] = "(" . implode(" OR ", $searchParts) . ")";
    }

    if (($filters["branch"] ?? "") !== "") {
        $conditions[] = "branch = :branch";
        $bindings["branch"] = $filters["branch"];
    }

    if (($filters["ticket_status"] ?? "") !== "") {
        $conditions[] = "ticket_status = :ticket_status";
        $bindings["ticket_status"] = $filters["ticket_status"];
    }

    return " WHERE " . implode(" AND ", $conditions);
}

function countMonitoringRecords(PDO $pdo, string $tableNameSql, array $filters): int
{
    $bindings = [];
    $whereClause = buildMonitoringWhereClause($filters, $bindings);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$tableNameSql}{$whereClause}");

    foreach ($bindings as $key => $value) {
        $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function fetchMonitoringRecords(
    PDO $pdo,
    string $tableNameSql,
    array $filters,
    ?int $limit = null,
    int $offset = 0,
    string $orderDirection = "DESC"
): array {
    $bindings = [];
    $whereClause = buildMonitoringWhereClause($filters, $bindings);
    $direction = strtoupper($orderDirection) === "ASC" ? "ASC" : "DESC";

    $sql = "SELECT * FROM {$tableNameSql}{$whereClause} ORDER BY id {$direction}";
    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);

    foreach ($bindings as $key => $value) {
        $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
    }

    if ($limit !== null) {
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", max(0, $offset), PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countTicketMonitoringRecords(PDO $pdo, string $tableNameSql, array $filters): int
{
    $bindings = [];
    $whereClause = buildTicketMonitoringWhereClause($filters, $bindings);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$tableNameSql}{$whereClause}");

    foreach ($bindings as $key => $value) {
        $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function fetchTicketMonitoringRecords(
    PDO $pdo,
    string $tableNameSql,
    array $filters,
    ?int $limit = null,
    int $offset = 0,
    string $orderDirection = "DESC"
): array {
    $bindings = [];
    $whereClause = buildTicketMonitoringWhereClause($filters, $bindings);
    $direction = strtoupper($orderDirection) === "ASC" ? "ASC" : "DESC";

    $sql = "SELECT * FROM {$tableNameSql}{$whereClause} ORDER BY id {$direction}";
    if ($limit !== null) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);

    foreach ($bindings as $key => $value) {
        $stmt->bindValue(":" . $key, $value, PDO::PARAM_STR);
    }

    if ($limit !== null) {
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", max(0, $offset), PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchTicketMonitoringRecordById(PDO $pdo, string $tableNameSql, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM {$tableNameSql} WHERE id = :id LIMIT 1");
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record === false ? null : $record;
}

function updateTicketMonitoringRecordStatus(PDO $pdo, string $tableNameSql, int $id, string $ticketStatus, ?string $resolvedAt): void
{
    $stmt = $pdo->prepare(
        "UPDATE {$tableNameSql}
         SET ticket_status = :ticket_status,
             resolved_at = :resolved_at
         WHERE id = :id"
    );

    $stmt->bindValue(":ticket_status", $ticketStatus, PDO::PARAM_STR);
    if ($resolvedAt === null) {
        $stmt->bindValue(":resolved_at", null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(":resolved_at", $resolvedAt, PDO::PARAM_STR);
    }
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
}
