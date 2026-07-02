<?php
/*
 * PolicyMaker
 * GitHub: https://github.com/mypapit/policymaker
 * Copyright (c) 2026 Mohammad Hafiz bin Ismail.
 * Licensed under the Simple 2-Clause BSD license. See LICENSE for details.
 */
declare(strict_types=1);

const APP_TITLE = 'PolicyMaker';
const APP_ATTRIBUTION = 'Mohammad Hafiz bin Ismail (mypapit@gmail.com)';
const DB_PATH = __DIR__ . '/../data/policymaker.sqlite';

const POLICY_STYLES = [
    'clean' => 'Clean',
    'formal' => 'Formal',
    'compact' => 'Compact',
    'friendly' => 'Friendly',
    'developer' => 'Developer',
];

date_default_timezone_set('Asia/Kuala_Lumpur');

if (session_status() === PHP_SESSION_NONE) {
    $sessionDir = __DIR__ . '/../data/sessions';
    if (!is_dir($sessionDir)) {
        @mkdir($sessionDir, 0775, true);
    }
    if (is_dir($sessionDir) && is_writable($sessionDir)) {
        session_save_path($sessionDir);
    }
    session_start();
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = dirname(DB_PATH);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function table_exists(string $table): bool
{
    try {
        $stmt = db()->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = :name");
        $stmt->execute([':name' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function is_installed(): bool
{
    if (!file_exists(DB_PATH)) {
        return false;
    }

    try {
        if (!table_exists('admins')) {
            return false;
        }

        return (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function ensure_installed(): void
{
    if (!is_installed()) {
        redirect('install.php');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $posted = $_POST['csrf_token'] ?? '';
    if (!is_string($posted) || !hash_equals(csrf_token(), $posted)) {
        http_response_code(419);
        exit('Invalid session token. Please refresh the page and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    try {
        if (!table_exists('admins')) {
            return null;
        }

        $stmt = db()->prepare('SELECT * FROM admins WHERE id = :id AND is_active = 1');
        $stmt->execute([':id' => (int) $_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        return $admin ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function require_admin(): array
{
    ensure_installed();
    $admin = current_admin();

    if (!$admin) {
        redirect('login.php');
    }

    return $admin;
}

function now_sql(): string
{
    return date('Y-m-d H:i:s');
}

function app_base_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = $dir === '/' || $dir === '.' ? '' : rtrim($dir, '/');

    return $scheme . '://' . $host . $dir;
}

function policy_public_url(int $policyId): string
{
    return app_base_url() . '/view.php?policy_id=' . $policyId;
}

function random_password(int $bytes = 12): string
{
    return substr(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), 0, 16);
}

function status_badge(string $status): string
{
    $classes = [
        'active' => 'text-bg-success',
        'draft' => 'text-bg-secondary',
        'inactive' => 'text-bg-warning',
    ];

    $class = $classes[$status] ?? 'text-bg-secondary';
    return '<span class="badge rounded-pill ' . $class . '">' . h(ucfirst($status)) . '</span>';
}

function render_policy_content(string $content): string
{
    $lines = preg_split('/\r\n|\r|\n/', $content);
    $html = '';
    $inList = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            continue;
        }

        if (strpos($trimmed, '# ') === 0) {
            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            $html .= '<h1>' . h(substr($trimmed, 2)) . '</h1>';
            continue;
        }

        if (strpos($trimmed, '## ') === 0) {
            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            $html .= '<h2>' . h(substr($trimmed, 3)) . '</h2>';
            continue;
        }

        if (strpos($trimmed, '- ') === 0) {
            if (!$inList) {
                $html .= '<ul>';
                $inList = true;
            }
            $html .= '<li>' . h(substr($trimmed, 2)) . '</li>';
            continue;
        }

        if ($inList) {
            $html .= '</ul>';
            $inList = false;
        }

        $html .= '<p>' . h($trimmed) . '</p>';
    }

    if ($inList) {
        $html .= '</ul>';
    }

    return $html;
}

function plain_policy_text(string $content): string
{
    $content = preg_replace('/^#{1,6}\s+/m', '', $content);
    $content = preg_replace('/^\-\s+/m', '', $content);
    $content = preg_replace('/\s+/', ' ', (string) $content);

    return trim((string) $content);
}
