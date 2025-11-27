<?php
session_start();
require_once "db.php";

// Login check
if (!isset($_SESSION["teacher_id"])) {
    header("Location: login.php");
    exit();
}

$teacher_id = intval($_SESSION["teacher_id"]);

// Get schedule ID
if (!isset($_GET['id'])) {
    header("Location: entries.php");
    exit();
}

$schedule_id = intval($_GET['id']);

// Fetch schedule (must belong to this teacher)
$stmt = $conn->prepare("SELECT * FROM teachers_schedule WHERE id = ? AND teacher_id = ? LIMIT 1");
$stmt->bind_param("ii", $schedule_id, $teacher_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$schedule) {
    echo "<h2>Invalid Schedule ID or Permission Denied</h2>";
    exit();
}

// Extract details
$start_date = $schedule['start_date'];
$days_selected = array_map('trim', explode(",", $schedule['days_selected']));

// Fetch saved entries
$q = $conn->prepare("SELECT * FROM lesson_entries WHERE schedule_id = ? ORDER BY date ASC");
$q->bind_param("i", $schedule_id);
$q->execute();
$r = $q->get_result();

$saved = [];
while ($row = $r->fetch_assoc()) {
    $saved[$row['date']] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>View Schedule</title>

<style>
body{
    margin:0;
    padding:0;
    font-family:Inter, Arial;
    background:#eef1f7;
}

.container{
    max-width:1000px;
    margin:40px auto;
    background:white;
    padding:25px;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

.title{
    font-size:28px;
    font-weight:700;
    margin-bottom:20px;
    color:#222;
}

.top-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.back-btn{
    padding:10px 16px;
    background:#0066ff;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}
.back-btn:hover{ background:#004ecc; }

.info{
    background:#f7f8fc;
    padding:15px;
    border-radius:12px;
    margin-bottom:25px;
    font-size:15px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
th, td{
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}
th{
    background:#0066ff;
    color:white;
}

.edit-small{
    padding:6px 10px;
    background:#28a745;
    color:white;
    border-radius:6px;
    font-size:13px;
    text-decoration:none;
}
.edit-small:hover{
    background:#1c7e32;
}
</style>

</head>
<body>

<div class="container">

    <div class="top-row">
        <button class="back-btn" onclick="window.location.href='entries.php'">← Back</button>
    </div>

    <div class="title">View Schedule Details</div>

    <div class="info">
        <strong>Teacher:</strong> <?= htmlspecialchars($schedule['teacher_name']) ?><br>
        <strong>Subject:</strong> <?= htmlspecialchars($schedule['subject']) ?><br>
        <strong>Class:</strong> <?= htmlspecialchars($schedule['class_name']) ?><br>
        <strong>Start Date:</strong> <?= htmlspecialchars($schedule['start_date']) ?><br>
        <strong>Days:</strong> <?= htmlspecialchars($schedule['days_selected']) ?>
    </div>

    <table>
        <tr>
            <th>Day</th>
            <th>Date</th>
            <th>Topic</th>
            <th>Attendance</th>
            <th>Status</th>
            <th>Notes</th>
            <th>Action</th>
        </tr>

        <?php
        $current = strtotime($start_date);
        $count = 0;

        $map = [
            "Monday"=>"Mon", "Tuesday"=>"Tue", "Wednesday"=>"Wed",
            "Thursday"=>"Thu", "Friday"=>"Fri", "Saturday"=>"Sat", "Sunday"=>"Sun"
        ];

        while ($count < 10) {
            $day_full = date("l", $current);
            $short = $map[$day_full];

            if (in_array($short, $days_selected)) {

                $date_val = date("Y-m-d", $current);
                $entry = $saved[$date_val] ?? null;
                $entry_id = $entry['entry_id'] ?? null;

                echo "<tr>";

                echo "<td>$short</td>";
                echo "<td>$date_val</td>";

                echo "<td>" . ($entry['topic'] ?? '') . "</td>";
                echo "<td>" . ($entry['attendance'] ?? '') . "</td>";
                echo "<td>" . ($entry['status'] ?? '') . "</td>";
                echo "<td>" . ($entry['notes'] ?? '') . "</td>";

                if ($entry_id) {
                    echo "<td><a class='edit-small' href='edit_entry_new.php?entry_id=$entry_id'>Edit</a></td>";
                } else {
                    echo "<td style='color:#777;'>—</td>";
                }

                echo "</tr>";

                $count++;
            }

            $current = strtotime("+1 day", $current);
        }
        ?>
    </table>

</div>

</body>
</html>
