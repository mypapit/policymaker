<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function create_schema(): void
{
    $sql = [
        "CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            display_name TEXT NOT NULL,
            organization TEXT,
            email TEXT,
            website TEXT,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            last_login_at TEXT
        )",
        "CREATE TABLE IF NOT EXISTS policies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            app_name TEXT NOT NULL,
            app_website TEXT,
            package_name TEXT,
            platform TEXT NOT NULL DEFAULT 'Android',
            developer_name TEXT,
            organization TEXT,
            contact_email TEXT,
            organization_website TEXT,
            style TEXT NOT NULL DEFAULT 'clean',
            status TEXT NOT NULL DEFAULT 'draft',
            content TEXT NOT NULL,
            answers_json TEXT NOT NULL,
            created_by INTEGER,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            activated_at TEXT,
            FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_policies_status ON policies(status)",
        "CREATE INDEX IF NOT EXISTS idx_policies_app_name ON policies(app_name)",
    ];

    foreach ($sql as $statement) {
        db()->exec($statement);
    }
}
