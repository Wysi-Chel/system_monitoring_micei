<?php
$branchOptions = ["GSC", "KID", "GLA"];
$departmentOptions = ["Accounting", "Sales", "Service", "Parts", "BNC", "CNC", "Manila", "BRP"];
$moduleOptions = ["AMIS", "CMIS", "CSMS", "SMIS", "PMIS"];
$classificationOptions = ["User Error", "System Error", "Data Correction", "Others"];
$processedTypeOptions = ["Cancellation", "Unposting", "Void", "Others"];
$statusOptions = ["Pending", "Cancelled", "Unposted", "Voided"];
$rowsPerPageOptions = [25, 50, 100];

$summaryColumns = [
    ["key" => "date_recorded", "label" => "Date", "format" => "date"],
    ["key" => "transaction_date", "label" => "Transaction Date", "format" => "date"],
    ["key" => "branch", "label" => "Branch", "format" => "text"],
    ["key" => "department", "label" => "Department", "format" => "text"],
    ["key" => "module", "label" => "Module", "format" => "text"],
    ["key" => "user_name", "label" => "User", "format" => "text"],
    ["key" => "invoice_reference", "label" => "Invoice Reference", "format" => "text"],
    ["key" => "payment_reference", "label" => "Payment Reference", "format" => "text"],
    ["key" => "client_name", "label" => "Client Name", "format" => "text"],
    ["key" => "amount", "label" => "Amount", "format" => "amount"],
    ["key" => "reason", "label" => "Reason", "format" => "text"],
    ["key" => "approved_by", "label" => "Approved By", "format" => "text"],
    ["key" => "processed_type", "label" => "Processed Type", "format" => "text"],
    ["key" => "processed_by", "label" => "Processed By", "format" => "text"],
    ["key" => "remarks", "label" => "Remarks", "format" => "text"],
    ["key" => "classification", "label" => "Classification", "format" => "text"],
    ["key" => "system_admin", "label" => "System Admin", "format" => "text"],
    ["key" => "ticket", "label" => "Ticket", "format" => "text"],
    ["key" => "status", "label" => "Status", "format" => "text"],
    ["key" => "offense", "label" => "Offense", "format" => "text"],
    ["key" => "created_at", "label" => "Encoded At", "format" => "timestamp"],
];
