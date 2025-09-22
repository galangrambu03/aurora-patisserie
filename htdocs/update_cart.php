<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cart_id = intval($_POST['cart_id']);
    $action  = $_POST['action'];

    if ($action === "plus") {
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE id = ?";
    } elseif ($action === "minus") {
        $sql = "UPDATE cart SET quantity = quantity - 1 WHERE id = ? AND quantity > 1";
    } elseif ($action === "delete") {
        $sql = "DELETE FROM cart WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
}

header("Location: cart.php");
exit;
