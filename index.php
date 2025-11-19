<?php
session_start();
if (!isset($_SESSION["teacher_id"])) header("Location: login.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Schedule</title>
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

<div class="card">
<h2>Create Schedule</h2>

<form method="POST" action="save_schedule.php">

<label>Subject</label>
<select name="subject" required>
    <option value="">Select Subject</option>
    <option>AI</option>
    <option>Cloud Computing</option>
    <option>Advanced Web Design</option>
    <option>Entrepreneurship Development</option>
    <option>Responsible AI</option>
    <option>IoT</option>
</select>

<label>Class</label>
<select name="class_name" required>
    <option value="">Select Class</option>
    <option>BCA</option>
    <option>BBA</option>
    <option>MCA</option>
    <option>MBA</option>
    <option>BCOM</option>
</select>


<label>Start Date</label>
<input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>

<label>Select Days</label>
<div class="days">
    <label><input type="checkbox" name="days[]" value="Mon">Mon</label>
    <label><input type="checkbox" name="days[]" value="Tue">Tue</label>
    <label><input type="checkbox" name="days[]" value="Wed">Wed</label>
    <label><input type="checkbox" name="days[]" value="Thu">Thu</label>
    <label><input type="checkbox" name="days[]" value="Fri">Fri</label>
    <label><input type="checkbox" name="days[]" value="Sat">Sat</label>
</div>

<button type="submit">Next →</button>
</form>

</div>

</body>
</html>
