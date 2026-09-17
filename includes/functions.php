<?php
// Shared helpers used across every module.

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

// NFR-S02: role checks enforced server-side, not just hidden in the UI.
function require_role(array $roles) {
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

function require_login() {
    if (!current_user()) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

function base_url($path = '') {
    return '/fitcore/' . ltrim($path, '/');
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// BR-02 style auto-generated display codes: MEM001, EMP001, TRN001, EQP001, PAY1001 ...
function next_code(PDO $pdo, string $table, string $column, string $prefix, int $pad = 3, int $start = 1) {
    $stmt = $pdo->query("SELECT {$column} FROM {$table} ORDER BY {$column} DESC LIMIT 1");
    $last = $stmt->fetchColumn();
    $nextNumber = $start;
    if ($last) {
        $digits = preg_replace('/[^0-9]/', '', $last);
        $nextNumber = ((int) $digits) + 1;
    }
    return $prefix . str_pad((string) $nextNumber, $pad, '0', STR_PAD_LEFT);
}

// BR-04: "Expiring Soon" is computed at read-time only, never stored.
function subscription_display_status(string $expiryDate, string $storedStatus): array {
    if ($storedStatus !== 'active') {
        return [$storedStatus, ucfirst($storedStatus)];
    }
    $daysLeft = (int) floor((strtotime($expiryDate) - strtotime(date('Y-m-d'))) / 86400);
    if ($daysLeft < 0) {
        return ['expired', 'Expired'];
    }
    if ($daysLeft <= 7) {
        return ['expiring_soon', "Expiring in {$daysLeft}d"];
    }
    return ['active', 'Active'];
}

function redirect_with_flash(string $to, string $key, string $message) {
    flash($key, $message);
    header('Location: ' . base_url($to));
    exit;
}
