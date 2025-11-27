<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: entries.php");
    exit();
}

$teacher_id = intval($_SESSION['teacher_id']);
$schedule_id = intval($_GET['id']);

// Verify the schedule belongs to the logged-in teacher
$check = $conn->prepare("SELECT teacher_id FROM teachers_schedule WHERE id = ? LIMIT 1");
$check->bind_param("i", $schedule_id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if (!$res || intval($res['teacher_id']) !== $teacher_id) {
    echo "Permission Denied!";
    exit();
}

// Delete all lesson_entries
$d1 = $conn->prepare("DELETE FROM lesson_entries WHERE schedule_id = ?");
$d1->bind_param("i", $schedule_id);
$d1->execute();

// Delete the schedule itself
$d2 = $conn->prepare("DELETE FROM teachers_schedule WHERE id = ?");
$d2->bind_param("i", $schedule_id);
$d2->execute();

header("Location: entries.php?deleted=1");
exit();
?>
