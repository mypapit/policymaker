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

$policyId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($policyId <= 0) {
    http_response_code(404);
    exit('Policy not found.');
}

$stmt = db()->prepare('SELECT * FROM policies WHERE id = :id');
$stmt->execute([':id' => $policyId]);
$policy = $stmt->fetch();

if (!$policy) {
    http_response_code(404);
    exit('Policy not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $style = trim((string) ($_POST['style'] ?? 'clean'));
    if (!array_key_exists($style, POLICY_STYLES)) {
        $style = 'clean';
    }

    $status = trim((string) ($_POST['status'] ?? 'draft'));
    if (!in_array($status, ['draft', 'active', 'inactive'], true)) {
        $status = 'draft';
    }

    $now = now_sql();
    $activatedAt = $policy['activated_at'];
    if ($status === 'active' && empty($activatedAt)) {
        $activatedAt = $now;
    }

    $update = db()->prepare('UPDATE policies SET app_name = :app_name, app_website = :app_website, package_name = :package_name, developer_name = :developer_name, organization = :organization, contact_email = :contact_email, organization_website = :organization_website, style = :style, status = :status, content = :content, updated_at = :updated_at, activated_at = :activated_at WHERE id = :id');
    $update->execute([
        ':app_name' => trim((string) ($_POST['app_name'] ?? $policy['app_name'])),
        ':app_website' => trim((string) ($_POST['app_website'] ?? '')),
        ':package_name' => trim((string) ($_POST['package_name'] ?? '')),
        ':developer_name' => trim((string) ($_POST['developer_name'] ?? '')),
        ':organization' => trim((string) ($_POST['organization'] ?? '')),
        ':contact_email' => trim((string) ($_POST['contact_email'] ?? '')),
        ':organization_website' => trim((string) ($_POST['organization_website'] ?? '')),
        ':style' => $style,
        ':status' => $status,
        ':content' => (string) ($_POST['content'] ?? ''),
        ':updated_at' => $now,
        ':activated_at' => $activatedAt,
        ':id' => $policyId,
    ]);

    flash('success', 'Policy #' . $policyId . ' was updated.');
    redirect('policy_edit.php?id=' . $policyId);
}

render_header('Edit Policy #' . $policyId, 'policies');
?>
<form method="post" class="row g-4">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h($policyId) ?>">

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Policy Content</h2>
                <?php if ($policy['status'] === 'active'): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="view.php?policy_id=<?= h($policy['id']) ?>" target="_blank"><i class="fa-solid fa-eye"></i> Public View</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <label class="form-label" for="content">Markdown-style policy text</label>
                <textarea class="form-control font-monospace" id="content" name="content" rows="26" required><?= h($policy['content']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Publication</h2>
            </div>
            <div class="card-body vstack gap-3">
                <div>
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <?php foreach (['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label): ?>
                            <option value="<?= h($key) ?>" <?= $policy['status'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="style">Display style</label>
                    <select class="form-select" id="style" name="style">
                        <?php foreach (POLICY_STYLES as $key => $label): ?>
                            <option value="<?= h($key) ?>" <?= $policy['style'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="p-3 bg-light rounded-2">
                    <div class="small text-secondary">Public URL</div>
                    <?php if ($policy['status'] === 'active'): ?>
                        <a href="view.php?policy_id=<?= h($policy['id']) ?>" target="_blank"><?= h(policy_public_url((int) $policy['id'])) ?></a>
                    <?php else: ?>
                        <span class="text-secondary">Activate this policy to publish it.</span>
                    <?php endif; ?>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Policy</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h2 class="h5 mb-0">Application Metadata</h2>
            </div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label" for="app_name">Application name</label>
                    <input class="form-control" id="app_name" name="app_name" value="<?= h($policy['app_name']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="package_name">Package name</label>
                    <input class="form-control" id="package_name" name="package_name" value="<?= h($policy['package_name']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="app_website">Application website</label>
                    <input class="form-control" id="app_website" name="app_website" type="url" value="<?= h($policy['app_website']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="developer_name">Author name</label>
                    <input class="form-control" id="developer_name" name="developer_name" value="<?= h($policy['developer_name']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="organization">Organization</label>
                    <input class="form-control" id="organization" name="organization" value="<?= h($policy['organization']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="contact_email">Contact email</label>
                    <input class="form-control" id="contact_email" name="contact_email" type="email" value="<?= h($policy['contact_email']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="organization_website">Organization website</label>
                    <input class="form-control" id="organization_website" name="organization_website" type="url" value="<?= h($policy['organization_website']) ?>">
                </div>
            </div>
        </div>
    </div>
</form>
<?php render_footer(); ?>
