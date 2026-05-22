<?php
require "config.php";

function shouldPreserveToken(string $token): bool
{
    $core = preg_replace('/^[^\p{L}\p{N}_]+|[^\p{L}\p{N}_#\/\-\(\)]+$/u', '', $token) ?? '';
    if ($core === '') {
        return false;
    }

    if (preg_match('/\d/u', $core)) {
        return true;
    }

    if (preg_match('/[#\/\-\(\)]/u', $core)) {
        return true;
    }

    $lettersOnly = preg_replace('/[^\p{L}]/u', '', $core) ?? '';
    if ($lettersOnly === '') {
        return false;
    }

    $length = mb_strlen($lettersOnly, 'UTF-8');
    return $length >= 2
        && $length <= 6
        && mb_strtoupper($lettersOnly, 'UTF-8') === $lettersOnly;
}

function capitalizeFirstLetter(string $text): string
{
    return preg_replace_callback(
        '/\p{L}/u',
        static fn(array $matches): string => mb_strtoupper($matches[0], 'UTF-8'),
        $text,
        1
    ) ?? $text;
}

function sentenceCaseInput(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    $parts = preg_split('/([.!?]+\s*)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return $value;
    }

    $normalizedParts = [];

    foreach ($parts as $index => $part) {
        if ($index % 2 === 1) {
            $normalizedParts[] = $part;
            continue;
        }

        $tokens = preg_split('/(\s+)/u', $part, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($tokens === false) {
            $normalizedParts[] = capitalizeFirstLetter(mb_strtolower($part, 'UTF-8'));
            continue;
        }

        $normalizedTokens = [];

        foreach ($tokens as $token) {
            if ($token === '' || preg_match('/^\s+$/u', $token)) {
                $normalizedTokens[] = $token;
            } elseif (shouldPreserveToken($token)) {
                $normalizedTokens[] = $token;
            } else {
                $normalizedTokens[] = mb_strtolower($token, 'UTF-8');
            }
        }

        $normalizedParts[] = capitalizeFirstLetter(implode('', $normalizedTokens));
    }

    return implode('', $normalizedParts);
}

function normalizeMultiSelectInput($value): string
{
    if (!is_array($value)) {
        return sentenceCaseInput($value === null ? '' : (string) $value);
    }

    $normalizedValues = [];

    foreach ($value as $item) {
        if (!is_scalar($item)) {
            continue;
        }

        $normalizedItem = sentenceCaseInput((string) $item);
        if ($normalizedItem === '' || in_array($normalizedItem, $normalizedValues, true)) {
            continue;
        }

        $normalizedValues[] = $normalizedItem;
    }

    return implode(', ', $normalizedValues);
}

$sql = "INSERT INTO monitoring_records (
    date_recorded,
    transaction_date,
    branch,
    department,
    module,
    user_name,
    invoice_reference,
    payment_reference,
    client_name,
    amount,
    reason,
    approved_by,
    processed_type,
    processed_by,
    remarks,
    classification,
    system_admin,
    ticket,
    status,
    offense
) VALUES (
    :date_recorded,
    :transaction_date,
    :branch,
    :department,
    :module,
    :user_name,
    :invoice_reference,
    :payment_reference,
    :client_name,
    :amount,
    :reason,
    :approved_by,
    :processed_type,
    :processed_by,
    :remarks,
    :classification,
    :system_admin,
    :ticket,
    :status,
    :offense
)";

$stmt = $pdo->prepare($sql);

$amount = $_POST["amount"] ?? null;
if ($amount === "") {
    $amount = null;
}

$textFields = [
    "branch",
    "department",
    "module",
    "user_name",
    "invoice_reference",
    "payment_reference",
    "client_name",
    "reason",
    "approved_by",
    "processed_by",
    "remarks",
    "classification",
    "system_admin",
    "ticket",
    "offense",
];

$normalizedText = [];
foreach ($textFields as $field) {
    $normalizedText[$field] = sentenceCaseInput($_POST[$field] ?? "");
}

$normalizedText["processed_type"] = normalizeMultiSelectInput($_POST["processed_type"] ?? []);
$normalizedText["status"] = normalizeMultiSelectInput($_POST["status"] ?? []);

$stmt->execute([
    ":date_recorded" => $_POST["date_recorded"] ?? null,
    ":transaction_date" => $_POST["transaction_date"] ?? null,
    ":branch" => $normalizedText["branch"],
    ":department" => $normalizedText["department"],
    ":module" => $normalizedText["module"],
    ":user_name" => $normalizedText["user_name"],
    ":invoice_reference" => $normalizedText["invoice_reference"],
    ":payment_reference" => $normalizedText["payment_reference"],
    ":client_name" => $normalizedText["client_name"],
    ":amount" => $amount,
    ":reason" => $normalizedText["reason"],
    ":approved_by" => $normalizedText["approved_by"],
    ":processed_type" => $normalizedText["processed_type"],
    ":processed_by" => $normalizedText["processed_by"],
    ":remarks" => $normalizedText["remarks"],
    ":classification" => $normalizedText["classification"],
    ":system_admin" => $normalizedText["system_admin"],
    ":ticket" => $normalizedText["ticket"],
    ":status" => $normalizedText["status"],
    ":offense" => $normalizedText["offense"]
]);

header("Location: index.php?saved=1");
exit;
?>
