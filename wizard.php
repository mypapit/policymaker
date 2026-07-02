<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/policy_builder.php';

$admin = require_admin();

$profiles = db()->query('SELECT id, display_name, organization, email, website FROM admins WHERE is_active = 1 ORDER BY display_name')->fetchAll();
$profilePayload = [];
foreach ($profiles as $profile) {
    $profilePayload[(string) $profile['id']] = [
        'developer_name' => $profile['display_name'] ?? '',
        'organization' => $profile['organization'] ?? '',
        'contact_email' => $profile['email'] ?? '',
        'organization_website' => $profile['website'] ?? '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $appName = form_value($_POST, 'app_name');
    if ($appName === '') {
        flash('danger', 'Application name is required.');
        redirect('wizard.php');
    }

    $style = form_value($_POST, 'style', 'clean');
    if (!array_key_exists($style, POLICY_STYLES)) {
        $style = 'clean';
    }

    $status = ($_POST['submit_action'] ?? '') === 'activate' ? 'active' : 'draft';
    $now = now_sql();
    $content = generate_policy_content($_POST);
    $answers = policy_answers_json($_POST);

    $stmt = db()->prepare('INSERT INTO policies (app_name, app_website, package_name, platform, developer_name, organization, contact_email, organization_website, style, status, content, answers_json, created_by, created_at, updated_at, activated_at) VALUES (:app_name, :app_website, :package_name, :platform, :developer_name, :organization, :contact_email, :organization_website, :style, :status, :content, :answers_json, :created_by, :created_at, :updated_at, :activated_at)');
    $stmt->execute([
        ':app_name' => $appName,
        ':app_website' => form_value($_POST, 'app_website'),
        ':package_name' => form_value($_POST, 'package_name'),
        ':platform' => 'Android',
        ':developer_name' => form_value($_POST, 'developer_name'),
        ':organization' => form_value($_POST, 'organization'),
        ':contact_email' => form_value($_POST, 'contact_email'),
        ':organization_website' => form_value($_POST, 'organization_website'),
        ':style' => $style,
        ':status' => $status,
        ':content' => $content,
        ':answers_json' => $answers,
        ':created_by' => (int) $admin['id'],
        ':created_at' => $now,
        ':updated_at' => $now,
        ':activated_at' => $status === 'active' ? $now : null,
    ]);

    $policyId = (int) db()->lastInsertId();
    flash('success', 'Privacy Policy #' . $policyId . ' was generated for ' . $appName . '.');
    redirect('policy_edit.php?id=' . $policyId);
}

render_header('Policy Wizard', 'wizard');
?>
<script id="profile-data" type="application/json"><?= h(json_encode($profilePayload)) ?></script>

<form method="post">
    <?= csrf_field() ?>
    <div class="wizard-grid">
        <div class="vstack gap-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fa-solid fa-mobile-screen-button text-primary me-2"></i> Application Details</h2>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="app_name">Application name</label>
                        <input class="form-control" id="app_name" name="app_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="package_name">Android package name</label>
                        <input class="form-control" id="package_name" name="package_name" placeholder="com.example.app">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="app_website">Application website</label>
                        <input class="form-control" id="app_website" name="app_website" type="url" placeholder="https://example.com/app">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="effective_date">Effective date</label>
                        <input class="form-control" id="effective_date" name="effective_date" type="date" value="<?= h(date('Y-m-d')) ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fa-solid fa-user-tie text-primary me-2"></i> Publisher Profile</h2>
                </div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label class="form-label" for="profile_selector">Profile selector</label>
                        <select class="form-select" id="profile_selector" data-profile-selector>
                            <option value="">Enter manually</option>
                            <?php foreach ($profiles as $profile): ?>
                                <option value="<?= h($profile['id']) ?>" <?= (int) $profile['id'] === (int) $admin['id'] ? 'selected' : '' ?>>
                                    <?= h($profile['display_name']) ?><?= $profile['organization'] ? ' - ' . h($profile['organization']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="developer_name">Author name</label>
                        <input class="form-control" id="developer_name" name="developer_name" value="<?= h($admin['display_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="organization">Organization name</label>
                        <input class="form-control" id="organization" name="organization" value="<?= h($admin['organization'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="contact_email">Contact email</label>
                        <input class="form-control" id="contact_email" name="contact_email" type="email" value="<?= h($admin['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="organization_website">Organization website</label>
                        <input class="form-control" id="organization_website" name="organization_website" type="url" value="<?= h($admin['website'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i> Privacy Components</h2>
                </div>
                <div class="card-body">
                    <?php
                    $questions = [
                        ['collects_personal_info', 'Does the application collect personal information?', 'personal_info_details', 'What personal information is collected?', 'Name, email address, user ID, profile data'],
                        ['account_registration', 'Does the application use account registration or login?', null, '', ''],
                        ['analytics', 'Does the application use analytics or crash reporting?', 'analytics_provider', 'Analytics provider', 'Firebase Analytics, Google Analytics, Sentry'],
                        ['ads', 'Does the application display advertisements?', 'ad_networks', 'Ad networks or partners', 'Google AdMob'],
                        ['location', 'Does the application request location access?', 'location_purpose', 'Why is location needed?', 'To show nearby content and location-based features'],
                        ['camera_microphone', 'Does the application use camera or microphone access?', 'camera_microphone_purpose', 'Why is camera or microphone access needed?', 'To capture photos, video, or audio selected by the user'],
                        ['contacts', 'Does the application access contacts?', 'contacts_purpose', 'Why are contacts needed?', 'To help users select or share with contacts'],
                        ['in_app_purchases', 'Does the application include in-app purchases or paid features?', 'payment_processor', 'Payment processor', 'Google Play Billing'],
                        ['security_measures', 'Should the policy include a security measures section?', null, '', ''],
                        ['user_rights', 'Should the policy include user privacy rights?', null, '', ''],
                    ];
                    ?>
                    <?php foreach ($questions as $question): ?>
                        <div class="question-row">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="<?= h($question[0]) ?>" name="<?= h($question[0]) ?>" value="1" <?= $question[2] ? 'data-conditional-target="#field_' . h($question[0]) . '"' : '' ?>>
                                <label class="form-check-label fw-semibold" for="<?= h($question[0]) ?>"><?= h($question[1]) ?></label>
                            </div>
                            <?php if ($question[2]): ?>
                                <div class="conditional-field" id="field_<?= h($question[0]) ?>">
                                    <label class="form-label" for="<?= h($question[2]) ?>"><?= h($question[3]) ?></label>
                                    <input class="form-control" id="<?= h($question[2]) ?>" name="<?= h($question[2]) ?>" placeholder="<?= h($question[4]) ?>" disabled>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="question-row">
                        <label class="form-label d-block">Service Providers</label>
                        <div class="vstack gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="service_provider_no_section" name="service_provider_mode" value="no_section" checked>
                                <label class="form-check-label" for="service_provider_no_section">Do not add a Service Providers section</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="service_provider_none" name="service_provider_mode" value="no_providers">
                                <label class="form-check-label" for="service_provider_none">No third-party service providers collect, process, analyze, or store user information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="service_provider_offline" name="service_provider_mode" value="offline_no_data">
                                <label class="form-check-label" for="service_provider_offline">Fully offline app with no user data shared with service providers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="service_provider_uses" name="service_provider_mode" value="uses_providers" data-conditional-target="#field_service_provider_details">
                                <label class="form-check-label" for="service_provider_uses">Uses third-party service providers</label>
                            </div>
                        </div>
                        <div class="conditional-field" id="field_service_provider_details">
                            <label class="form-label" for="third_party_services_details">Service providers</label>
                            <input class="form-control" id="third_party_services_details" name="third_party_services_details" placeholder="Firebase, Google Play Services, payment or hosting providers" disabled>
                        </div>
                    </div>

                    <div class="question-row">
                        <label class="form-label d-block">Children's Privacy</label>
                        <div class="vstack gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="children_not_directed" name="children_privacy_mode" value="not_directed" checked>
                                <label class="form-check-label" for="children_not_directed">Not directed to children under 13</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="children_offline" name="children_privacy_mode" value="offline_no_collection">
                                <label class="form-check-label" for="children_offline">Simple offline app with no children's data, accounts, chat, profiles, ads, or external collection</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="children_general" name="children_privacy_mode" value="general_no_collection">
                                <label class="form-check-label" for="children_general">General audience app that does not collect children's personal information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="children_consent" name="children_privacy_mode" value="directed_with_consent">
                                <label class="form-check-label" for="children_consent">Child audience with parent or guardian consent where required</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="android_permissions">Android permissions to mention</label>
                        <textarea class="form-control" id="android_permissions" name="android_permissions" rows="2" placeholder="Internet, location, camera, storage, notifications"></textarea>
                    </div>
                    <div class="question-row mt-3">
                        <label class="form-label d-block">Data retention statement</label>
                        <div class="vstack gap-2 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="retention_no_section" name="data_retention_mode" value="no_section" checked>
                                <label class="form-check-label" for="retention_no_section">Do not add a Data Retention section</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="retention_offline" name="data_retention_mode" value="offline_no_data">
                                <label class="form-check-label" for="retention_offline">Fully offline app: no personal data retained or sent to servers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="retention_needed" name="data_retention_mode" value="only_as_needed">
                                <label class="form-check-label" for="retention_needed">Retain information only as long as needed for app features and legal obligations</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="retention_account" name="data_retention_mode" value="account_or_user_deleted">
                                <label class="form-check-label" for="retention_account">Retain account or user-provided data until no longer needed or deletion is requested</label>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retention_local_only" name="data_retention_components[]" value="local_only">
                                    <label class="form-check-label" for="retention_local_only">Mention local device storage</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retention_no_server" name="data_retention_components[]" value="no_server_storage">
                                    <label class="form-check-label" for="retention_no_server">Mention no server-side storage</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retention_uninstall" name="data_retention_components[]" value="delete_on_uninstall">
                                    <label class="form-check-label" for="retention_uninstall">Mention uninstall or clear app data</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retention_legal" name="data_retention_components[]" value="legal_compliance">
                                    <label class="form-check-label" for="retention_legal">Mention legal or security retention</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="retention_support" name="data_retention_components[]" value="support_records">
                                    <label class="form-check-label" for="retention_support">Mention support email records</label>
                                </div>
                            </div>
                        </div>

                        <label class="form-label" for="data_retention">Custom retention statement</label>
                        <textarea class="form-control" id="data_retention" name="data_retention" rows="2" placeholder="Example: We retain information only as long as needed to provide the application and comply with legal obligations."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <aside class="vstack gap-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fa-solid fa-palette text-primary me-2"></i> Policy Style</h2>
                </div>
                <div class="card-body">
                    <label class="form-label" for="style">Display style</label>
                    <select class="form-select" id="style" name="style">
                        <?php foreach (POLICY_STYLES as $key => $label): ?>
                            <option value="<?= h($key) ?>"><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-secondary small mt-3 mb-0">The selected style controls the public privacy policy presentation without changing the legal text.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h2 class="h5">Finalize</h2>
                    <p class="text-secondary">Generating creates a permanent policy ID. You can keep it as a draft or activate it immediately for public viewing.</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" name="submit_action" value="draft" type="submit"><i class="fa-solid fa-floppy-disk"></i> Generate Draft</button>
                        <button class="btn btn-primary" name="submit_action" value="activate" type="submit"><i class="fa-solid fa-circle-check"></i> Generate and Activate</button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</form>
<?php render_footer(); ?>
