<?php
require "config.php";

$branchOptions = ["GSC", "KID", "GLA"];
$departmentOptions = ["Accounting", "Sales", "Service", "Parts", "BNC", "CNC", "Manila", "BRP"];
$moduleOptions = ["AMIS", "CMIS", "CSMS", "SMIS", "PMIS"];
$classificationOptions = ["User Error", "System Error", "Data Correction", "Others"];
$processedTypeOptions = ["Cancellation", "Unposting", "Void", "Others"];
$statusOptions = ["Pending", "Done", "Cancelled", "Unposted", "Voided"];
$today = (new DateTimeImmutable("now", new DateTimeZone("Asia/Manila")))->format("Y-m-d");
$company = resolveCompanyConfig($_GET["company"] ?? null, $companyConfigs);

function renderOptionButtons(string $name, array $options, bool $allowMultiple = false): void
{
    $groupRole = $allowMultiple ? "group" : "radiogroup";
    $inputType = $allowMultiple ? "checkbox" : "radio";
    $inputName = $allowMultiple ? $name . "[]" : $name;

    echo '<div class="option-group" role="' . $groupRole . '" aria-label="' . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . '">';

    foreach ($options as $option) {
        $id = $name . "_" . preg_replace('/[^a-z0-9]+/i', "_", strtolower($option));
        $safeId = htmlspecialchars($id, ENT_QUOTES, "UTF-8");
        $safeName = htmlspecialchars($inputName, ENT_QUOTES, "UTF-8");
        $safeOption = htmlspecialchars($option, ENT_QUOTES, "UTF-8");

        echo '<label class="option-button" for="' . $safeId . '">';
        echo '<input type="' . $inputType . '" id="' . $safeId . '" name="' . $safeName . '" value="' . $safeOption . '">';
        echo '<span>' . $safeOption . '</span>';
        echo '</label>';
    }

    echo "</div>";
}

function buildIndexUrl(array $changes = []): string
{
    $params = $_GET;

    foreach ($changes as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
            continue;
        }

        $params[$key] = $value;
    }

    $query = http_build_query($params);
    return "index.php" . ($query !== "" ? "?" . $query : "");
}

