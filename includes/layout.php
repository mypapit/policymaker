<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function nav_link(string $href, string $label, string $icon, string $active, string $key): string
{
    $class = $active === $key ? 'active' : '';
    return '<a class="nav-link ' . $class . '" href="' . h($href) . '"><i class="fa-solid ' . h($icon) . '"></i><span>' . h($label) . '</span></a>';
}

function render_header(string $title, string $active = '', bool $public = false): void
{
    $admin = current_admin();
    $bodyClass = $public ? 'public-page' : 'admin-page';
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="<?= h(APP_TITLE) ?>">
    <title><?= h($title) ?> - <?= h(APP_TITLE) ?></title>
    <base href="<?= h(app_base_url()) ?>/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="<?= h($bodyClass) ?>">
<?php if ($admin && !$public): ?>
    <div class="app-shell">
        <aside class="sidebar">
            <a href="index.php" class="brand">
                <span class="brand-mark"><i class="fa-solid fa-shield-halved"></i></span>
                <span><?= h(APP_TITLE) ?></span>
            </a>
            <nav class="sidebar-nav">
                <?= nav_link('index.php', 'Dashboard', 'fa-gauge-high', $active, 'dashboard') ?>
                <?= nav_link('wizard.php', 'Policy Wizard', 'fa-wand-magic-sparkles', $active, 'wizard') ?>
                <?= nav_link('policies.php', 'Policies', 'fa-file-shield', $active, 'policies') ?>
                <?= nav_link('admins.php', 'Administrators', 'fa-user-gear', $active, 'admins') ?>
            </nav>
            <div class="sidebar-footer">
                <div class="small text-secondary">Signed in as</div>
                <strong><?= h($admin['display_name']) ?></strong>
                <a href="logout.php" class="btn btn-sm btn-outline-light mt-3"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </div>
        </aside>
        <main class="main-panel">
            <div class="topbar">
                <div>
                    <div class="eyeline">Android privacy policy manager</div>
                    <h1><?= h($title) ?></h1>
                </div>
                <a class="btn btn-primary" href="wizard.php"><i class="fa-solid fa-plus"></i> New Policy</a>
            </div>
            <?php foreach (pull_flash_messages() as $message): ?>
                <div class="alert alert-<?= h($message['type']) ?> alert-dismissible fade show" role="alert">
                    <?= h($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
<?php else: ?>
    <main class="<?= $public ? 'public-main' : 'auth-main' ?>">
<?php endif; ?>
    <?php
}

function render_footer(bool $public = false): void
{
    $admin = current_admin();
    ?>
        <footer class="<?= ($admin && !$public) ? 'app-footer' : 'public-footer' ?>">
            <span>Attributed to <?= h(APP_ATTRIBUTION) ?></span>
        </footer>
<?php if ($admin && !$public): ?>
        </main>
    </div>
<?php else: ?>
    </main>
<?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
</body>
</html>
    <?php
}
