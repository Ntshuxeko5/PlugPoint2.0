<?php
$host = 'sql211.infinityfree.com';
$user = 'if0_39291417';
$pass = 'rotTa6s91r11W';
$db = 'if0_39291417_plugpoint';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?> 