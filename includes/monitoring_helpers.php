<?php
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function renderOptionButtons(string $name, array $options, bool $allowMultiple = false): void
{
    $groupRole = $allowMultiple ? "group" : "radiogroup";
    $inputType = $allowMultiple ? "checkbox" : "radio";
    $inputName = $allowMultiple ? $name . "[]" : $name;

    echo '<div class="option-group" role="' . $groupRole . '" aria-label="' . e($name) . '">';

    foreach ($options as $option) {
        $id = $name . "_" . preg_replace('/[^a-z0-9]+/i', "_", strtolower($option));
        $safeId = e($id);
        $safeName = e($inputName);
        $safeOption = e($option);

        echo '<label class="option-button" for="' . $safeId . '">';
        echo '<input type="' . $inputType . '" id="' . $safeId . '" name="' . $safeName . '" value="' . $safeOption . '">';
        echo '<span>' . $safeOption . '</span>';
        echo '</label>';
    }

    echo "</div>";
}

function buildUrl(string $script, array $currentParams = [], array $changes = []): string
{
    $params = $currentParams;

    foreach ($changes as $key => $value) {
        if ($value === null || $value === "") {
            unset($params[$key]);
            continue;
        }

        $params[$key] = $value;
    }

    $query = http_build_query($params);
    return $script . ($query !== "" ? "?" . $query : "");
}

function buildMonitoringListQueryParams(string $companyKey, array $filters, bool $includePaging = true): array
{
    $params = [
        "company" => $companyKey,
    ];

    $fieldMap = [
        "search" => "q",
        "date_from" => "date_from",
        "date_to" => "date_to",
        "branch" => "branch",
        "department" => "department",
        "module" => "module",
        "status" => "status",
    ];

    foreach ($fieldMap as $filterKey => $queryKey) {
        $value = $filters[$filterKey] ?? "";
        if ($value !== "") {
            $params[$queryKey] = $value;
        }
    }

    if (($filters["per_page"] ?? 25) !== 25) {
        $params["per_page"] = $filters["per_page"];
    }

    if ($includePaging && ($filters["page"] ?? 1) > 1) {
        $params["page"] = $filters["page"];
    }

    return $params;
}

function buildPaginationState(int $page, int $perPage, int $totalItems): array
{
    $safePerPage = max(1, $perPage);
    $totalPages = max(1, (int) ceil($totalItems / $safePerPage));
    $currentPage = min(max(1, $page), $totalPages);
    $offset = ($currentPage - 1) * $safePerPage;

    return [
        "page" => $currentPage,
        "per_page" => $safePerPage,
        "total_items" => $totalItems,
        "total_pages" => $totalPages,
        "offset" => $offset,
        "limit" => $safePerPage,
        "start_item" => $totalItems === 0 ? 0 : ($offset + 1),
        "end_item" => $totalItems === 0 ? 0 : min($offset + $safePerPage, $totalItems),
        "has_previous" => $currentPage > 1,
        "has_next" => $currentPage < $totalPages,
    ];
}

function buildPaginationPages(int $currentPage, int $totalPages, int $radius = 2): array
{
    $startPage = max(1, $currentPage - $radius);
    $endPage = min($totalPages, $currentPage + $radius);

    return range($startPage, $endPage);
}

function formatDisplayDate(?string $value): string
{
    if (!$value) {
        return "";
    }

    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date("n/j/Y", $timestamp);
}

function formatDisplayTimestamp(?string $value): string
{
    if (!$value) {
        return "";
    }

    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date("n/j/Y g:i A", $timestamp);
}

function formatSummaryValue(array $column, array $row): string
{
    $value = $row[$column["key"]] ?? "";

    switch ($column["format"] ?? "text") {
        case "date":
            return formatDisplayDate($value === "" ? null : (string) $value);

        case "timestamp":
            return formatDisplayTimestamp($value === "" ? null : (string) $value);

        case "amount":
            if ($value === null || $value === "") {
                return "";
            }

            return is_numeric((string) $value) ? number_format((float) $value, 2) : (string) $value;

        default:
            return (string) $value;
    }
}

function getSummaryHeaders(array $summaryColumns): array
{
    return array_map(
        static fn(array $column): string => $column["label"],
        $summaryColumns
    );
}

function buildActiveFilterBadges(array $filters, ?string $fixedBranch = null): array
{
    $badges = [];

    if ($fixedBranch !== null) {
        $badges[] = "Branch: " . $fixedBranch;
    } elseif ($filters["branch"] !== "") {
        $badges[] = "Branch: " . $filters["branch"];
    }

    if ($filters["status"] !== "") {
        $badges[] = "Status: " . $filters["status"];
    }

    return $badges;
}
