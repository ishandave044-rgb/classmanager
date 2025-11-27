<?php
session_start();
require_once "db.php"; // must set $conn = new mysqli(...)

// Protect: require login & schedule
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['schedule_id'])) {
    header("Location: index.php");
    exit();
}

$schedule_id = (int)$_SESSION['schedule_id'];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;

// --- fetch schedule meta (and days) ---
$stmt = $conn->prepare("SELECT * FROM teachers_schedule WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$schedule) {
    echo "Schedule not found.";
    exit();
}

// preferred source of days: session (if present) else DB
if (isset($_SESSION['days']) && is_array($_SESSION['days']) && count($_SESSION['days'])>0) {
    $days = array_values($_SESSION['days']); // e.g. ["Mon","Tue","Wed"]
} else {
    // DB stores days_selected as comma separated full names (Mon,Tue,...)
    $days = array_map('trim', explode(",", $schedule['days_selected']));
}

// safety: ensure we have days
if (count($days) === 0) {
    echo "No days selected for this schedule.";
    exit();
}

// map full day names to PHP weekday numbers (N: 1=Mon .. 7=Sun)
$weekday_num = [
    "Mon" => 1,
    "Tue" => 2,
    "Wed" => 3,
    "Thu" => 4,
    "Fri" => 5,
    "Sat" => 6,
    "Sun" => 7
];

// normalize days: ensure values like "Mon","Tue"
$days = array_map(function($d){
    $d = trim($d);
    // accept some variants
    $map = [
        "M" => "Mon","Mon" => "Mon",
        "T" => "Tue","Tue" => "Tue",
        "W" => "Wed","Wed" => "Wed",
        "Th" => "Thu","Thu" => "Thu",
        "F" => "Fri","Fri" => "Fri",
        "S" => "Sat","Sat" => "Sat"
    ];
    return $map[$d] ?? $d;
}, $days);

// remove duplicates & reindex
$days = array_values(array_unique($days));

// fetch all saved entries for this schedule (one query)
$saved = [];
$qr = $conn->prepare("SELECT * FROM lesson_entries WHERE schedule_id = ?");
$qr->bind_param("i", $schedule_id);
$qr->execute();
$res = $qr->get_result();
while ($r = $res->fetch_assoc()) {
    // key by date string Y-m-d
    $saved[$r['date']] = $r;
}
$qr->close();

// start date from schedule (DB)
$start_date = $schedule['start_date'];
$start_dt = new DateTime($start_date);

// Determine initial pattern index and first_date
// If start_date weekday is in pattern -> start from that index and first_date = start_date
// Else -> start from index 0 and first_date = next occurrence of days[0] AFTER start_date

$start_weekday = intval($start_dt->format('N')); // 1..7
$pattern_index = null;
$found_index = null;
foreach ($days as $idx => $d) {
    if (isset($weekday_num[$d]) && $weekday_num[$d] === $start_weekday) {
        $found_index = $idx;
        break;
    }
}

if ($found_index !== null) {
    $pattern_start_index = $found_index;
    $first_date = clone $start_dt;
} else {
    // pattern index 0; next occurrence of days[0] strictly after start_date
    $pattern_start_index = 0;
    $target = isset($weekday_num[$days[0]]) ? $weekday_num[$days[0]] : null;
    $tmp = clone $start_dt;
    $tmp->modify('+1 day');
    while ($target !== null && intval($tmp->format('N')) !== $target) {
        $tmp->modify('+1 day');
    }
    $first_date = $tmp;
}

// Advance by pages: for page>1 we must move forward (page-1)*limit steps
$advance = ($page - 1) * $limit;
$current_pattern_index = $pattern_start_index;
$current_date = clone $first_date;

// Step forward 'advance' times to reach page's starting row
for ($a = 0; $a < $advance; $a++) {
    // move to next pattern index
    $current_pattern_index = ($current_pattern_index + 1) % count($days);
    $target_day = $days[$current_pattern_index];
    $target_num = $weekday_num[$target_day];

    // move current_date to next occurrence of target_num strictly after current_date
    $tmp = clone $current_date;
    $tmp->modify('+1 day');
    // safe loop guard: limit iterations (max 14 days)
    $iter = 0;
    while (intval($tmp->format('N')) !== $target_num && $iter < 14) {
        $tmp->modify('+1 day');
        $iter++;
    }
    $current_date = $tmp;
}

