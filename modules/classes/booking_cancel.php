<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$classId = (int) ($_GET['class_id'] ?? 0);

$stmt = $pdo->prepare("SELECT b.*, c.class_date, c.start_time FROM bookings b JOIN classes c ON c.class_id = b.class_id WHERE b.booking_id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if ($booking) {
    // BR-12: members may cancel only when >= 2 hours remain before the session.
    // Admin acting on their behalf follows the same business rule here.
    $sessionStart = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
    $hoursLeft = ($sessionStart - time()) / 3600;

    if ($hoursLeft < 2) {
        redirect_with_flash('modules/classes/bookings.php?class_id=' . $classId, 'error', 'Cannot cancel: less than 2 hours remain before the session (BR-12).');
    }

    $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?")->execute([$id]);
    redirect_with_flash('modules/classes/bookings.php?class_id=' . $classId, 'success', 'Booking cancelled.');
}

redirect_with_flash('modules/classes/bookings.php?class_id=' . $classId, 'error', 'Booking not found.');
