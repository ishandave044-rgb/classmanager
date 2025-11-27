<?php
session_start();
include "db.php";

$teacher_id = $_SESSION["teacher_id"];
$teacher_name = $_SESSION["teacher_name"];

$subject = $_POST["subject"];
$class_name = $_POST["class_name"];
$start_date = $_POST["start_date"];
$days = implode(",", $_POST["days"]);

$q = $conn->prepare("INSERT INTO teachers_schedule 
(teacher_id, teacher_name, subject, class_name, start_date, days_selected) 
VALUES (?, ?, ?, ?, ?, ?)");

$q->bind_param("isssss", $teacher_id, $teacher_name, $subject, $class_name, $start_date, $days);
$q->execute();

$_SESSION["schedule_id"] = $q->insert_id;
$_SESSION["start_date"] = $start_date;
$_SESSION["days"] = $_POST["days"];

header("Location: page2.php");
