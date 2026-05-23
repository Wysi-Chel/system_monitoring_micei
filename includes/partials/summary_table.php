<?php $paginationPages = buildPaginationPages($pagination["page"], $pagination["total_pages"]); ?>
<?php $summaryAnchor = "#summary-section"; ?>
<section class="card" id="summary-section">
    <div class="summary-header">
        <div>
            <h2><?= e($company["system_name"]) ?> Summary</h2>
            <p class="note summary-note">Filter the summary by branch and status, then page through the matching records without leaving this section.</p>
        </div>
        <a href="<?= e($exportUrl) ?>" class="button-link secondary">Export Filtered Excel</a>
    </div>

    <form action="index.php#summary-section" method="GET" class="summary-filter-form">
        <input type="hidden" name="company" value="<?= e($company["key"]) ?>">

        <div class="summary-filter-grid">
            <?php if ($showBranchSelector): ?>
            <div class="field">
                <label for="filter-branch">Branch</label>
                <select id="filter-branch" name="branch">a
                    <option value="">All branches</option>
                    <?php foreach ($branchOptions as $option): ?>
                    <option value="<?= e($option) ?>"<?= $filters["branch"] === $option ? " selected" : "" ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="field">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($statusOptions as $option): ?>
                    <option value="<?= e($option) ?>"<?= $filters["status"] === $option ? " selected" : "" ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="summary-toolbar">
            <div class="summary-actions">
                <button type="submit" class="primary">Apply Filters</button>
                <a href="<?= e($clearFiltersUrl . $summaryAnchor) ?>" class="button-link secondary">Clear Filters</a>
            </div>

            <div class="results-meta">
                <strong><?= e($pagination["start_item"]) ?>-<?= e($pagination["end_item"]) ?></strong> of <strong><?= e($totalRecords) ?></strong> records
            </div>
        </div>
        <br>    
    </form>

    <?php if ($activeFilterBadges !== []): ?>
    <div class="active-filters" aria-label="Active filters">
        <?php foreach ($activeFilterBadges as $badge): ?>
        <span class="filter-badge"><?= e($badge) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <?php foreach ($summaryColumns as $column): ?>
                    <th><?= e($column["label"]) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($records === []): ?>
                    <tr>
                        <td colspan="<?= e(count($summaryColumns)) ?>" class="empty-table">No records matched the current filters.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <?php foreach ($summaryColumns as $column): ?>
                            <td><?= e(formatSummaryValue($column, $row)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination["total_pages"] > 1): ?>
    <nav class="pagination" aria-label="Summary pages">
        <?php if ($pagination["has_previous"]): ?>
        <a href="<?= e(buildUrl("index.php", $listQueryParams, ["page" => $pagination["page"] - 1]) . $summaryAnchor) ?>" class="button-link secondary">Previous</a>
        <?php else: ?>
        <span class="button-link secondary disabled" aria-disabled="true">Previous</span>
        <?php endif; ?>

        <div class="page-numbers">
            <?php foreach ($paginationPages as $pageNumber): ?>
            <a
                href="<?= e(buildUrl("index.php", $listQueryParams, ["page" => $pageNumber]) . $summaryAnchor) ?>"
                class="page-number<?= $pageNumber === $pagination["page"] ? " active" : "" ?>"
                <?= $pageNumber === $pagination["page"] ? 'aria-current="page"' : "" ?>
            ><?= e($pageNumber) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($pagination["has_next"]): ?>
        <a href="<?= e(buildUrl("index.php", $listQueryParams, ["page" => $pagination["page"] + 1]) . $summaryAnchor) ?>" class="button-link secondary">Next</a>
        <?php else: ?>
        <span class="button-link secondary disabled" aria-disabled="true">Next</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</section>
