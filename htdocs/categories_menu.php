<?php
session_start();
include "config.php"; // koneksi DB

$loggedIn = isset($_SESSION['username']);
$username = $loggedIn ? $_SESSION['username'] : null;

// Ambil kategori & search dari URL
$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$search   = isset($_GET['search']) ? trim($_GET['search']) : null;

if ($category && $search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE LOWER(category) = LOWER(?) AND (LOWER(name) LIKE LOWER(?) OR LOWER(category) LIKE LOWER(?))");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("sss", $category, $searchTerm, $searchTerm);
} elseif ($category) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE LOWER(category) = LOWER(?)");
    $stmt->bind_param("s", $category);
} elseif ($search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE LOWER(name) LIKE LOWER(?) OR LOWER(category) LIKE LOWER(?)");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT * FROM products");
}

$stmt->execute();
$result = $stmt->get_result();

// Query Best Seller
$best = $conn->query("
    SELECT p.id, p.name, p.price, p.img, p.category, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 4
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Categories & Menu - Aurora Patisserie</title>
  <link rel="icon" type="image/png" href="images_new/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- AOS Animation -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body class="bg-[#fdf6f2] text-[#7b5550] font-sans">

<!-- Navbar -->
<header id="navbar" class="fixed top-0 left-0 w-full z-50 transition-colors duration-300">
  <div class="grid grid-cols-2 items-center w-full px-4 sm:px-8 py-3">
    <div class="flex items-center">
      <a href="index.php">
        <img src="images_new/logo.png" alt="Aurora Logo" class="h-10 sm:h-12">
      </a>
    </div>
    <div class="flex items-center justify-end gap-4 sm:gap-6 text-sm sm:text-base font-semibold">
      <a href="index.php" class="hover:text-[#e9a79c] transition">Home</a>
      <a href="categories_menu.php" class="hover:text-[#e9a79c] transition font-bold underline">Categories</a>
      <a href="cart.php" class="hover:text-[#e9a79c] transition">Cart</a>
      <?php if ($loggedIn): ?>
        <span class="hidden sm:inline">Hello, <?= htmlspecialchars($username) ?></span>
        <a href="logout.php" class="px-3 sm:px-4 py-1 rounded-full bg-red-500 text-white hover:bg-red-600">Logout</a>
      <?php else: ?>
        <a href="auth.php" class="px-3 sm:px-4 py-1 rounded-full bg-[#e9a79c] text-white hover:bg-[#d78c7f]">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Best Seller Section -->
<section class="pt-28 pb-12 px-4 sm:px-10">
  <h2 class="text-2xl sm:text-3xl font-bold mb-8 text-center">🔥 Best Seller</h2>
  <?php if ($best->num_rows > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-10">
      <?php while ($row = $best->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-md p-6 text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-xl" data-aos="fade-up">
          <img src="<?= htmlspecialchars($row['img']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-32 h-32 sm:w-40 sm:h-40 mx-auto rounded-full object-cover">
          <h3 class="mt-4 font-semibold text-lg"><?= htmlspecialchars($row['name']) ?></h3>
          <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['category']) ?> • Terjual <?= $row['total_sold'] ?>+</p>
          <p class="font-bold mt-2 text-[#7b5550]">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
          <form method="POST" action="add_to_cart.php" class="mt-3 flex justify-center gap-2">
            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="border w-16 text-center">
            <button type="submit" class="bg-[#7b5550] text-white px-3 py-1 rounded hover:bg-[#5a3f3b]">Add to Cart</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-center text-gray-500">Belum ada data best seller.</p>
  <?php endif; ?>
</section>

<!-- Categories Section -->
<section class="pb-16 px-4 sm:px-10">
  <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-center">
    <?php 
      if ($search) {
        echo "Hasil pencarian untuk: \"" . htmlspecialchars($search) . "\"";
        if ($category) echo " di kategori " . htmlspecialchars($category);
      } else {
        echo $category ? htmlspecialchars($category) : "All Products";
      }
    ?>
  </h2>

  <!-- Form Search -->
  <form action="categories_menu.php" method="get" class="flex flex-col sm:flex-row justify-center mb-10 items-center">
    <?php if ($category): ?>
      <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
    <?php endif; ?>
    <input type="text" name="search" placeholder="Cari roti, kue, dll..." value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 w-full sm:w-64 border rounded-full sm:rounded-l-full sm:rounded-r-none text-[#7b5550] mb-3 sm:mb-0">
    <button type="submit" class="bg-[#e9a79c] text-white px-6 py-2 rounded-full sm:rounded-r-full sm:rounded-l-none hover:bg-[#d78c7f] w-full sm:w-auto">Search</button>
  </form>

  <?php if ($result->num_rows == 0): ?>
    <p class="text-center text-gray-500">No products found.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-10">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-md p-6 text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-xl" data-aos="fade-up">
          <img src="<?= htmlspecialchars($row['img']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-32 h-32 sm:w-40 sm:h-40 mx-auto rounded-full object-cover">
          <h3 class="mt-4 font-semibold text-lg"><?= htmlspecialchars($row['name']) ?></h3>
          <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($row['category']) ?></p>
          <p class="font-bold mt-2 text-[#7b5550]">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
          <form method="POST" action="add_to_cart.php" class="mt-3 flex justify-center gap-2">
            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" class="border w-16 text-center">
            <button type="submit" class="bg-[#7b5550] text-white px-3 py-1 rounded hover:bg-[#5a3f3b]">Add to Cart</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Scripts -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: false
  });

  // Scroll effect untuk navbar
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
</script>
</body>
</html>
