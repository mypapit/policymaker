<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$admin = require_admin();
$generatedPassword = null;
$generatedUsername = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $now = now_sql();

    if ($action === 'update_profile') {
        $stmt = db()->prepare('UPDATE admins SET display_name = :display_name, organization = :organization, email = :email, website = :website, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            ':display_name' => trim((string) ($_POST['display_name'] ?? $admin['display_name'])) ?: $admin['display_name'],
            ':organization' => trim((string) ($_POST['organization'] ?? '')),
            ':email' => trim((string) ($_POST['email'] ?? '')),
            ':website' => trim((string) ($_POST['website'] ?? '')),
            ':updated_at' => $now,
            ':id' => (int) $admin['id'],
        ]);
        flash('success', 'Your administrator profile was updated.');
        redirect('admins.php');
    }

    if ($action === 'add_admin') {
        $generatedUsername = trim((string) ($_POST['username'] ?? ''));
        if ($generatedUsername === '') {
            flash('danger', 'Username is required.');
            redirect('admins.php');
        }

        $generatedPassword = random_password();
        try {
            $stmt = db()->prepare('INSERT INTO admins (username, password_hash, display_name, organization, email, website, created_at, updated_at) VALUES (:username, :password_hash, :display_name, :organization, :email, :website, :created_at, :updated_at)');
            $stmt->execute([
                ':username' => $generatedUsername,
                ':password_hash' => password_hash($generatedPassword, PASSWORD_DEFAULT),
                ':display_name' => trim((string) ($_POST['display_name'] ?? $generatedUsername)) ?: $generatedUsername,
                ':organization' => trim((string) ($_POST['organization'] ?? '')),
                ':email' => trim((string) ($_POST['email'] ?? '')),
                ':website' => trim((string) ($_POST['website'] ?? '')),
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        } catch (Throwable $e) {
            flash('danger', 'Unable to add administrator. The username may already exist.');
            redirect('admins.php');
        }
    }

    if ($action === 'change_password') {
        $targetId = (int) ($_POST['admin_id'] ?? 0);
        $password = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($targetId <= 0 || $password === '' || $password !== $confirm) {
            flash('danger', 'Password change failed. Confirm the selected administrator and matching password fields.');
            redirect('admins.php');
        }

        $stmt = db()->prepare('UPDATE admins SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id');
        $stmt->execute([
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':updated_at' => $now,
            ':id' => $targetId,
        ]);
        flash('success', 'Administrator password was changed.');
        redirect('admins.php');
    }
}

$admins = db()->query('SELECT id, username, display_name, organization, email, website, is_active, created_at, last_login_at FROM admins ORDER BY display_name')->fetchAll();

render_header('Administrators', 'admins');
?>
<?php if ($generatedPassword && $generatedUsername): ?>
    <div class="alert alert-success">
        New administrator created. Username: <strong><?= h($generatedUsername) ?></strong>. Generated password: <strong><?= h($generatedPassword) ?></strong>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Your Profile</h2>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="col-12">
                        <label class="form-label" for="display_name">Author name</label>
                        <input class="form-control" id="display_name" name="display_name" value="<?= h($admin['display_name']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="organization">Organization name</label>
                        <input class="form-control" id="organization" name="organization" value="<?= h($admin['organization'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="email">Contact email</label>
                        <input class="form-control" id="email" name="email" type="email" value="<?= h($admin['email'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="website">Organization website</label>
                        <input class="form-control" id="website" name="website" type="url" value="<?= h($admin['website'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Profile</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Add Administrator</h2>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_admin">
                    <div class="col-md-6">
                        <label class="form-label" for="new_username">Username</label>
                        <input class="form-control" id="new_username" name="username" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="new_display_name">Author name</label>
                        <input class="form-control" id="new_display_name" name="display_name" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="new_email">Contact email</label>
                        <input class="form-control" id="new_email" name="email" type="email">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="new_organization">Organization</label>
                        <input class="form-control" id="new_organization" name="organization">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="new_website">Organization website</label>
                        <input class="form-control" id="new_website" name="website" type="url">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-user-plus"></i> Add and Generate Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Administrator Accounts</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Profile</th>
                                <th>Contact</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= h($row['username']) ?></td>
                                    <td>
                                        <div><?= h($row['display_name']) ?></div>
                                        <div class="small text-secondary"><?= h($row['organization'] ?: 'No organization') ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($row['email'] ?: 'No email') ?></div>
                                        <div class="small text-secondary"><?= h($row['website'] ?: 'No website') ?></div>
                                    </td>
                                    <td><?= h($row['last_login_at'] ?: 'Never') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Change Password</h2>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="col-12">
                        <label class="form-label" for="admin_id">Administrator</label>
                        <select class="form-select" id="admin_id" name="admin_id" required>
                            <?php foreach ($admins as $row): ?>
                                <option value="<?= h($row['id']) ?>" <?= (int) $row['id'] === (int) $admin['id'] ? 'selected' : '' ?>><?= h($row['username']) ?> - <?= h($row['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="new_password">New password</label>
                        <input class="form-control" id="new_password" name="new_password" type="password" minlength="8" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="confirm_password">Confirm password</label>
                        <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="8" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-key"></i> Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
