<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function form_value(array $data, string $key, string $default = ''): string
{
    $value = $data[$key] ?? $default;
    return trim((string) $value);
}

function form_bool(array $data, string $key): bool
{
    return isset($data[$key]) && (string) $data[$key] === '1';
}

function form_array(array $data, string $key): array
{
    $value = $data[$key] ?? [];
    if (!is_array($value)) {
        $value = [$value];
    }

    $items = [];
    foreach ($value as $item) {
        $item = trim((string) $item);
        if ($item !== '') {
            $items[] = $item;
        }
    }

    return $items;
}

function append_section(array &$sections, string $title, array $paragraphs): void
{
    $cleanParagraphs = [];
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim((string) $paragraph);
        if ($paragraph !== '') {
            $cleanParagraphs[] = $paragraph;
        }
    }

    if ($cleanParagraphs) {
        $sections[] = [
            'title' => $title,
            'paragraphs' => $cleanParagraphs,
        ];
    }
}

function generate_policy_content(array $data): string
{
    $appName = form_value($data, 'app_name', 'Android Application');
    $effectiveDate = form_value($data, 'effective_date', date('Y-m-d'));
    $developerName = form_value($data, 'developer_name');
    $organization = form_value($data, 'organization');
    $contactEmail = form_value($data, 'contact_email');
    $organizationWebsite = form_value($data, 'organization_website');
    $appWebsite = form_value($data, 'app_website');

    $owner = $organization !== '' ? $organization : $developerName;
    if ($owner === '') {
        $owner = 'the application developer';
    }

    $sections = [];

    append_section($sections, 'Introduction', [
        $owner . ' operates ' . $appName . ' for Android. This Privacy Policy explains how information is handled when you install, access, or use the application.',
        'By using the application, you agree to the practices described in this Privacy Policy.',
    ]);

    $collection = [];
    if (form_bool($data, 'collects_personal_info')) {
        $collection[] = $appName . ' may collect personal information that you provide directly or that is needed to provide requested features.';
        $details = form_value($data, 'personal_info_details');
        if ($details !== '') {
            $collection[] = 'Personal information may include: ' . $details . '.';
        }
    } else {
        $collection[] = $appName . ' is designed to avoid collecting personally identifiable information unless you voluntarily provide it or it is required for the application to operate.';
    }

    append_section($sections, 'Information We Collect', $collection);

    $use = [
        'Information is used to operate, maintain, protect, and improve the application, respond to support requests, and comply with applicable legal obligations.',
    ];
    append_section($sections, 'How We Use Information', $use);

    if (form_bool($data, 'account_registration')) {
        append_section($sections, 'Account Registration', [
            'If the application includes account registration or sign-in features, account details may be used to authenticate you and provide account-related functionality.',
        ]);
    }

    if (form_bool($data, 'analytics')) {
        $provider = form_value($data, 'analytics_provider', 'analytics services');
        append_section($sections, 'Analytics and Diagnostics', [
            'Usage and diagnostic information may be processed through ' . $provider . ' to understand app performance, identify errors, and improve features.',
        ]);
    }

    if (form_bool($data, 'ads')) {
        $network = form_value($data, 'ad_networks', 'advertising partners');
        append_section($sections, 'Advertising', [
            'Advertising identifiers or similar data may be used by ' . $network . ' to display, personalize, or measure advertisements where advertising features are enabled.',
        ]);
    }

    if (form_bool($data, 'location')) {
        append_section($sections, 'Location Information', [
            $appName . ' may request access to device location only when location-based functionality is enabled.',
            form_value($data, 'location_purpose', 'Location information is used to provide location-based application features.'),
        ]);
    }

    if (form_bool($data, 'camera_microphone')) {
        append_section($sections, 'Camera and Microphone Access', [
            $appName . ' may request camera or microphone access for features that require capturing photos, video, audio, or related media.',
            form_value($data, 'camera_microphone_purpose', 'Camera or microphone access is used only for the feature you choose to use.'),
        ]);
    }

    if (form_bool($data, 'contacts')) {
        append_section($sections, 'Contacts Access', [
            $appName . ' may request access to contacts when you choose features that require selecting or interacting with contacts.',
            form_value($data, 'contacts_purpose', 'Contacts information is used only for the requested feature.'),
        ]);
    }

    $permissions = form_value($data, 'android_permissions');
    if ($permissions !== '') {
        append_section($sections, 'Android Permissions', [
            'The application may request the following Android permissions: ' . $permissions . '. These permissions are requested only when needed for application functionality.',
        ]);
    }

    $serviceProviderMode = form_value($data, 'service_provider_mode');
    if ($serviceProviderMode === '' && form_bool($data, 'third_party_services')) {
        $serviceProviderMode = 'uses_providers';
    }

    if ($serviceProviderMode === 'uses_providers') {
        $services = form_value($data, 'third_party_services_details', 'third-party service providers');
        append_section($sections, 'Service Providers', [
            $appName . ' may use ' . $services . '. These providers may process information according to their own privacy policies and security practices.',
            'You should review the privacy practices of any third-party services used by the application.',
        ]);
    } elseif ($serviceProviderMode === 'no_providers') {
        append_section($sections, 'Service Providers', [
            'This application does not employ third-party companies or individuals to collect, process, analyze, or store user information.',
            'No personal information is shared with any third-party service provider through ' . $appName . '.',
        ]);
    } elseif ($serviceProviderMode === 'offline_no_data') {
        append_section($sections, 'Service Providers', [
            'This application does not employ third-party companies or individuals to collect, process, analyze, or store user information.',
            'Since ' . $appName . ' is fully offline and does not collect user data, no personal information is shared with any third-party service provider.',
        ]);
    }

    if (form_bool($data, 'in_app_purchases')) {
        $processor = form_value($data, 'payment_processor', 'the app store or payment processor');
        append_section($sections, 'Payments and In-App Purchases', [
            'If the application offers purchases, payment information is processed by ' . $processor . '. The application does not store full payment card details unless specifically stated by the payment provider.',
        ]);
    }

    $retentionMode = form_value($data, 'data_retention_mode');
    $retention = form_value($data, 'data_retention');
    $retentionParagraphs = [];

    if ($retentionMode === 'offline_no_data') {
        $retentionParagraphs[] = 'Because ' . $appName . ' is a fully offline application and does not collect personal data, no personal information is retained by the application or sent to external servers.';
    } elseif ($retentionMode === 'only_as_needed') {
        $retentionParagraphs[] = 'Personal information is retained only for as long as necessary to provide the application, support requested features, resolve disputes, and comply with legal obligations.';
    } elseif ($retentionMode === 'account_or_user_deleted') {
        $retentionParagraphs[] = 'Where the application stores account or user-provided information, it is retained until it is no longer needed or until you request deletion, subject to legal or operational requirements.';
    }

    $retentionComponentText = [
        'local_only' => 'Information stored locally on the device may remain there until you clear the application data or uninstall the application.',
        'no_server_storage' => 'The application does not store personal information on external servers unless a feature described in this policy requires it.',
        'delete_on_uninstall' => 'Uninstalling the application or clearing app data may remove locally stored information from the device.',
        'legal_compliance' => 'Some records may be retained when required for security, fraud prevention, dispute resolution, or compliance with law.',
        'support_records' => 'Support requests or email correspondence may be retained as long as needed to respond and maintain a support history.',
    ];

    foreach (form_array($data, 'data_retention_components') as $component) {
        if (isset($retentionComponentText[$component])) {
            $retentionParagraphs[] = $retentionComponentText[$component];
        }
    }

    if ($retention !== '') {
        $retentionParagraphs[] = $retention;
    }

    if ($retentionParagraphs) {
        append_section($sections, 'Data Retention', $retentionParagraphs);
    }

    if (form_bool($data, 'security_measures')) {
        append_section($sections, 'Security', [
            'Reasonable administrative, technical, and organizational measures are used to protect information handled by the application.',
            'No method of transmission or storage is completely secure, and absolute security cannot be guaranteed.',
        ]);
    }

    if (form_bool($data, 'user_rights')) {
        append_section($sections, 'Your Choices and Rights', [
            'Depending on your location, you may have rights to access, correct, delete, restrict, or object to certain processing of personal information.',
            'To exercise available rights, contact us using the contact details in this Privacy Policy.',
        ]);
    }

    $childrenMode = form_value($data, 'children_privacy_mode');
    if ($childrenMode === '') {
        $childrenMode = form_bool($data, 'children') ? 'directed_with_consent' : 'not_directed';
    }

    if ($childrenMode === 'offline_no_collection') {
        append_section($sections, 'Children\'s Privacy', [
            $appName . ' is an extremely simple offline application. The application does not knowingly collect personally identifiable information from children.',
            'The application does not request, collect, store, transmit, or share children\'s information. It does not contain account registration, chat features, user profiles, social interaction features, advertising, or external data collection.',
        ]);
    } elseif ($childrenMode === 'general_no_collection') {
        append_section($sections, 'Children\'s Privacy', [
            $appName . ' may be used by a general audience, including children, but it is designed not to collect personal information from children.',
            'The application does not knowingly request, store, transmit, or share children\'s personal information.',
        ]);
    } elseif ($childrenMode === 'directed_with_consent') {
        append_section($sections, 'Children\'s Privacy', [
            $appName . ' may be used by children only where permitted by applicable law and with appropriate consent where required.',
            'If you believe a child has provided personal information without proper consent, please contact us so appropriate action can be taken.',
        ]);
    } else {
        append_section($sections, 'Children\'s Privacy', [
            $appName . ' is not directed to children under 13, and we do not knowingly collect personal information from children under 13.',
        ]);
    }

    append_section($sections, 'Changes to This Privacy Policy', [
        'This Privacy Policy may be updated from time to time. Updates will be posted on this page with a revised effective date.',
    ]);

    $contact = [];
    if ($developerName !== '') {
        $contact[] = 'Name: ' . $developerName;
    }
    if ($organization !== '') {
        $contact[] = 'Organization: ' . $organization;
    }
    if ($contactEmail !== '') {
        $contact[] = 'Email: ' . $contactEmail;
    }
    if ($organizationWebsite !== '') {
        $contact[] = 'Website: ' . $organizationWebsite;
    } elseif ($appWebsite !== '') {
        $contact[] = 'Website: ' . $appWebsite;
    }
    if (!$contact) {
        $contact[] = 'Contact details will be provided by the application developer.';
    }
    append_section($sections, 'Contact Us', $contact);

    $markdown = '# Privacy Policy for ' . $appName . "\n\n";
    $markdown .= 'Effective date: ' . $effectiveDate . "\n\n";

    foreach ($sections as $section) {
        $markdown .= '## ' . $section['title'] . "\n\n";
        foreach ($section['paragraphs'] as $paragraph) {
            if (strpos($paragraph, 'Name: ') === 0 || strpos($paragraph, 'Organization: ') === 0 || strpos($paragraph, 'Email: ') === 0 || strpos($paragraph, 'Website: ') === 0) {
                $markdown .= '- ' . $paragraph . "\n";
            } else {
                $markdown .= $paragraph . "\n\n";
            }
        }
        $markdown .= "\n";
    }

    return trim($markdown) . "\n";
}

function policy_answers_json(array $data): string
{
    $allowed = [
        'app_name',
        'app_website',
        'package_name',
        'effective_date',
        'developer_name',
        'organization',
        'contact_email',
        'organization_website',
        'collects_personal_info',
        'personal_info_details',
        'account_registration',
        'analytics',
        'analytics_provider',
        'ads',
        'ad_networks',
        'location',
        'location_purpose',
        'camera_microphone',
        'camera_microphone_purpose',
        'contacts',
        'contacts_purpose',
        'android_permissions',
        'third_party_services',
        'third_party_services_details',
        'service_provider_mode',
        'in_app_purchases',
        'payment_processor',
        'data_retention_mode',
        'data_retention_components',
        'data_retention',
        'security_measures',
        'user_rights',
        'children',
        'children_privacy_mode',
    ];

    $answers = [];
    foreach ($allowed as $key) {
        if (isset($data[$key])) {
            if (is_array($data[$key])) {
                $answers[$key] = form_array($data, $key);
            } else {
                $answers[$key] = trim((string) $data[$key]);
            }
        }
    }

    $json = json_encode($answers, JSON_PRETTY_PRINT);
    return $json === false ? '{}' : $json;
}
