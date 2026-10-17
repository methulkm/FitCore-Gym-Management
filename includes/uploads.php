<?php
// Shared image-upload handler (NFR-S04 style: real MIME check, safe random filename, size cap).
// Returns ['path' => 'assets/uploads/...'] on success, or ['error' => '...'] on failure.
// Pass $file = null / no file selected to mean "no upload attempted" -> returns ['path' => null].
function handle_image_upload(?array $file, string $subfolder, int $maxBytes = 3 * 1024 * 1024): array {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['path' => null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed. Please try again.'];
    }
    if ($file['size'] > $maxBytes) {
        return ['error' => 'Image must be ' . round($maxBytes / 1024 / 1024, 1) . 'MB or smaller.'];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['error' => 'Only JPG, PNG or WEBP images are accepted.'];
    }

    $dir = __DIR__ . '/../assets/uploads/' . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $safeName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $safeName)) {
        return ['error' => 'Could not save the uploaded image.'];
    }

    return ['path' => 'assets/uploads/' . $subfolder . '/' . $safeName];
}

// Deletes a previously-uploaded file given its stored relative path (e.g. when replacing a photo).
function delete_uploaded_file(?string $relativePath): void {
    if (!$relativePath) return;
    $full = __DIR__ . '/../' . $relativePath;
    if (is_file($full)) @unlink($full);
}
