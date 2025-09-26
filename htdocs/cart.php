<?php
session_start();
include "config.php";

$loggedIn = isset($_SESSION['username']);
$username = $loggedIn ? $_SESSION['username'] : null;

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT c.id, p.id AS product_id, p.name, p.price, p.img, c.quantity 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$discount = 0;
$promo_message = "";

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $promo = trim($_POST['promo_code']);
    $check = $conn->prepare("SELECT * FROM promo_codes WHERE code = ?");
    $check->bind_param("s", $promo);
    $check->execute();
    $promoResult = $check->get_result();

    if ($promoResult->num_rows > 0) {
        $promoRow = $promoResult->fetch_assoc();

        if ($promoRow['discount_type'] === 'percent') {
            $discount = ($total * $promoRow['discount_value']) / 100;
            $promo_message = "Kode promo berhasil digunakan (-{$promoRow['discount_value']}%)";
        } elseif ($promoRow['discount_type'] === 'fixed') {
            $discount = $promoRow['discount_value'];
            $promo_message = "Kode promo berhasil digunakan (-Rp " . number_format($discount, 0, ',', '.') . ")";
        }
    } else {
        $promo_message = "Kode promo tidak valid.";
    }
}

$grandTotal = $total - $discount;
if ($grandTotal < 0) $grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="images_new/logo.png">

  <meta charset="UTF-8">
  <title>Your Cart</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fdf6f2] text-[#7b5550] font-sans">

<header id="navbar" class="fixed top-0 left-0 w-full z-50 transition-colors duration-300">
  <div class="grid grid-cols-2 items-center w-full px-4 sm:px-6 lg:px-10 py-3">
    <div class="flex items-center">
      <a href="index.php">
        <img src="images_new/logo.png" alt="Aurora Logo" class="h-8 sm:h-10 md:h-12">
      </a>
    </div>
    <div class="flex items-center justify-end gap-3 sm:gap-5 text-xs sm:text-sm md:text-base font-semibold">
      <a href="index.php" class="hover:text-[#e9a79c] transition">Home</a>
      <a href="categories_menu.php" class="hover:text-[#e9a79c] transition">Categories</a>
      <a href="cart.php" class="font-bold underline">Cart</a>
      <?php if ($loggedIn): ?>
        <span class="hidden sm:inline">Hello, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php"
           class="px-2 sm:px-3 md:px-4 py-1 rounded-full bg-red-500 text-white hover:bg-red-600">
          Logout
        </a>
      <?php else: ?>
        <a href="auth.php"
           class="px-2 sm:px-3 md:px-4 py-1 rounded-full bg-[#e9a79c] text-white hover:bg-[#d78c7f]">
           Login
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="pt-28 pb-20 px-4 sm:px-8 lg:px-12 max-w-5xl mx-auto">
  <h1 class="text-2xl sm:text-3xl font-bold mb-6">Keranjang Belanja</h1>

  <?php if (count($cart_items) > 0): ?>
    <div class="space-y-4">
      <?php foreach ($cart_items as $row): ?>
        <div class="flex items-center bg-white shadow rounded-lg p-4 gap-4">
          <img src="<?= htmlspecialchars($row['img']) ?>" 
               alt="<?= htmlspecialchars($row['name']) ?>" 
               class="w-20 h-20 object-cover rounded">
          <div class="flex-1">
            <h2 class="font-semibold text-lg"><?= htmlspecialchars($row['name']) ?></h2>
            <p class="text-sm text-gray-500">Rp<?= number_format($row['price'], 0, ',', '.') ?></p>
            <div class="flex items-center mt-2 gap-2">
              <form action="update_cart.php" method="POST" class="flex items-center gap-2">
                <input type="hidden" name="cart_id" value="<?= $row['id'] ?>">
                <button name="action" value="minus" class="px-2 bg-gray-200 rounded">-</button>
                <span><?= $row['quantity'] ?></span>
                <button name="action" value="plus" class="px-2 bg-gray-200 rounded">+</button>
                <button name="action" value="delete" class="px-2 bg-red-500 text-white rounded">Hapus</button>
              </form>
            </div>
          </div>
          <div class="text-right font-bold text-[#7b5550]">
            Rp<?= number_format($row['subtotal'], 0, ',', '.') ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center">
      <form method="POST" class="flex gap-2 w-full sm:w-auto">
        <input type="text" name="promo_code" placeholder="Kode Promo" 
               class="border rounded px-4 py-2 w-full sm:w-60">
        <button type="submit" class="bg-[#7b5550] text-white px-4 py-2 rounded">Terapkan</button>
      </form>
      <?php if ($promo_message): ?>
        <p class="text-sm text-red-500"><?= $promo_message ?></p>
      <?php endif; ?>
    </div>

    <div class="text-right mt-6">
      <p class="text-lg">Subtotal: Rp<?= number_format($total, 0, ',', '.') ?></p>
      <?php if ($discount > 0): ?>
        <p class="text-green-600">Diskon: -Rp<?= number_format($discount, 0, ',', '.') ?></p>
      <?php endif; ?>
      <p class="text-2xl font-bold mt-2">Total: Rp<?= number_format($grandTotal, 0, ',', '.') ?></p>
    </div>

    <div class="text-right mt-6">
      <form id="checkoutForm" action="checkout.php" method="POST" target="_blank">
        <input type="hidden" name="promo_code" value="<?= isset($_POST['promo_code']) ? htmlspecialchars($_POST['promo_code']) : '' ?>">
        <button type="submit" 
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
          Checkout
        </button>
      </form>
    </div>

  <?php else: ?>
    <p class="text-gray-500">Keranjang kamu masih kosong.</p>
  <?php endif; ?>
</div>

<script>
  window.addEventListener("scroll", function () {
    const navbar = document.getElementById("navbar");
    const links = navbar.querySelectorAll("a:not([class*='bg-'])");
    const username = navbar.querySelector("span");

    if (window.scrollY > 50) {
      navbar.classList.add("bg-[#e9a79c]/90", "shadow-md");
      links.forEach(link => link.classList.add("text-white"));
      if (username) username.classList.add("text-white");
    } else {
      navbar.classList.remove("bg-[#e9a79c]/90", "shadow-md");
      links.forEach(link => link.classList.remove("text-white"));
      if (username) username.classList.remove("text-white");
    }
  });

  document.getElementById("checkoutForm").addEventListener("submit", function() {
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  });
</script>
</body>
</html>
