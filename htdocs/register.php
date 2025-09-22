<?php
session_start();
include "config.php"; // koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        header("Location: index.php"); // langsung ke halaman utama
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
