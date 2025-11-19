<?php
session_start();
require_once "db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $cpass = trim($_POST["cpassword"]);

    if ($password !== $cpass) {
        $msg = "Passwords do not match!";
    } elseif ($username == "" || $password == "") {
        $msg = "All fields are required.";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM teachers WHERE username=?");
        $check->bind_param("s", $username);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $msg = "Username already exists!";
        } else {
            // Insert
            $q = $conn->prepare("INSERT INTO teachers (username, password) VALUES (?, ?)");
            $q->bind_param("ss", $username, $password);
            $q->execute();

            $_SESSION["teacher_id"] = $q->insert_id;
            $_SESSION["teacher_name"] = $username;
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>

<!-- INTERNAL CSS -->
<style>
body {
    margin: 0;
    padding: 0;
    font-family: "Inter", "Segoe UI", Arial;
    background:#eef0f8;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card {
    width: 450px;
    background:#fff;
    padding:35px 40px;
    border-radius:20px;
    box-shadow:0 10px 40px rgba(0,0,0,0.08);
}

.card h2 {
    text-align:center;
    margin-bottom:25px;
    color:#222;
    font-weight:700;
}

label {
    font-weight:600;
    margin-bottom:6px;
    display:block;
}

input[type="text"],
input[type="password"] {
    width:100%;
    padding:13px;
    border-radius:10px;
    border:1px solid #ccc;
    background:#fafafa;
    margin-bottom:18px;
    font-size:15px;
}

input:focus {
    border-color:#4c8bff;
    box-shadow:0 0 0 3px rgba(76,139,255,0.2);
    outline:none;
    background:#fff;
}

button {
    width:100%;
    padding:14px;
    background:#0066ff;
    color:white;
    font-size:16px;
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
}

button:hover {
    background:#004ecc;
}

.card p {
    text-align:center;
    margin-top:15px;
    font-size:14px;
}

.card a {
    color:#0066ff;
    font-weight:600;
    text-decoration:none;
}

.card a:hover {
    text-decoration:underline;
}

.error {
    color:red;
    text-align:center;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="card">
    <h2>Create Account</h2>

    <?php if($msg != ""): ?>
        <p class="error"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="cpassword" required>

        <button type="submit">Register</button>

        <p>Already have an account?
            <a href="login.php">Login</a>
        </p>
    </form>
</div>

</body>
</html>
