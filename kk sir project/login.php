<?php
session_start();
require_once "db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = $_POST["username"];
    $p = $_POST["password"];

    $q = $conn->prepare("SELECT * FROM teachers WHERE username=? AND password=?");
    $q->bind_param("ss", $u, $p);
    $q->execute();
    $res = $q->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION["teacher_id"] = $row["id"];
        $_SESSION["teacher_name"] = $row["username"];
       header("Location: dashboard.php");
        exit();
    } else {
        $msg = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<style>
body {
    margin:0; padding:0;
    font-family:"Inter","Segoe UI",Arial;
    background:#eef0f8;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card {
    width: 430px;
    background:#fff;
    padding:35px 40px;
    border-radius:20px;
    box-shadow:0 10px 40px rgba(0,0,0,0.08);
}

.card h2 {
    text-align:center;
    color:#222;
    margin-bottom:25px;
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
    box-shadow:0 0 0 3px rgba(76,139,255,.2);
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
    cursor:pointer;
    font-weight:600;
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
    text-decoration:none;
    font-weight:600;
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
    <h2>Login</h2>

    <?php if($msg != ""): ?>
    <p class="error"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>

        <p>Don't have an account?
            <a href="register.php">Register</a>
        </p>
    </form>
</div>

</body>
</html>
