<?php
// Shared field validators, used by every create/edit form so the same rule (e.g. "no negative
// numbers", "real phone format") applies everywhere instead of being checked in some forms and not others.

// Sri Lankan mobile/landline: 0XXXXXXXXX, or +94XXXXXXXXX.
function v_phone(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null; // required-ness is checked separately
    if (!preg_match('/^(?:\+94|0)[0-9]{9}$/', $value)) {
        return 'Phone number must be a valid Sri Lankan number, e.g. 0771234567.';
    }
    return null;
}

function v_email(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;
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

function v_required($value, string $label): ?string {
    if (trim((string) $value) === '') return "{$label} is required.";
    return null;
}

// Appends $error to $errors only if it isn't null - keeps call sites terse: v_push($errors, v_phone($phone));
function v_push(array &$errors, ?string $error): void {
    if ($error !== null) $errors[] = $error;
}
