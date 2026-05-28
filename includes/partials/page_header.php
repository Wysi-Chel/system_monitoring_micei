<?php
$headerKicker = $headerKicker ?? $company["company_name"];
$headerTitle = $headerTitle ?? $company["system_name"];
$headerDescription = $headerDescription ?? ("");
$showCompanySwitch = $showCompanySwitch ?? true;
$appEnvironmentLabel = getApplicationEnvironmentDisplayLabel();
?>
<header>
    <div class="header-bar">
        <div class="header-copy">
            <div class="header-kicker-row">
                <div class="header-kicker"><?= e($headerKicker) ?></div>
                <?php if (isApplicationTestEnvironment()): ?>
                <div class="environment-badge"><?= e($appEnvironmentLabel) ?></div>
                <?php endif; ?>
            </div>
            <h1><?= e($headerTitle) ?></h1>
            <p><?= e($headerDescription) ?></p>
        </div>

        <div class="header-meta">
            <div class="header-actions">
                <?php if ($showCompanySwitch): ?>
                <div class="company-switch" aria-label="Switch company">
                    <a href="<?= e($mitsubishiUrl) ?>" class="switch-link<?= $company["key"] === "mitsubishi" ? " active" : "" ?>">Mitsubishi</a>
                    <a href="<?= e($hyundaiUrl) ?>" class="switch-link<?= $company["key"] === "hyundai" ? " active" : "" ?>">Hyundai</a>
                </div>
                <?php endif; ?>

                <?php if (canAccessPromoteToLiveUi()): ?>
                <a href="<?= e(buildUrl("promote_to_live.php", ["company" => $company["key"]])) ?>" class="button-link secondary header-promote-link">Promote To Live</a>
                <?php endif; ?>

                <button type="button" class="theme-toggle" id="theme-toggle" aria-pressed="false">Dark Mode</button>
            </div>
        </div>
    </div>
</header>
