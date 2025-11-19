<?php
session_start();
include "db.php";

$schedule_id = $_POST["schedule_id"];

$days = $_POST["day"];
$dates = $_POST["date"];
$topics = $_POST["topic"];
$att = $_POST["attendance"];
$status = $_POST["status"];
$notes = $_POST["notes"];

$checkStmt = $conn->prepare("SELECT entry_id FROM lesson_entries WHERE schedule_id=? AND date=? LIMIT 1");

$updateStmt = $conn->prepare("
UPDATE lesson_entries SET day=?, topic=?, attendance=?, status=?, notes=? WHERE entry_id=?
");

$insertStmt = $conn->prepare("
INSERT INTO lesson_entries (schedule_id, day, date, topic, attendance, status, notes)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

for ($i=0; $i<count($dates); $i++) {

    $date_val = $dates[$i];
    $day_val = $days[$i];
    $topic_val = $topics[$i];
    $att_val = intval($att[$i]);
    $status_val = $status[$i];
    $notes_val = $notes[$i];

    $checkStmt->bind_param("is", $schedule_id, $date_val);
    $checkStmt->execute();
    $res = $checkStmt->get_result();

    if ($res->num_rows > 0) {
        $eid = $res->fetch_assoc()["entry_id"];

        $updateStmt->bind_param("ssissi",
            $day_val, $topic_val, $att_val, $status_val, $notes_val, $eid
        );
        $updateStmt->execute();

    } else {
        $insertStmt->bind_param("isssiss",
            $schedule_id, $day_val, $date_val, $topic_val, $att_val, $status_val, $notes_val
        );
        $insertStmt->execute();
    }
}

header("Location: page2.php");
exit();
?>
