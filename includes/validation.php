<?php
// Shared field validators, used by every create/edit form so the same rule (e.g. "no numbers in a
// name", "real phone format") applies everywhere instead of being checked in some forms and not others.
// Each returns null when the value is fine, or a ready-to-show error message.
// Empty values return null (except v_required) - required-ness is checked separately, so optional
// fields can reuse the same format rules.

const GYM_OPEN_TIME = '05:00';   // opening hours shown on the public site: daily 05:00 - 23:00
const GYM_CLOSE_TIME = '23:00';

function v_required($value, string $label): ?string {
    if (trim((string) $value) === '') return "{$label} is required.";
    return null;
}

// Appends $error to $errors only if it isn't null - keeps call sites terse: v_push($errors, v_phone($phone));
function v_push(array &$errors, ?string $error): void {
    if ($error !== null) $errors[] = $error;
}

function v_max_length(string $value, int $max, string $label): ?string {
    if (mb_strlen($value) > $max) return "{$label} must be {$max} characters or fewer.";
    return null;
}

// People's names: letters (including Sinhala/Tamil), spaces, dots, hyphens, apostrophes. No digits or symbols.
function v_person_name(string $value, string $label = 'Full name'): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (mb_strlen($value) < 2) return "{$label} must be at least 2 characters.";
    if (mb_strlen($value) > 120) return "{$label} must be 120 characters or fewer.";
    if (!preg_match("/^\p{L}[\p{L}\p{M} .'\-]*$/u", $value)) {
        return "{$label} can only contain letters, spaces, dots, hyphens and apostrophes (no numbers or symbols).";
    }
    return null;
}

// Names of things (class, plan, equipment, supplier, location, qualification...): must contain a letter;
// letters, digits, spaces and everyday punctuation only.
function v_label_text(string $value, string $label, int $max): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (mb_strlen($value) > $max) return "{$label} must be {$max} characters or fewer.";
    if (!preg_match('/\p{L}/u', $value)) return "{$label} must contain at least one letter.";
    if (!preg_match("/^[\p{L}\p{M}\p{N} .,'&()\/+#:\-]+$/u", $value)) {
        return "{$label} contains characters that are not allowed.";
    }
    return null;
}

// Free text (address, notes, reasons, messages): length limit and no invisible control characters.
function v_free_text(string $value, string $label, int $max, int $min = 0): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if ($min > 0 && mb_strlen($value) < $min) return "{$label} must be at least {$min} characters.";
    if (mb_strlen($value) > $max) return "{$label} must be {$max} characters or fewer.";
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) return "{$label} contains invalid characters.";
    return null;
}

// Sri Lankan mobile/landline: 0XXXXXXXXX, or +94XXXXXXXXX.
function v_phone(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (!preg_match('/^(?:\+94|0)[0-9]{9}$/', $value)) {
        return 'Phone number must be a valid Sri Lankan number, e.g. 0771234567.';
    }
    return null;
}

function v_email(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (mb_strlen($value) > 150) return 'Email must be 150 characters or fewer.';
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    return null;
}

// Sri Lankan NIC: old 9-digit + V/X, or new 12-digit.
function v_nic(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (!preg_match('/^(?:[0-9]{9}[vVxX]|[0-9]{12})$/', $value)) {
        return 'NIC must be 9 digits followed by V/X, or 12 digits.';
    }
    return null;
}

// "NIC / Passport" fields: a Sri Lankan NIC, or a passport number (1-2 letters then 6-8 digits, e.g. N1234567).
function v_nic_or_passport(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (v_nic($value) === null || preg_match('/^[A-Za-z]{1,2}[0-9]{6,8}$/', $value)) return null;
    return 'Enter a valid NIC (9 digits + V/X, or 12 digits) or passport number (e.g. N1234567).';
}

// Real calendar date in YYYY-MM-DD (rejects 2026-02-30, "abc", partial dates...).
function v_date(string $value, string $label): ?string {
    $value = trim($value);
    if ($value === '') return null;
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) return "{$label} is not a valid date.";
    if ((int) $d->format('Y') < 2000 || (int) $d->format('Y') > 2100) return "{$label} is not a valid date.";
    return null;
}

// Real time of day as HH:MM (or HH:MM:SS).
function v_time(string $value, string $label): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $value)) return "{$label} is not a valid time.";
    return null;
}

// Whole number typed into a box, within a range (rejects "1.5", "-3", "abc", "1e3").
function v_int_range($value, int $min, int $max, string $label): ?string {
    $value = trim((string) $value);
    if ($value === '') return null;
    if (!preg_match('/^\d+$/', $value) || (int) $value < $min || (int) $value > $max) {
        return "{$label} must be a whole number between {$min} and {$max}.";
    }
    return null;
}

// Money in LKR: positive, at most 2 decimal places, within a sensible ceiling.
function v_money($value, string $label, float $max = 1000000): ?string {
    $value = trim((string) $value);
    if ($value === '') return null;
    if (is_numeric($value) && (float) $value <= 0) return "{$label} must be greater than 0.";
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) return "{$label} must be a number with at most 2 decimal places.";
    if ((float) $value > $max) return "{$label} cannot be more than LKR " . number_format($max) . '.';
    return null;
}

// Value must be one of a fixed list (dropdowns / radio buttons) - stops tampered form posts.
function v_in($value, array $allowed, string $label): ?string {
    if (!in_array($value, $allowed, true)) return "Please choose a valid {$label}.";
    return null;
}

// Passwords: 8-72 characters (bcrypt only uses the first 72), at least one letter and one number.
function v_password(string $value): ?string {
    if (strlen($value) < 8) return 'Password must be at least 8 characters.';
    if (strlen($value) > 72) return 'Password must be 72 characters or fewer.';
    if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/\d/', $value)) {
        return 'Password must contain at least one letter and one number.';
    }
    return null;
}

// Bank transfer reference: letters, digits, dashes and slashes, 4-40 characters.
function v_reference(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
    if (!preg_match('/^[A-Za-z0-9\/\-]{4,40}$/', $value)) {
        return 'Reference number must be 4-40 letters/digits (dashes and slashes allowed, no spaces).';
    }
    return null;
}

// --- kept for older call sites ---------------------------------------------------------------
function v_positive_int($value, string $label): ?string {
    if (!is_numeric($value) || (int) $value <= 0 || (float) $value != (int) $value) {
        return "{$label} must be a whole number greater than 0.";
    }
    return null;
}

function v_positive_number($value, string $label): ?string {
    if (!is_numeric($value) || (float) $value <= 0) {
        return "{$label} must be a number greater than 0.";
    }
    return null;
}

function v_non_negative_number($value, string $label): ?string {
    if (!is_numeric($value) || (float) $value < 0) {
        return "{$label} cannot be negative.";
    }
    return null;
}
