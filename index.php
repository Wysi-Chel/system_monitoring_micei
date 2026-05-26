<?php
require "config.php";
require __DIR__ . "/includes/monitoring_options.php";
require __DIR__ . "/includes/monitoring_helpers.php";
require __DIR__ . "/includes/monitoring_repository.php";

$today = (new DateTimeImmutable("now", new DateTimeZone("Asia/Manila")))->format("Y-m-d");
$company = resolveCompanyConfig($_GET["company"] ?? null, $companyConfigs);
$fixedBranch = $company["fixed_branch"] ?? null;
$showBranchSelector = $fixedBranch === null;
ensureMonitoringTable($pdo, $company);

$filterOptions = [
    "branch" => $branchOptions,
    "dealer" => $dealerOptions,
    "department" => $departmentOptions,
    "module" => $moduleOptions,
    "status" => $statusOptions,
    "per_page" => $rowsPerPageOptions,
];

$tableNameSql = quoteMysqlIdentifier($company["table_name"]);
$filters = buildMonitoringFilters($_GET, $company, $filterOptions);
$totalRecords = countMonitoringRecords($pdo, $tableNameSql, $filters);
$pagination = buildPaginationState($filters["page"], $filters["per_page"], $totalRecords);
$filters["page"] = $pagination["page"];
$records = fetchMonitoringRecords($pdo, $tableNameSql, $filters, $pagination["limit"], $pagination["offset"]);
$records = enrichMonitoringRecordsWithDataCorrectionActions($pdo, $tableNameSql, $records);

$listQueryParams = buildMonitoringListQueryParams($company["key"], $filters);
$mitsubishiUrl = buildUrl("index.php", $listQueryParams, [
    "company" => "mitsubishi",
    "saved" => null,
    "page" => 1,
]);
$hyundaiUrl = buildUrl("index.php", $listQueryParams, [
    "company" => "hyundai",
    "saved" => null,
    "page" => 1,
]);
$ticketMonitoringUrl = buildUrl("ticket_monitoring.php", [
    "company" => $company["key"],
]);
$clearFiltersUrl = buildUrl("index.php", ["company" => $company["key"]]);
$exportUrl = buildUrl("export_excel.php", buildMonitoringListQueryParams($company["key"], $filters, false));
$activeFilterBadges = buildActiveFilterBadges($filters);
$savedMessage = "Record successfully saved to the " . $company["table_name"] . " table.";
$validationErrorMessage = resolveMonitoringValidationErrorMessage($_GET["error"] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($company["system_name"]) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="<?= e($company["logo_type"]) ?>" href="<?= e($company["logo_path"]) ?>">
    <link rel="shortcut icon" type="<?= e($company["logo_type"]) ?>" href="<?= e($company["logo_path"]) ?>">
    <script src="assets/js/theme-init.js"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="company-<?= e($company["key"]) ?> page-system-monitoring">
<?php require __DIR__ . "/includes/partials/page_header.php"; ?>

<main>
    <?php require __DIR__ . "/includes/partials/encoding_form.php"; ?>
    <?php require __DIR__ . "/includes/partials/summary_table.php"; ?>
</main>

<?php if (isset($_GET["saved"])): ?>
    <?php require __DIR__ . "/includes/partials/saved_modal.php"; ?>
<?php endif; ?>

<script src="assets/js/index.js" defer></script>
</body>
</html>
    