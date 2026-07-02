<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/layout.php';

ensure_installed();

if (current_admin()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = :username AND is_active = 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $update = db()->prepare('UPDATE admins SET last_login_at = :last_login_at WHERE id = :id');
        $update->execute([
            ':last_login_at' => now_sql(),
            ':id' => (int) $admin['id'],
        ]);
        redirect('index.php');
    }

    $error = 'Invalid administrator username or password.';
}

render_header('Administrator Login');
?>
<div class="card auth-card">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="brand-mark text-white"><i class="fa-solid fa-lock"></i></span>
            <div>
                <h1 class="h3 mb-1">Administrator Login</h1>
                <p class="text-secondary mb-0">Protected access for policy generation and management.</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="vstack gap-3">
            <?= csrf_field() ?>
            <div>
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username" autocomplete="username" required autofocus>
            </div>
            <div>
                <label class="form-label" for="password">Password</label>
                <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </form>
    </div>
</div>
<?php render_footer(); ?>
