<?php
session_start();
include "config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "Customer";

// ambil isi cart
$sql = "SELECT c.id, p.id AS product_id, p.name, p.price, c.quantity 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Keranjang kosong!'); window.location.href='cart.php';</script>";
    exit;
}

// hitung total
$total = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;
    $items[] = $row;
}

// cek promo code
$discountAmount = 0;
$discountText = "";
if (!empty($_POST['promo_code'])) {
    $promo = trim($_POST['promo_code']);
    $check = $conn->prepare("SELECT * FROM promo_codes WHERE code = ?");
    $check->bind_param("s", $promo);
    $check->execute();
    $promoResult = $check->get_result();

    if ($promoResult->num_rows > 0) {
        $promoRow = $promoResult->fetch_assoc();
        if ($promoRow['discount_type'] === 'percent') {
            $discountAmount = $total * ($promoRow['discount_value'] / 100);
            $discountText = "Diskon ({$promoRow['discount_value']}%)";
        } elseif ($promoRow['discount_type'] === 'fixed') {
            $discountAmount = $promoRow['discount_value'];
            $discountText = "Diskon Rp" . number_format($promoRow['discount_value'], 0, ',', '.');
        }
    }
}

$grandTotal = max(0, $total - $discountAmount);

// simpan ke orders (pakai kolom yang ada: total)
$orderStmt = $conn->prepare("INSERT INTO orders (user_id, total, created_at) VALUES (?, ?, NOW())");
$orderStmt->bind_param("id", $user_id, $grandTotal);
$orderStmt->execute();
$order_id = $orderStmt->insert_id;

// simpan detail order
foreach ($items as $item) {
    $orderItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $orderItem->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $orderItem->execute();

    // update sold_count
    $updateSold = $conn->prepare("UPDATE products SET sold_count = sold_count + ? WHERE id = ?");
    $updateSold->bind_param("ii", $item['quantity'], $item['product_id']);
    $updateSold->execute();
}

// hapus cart
$delete = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$delete->bind_param("i", $user_id);
$delete->execute();

// buat pesan WhatsApp
$message = "Halo saya $username ingin pesan:%0A";
foreach ($items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $message .= "- {$item['name']} x{$item['quantity']} = Rp" . number_format($subtotal, 0, ',', '.') . "%0A";
}
$message .= "%0ATotal: Rp" . number_format($total, 0, ',', '.');
if ($discountAmount > 0) {
    $message .= "%0A$discountText: -Rp" . number_format($discountAmount, 0, ',', '.');
}
$message .= "%0AGrand Total: Rp" . number_format($grandTotal, 0, ',', '.');

// redirect ke WA
$waNumber = "6283870826406";
$waLink = "https://wa.me/$waNumber?text=" . $message;
header("Location: $waLink");
exit;
?>