// Now generate $limit rows starting at current_pattern_index and current_date
$rows = [];
for ($i = 0; $i < $limit; $i++) {
    $pattern_index = ($current_pattern_index + $i) % count($days);
    $day_name = $days[$pattern_index];
    $target_num = $weekday_num[$day_name];

    if ($i === 0) {
        // ensure current_date matches this day (it should)
        if (intval($current_date->format('N')) !== $target_num) {
            $tmp = clone $current_date;
            $tmp->modify('+1 day');
            $iter = 0;
            while (intval($tmp->format('N')) !== $target_num && $iter < 14) {
                $tmp->modify('+1 day');
                $iter++;
            }
            $current_date = $tmp;
        }
    } else {
        // move to next occurrence of this target strictly after previous current_date
        $tmp = clone $current_date;
        $tmp->modify('+1 day');
        $iter = 0;
        while (intval($tmp->format('N')) !== $target_num && $iter < 14) {
            $tmp->modify('+1 day');
            $iter++;
        }
        $current_date = $tmp;
    }

    $date_str = $current_date->format('Y-m-d');
    $existing = $saved[$date_str] ?? null;

    $rows[] = [
        'day' => $day_name,
        'date' => $date_str,
        'topic' => $existing['topic'] ?? '',
        'attendance' => $existing['attendance'] ?? '',
        'status' => $existing['status'] ?? '',
        'notes' => $existing['notes'] ?? ''
    ];
}

// --- HTML Output ---
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule Entries</title>
   <link rel="stylesheet" href="style.css">
<style>
.top-buttons-row {
    width: 100%;
    display: flex;
    justify-content: space-between;   /* LEFT + RIGHT */
    align-items: center;
    margin-bottom: 15px;
}

/* Dashboard Button (Left) */
.dash-btn {
    padding: 10px 20px;
    font-size: 15px;
    background: #0066ff;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}
.dash-btn:hover {
    background: #004ecc;
}

/* Logout Button (Right) */
.logout-btn {
    padding: 10px 20px;
    font-size: 15px;
    background: #ff3333;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}
.logout-btn:hover {
    background: #d62828;
}
</style>

</head>
<body>
<div class="top-buttons-row">

    <a href="dashboard.php">
        <button class="dash-btn">Dashboard</button>
    </a>

    <a href="logout.php">
        <button class="logout-btn">Logout</button>
    </a>

</div>

<h2>Fill Class Details</h2>

<a href="index.php"><button class="btn btn-back">← Back</button></a>
<br><br>

<div class="header-box">
    <h3>Schedule Info</h3>
    <p><strong>Teacher:</strong> <?= htmlspecialchars($schedule['teacher_name']) ?></p>
    <p><strong>Subject:</strong> <?= htmlspecialchars($schedule['subject']) ?></p>
    <p><strong>Class:</strong> <?= htmlspecialchars($schedule['class_name']) ?></p>
    <p><strong>Start Date:</strong> <?= htmlspecialchars($schedule['start_date']) ?></p>
    <p><strong>Days:</strong> <?= htmlspecialchars(implode(", ", $days)) ?></p>
</div>

<form action="save_entries.php?page=<?= $page ?>" method="POST">
    <input type="hidden" name="schedule_id" value="<?= $schedule_id ?>">
    <table>
        <tr>
            <th>Day</th>
            <th>Date</th>
            <th>Topic</th>
            <th>Attendance</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>

        <?php foreach ($rows as $r): ?>
        <tr>
            <td><input type="text" name="day[]" value="<?= htmlspecialchars($r['day']) ?>" readonly></td>
            <td><input type="text" name="date[]" value="<?= htmlspecialchars($r['date']) ?>" readonly></td>
            <td><input type="text" name="topic[]" value="<?= htmlspecialchars($r['topic']) ?>"></td>
            <td><input type="number" name="attendance[]" min="0" value="<?= htmlspecialchars($r['attendance']) ?>"></td>
            <td><input type="text" name="status[]" value="<?= htmlspecialchars($r['status']) ?>"></td>
            <td><input type="text" name="notes[]" value="<?= htmlspecialchars($r['notes']) ?>"></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <button type="submit" class="btn btn-save">Save This Page</button>
</form>
<br>

<div style="display:flex; justify-content:space-between; margin-top:20px;">

    <!-- Previous Button -->
    <?php if ($page > 1): ?>
        <a href="page2.php?page=<?= $page - 1 ?>">
            <button type="button" class="btn btn-back btn-small">← Previous 10</button>
        </a>
    <?php else: ?>
        <div></div>
    <?php endif; ?>

    <!-- Next Button -->
    <a href="page2.php?page=<?= $page + 1 ?>">
        <button type="button" class="btn btn-next btn-small">Next 10 →</button>
    </a>

</div>
</body>
</html>
