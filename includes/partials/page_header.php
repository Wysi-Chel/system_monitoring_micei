<header>
    <div class="header-bar">
        <div class="header-copy">
            <div class="header-kicker"><?= e($company["company_name"]) ?></div>
            <h1><?= e($company["system_name"]) ?></h1>
            <p>Records are saved to the <?= e($company["table_name"]) ?> table and can be viewed by all computers on the same network.</p>
        </div>

        <div class="header-meta">
            <div class="header-actions">
                <div class="company-switch" aria-label="Switch company">
                    <a href="<?= e($mitsubishiUrl) ?>" class="switch-link<?= $company["key"] === "mitsubishi" ? " active" : "" ?>">Mitsubishi</a>
                    <a href="<?= e($hyundaiUrl) ?>" class="switch-link<?= $company["key"] === "hyundai" ? " active" : "" ?>">Hyundai</a>
                </div>

                <button type="button" class="theme-toggle" id="theme-toggle" aria-pressed="false">Dark Mode</button>
            </div>
        </div>
    </div>
</header>
