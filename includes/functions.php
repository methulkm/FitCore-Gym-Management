<?php
// Shared helpers used across every module.

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

// NFR-S02: role checks enforced server-side, not just hidden in the UI.
// Signs the user out if their account was deactivated after they logged in.
function ensure_account_active(): void {
    global $pdo;
    $user = current_user();
    if (!$user) return;
    $stmt = $pdo->prepare('SELECT status FROM users WHERE user_id = ?');
    $stmt->execute([$user['user_id']]);
    if ($stmt->fetchColumn() !== 'active') {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Your account is inactive. Please contact the gym admin.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

function require_role(array $roles) {
    ensure_account_active();
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        // A member-only or trainer-only page opens the login on that role's tab (the sidebar's portal links rely on this).
        $tab = (count($roles) === 1 && in_array($roles[0], ['member', 'trainer'], true)) ? '?role=' . $roles[0] : '';
        header('Location: ' . base_url('auth/login.php' . $tab));
        exit;
    }
}

function require_login() {
    ensure_account_active();
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

// Staff hours worked between two same-day times ('HH:MM:SS'), shown as e.g. "8h 05m".
// Counted from the HH:MM shown on screen, so "In 08:00 AM, Out 04:15 PM" always reads 8h 15m.
function worked_time(string $checkIn, string $checkOut): string {
    $minutes = max(0, intdiv(strtotime(substr($checkOut, 0, 5)) - strtotime(substr($checkIn, 0, 5)), 60));
    return intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
}

// Shared rules for a trainer availability slot (admin page and trainer's own portal page use the same checks).
function validate_availability_slot(PDO $pdo, int $trainerId, string $day, string $start, string $end): array {
    $errors = [];
    v_push($errors, v_required($day, 'Date'));
    v_push($errors, v_required($start, 'Start time'));
    v_push($errors, v_required($end, 'End time'));
    v_push($errors, v_date($day, 'Date'));
    v_push($errors, v_time($start, 'Start time'));
    v_push($errors, v_time($end, 'End time'));
    if ($errors) return $errors;

    $start = substr($start, 0, 5);
    $end = substr($end, 0, 5);
    if ($day < date('Y-m-d')) $errors[] = 'Date cannot be in the past.';
    elseif ($day > date('Y-m-d', strtotime('+1 year'))) $errors[] = 'Date cannot be more than a year ahead.';
    if ($end <= $start) $errors[] = 'End time must be after start time.'; // UC-06 validation
    if ($start < GYM_OPEN_TIME || $end > GYM_CLOSE_TIME) $errors[] = 'Availability must be within gym hours (05:00 - 23:00).';
    if ($day === date('Y-m-d') && $start < date('H:i')) $errors[] = 'Start time has already passed today.';
    if ($errors) return $errors;

    $overlap = $pdo->prepare('SELECT COUNT(*) FROM trainer_availability WHERE trainer_id = ? AND day_date = ? AND start_time < ? AND end_time > ?');
    $overlap->execute([$trainerId, $day, $end . ':00', $start . ':00']);
    if ($overlap->fetchColumn() > 0) $errors[] = 'This slot overlaps an availability slot that already exists for that day.';
    return $errors;
}

// Shared rules for creating/editing a class. $excludeClassId skips the class being edited in the clash check;
// $timeChanged=false lets an existing (already-started) class be re-saved without tripping the "not in the past" rule.
function validate_class_form(PDO $pdo, array $activeTrainerIds, string $name, $trainerId, string $date, string $start,
                             $duration, $capacity, int $excludeClassId = 0, bool $timeChanged = true): array {
    $errors = [];
    v_push($errors, v_required($name, 'Class name'));
    v_push($errors, v_label_text($name, 'Class name', 120));
    if (!in_array((int) $trainerId, $activeTrainerIds, true)) $errors[] = 'Please choose an active trainer.';
    v_push($errors, v_required($date, 'Class date'));
    v_push($errors, v_required($start, 'Start time'));
    v_push($errors, v_date($date, 'Class date'));
    v_push($errors, v_time($start, 'Start time'));
    v_push($errors, v_required($duration, 'Duration'));
    v_push($errors, v_int_range($duration, 15, 180, 'Duration (minutes)'));
    v_push($errors, v_required($capacity, 'Capacity'));
    v_push($errors, v_int_range($capacity, 1, 100, 'Capacity'));   // BR-11: admin sets capacity per class
    if ($errors) return $errors;

    $start = substr($start, 0, 5);
    $end = date('H:i', strtotime("{$date} {$start} +" . (int) $duration . ' minutes'));
    $endsNextDay = date('Y-m-d', strtotime("{$date} {$start} +" . (int) $duration . ' minutes')) !== $date;
    if ($timeChanged) {
        if ($date < date('Y-m-d') || ($date === date('Y-m-d') && $start <= date('H:i'))) $errors[] = 'Class date and time cannot be in the past.';
        elseif ($date > date('Y-m-d', strtotime('+1 year'))) $errors[] = 'Class date cannot be more than a year ahead.';
    }
    if ($start < GYM_OPEN_TIME || $endsNextDay || $end > GYM_CLOSE_TIME) $errors[] = 'The class must start and finish within gym hours (05:00 - 23:00).';
    if ($errors) return $errors;

    // BR-13: the same trainer cannot run two classes whose times overlap.
    $clash = $pdo->prepare("SELECT class_name FROM classes WHERE trainer_id = ? AND class_date = ? AND status = 'scheduled' AND class_id <> ?
                            AND start_time < ? AND ADDTIME(start_time, SEC_TO_TIME(duration_minutes * 60)) > ? LIMIT 1");
    $clash->execute([(int) $trainerId, $date, $excludeClassId, $end . ':00', $start . ':00']);
    if ($other = $clash->fetchColumn()) $errors[] = "This trainer already has \"{$other}\" scheduled at an overlapping time on that day.";
    return $errors;
}

// Shared rules for the Add/Edit Membership Plan form. Returns [errors, pipe-joined perks string].
function validate_plan_form(PDO $pdo, string $name, $duration, $price, string $perks, int $excludePlanId = 0): array {
    $errors = [];
    v_push($errors, v_required($name, 'Plan name'));
    v_push($errors, v_label_text($name, 'Plan name', 80));
    v_push($errors, v_required($duration, 'Duration'));
    v_push($errors, v_int_range($duration, 1, 36, 'Duration (months)'));   // BR-05
    v_push($errors, v_required($price, 'Price'));
    v_push($errors, v_money($price, 'Price'));

    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $perks)), 'strlen'));
    if (count($lines) > 8) $errors[] = 'Please list at most 8 perks.';
    foreach ($lines as $line) {
        if ($err = v_free_text($line, 'Each perk', 60)) { $errors[] = $err; break; }
        if (str_contains($line, '|')) { $errors[] = 'Perks cannot contain the | character.'; break; }
    }
    $description = implode('|', $lines);
    if (mb_strlen($description) > 255) $errors[] = 'Perks are too long in total - please shorten them (255 characters max).';

    if ($name !== '' && !$errors) {
        $dup = $pdo->prepare('SELECT COUNT(*) FROM membership_plans WHERE LOWER(plan_name) = LOWER(?) AND plan_id <> ?');
        $dup->execute([$name, $excludePlanId]);
        if ($dup->fetchColumn() > 0) $errors[] = 'A plan with this name already exists.';
    }
    return [$errors, $description];
}

const EQUIPMENT_STATUSES = ['available' => 'Available', 'in_use' => 'In Use', 'under_maintenance' => 'Under Maintenance', 'out_of_service' => 'Out of Service'];

// Shared rules for Add/Edit Equipment (BR-18 status list).
function validate_equipment_form(string $name, string $supplier, string $condition, string $location, $status): array {
    $errors = [];
    v_push($errors, v_required($name, 'Equipment name'));
    v_push($errors, v_label_text($name, 'Equipment name', 120));
    v_push($errors, v_label_text($supplier, 'Supplier', 120));
    v_push($errors, v_label_text($condition, 'Condition', 60));
    v_push($errors, v_label_text($location, 'Location', 60));
    v_push($errors, v_in($status, array_keys(EQUIPMENT_STATUSES), 'status'));
    return $errors;
}

// Value to re-show in a form field after a failed submit: what the user typed, else the stored/default value.
function old(string $key, $default = ''): string {
    return (string) ($_POST[$key] ?? $default);
}

// ' selected' when a dropdown option matches the posted (or stored) value - keeps choices after a validation error.
function selected($optionValue, $currentValue): string {
    return (string) $optionValue === (string) $currentValue ? ' selected' : '';
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
