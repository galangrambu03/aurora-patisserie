<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $comment  = trim($_POST['comment']);
    $rating   = (int)$_POST['rating'];

    // pakai default profile pic
    $profile_pic = "images_new/default.png";

    if ($username && $comment && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (username, comment, rating, profile_pic) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $username, $comment, $rating, $profile_pic);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php#reviews"); // balik ke reviews section
    exit;
}
?>
