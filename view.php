<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (!is_installed()) {
    http_response_code(503);
    exit('PolicyMaker is not installed.');
}

$policyId = (int) ($_GET['policy_id'] ?? 0);
if ($policyId <= 0) {
    http_response_code(404);
    exit('Privacy Policy not found.');
}

$stmt = db()->prepare("SELECT * FROM policies WHERE id = :id AND status = 'active'");
$stmt->execute([':id' => $policyId]);
$policy = $stmt->fetch();

if (!$policy) {
    http_response_code(404);
    exit('Privacy Policy not found or not active.');
}

$publisherType = trim((string) $policy['organization']) !== '' ? 'Organization' : 'Person';
$publisherName = trim((string) $policy['organization']) !== '' ? $policy['organization'] : $policy['developer_name'];
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Privacy Policy for ' . $policy['app_name'],
    'url' => policy_public_url((int) $policy['id']),
    'datePublished' => $policy['created_at'],
    'dateModified' => $policy['updated_at'],
    'inLanguage' => 'en',
    'about' => [
        '@type' => 'SoftwareApplication',
        'name' => $policy['app_name'],
        'operatingSystem' => 'Android',
        'applicationCategory' => 'MobileApplication',
        'url' => $policy['app_website'] ?: null,
    ],
    'publisher' => [
        '@type' => $publisherType,
        'name' => $publisherName ?: $policy['app_name'],
        'url' => $policy['organization_website'] ?: null,
        'email' => $policy['contact_email'] ?: null,
    ],
    'mainEntity' => [
        '@type' => 'CreativeWork',
        'name' => 'Privacy Policy for ' . $policy['app_name'],
        'text' => substr(plain_policy_text($policy['content']), 0, 4500),
    ],
];
$schemaJson = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
$style = array_key_exists($policy['style'], POLICY_STYLES) ? $policy['style'] : 'clean';

render_header('Privacy Policy for ' . $policy['app_name'], '', true);
?>
<script type="application/ld+json">
<?= $schemaJson ?: '{}' ?>
</script>

<article class="policy-document policy-style-<?= h($style) ?>">
    <div class="policy-meta">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <span><i class="fa-solid fa-shield-halved"></i> Privacy Policy ID #<?= h($policy['id']) ?></span>
            <span>Last updated <?= h($policy['updated_at']) ?></span>
        </div>
    </div>
    <?= render_policy_content($policy['content']) ?>
</article>
<?php render_footer(true); ?>
