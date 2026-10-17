<?php
// Lightweight admin notifications: a row per event (new payment, leave request, enquiry, equipment issue).

function notify_admin(PDO $pdo, string $type, string $message, ?string $link = null): void {
    $pdo->prepare('INSERT INTO notifications (type, message, link, target_role, is_read) VALUES (?, ?, ?, "admin", 0)')
        ->execute([$type, $message, $link]);
}

function unread_notification_count(PDO $pdo): int {
    return (int) $pdo->query("SELECT COUNT(*) FROM notifications WHERE target_role='admin' AND is_read=0")->fetchColumn();
}

function recent_notifications(PDO $pdo, int $limit = 8): array {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE target_role='admin' ORDER BY notification_id DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Small icon + color per notification type, used by both the topbar dropdown and the full page.
function notification_style(string $type): array {
    return match ($type) {
        'payment'   => ['icon' => 'card', 'tone' => 'emerald'],
        'leave'     => ['icon' => 'calendar', 'tone' => 'amber'],
        'enquiry'   => ['icon' => 'doc', 'tone' => 'indigo'],
        'equipment' => ['icon' => 'tool', 'tone' => 'rose'],
        default     => ['icon' => 'grid', 'tone' => 'slate'],
    };
}

function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
