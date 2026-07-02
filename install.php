<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/schema.php';
require_once __DIR__ . '/includes/layout.php';

$createdPassword = null;
$createdUsername = 'admin';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (is_installed()) {
        $error = 'PolicyMaker has already been installed.';
    } else {
        try {
            create_schema();
            $createdPassword = random_password();
            $now = now_sql();

            $stmt = db()->prepare('INSERT INTO admins (username, password_hash, display_name, organization, email, website, created_at, updated_at) VALUES (:username, :password_hash, :display_name, :organization, :email, :website, :created_at, :updated_at)');
            $stmt->execute([
                ':username' => $createdUsername,
                ':password_hash' => password_hash($createdPassword, PASSWORD_DEFAULT),
                ':display_name' => trim((string) ($_POST['display_name'] ?? 'Administrator')) ?: 'Administrator',
                ':organization' => trim((string) ($_POST['organization'] ?? '')),
                ':email' => trim((string) ($_POST['email'] ?? '')),
                ':website' => trim((string) ($_POST['website'] ?? '')),
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

render_header('Install');
?>
<div class="card auth-card">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="brand-mark text-white"><i class="fa-solid fa-shield-halved"></i></span>
            <div>
                <h1 class="h3 mb-1">Install <?= h(APP_TITLE) ?></h1>
                <p class="text-secondary mb-0">Create the SQLite database and the first administrator account.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if ($createdPassword): ?>
            <div class="alert alert-success">
                Installation completed. Save the generated administrator password now; it will not be shown again.
            </div>
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label">Username</label>
                    <input class="form-control" value="<?= h($createdUsername) ?>" readonly>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Generated password</label>
                    <input class="form-control fw-bold" value="<?= h($createdPassword) ?>" readonly>
                </div>
            </div>
            <a href="login.php" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Continue to Login</a>
        <?php elseif (is_installed()): ?>
            <div class="alert alert-info">PolicyMaker is already installed.</div>
            <a href="login.php" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Go to Login</a>
        <?php else: ?>
            <form method="post" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-md-6">
                    <label class="form-label" for="display_name">Administrator name</label>
                    <input class="form-control" id="display_name" name="display_name" value="Administrator" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Contact email</label>
                    <input class="form-control" id="email" name="email" type="email" placeholder="admin@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="organization">Organization</label>
                    <input class="form-control" id="organization" name="organization" placeholder="Your organization">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="website">Organization website</label>
                    <input class="form-control" id="website" name="website" type="url" placeholder="https://example.com">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-database"></i> Install and Generate Password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php render_footer(); ?>
