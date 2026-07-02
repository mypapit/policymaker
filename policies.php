<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $policyId = (int) ($_POST['policy_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($policyId > 0) {
        if ($action === 'activate') {
            $stmt = db()->prepare("UPDATE policies SET status = 'active', activated_at = :activated_at, updated_at = :updated_at WHERE id = :id");
            $stmt->execute([':activated_at' => now_sql(), ':updated_at' => now_sql(), ':id' => $policyId]);
            flash('success', 'Policy #' . $policyId . ' is now active.');
        } elseif ($action === 'deactivate') {
            $stmt = db()->prepare("UPDATE policies SET status = 'inactive', updated_at = :updated_at WHERE id = :id");
            $stmt->execute([':updated_at' => now_sql(), ':id' => $policyId]);
            flash('warning', 'Policy #' . $policyId . ' was deactivated.');
        } elseif ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM policies WHERE id = :id');
            $stmt->execute([':id' => $policyId]);
            flash('success', 'Policy #' . $policyId . ' was deleted.');
        }
    }

    redirect('policies.php');
}

$policies = db()->query('SELECT p.*, a.display_name AS creator_name FROM policies p LEFT JOIN admins a ON a.id = p.created_by ORDER BY p.updated_at DESC, p.id DESC')->fetchAll();

render_header('Privacy Policies', 'policies');
?>
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div>
            <h2 class="h5 mb-0">Generated Policies</h2>
            <p class="text-secondary mb-0 small">Manage active, inactive, draft, editable, and public policy records.</p>
        </div>
        <a class="btn btn-primary" href="wizard.php"><i class="fa-solid fa-plus"></i> New Policy</a>
    </div>
    <div class="card-body">
        <?php if (!$policies): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-file-circle-plus display-5 text-secondary"></i>
                <p class="mt-3 mb-3 text-secondary">No generated privacy policies yet.</p>
                <a href="wizard.php" class="btn btn-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Start Wizard</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Policy ID</th>
                            <th>App Name</th>
                            <th>Status</th>
                            <th>Style</th>
                            <th>Public URL</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($policies as $policy): ?>
                            <tr>
                                <td class="fw-semibold">#<?= h($policy['id']) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= h($policy['app_name']) ?></div>
                                    <div class="small text-secondary"><?= h($policy['package_name'] ?: 'Android') ?></div>
                                </td>
                                <td><?= status_badge($policy['status']) ?></td>
                                <td><?= h(POLICY_STYLES[$policy['style']] ?? $policy['style']) ?></td>
                                <td>
                                    <?php if ($policy['status'] === 'active'): ?>
                                        <a href="view.php?policy_id=<?= h($policy['id']) ?>" target="_blank">view.php?policy_id=<?= h($policy['id']) ?></a>
                                    <?php else: ?>
                                        <span class="text-secondary">Activate to publish</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($policy['updated_at']) ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Policy actions">
                                        <a class="btn btn-outline-primary" href="policy_edit.php?id=<?= h($policy['id']) ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <?php if ($policy['status'] === 'active'): ?>
                                            <form method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="policy_id" value="<?= h($policy['id']) ?>">
                                                <button class="btn btn-outline-warning" name="action" value="deactivate" type="submit"><i class="fa-solid fa-ban"></i> Deactivate</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="policy_id" value="<?= h($policy['id']) ?>">
                                                <button class="btn btn-outline-success" name="action" value="activate" type="submit"><i class="fa-solid fa-circle-check"></i> Activate</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this privacy policy permanently?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="policy_id" value="<?= h($policy['id']) ?>">
                                            <button class="btn btn-outline-danger" name="action" value="delete" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php render_footer(); ?>
