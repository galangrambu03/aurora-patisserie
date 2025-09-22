<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id    = intval($_SESSION['user_id']);
    $product_id = intval($_POST['product_id']);
    $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // cek apakah produk sudah ada di cart user ini
    $sql = "SELECT id FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // update quantity
        $update = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $update->bind_param("iii", $quantity, $user_id, $product_id);
        $update->execute();
    } else {
        // insert baru
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $user_id, $product_id, $quantity);
        $insert->execute();
    }

    // balik ke halaman sebelumnya (categories)
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