$tableNameSql = quoteMysqlIdentifier($company["table_name"]);
$stmt = $pdo->query("SELECT * FROM {$tableNameSql} ORDER BY id DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mitsubishiUrl = buildIndexUrl([
    "company" => "mitsubishi",
    "saved" => null,
]);
$hyundaiUrl = buildIndexUrl([
    "company" => "hyundai",
    "saved" => null,
]);
$exportUrl = "export_excel.php?" . http_build_query([
    "company" => $company["key"],
]);
$savedMessage = "Record successfully saved to the " . $company["table_name"] . " table.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($company["system_name"], ENT_QUOTES, "UTF-8") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="<?= htmlspecialchars($company["logo_type"], ENT_QUOTES, "UTF-8") ?>" href="<?= htmlspecialchars($company["logo_path"], ENT_QUOTES, "UTF-8") ?>">
    <link rel="shortcut icon" type="<?= htmlspecialchars($company["logo_type"], ENT_QUOTES, "UTF-8") ?>" href="<?= htmlspecialchars($company["logo_path"], ENT_QUOTES, "UTF-8") ?>">
    <script>
        (function () {
            try {
                if (window.localStorage && window.localStorage.getItem('systemMonitoringTheme') === 'dark') {
                    document.documentElement.classList.add('dark-theme');
                }
            } catch (error) {
            }
        }());
    </script>

    <style>
        :root {
            --bg: #f4f6f8;
            --text: #222222;
            --text-strong: #0f172a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --header-text: #ffffff;
            --header-muted: #d7deea;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --section-bg: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --input-bg: #ffffff;
            --table-head: #e2e8f0;
            --table-alt: #f8fafc;
            --overlay: rgba(15, 23, 42, 0.64);
            --shadow-card: 0 4px 14px rgba(0, 0, 0, 0.08);
            --shadow-option: 0 12px 28px rgba(148, 163, 184, 0.18);
            --shadow-modal: 0 24px 60px rgba(15, 23, 42, 0.3);
            --success-bg: #dcfce7;
            --success-text: #166534;
            --brand: #d71920;
            --brand-soft: rgba(215, 25, 32, 0.1);
            --brand-hover: rgba(215, 25, 32, 0.08);
            --brand-shadow: rgba(215, 25, 32, 0.28);
            --brand-focus: rgba(215, 25, 32, 0.18);
            --header-bg: linear-gradient(135deg, #450a0a 0%, #7f1d1d 42%, #0f172a 100%);
        }

        body.company-hyundai {
            --brand: #0b63ce;
            --brand-soft: rgba(11, 99, 206, 0.1);
            --brand-hover: rgba(11, 99, 206, 0.08);
            --brand-shadow: rgba(11, 99, 206, 0.28);
            --brand-focus: rgba(11, 99, 206, 0.18);
            --header-bg: linear-gradient(135deg, #08254b 0%, #0b63ce 42%, #0f172a 100%);
        }

        html.dark-theme {
            --bg: #020617;
            --text: #e2e8f0;
            --text-strong: #f8fafc;
            --text-soft: #cbd5e1;
            --text-muted: #94a3b8;
            --header-muted: #c8d4e8;
            --surface: #0f172a;
            --surface-alt: #111c2f;
            --section-bg: linear-gradient(180deg, #10192d 0%, #0b1220 100%);
            --border: #1e293b;
            --border-strong: #334155;
            --input-bg: #0b1220;
            --table-head: #132033;
            --table-alt: #0b1322;
            --overlay: rgba(2, 6, 23, 0.8);
            --shadow-card: 0 18px 42px rgba(0, 0, 0, 0.36);
            --shadow-option: 0 16px 34px rgba(0, 0, 0, 0.28);
            --shadow-modal: 0 28px 72px rgba(0, 0, 0, 0.5);
            --success-bg: #083826;
            --success-text: #86efac;
        }

        html.dark-theme body.company-mitsubishi {
            --header-bg: linear-gradient(135deg, #25070a 0%, #6c1017 42%, #020617 100%);
        }

        html.dark-theme body.company-hyundai {
            --header-bg: linear-gradient(135deg, #031a39 0%, #0a4b99 42%, #020617 100%);
        }

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            transition: background 0.2s ease, color 0.2s ease;
        }

        header {
            background: var(--header-bg);
            color: var(--header-text);
            padding: 18px 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.14);
        }

        .header-bar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
        }

        .header-copy {
            flex: 1 1 auto;
            min-width: 0;
        }

        .header-kicker {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        header h1 {
            margin: 12px 0 0;
            font-size: 50px;
        }

        header p {
            margin: 8px 0 0;
            color: var(--header-muted);
            font-size: 14px;
            max-width: 700px;
        }

        .header-meta {
            display: flex;
            flex: 0 0 auto;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 10px;
        }

        .company-switch {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .switch-link,
        .theme-toggle {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            padding: 10px 14px;
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
            line-height: 1;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.06);
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .switch-link:hover,
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .switch-link.active {
            background: #ffffff;
            border-color: #ffffff;
            color: var(--brand);
        }

        .header-logo {
            width: 110px;
            max-width: 32vw;
            height: auto;
            display: block;
            background: #ffffff;
            border-radius: 16px;
            padding: 10px 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.22);
        }

        main {
            padding: 20px;
            max-width: 1520px;
            margin: 0 auto;
        }

        .card {
            background: var(--surface);
            border-radius: 12px;
            padding: 22px;
            box-shadow: var(--shadow-card);
            margin-bottom: 20px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        h2 {
            margin-top: 0;
            color: var(--text-strong);
            font-size: 18px;
        }

        form {
            display: grid;
            gap: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-soft);
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text);
            background: var(--input-bg);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--text-muted);
        }

        input[type="text"],
        textarea {
            text-transform: uppercase;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-focus);
        }

        .form-section {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
            background: var(--section-bg);
        }

        .form-section.compact-section {
            width: fit-content;
            max-width: 100%;
        }

        .section-header {
            margin-bottom: 14px;
        }

        .section-header h3 {
            margin: 0;
            color: var(--text-strong);
            font-size: 15px;
        }

        .section-header p {
            margin: 5px 0 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .field-grid.compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .date-grid {
            grid-template-columns: repeat(2, max-content);
            justify-content: start;
        }

        .date-field {
            width: 210px;
            max-width: 100%;
        }

        .date-field input[type="date"] {
            width: 100%;
            min-width: 0;
        }

        .selector-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .selector-grid.triple {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .field-span-2 {
            grid-column: span 2;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        .selector-field,
        .field {
            min-width: 0;
        }

        .selector-field {
            display: flex;
            flex-direction: column;
        }

        .selector-field-medium .option-group {
            min-height: 105px;
        }

        .selector-field-tall .option-group {
            min-height: 184px;
        }

        .option-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            padding: 8px 10px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: var(--shadow-option);
        }

        .option-button {
            display: inline-flex;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: normal;
            color: inherit;
            cursor: pointer;
            position: relative;
        }

        .option-button input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .option-button span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 18px 12px;
            border: none;
            border-radius: 14px;
            background: transparent;
            color: var(--text-strong);
            position: relative;
            transition: color 0.15s ease, background 0.15s ease, transform 0.15s ease;
        }

        .option-button span::after {
            content: "";
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 6px;
            height: 4px;
            border-radius: 999px;
            background: transparent;
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }

        .option-button:hover span {
            background: var(--brand-hover);
            color: var(--brand);
        }

        .option-button input:checked + span {
            background: var(--brand-soft);
            color: var(--brand);
            font-weight: bold;
            transform: translateY(-1px);
        }

        .option-button input:checked + span::before {
            content: "\2713";
            margin-right: 6px;
            color: var(--success-text);
            font-size: 13px;
            font-weight: bold;
        }

        .option-button input:checked + span::after {
            background: var(--brand);
            box-shadow: 0 4px 10px var(--brand-shadow);
        }

        textarea {
            min-height: 38px;
            resize: vertical;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
        }

        button,
        .button-link {
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }

        .primary {
            border: 1px solid var(--brand);
            background: var(--brand);
            color: #ffffff;
            box-shadow: 0 10px 24px var(--brand-shadow);
        }

        .secondary {
            border: 1px solid var(--border);
            background: var(--surface-alt);
            color: var(--text-strong);
        }

        .modal-open {
            overflow: hidden;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--overlay);
            z-index: 1000;
        }

        .modal-window {
            width: min(100%, 420px);
            background: var(--surface);
            border-radius: 18px;
            box-shadow: var(--shadow-modal);
            position: relative;
        }

        .modal-body {
            padding: 26px 24px 22px;
            text-align: center;
        }

        .modal-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--success-bg);
            color: var(--success-text);
            font-size: 28px;
            font-weight: bold;
        }

        .modal-title {
            margin: 0 0 8px;
            color: var(--text-strong);
            font-size: 22px;
        }

        .modal-message {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .modal-button {
            min-width: 110px;
        }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
        }

        table {
            width: 100%;
            min-width: 2300px;
            border-collapse: collapse;
            background: var(--surface);
        }

        th,
        td {
            border: 1px solid var(--border-strong);
            padding: 8px;
            font-size: 13px;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: var(--table-head);
            color: var(--text-strong);
        }

        tr:nth-child(even) {
            background: var(--table-alt);
        }

        .note {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 10px;
        }

        @media (max-width: 1100px) {
            .field-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .selector-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            header {
                padding: 14px 16px;
            }

            .header-bar {
                flex-direction: column;
                gap: 14px;
            }

            .header-meta {
                width: 100%;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .company-switch {
                width: 100%;
                justify-content: space-between;
            }

            .header-kicker {
                font-size: 11px;
            }

            .field-grid,
            .field-grid.compact {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            header h1 {
                font-size: 28px;
                line-height: 1.1;
            }

            header p {
                font-size: 12px;
            }

            .header-logo {
                width: 92px;
                max-width: 26vw;
                padding: 7px 8px;
                border-radius: 12px;
            }

            main {
                padding: 14px;
            }

            .card {
                padding: 16px;
                border-radius: 10px;
            }

            h2 {
                font-size: 16px;
            }

            form {
                gap: 14px;
            }

            .form-section {
                padding: 14px;
                border-radius: 12px;
            }

            label,
            .note,
            .modal-message {
                font-size: 12px;
            }

            .modal-overlay {
                padding: 16px;
            }

            .modal-window {
                border-radius: 14px;
            }

            .modal-body {
                padding: 22px 18px 18px;
            }

            .modal-icon {
                width: 50px;
                height: 50px;
                margin-bottom: 12px;
                font-size: 24px;
            }

            .modal-title {
                font-size: 18px;
            }

            .form-section.compact-section {
                width: 100%;
            }

            .date-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .date-field {
                width: 100%;
            }

            .option-group {
                padding: 7px;
            }

            .selector-field-tall .option-group {
                min-height: 0;
            }

            .option-button {
                flex: 1 1 auto;
            }

            .option-button span {
                width: 100%;
                min-height: 38px;
                padding: 8px 12px 10px;
                font-size: 13px;
            }

            input,
            select,
            textarea,
            button,
            .button-link,
            .switch-link,
            .theme-toggle {
                font-size: 13px;
            }

            input,
            select,
            textarea {
                padding: 8px 9px;
            }

            button,
            .button-link,
            .switch-link,
            .theme-toggle {
                padding: 9px 14px;
            }

            th,
            td {
                padding: 7px;
                font-size: 12px;
            }
        }

        @media (max-width: 520px) {
            header h1 {
                font-size: 24px;
            }

            .header-logo {
                width: 80px;
            }

            .card {
                padding: 14px;
            }

            .form-section {
                padding: 12px;
            }

            .option-button span {
                min-height: 36px;
                padding: 7px 10px 9px;
            }
        }
    </style>
</head>
<body class="company-<?= htmlspecialchars($company["key"], ENT_QUOTES, "UTF-8") ?>">

<header>
    <div class="header-bar">
        <div class="header-copy">
            <div class="header-kicker"><?= htmlspecialchars($company["company_name"], ENT_QUOTES, "UTF-8") ?></div>
            <h1><?= htmlspecialchars($company["system_name"], ENT_QUOTES, "UTF-8") ?></h1>
            <p>Records are saved to the <?= htmlspecialchars($company["table_name"], ENT_QUOTES, "UTF-8") ?> table and can be viewed by all computers on the same network.</p>
        </div>

        <div class="header-meta">
            <div class="header-actions">
                <div class="company-switch" aria-label="Switch company">
                    <a href="<?= htmlspecialchars($mitsubishiUrl, ENT_QUOTES, "UTF-8") ?>" class="switch-link<?= $company["key"] === "mitsubishi" ? " active" : "" ?>">Mitsubishi</a>
                    <a href="<?= htmlspecialchars($hyundaiUrl, ENT_QUOTES, "UTF-8") ?>" class="switch-link<?= $company["key"] === "hyundai" ? " active" : "" ?>">Hyundai</a>
                </div>

                <button type="button" class="theme-toggle" id="theme-toggle" aria-pressed="false">Dark Mode</button>
            </div>

            
        </div>
    </div>
</header>

<main>
    <section class="card">
        <h2>Encode New Record</h2>

        <form action="save.php" method="POST">
            <input type="hidden" name="company" value="<?= htmlspecialchars($company["key"], ENT_QUOTES, "UTF-8") ?>">

            <section class="form-section compact-section">
                <div class="section-header">
                </div>

                <div class="field-grid compact date-grid">
                    <div class="field date-field">
                        <label>Date</label>
                        <input type="date" name="date_recorded" value="<?= htmlspecialchars($today, ENT_QUOTES, "UTF-8") ?>" required>
                    </div>

                    <div class="field date-field">
                        <label>Transaction Date</label>
                        <input type="date" name="transaction_date" required>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-header">
                </div>

                <div class="selector-grid triple">
                    <div class="selector-field selector-field-medium">
                        <label>Branch</label>
                        <?php renderOptionButtons("branch", $branchOptions); ?>
                    </div>

                    <div class="selector-field selector-field-medium">
                        <label>Department</label>
                        <?php renderOptionButtons("department", $departmentOptions); ?>
                    </div>

                    <div class="selector-field selector-field-medium">
                        <label>Module</label>
                        <?php renderOptionButtons("module", $moduleOptions); ?>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-header">
                </div>

                <div class="field-grid">
                    <div class="field field-span-2">
                        <label>User</label>
                        <input type="text" name="user_name">
                    </div>

                    <div class="field field-span-2">
                        <label>Client Name</label>
                        <input type="text" name="client_name">
                    </div>

                    <div class="field">
                        <label>Transaction Reference</label>
                        <input type="text" name="invoice_reference">
                    </div>

                    <div class="field">
                        <label>Payment Reference</label>
                        <input type="text" name="payment_reference">
                    </div>

                    <div class="field">
                        <label>Amount</label>
                        <input type="number" name="amount" step="0.01">
                    </div>

                    <div class="field">
                        <label>Ticket</label>
                        <input type="text" name="ticket">
                    </div>

                    <div class="field field-span-2">
                        <label>Reason</label>
                        <input type="text" name="reason">
                    </div>

                    <div class="field field-span-2">
                        <label>Remarks</label>
                        <input type="text" name="remarks">
                    </div>

                    <div class="field">
                        <label>Approved By</label>
                        <input type="text" name="approved_by">
                    </div>

                    <div class="field">
                        <label>Processed By</label>
                        <input type="text" name="processed_by" placeholder="e.g., ITA">
                    </div>

                    <div class="field">
                        <label>System Admin</label>
                        <input type="text" name="system_admin">
                    </div>

                    <div class="field">
                        <label>Offense</label>
                        <input type="text" name="offense">
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-header"></div>

                <div class="selector-grid triple">
                    <div class="selector-field selector-field-medium">
                        <label>Classification</label>
                        <?php renderOptionButtons("classification", $classificationOptions); ?>
                    </div>

                    <div class="selector-field selector-field-medium">
                        <label>Processed Type</label>
                        <?php renderOptionButtons("processed_type", $processedTypeOptions, true); ?>
                    </div>

                    <div class="selector-field selector-field-medium">
                        <label>Status</label>
                        <?php renderOptionButtons("status", $statusOptions, true); ?>
                    </div>
                </div>
            </section>

            <div class="buttons">
                <button type="submit" class="primary">Enter / Save to Database</button>
                <button type="reset" class="secondary">Clear Form</button>
                <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, "UTF-8") ?>" class="button-link secondary">Export to Excel</a>
            </div>
        </form>

        <p class="note">Tip: Leave this page open to monitor <?= htmlspecialchars($company["company_name"], ENT_QUOTES, "UTF-8") ?> records. Refresh the page to see new entries from other computers.</p>
    </section>

    <section class="card">
        <h2><?= htmlspecialchars($company["system_name"], ENT_QUOTES, "UTF-8") ?> Summary</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction Date</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Module</th>
                        <th>User</th>
                        <th>Invoice Reference</th>
                        <th>Payment Reference</th>
                        <th>Client Name</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Approved By</th>
                        <th>Processed Type</th>
                        <th>Processed By</th>
                        <th>Remarks</th>
                        <th>Classification</th>
                        <th>System Admin</th>
                        <th>Ticket</th>
                        <th>Status</th>
                        <th>Offense</th>
                        <th>Encoded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars(date("n/j/Y", strtotime($row["date_recorded"]))) ?></td>
                            <td><?= htmlspecialchars(date("n/j/Y", strtotime($row["transaction_date"]))) ?></td>
                            <td><?= htmlspecialchars($row["branch"]) ?></td>
                            <td><?= htmlspecialchars($row["department"]) ?></td>
                            <td><?= htmlspecialchars($row["module"]) ?></td>
                            <td><?= htmlspecialchars($row["user_name"]) ?></td>
                            <td><?= htmlspecialchars($row["invoice_reference"]) ?></td>
                            <td><?= htmlspecialchars($row["payment_reference"]) ?></td>
                            <td><?= htmlspecialchars($row["client_name"]) ?></td>
                            <td><?= htmlspecialchars($row["amount"]) ?></td>
                            <td><?= htmlspecialchars($row["reason"]) ?></td>
                            <td><?= htmlspecialchars($row["approved_by"]) ?></td>
                            <td><?= htmlspecialchars($row["processed_type"]) ?></td>
                            <td><?= htmlspecialchars($row["processed_by"]) ?></td>
                            <td><?= htmlspecialchars($row["remarks"]) ?></td>
                            <td><?= htmlspecialchars($row["classification"]) ?></td>
                            <td><?= htmlspecialchars($row["system_admin"]) ?></td>
                            <td><?= htmlspecialchars($row["ticket"]) ?></td>
                            <td><?= htmlspecialchars($row["status"]) ?></td>
                            <td><?= htmlspecialchars($row["offense"]) ?></td>
                            <td><?= htmlspecialchars($row["created_at"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    (function () {
        var uppercaseFields = document.querySelectorAll('input[type="text"], textarea');
        var root = document.documentElement;
        var themeToggle = document.getElementById('theme-toggle');

        for (var index = 0; index < uppercaseFields.length; index++) {
            uppercaseFields[index].addEventListener('input', function () {
                this.value = this.value.toUpperCase();
            });

            if (uppercaseFields[index].value) {
                uppercaseFields[index].value = uppercaseFields[index].value.toUpperCase();
            }
        }

        if (!themeToggle) {
            return;
        }

        var updateThemeToggle = function () {
            var isDark = root.classList.contains('dark-theme');
            themeToggle.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        };

        themeToggle.addEventListener('click', function () {
            root.classList.toggle('dark-theme');

            try {
                window.localStorage.setItem('systemMonitoringTheme', root.classList.contains('dark-theme') ? 'dark' : 'light');
            } catch (error) {
            }

            updateThemeToggle();
        });

        updateThemeToggle();
    }());
</script>

<?php if (isset($_GET["saved"])): ?>
    <div class="modal-overlay" id="saved-modal" role="presentation">
        <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="saved-modal-title" aria-describedby="saved-modal-message">
            <div class="modal-body">
                <div class="modal-icon" aria-hidden="true">&#10003;</div>
                <h3 class="modal-title" id="saved-modal-title">Record Saved</h3>
                <p class="modal-message" id="saved-modal-message"><?= htmlspecialchars($savedMessage, ENT_QUOTES, "UTF-8") ?></p>
                <div class="modal-actions">
                    <button type="button" class="primary modal-button" id="saved-modal-ok">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('saved-modal');
            var okButton = document.getElementById('saved-modal-ok');

            if (!modal || !okButton) {
                return;
            }

            document.body.classList.add('modal-open');

            var closeModal = function () {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');

                if (window.history && typeof window.history.replaceState === 'function' && typeof URL === 'function') {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('saved');
                    var query = url.searchParams.toString();
                    var nextUrl = url.pathname + (query ? '?' + query : '') + url.hash;
                    window.history.replaceState({}, document.title, nextUrl);
                }
            };

            okButton.addEventListener('click', function (event) {
                event.preventDefault();
                closeModal();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.style.display !== 'none') {
                    closeModal();
                }
            });

            okButton.focus();
        }());
    </script>
<?php endif; ?>

</body>
</html>
