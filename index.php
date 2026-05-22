<?php
require "config.php";

$branchOptions = ["GSC", "KID", "GLA"];
$departmentOptions = ["Accounting", "Sales", "Service", "Parts", "BNC", "CNC", "Manila", "BRP"];
$moduleOptions = ["AMIS", "CMIS", "CSMS", "SMIS", "PMIS", "Others"];
$classificationOptions = ["User Error", "System Error", "Data Correction", "Others"];
$processedTypeOptions = ["Cancellation", "Correction", "Adjustment", "Reversal", "Others"];
$statusOptions = ["Pending", "Done", "Cancelled", "Unposted"];
$today = (new DateTimeImmutable("now", new DateTimeZone("Asia/Manila")))->format("Y-m-d");

function renderOptionButtons(string $name, array $options): void
{
    echo '<div class="option-group" role="radiogroup" aria-label="' . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . '">';

    foreach ($options as $option) {
        $id = $name . '_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($option));
        $safeId = htmlspecialchars($id, ENT_QUOTES, "UTF-8");
        $safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        $safeOption = htmlspecialchars($option, ENT_QUOTES, "UTF-8");

        echo '<label class="option-button" for="' . $safeId . '">';
        echo '<input type="radio" id="' . $safeId . '" name="' . $safeName . '" value="' . $safeOption . '">';
        echo '<span>' . $safeOption . '</span>';
        echo '</label>';
    }

    echo '</div>';
}

$stmt = $pdo->query("SELECT * FROM monitoring_records ORDER BY id DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Monitoring Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/images/mitsubishi-logo.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/mitsubishi-logo.png">

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f8;
            color: #222;
        }

        header {
            background: #0f172a;
            color: white;
            padding: 18px 24px;
        }

        .header-bar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .header-copy {
            min-width: 0;
        }

        header h1 {
            margin: 0;
            font-size: 50px;
        }

        header p {
            margin: 6px 0 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .header-logo {
            flex: 0 0 auto;
            width: 100px;
            max-width: 32vw;
            height: auto;
            display: block;
            background: white;
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
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        h2 {
            margin-top: 0;
            color: #0f172a;
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
            color: #334155;
        }

        input, select, textarea {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.14);
        }

        .form-section {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
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
            color: #0f172a;
            font-size: 15px;
        }

        .section-header p {
            margin: 5px 0 0;
            color: #64748b;
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
            border: 1px solid #eef2ff;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(148, 163, 184, 0.18);
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
            color: #1f2937;
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
            background: rgba(124, 58, 237, 0.05);
            color: #6d28d9;
        }

        .option-button input:checked + span {
            background: rgba(124, 58, 237, 0.08);
            color: #7c3aed;
            font-weight: bold;
            transform: translateY(-1px);
        }

        .option-button input:checked + span::before {
            content: "\2713";
            margin-right: 6px;
            color: #16a34a;
            font-size: 13px;
            font-weight: bold;
        }

        .option-button input:checked + span::after {
            background: #7c3aed;
            box-shadow: 0 4px 10px rgba(124, 58, 237, 0.28);
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

        button, .button-link {
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .primary {
            background: #2563eb;
            color: white;
        }

        .secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        table {
            width: 100%;
            min-width: 2300px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 13px;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #e2e8f0;
            color: #0f172a;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .note {
            font-size: 13px;
            color: #64748b;
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

            .field-grid,
            .field-grid.compact {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .header-bar {
                align-items: flex-start;
                gap: 12px;
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
            .success-message {
                font-size: 12px;
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
            .button-link {
                font-size: 13px;
            }

            input,
            select,
            textarea {
                padding: 8px 9px;
            }

            button,
            .button-link {
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
<body>

<header>
    <div class="header-bar">
        <div class="header-copy">
            <h1>System Monitoring Form</h1>
            <p>Records are saved to MySQL and can be viewed by all computers on the same network.</p>
        </div>
        <img
            src="assets/images/mitsubishi-logo.png"
            alt="Mitsubishi Motors Drive your Ambition"
            class="header-logo"
        >
    </div>
</header>

<main>
    <section class="card">
        <h2>Encode New Record</h2>

        <?php if (isset($_GET["saved"])): ?>
            <div class="success-message">Record successfully saved to the database.</div>
        <?php endif; ?>

        <form action="save.php" method="POST">
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

                    <div class="selector-field">
                        <label>Department</label>
                        <?php renderOptionButtons("department", $departmentOptions); ?>
                    </div>

                    <div class="selector-field">
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
                        <label>Invoice Reference</label>
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
                        <input type="text" name="remarks" placeholder="e.g., Cancelled">
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

                    <div class="selector-field">
                        <label>Processed Type</label>
                        <?php renderOptionButtons("processed_type", $processedTypeOptions); ?>
                    </div>

                    <div class="selector-field selector-field-medium">
                        <label>Status</label>
                        <?php renderOptionButtons("status", $statusOptions); ?>
                    </div>
                </div>
            </section>

            <div class="buttons">
                <button type="submit" class="primary">Enter / Save to Database</button>
                <button type="reset" class="secondary">Clear Form</button>
                <a href="export_excel.php" class="button-link secondary">Export to Excel</a>
            </div>
        </form>

        <p class="note">Tip: Leave this page open to monitor records. Refresh the page to see new entries from other computers.</p>
    </section>

    <section class="card">
        <h2>System Monitoring Summary</h2>

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

</body>
</html>
