<?php
require "config.php";
require __DIR__ . "/includes/monitoring_options.php";
require __DIR__ . "/includes/monitoring_helpers.php";
require __DIR__ . "/includes/monitoring_repository.php";

$company = resolveCompanyConfig($_POST["company"] ?? $_GET["company"] ?? null, $companyConfigs);
ensureMonitoringTable($pdo, $company);
$tableNameSql = quoteMysqlIdentifier($company["table_name"]);

$recordId = is_numeric($_POST["record_id"] ?? null) ? (int) $_POST["record_id"] : 0;
$actionTaken = trim((string) ($_POST["action_taken"] ?? ""));
$allowedActions = getMonitoringActionOptions();

$redirectParams = [
    "company" => $company["key"],
];

$filterMonth = trim((string) ($_POST["filter_month"] ?? ""));
$filterBranch = trim((string) ($_POST["filter_branch"] ?? ""));
$filterDealer = trim((string) ($_POST["filter_dealer"] ?? ""));
$filterStatus = trim((string) ($_POST["filter_status"] ?? ""));
$filterPage = trim((string) ($_POST["filter_page"] ?? ""));

if ($filterMonth !== "") {
    $redirectParams["month"] = $filterMonth;
}

if ($filterBranch !== "") {
    $redirectParams["branch"] = $filterBranch;
}

if ($filterDealer !== "") {
    $redirectParams["dealer"] = $filterDealer;
}

if ($filterStatus !== "") {
    $redirectParams["status"] = $filterStatus;
}

if ($filterPage !== "" && $filterPage !== "1") {
    $redirectParams["page"] = $filterPage;
}

if ($recordId > 0 && in_array($actionTaken, $allowedActions, true)) {
    $record = fetchMonitoringRecordById($pdo, $tableNameSql, $recordId);

    if ($record !== null) {
        $enrichedRecord = enrichMonitoringRecordsWithDataCorrectionActions($pdo, $tableNameSql, [$record])[0] ?? null;

        if (
            $enrichedRecord !== null
            && containsMultiValueText((string) ($enrichedRecord["processed_type"] ?? ""), "Data Correction")
            && (int) ($enrichedRecord["data_correction_offense_count"] ?? 0) >= 3
        ) {
            updateMonitoringRecordActionTaken($pdo, $tableNameSql, $recordId, $actionTaken);
        }
    }
}

header("Location: index.php?" . http_build_query($redirectParams) . "#summary-section");
exit;
?>
