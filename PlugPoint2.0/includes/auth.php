<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'], $_POST['password'], $_POST['role'])) {
        // Registration
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $conn->real_escape_string($_POST['role']);
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            header('Location: ../pages/register.php?error=exists');
            exit();
        }
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            header('Location: ../pages/dashboard.php');
            exit();
        } else {
            header('Location: ../pages/register.php?error=failed');
            exit();
        }
    } elseif (isset($_POST['email'], $_POST['password'])) {
        // Login
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $sql = "SELECT id, name, password, role FROM users WHERE email='$email'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                // Redirect based on role
                if ($user['role'] === 'buyer') {
                    header('Location: ../pages/buyer_dashboard.php');
                } elseif ($user['role'] === 'seller') {
                    header('Location: ../pages/seller_dashboard.php');
                } elseif ($user['role'] === 'admin') {
                    header('Location: ../pages/admin_dashboard.php');
                } else {
                    header('Location: ../pages/dashboard.php');
                }
                exit();
            }
        }
        header('Location: ../pages/login.php?error=invalid');
        exit();
    }
}
header('Location: ../pages/login.php');
exit(); 