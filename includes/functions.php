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

// --- CSRF protection ---------------------------------------------------
// One secret token per session. Every state-changing request (a POST form, or one of the handful
// of plain GET action links like Delete/Approve/Reset Password) must echo it back, proving the
// request came from our own page and not a forged link/form on another site.
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Wraps a GET action link (Delete, Approve, Reactivate, ...) with the current token.
function csrf_url(string $url): string {
    $sep = str_contains($url, '?') ? '&' : '?';
    return $url . $sep . 'csrf=' . urlencode(csrf_token());
}

// Hidden field for POST forms: echo it right after the opening <form method="post"> tag.
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Call at the top of any script that mutates data. Wrong/missing token -> friendly redirect
// instead of performing the action - never trust a request just because the session cookie is valid.
function csrf_verify(string $redirectTo = 'auth/login.php'): void {
    $token = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
    $expected = $_SESSION['csrf'] ?? '';
    // Explicit empty checks first: hash_equals('', '') is true in PHP, which would otherwise let
    // a request with no token through against a session that never generated one either.
    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        redirect_with_flash($redirectTo, 'error', 'Your session expired or that link was invalid. Please try again.');
    }
}
