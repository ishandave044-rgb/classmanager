<?php
$conn = new mysqli("localhost", "root", "", "lmc");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}
?>
