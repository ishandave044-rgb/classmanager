<?php
// entries.php
session_start();
require_once "db.php";

if (!isset($_SESSION["teacher_id"])) {
    header("Location: login.php");
    exit();
}

$teacher_id = intval($_SESSION["teacher_id"]);

// Optional: show a message if delete happened
$deleted_msg = "";
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $deleted_msg = "Schedule deleted successfully.";
}

// Fetch all schedules of logged-in teacher
$q = $conn->prepare("SELECT * FROM teachers_schedule WHERE teacher_id = ? ORDER BY id DESC");
$q->bind_param("i", $teacher_id);
$q->execute();
$res = $q->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>My Schedules</title>

<style>
/* Page basics */
*{box-sizing:border-box}
body{
    margin:0;
    padding:20px;
    font-family:Inter, Arial, Helvetica, sans-serif;
    background:#eef1f7;
    color:#111;
}

/* Container */
.container{
    max-width:980px;
    margin:30px auto;
}

/* Header row */
.header-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    gap:12px;
}
.page-title{
    font-size:24px;
    font-weight:700;
}
.actions { display:flex; gap:10px; align-items:center; }

/* Buttons */
.btn {
    padding:10px 16px;
    border-radius:8px;
    border:none;
    cursor:pointer;
    font-weight:600;
    color:#fff;
}
.btn-primary { background:#0066ff; }
.btn-primary:hover { background:#004ecc; }
.btn-outline {
    background:transparent; color:#0066ff; border:1px solid #cfe0ff;
}
.btn-danger { background:#ff3b3b; }
.btn-danger:hover { background:#d62828; }

/* Info message */
.msg {
    margin-bottom:12px;
    padding:10px 14px;
    background:#e7f5ff;
    border-left:4px solid #0066ff;
    color:#024a7c;
    border-radius:8px;
}

/* Grid of cards */
.grid {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap:16px;
}

/* Schedule card */
.schedule-card{
    background:#fff;
    padding:16px;
    border-radius:12px;
    box-shadow:0 8px 30px rgba(2,6,23,0.06);
    border-left:5px solid #0066ff;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    min-height:160px;
}
.schedule-meta { margin-bottom:12px; }
.schedule-meta .line { margin:6px 0; color:#333; font-size:14px; }
.schedule-meta .label { font-weight:700; margin-right:6px; color:#111; }

/* Card footer - buttons */
.card-footer { display:flex; justify-content:space-between; gap:8px; margin-top:8px; align-items:center; }
.card-footer .left { display:flex; gap:8px; }
.small-muted { font-size:13px; color:#667085; }

/* Mobile adjustments */
@media (max-width:520px){
    .header-row { flex-direction:column; align-items:stretch; gap:8px; }
}
</style>

</head>
<body>

<div class="container">

    <div class="header-row">
        <div class="page-title">My Created Schedules</div>
        <div class="actions">
            <button class="btn btn-outline" onclick="window.location.href='dashboard.php'">← Dashboard</button>
            <button class="btn btn-primary" onclick="window.location.href='index.php'">+ Create Schedule</button>
        </div>
    </div>

    <?php if ($deleted_msg): ?>
        <div class="msg"><?= htmlspecialchars($deleted_msg) ?></div>
    <?php endif; ?>

    <?php if ($res->num_rows === 0): ?>
        <div class="msg" style="background:#fff6f6;border-left-color:#ff3b3b;color:#7a2a2a;">
            You have not created any schedule yet. Click <strong>Create Schedule</strong> to add one.
        </div>
    <?php endif; ?>

    <div class="grid">
        <?php while($row = $res->fetch_assoc()): ?>
        <div class="schedule-card" id="card-<?= (int)$row['id'] ?>">

            <div class="schedule-meta">
                <div class="line"><span class="label">Subject:</span> <?= htmlspecialchars($row['subject']) ?></div>
                <div class="line"><span class="label">Class:</span> <?= htmlspecialchars($row['class_name']) ?></div>
                <div class="line"><span class="label">Start Date:</span> <?= htmlspecialchars($row['start_date']) ?></div>
                <div class="line"><span class="label">Days:</span> <?= htmlspecialchars($row['days_selected']) ?></div>
                <!-- <div class="line small-muted"><span class="label">Created On:</span> <?= htmlspecialchars($row['id']) ?></div> -->
            </div>

            <div class="card-footer">
                <div class="left">
                    <button class="btn btn-primary" onclick="window.location.href='view_schedule.php?id=<?= (int)$row['id'] ?>'">View</button>
                    <!-- <button class="btn btn-outline" onclick="window.location.href='edit_entry.php?id=<?= (int)$row['id'] ?>'">Edit</button> -->
                </div>

                <div class="right">
                    <button class="btn btn-danger" onclick="confirmDelete(<?= (int)$row['id'] ?>)">🗑 Delete</button>
                </div>
            </div>

        </div>
        <?php endwhile; ?>
    </div>

</div>

<script>
// Confirm then redirect to delete handler
function confirmDelete(id){
    const ok = confirm("Are you sure you want to delete this schedule and all its entries? This action cannot be undone.");
    if(!ok) return;
    // redirect to delete_schedule.php (server will verify ownership)
    window.location.href = "delete_schedule.php?id=" + id;
}
</script>

</body>
</html>
