<?php
session_start();
require_once "db.php";

// -------------------------------
//  LOGIN CHECK
// -------------------------------
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// -------------------------------
//  CHECK entry_id in URL
// -------------------------------
if (!isset($_GET['entry_id'])) {
    echo "Entry ID missing!";
    exit();
}

$entry_id = intval($_GET['entry_id']);

// -------------------------------
//  FETCH ENTRY DETAILS
// -------------------------------
$q = $conn->prepare("
    SELECT e.*, s.teacher_id AS owner_id, s.id AS schedule_id
    FROM lesson_entries e
    JOIN teachers_schedule s ON e.schedule_id = s.id
    WHERE e.entry_id = ?
    LIMIT 1
");
$q->bind_param("i", $entry_id);
$q->execute();
$res = $q->get_result();
$entry = $res->fetch_assoc();

if (!$entry) {
    echo "Entry not found!";
    exit();
}

// Teacher permission check
if ($entry['owner_id'] != $teacher_id) {
    echo "Permission Denied!";
    exit();
}

$schedule_id = $entry["schedule_id"];

// -------------------------------
//  UPDATE ENTRY
// -------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $topic = trim($_POST['topic']);
    $attendance = intval($_POST['attendance']);
    $status = trim($_POST['status']);
    $notes = trim($_POST['notes']);

    $u = $conn->prepare("
        UPDATE lesson_entries
        SET topic=?, attendance=?, status=?, notes=?
        WHERE entry_id=?
    ");

    $u->bind_param("sissi", $topic, $attendance, $status, $notes, $entry_id);
    $u->execute();

    header("Location: view_schedule.php?id=" . $schedule_id);
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Entry</title>

<style>
body{
    margin:0;
    padding:0;
    font-family:Inter, Arial;
    background:#eef1f7;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:white;
    padding:25px;
    border-radius:14px;
    box-shadow:0 8px 30px rgba(0,0,0,0.08);
}

h2{
    margin-bottom:20px;
}

label{
    font-weight:600;
    margin-top:10px;
    display:block;
}

input, textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    margin-top:4px;
    background:#fafafa;
}

button{
    padding:12px 18px;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

.save-btn{
    background:#0066ff;
    color:white;
    width:100%;
    margin-top:20px;
}
.save-btn:hover{
    background:#004ecc;
}

.back-btn{
    background:#4b5563;
    color:white;
    margin-bottom:20px;
}
.back-btn:hover{
    background:#374151;
}

.info-block{
    background:#f5f7fc;
    padding:12px;
    border-radius:10px;
    margin-bottom:20px;
}
</style>

</head>
<body>

<div class="container">

    <button class="back-btn" onclick="window.location.href='view_schedule.php?id=<?= $schedule_id ?>'">← Back</button>

    <h2>Edit Entry</h2>

    <div class="info-block">
        <strong>Date:</strong> <?= $entry['date'] ?><br>
        <strong>Day:</strong> <?= $entry['day'] ?>
    </div>

    <form method="POST">

        <label>Topic</label>
        <input type="text" name="topic" value="<?= htmlspecialchars($entry['topic']) ?>">

        <label>Attendance</label>
        <input type="number" name="attendance" value="<?= htmlspecialchars($entry['attendance']) ?>">

        <label>Status</label>
        <input type="text" name="status" value="<?= htmlspecialchars($entry['status']) ?>">

        <label>Notes</label>
        <textarea name="notes" rows="3"><?= htmlspecialchars($entry['notes']) ?></textarea>

        <button type="submit" class="save-btn">Save Changes</button>

    </form>

</div>

</body>
</html>
