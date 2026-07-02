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

$totalPolicies = (int) db()->query('SELECT COUNT(*) FROM policies')->fetchColumn();
$activePolicies = (int) db()->query("SELECT COUNT(*) FROM policies WHERE status = 'active'")->fetchColumn();
$draftPolicies = (int) db()->query("SELECT COUNT(*) FROM policies WHERE status = 'draft'")->fetchColumn();
$adminCount = (int) db()->query('SELECT COUNT(*) FROM admins WHERE is_active = 1')->fetchColumn();

$recent = db()->query('SELECT id, app_name, status, updated_at, style FROM policies ORDER BY updated_at DESC, id DESC LIMIT 8')->fetchAll();

render_header('Dashboard', 'dashboard');
?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary fw-semibold">Total Policies</div>
                    <div class="display-6 fw-bold"><?= h($totalPolicies) ?></div>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-file-lines"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary fw-semibold">Active Policies</div>
                    <div class="display-6 fw-bold"><?= h($activePolicies) ?></div>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-circle-check"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary fw-semibold">Draft Policies</div>
                    <div class="display-6 fw-bold"><?= h($draftPolicies) ?></div>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-pen-to-square"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary fw-semibold">Administrators</div>
                    <div class="display-6 fw-bold"><?= h($adminCount) ?></div>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-user-shield"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Recent Privacy Policies</h2>
                <a href="policies.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-list"></i> View All</a>
            </div>
            <div class="card-body">
                <?php if (!$recent): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-file-circle-plus display-5 text-secondary"></i>
                        <p class="mt-3 mb-3 text-secondary">No policies have been generated yet.</p>
                        <a class="btn btn-primary" href="wizard.php"><i class="fa-solid fa-wand-magic-sparkles"></i> Start Wizard</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Policy ID</th>
                                    <th>Application</th>
                                    <th>Status</th>
                                    <th>Style</th>
                                    <th>Updated</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent as $policy): ?>
                                    <tr>
                                        <td>#<?= h($policy['id']) ?></td>
                                        <td class="fw-semibold"><?= h($policy['app_name']) ?></td>
                                        <td><?= status_badge($policy['status']) ?></td>
                                        <td><?= h(POLICY_STYLES[$policy['style']] ?? $policy['style']) ?></td>
                                        <td><?= h($policy['updated_at']) ?></td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-primary" href="policy_edit.php?id=<?= h($policy['id']) ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                                            <?php if ($policy['status'] === 'active'): ?>
                                                <a class="btn btn-sm btn-outline-secondary" href="view.php?policy_id=<?= h($policy['id']) ?>" target="_blank"><i class="fa-solid fa-eye"></i> View</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5">Quick Start</h2>
                <p class="text-secondary">Use the wizard to answer yes/no privacy questions, fill app details, select an administrator profile, and generate a policy with its own public policy ID.</p>
                <a class="btn btn-primary w-100" href="wizard.php"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Privacy Policy</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2 class="h5"><i class="fa-solid fa-scale-balanced text-primary me-2"></i>Credits and License</h2>
                <p class="mb-2">PolicyMaker</p>
                <p class="mb-2">Copyright &copy; 2026 Mohammad Hafiz bin Ismail.</p>
                <p class="mb-2">Licensed under the Simple 2-Clause BSD license.</p>
                <p class="mb-2">mypapit@gmail.com</p>
                <a href="https://github.com/mypapit/policymaker" target="_blank" rel="noopener" class="btn btn-outline-secondary w-100">
                    <i class="fa-brands fa-github"></i> GitHub Repository
                </a>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
