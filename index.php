<?php
session_start();
if (!isset($_SESSION["teacher_id"])) header("Location: login.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Schedule</title>

<style>
/* Page Base */
body {
    max-width: 96%;
    margin: 0;
    padding: 0;
    font-family: "Inter", Arial;
    background: #eef1f7;
}

/* Top Button Row */
.top-buttons-row {
    width: 100%;
    padding: 18px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

/* Buttons */
.dash-btn, .logout-btn {
    padding: 10px 22px;
    font-size: 15px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
}

.dash-btn {
    background: #0066ff;
    color: #fff;
}
.dash-btn:hover {
    background: #004ecc;
}

.logout-btn {
    background: #ff3b3b;
    color: white;
}
.logout-btn:hover {
    background: #d62828;
}

/* Card */
.card {
    width: 460px;
    background: #fff;
    padding: 35px;
    margin: auto;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.1);
}

/* Title */
.card h2 {
    text-align: center;
    margin-bottom: 22px;
    color: #222;
}

/* Inputs */
label {
    display: block;
    margin: 12px 0 6px;
    font-weight: 600;
    color: #333;
}

input[type="date"],
select {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    background: #fafafa;
    transition: 0.2s ease;
}

input:focus,
select:focus {
    border-color: #0066ff;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,102,255,0.15);
    outline: none;
}

/* Days Horizontal Row */
.days {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.days label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 15px;
    font-weight: 600;
}

/* Submit Button */
button[type="submit"] {
    width: 100%;
    padding: 14px;
    background: #0066ff;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    margin-top: 20px;
    font-weight: 600;
}
button[type="submit"]:hover {
    background: #004ecc;
}
</style>

</head>
<body>

<!-- Top Navigation Buttons -->
<div class="top-buttons-row">

    <a href="dashboard.php">
        <button class="dash-btn">Dashboard</button>
    </a>

    <a href="logout.php">
        <button class="logout-btn">Logout</button>
    </a>

</div>

<!-- Main Form Card -->
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
        <label><input type="checkbox" name="days[]" value="Mon"> Mon</label>
        <label><input type="checkbox" name="days[]" value="Tue"> Tue</label>
        <label><input type="checkbox" name="days[]" value="Wed"> Wed</label>
        <label><input type="checkbox" name="days[]" value="Thu"> Thu</label>
        <label><input type="checkbox" name="days[]" value="Fri"> Fri</label>
        <label><input type="checkbox" name="days[]" value="Sat"> Sat</label>
    </div>

    <button type="submit">Next →</button>

</form>

</div>

</body>
</html>
