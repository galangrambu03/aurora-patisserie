<?php
session_start();
include "config.php"; // koneksi DB

// cek login
$loggedIn = isset($_SESSION['username']);
$username = $loggedIn ? $_SESSION['username'] : null;

// ambil review dari DB
$reviews = [];
$sql = "SELECT username, comment, rating, profile_pic FROM reviews ORDER BY created_at DESC LIMIT 10";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) {
    $reviews[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="images_new/logo.png">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="title" content="Aurora Patisserie - Fresh Bread & Pastry">
<meta name="description" content="Aurora Patisserie menyajikan roti, kue, dan pastry segar dengan resep terbaik. Order online sekarang!">
<meta name="keywords" content="roti, kue, pastry, aurora patisserie, bakery online">

  <title>Aurora Patisserie</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <style>
  html, body { margin: 0; padding: 0; }
  ::-webkit-scrollbar { display: none; }
  html { scrollbar-width: none; }

  /* efek blur+scale untuk swiper */
  .swiper-slide {
    opacity: 0.4;
    transform: scale(0.9);
    transition: all 0.3s ease;
  }
  .swiper-slide-active {
    opacity: 1 !important;
    transform: scale(1.1);
  }
</style>
</head>
<body class="bg-[#fdf6f2] text-[#7b5550] font-sans">

<!-- Navbar -->
<header id="navbar" class="fixed top-0 left-0 w-full z-50 transition-colors duration-300">
  <div class="grid grid-cols-2 items-center w-full px-4 sm:px-8 py-3">
    <div class="flex items-center">
      <a href="index.php"><img src="images_new/logo.png" alt="Aurora Patisserie" class="h-10 sm:h-12"></a>
    </div>
    <div class="flex items-center justify-end gap-4 sm:gap-6 text-sm sm:text-base font-semibold">
      <a href="index.php" class="hover:text-[#e9a79c] transition">Home</a>
      <a href="categories_menu.php" class="hover:text-[#e9a79c] transition">Products</a>
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

<!-- Hero -->
<section class="relative min-h-screen flex flex-col md:flex-row items-center px-6 sm:px-12 pt-20 overflow-hidden">
  <img src="images_new/bg.png" alt="Background Motif" class="absolute inset-0 w-full h-full object-cover opacity-80">
  <div class="relative z-10 flex flex-col md:flex-row items-center w-full gap-10">
    <div class="flex-1 flex justify-center">
      <div class="rounded-full overflow-hidden w-60 h-60 sm:w-[400px] sm:h-[400px] shadow-lg border-4 border-white">
        <img src="images_new/dashboard.png" alt="Bread Banner" class="w-full h-full object-cover">
      </div>
    </div>
    <div class="flex-1 text-center md:text-left px-4 sm:px-8">
      <h1 class="text-3xl sm:text-5xl font-bold text-[#7b5550]"><span id="hero-typing"></span></h1>
      <p class="mt-4 text-base sm:text-lg text-[#a07d79]">Crafted with the finest ingredients, a touch of passion, and time-honored techniques.</p>
      <?php if (!$loggedIn): ?>
      <div class="mt-6 flex gap-4 justify-center md:justify-start">
        <a href="auth.php" class="bg-[#7b5550] text-white px-5 sm:px-6 py-3 rounded-full hover:bg-[#5a3f3b]">Sign Up</a>
      </div>
      <?php endif; ?>
      <form action="categories_menu.php" method="get" 
      class="flex flex-col sm:flex-row mt-8 items-center relative">
  <input type="text" id="search" name="search" 
         placeholder="Enter your search text" 
         class="px-5 py-3 w-full sm:w-72 border rounded-full sm:rounded-l-full sm:rounded-r-none mb-3 sm:mb-0">
  
  <button type="submit" 
          class="bg-[#e9a79c] text-white px-6 py-3 rounded-full sm:rounded-r-full sm:rounded-l-none hover:bg-[#d78c7f] w-full sm:w-auto">
    Search
  </button>

  <!-- tempat munculnya suggestion -->
  <ul id="suggestions" 
      class="absolute top-full left-0 bg-white border rounded w-full sm:w-72 mt-1 hidden z-50"></ul>
</form>

    </div>
  </div>
</section>

<!-- Categories -->
<section class="min-h-screen flex flex-col justify-center text-center px-4">
  <h2 class="text-2xl sm:text-3xl font-bold mb-12">Our Categories</h2>
  <div class="swiper mySwiper w-full">
    <div class="swiper-wrapper">
      <div class="swiper-slide flex flex-col items-center px-4">
        <img src="images_new/caegories_bread.png" class="rounded-full w-40 h-40 sm:w-[220px] sm:h-[220px] shadow-lg">
        <h3 class="font-semibold mt-4">Bread</h3>
        <p class="text-xs sm:text-sm mt-2">A variety of freshly baked loaves.</p>
      </div>
      <div class="swiper-slide flex flex-col items-center px-4">
        <img src="images_new/categories_loafCakes.png" class="rounded-full w-40 h-40 sm:w-[220px] sm:h-[220px] shadow-lg">
        <h3 class="font-semibold mt-4">Loaf Cakes</h3>
        <p class="text-xs sm:text-sm mt-2">Moist loaf cakes baked with love.</p>
      </div>
      <div class="swiper-slide flex flex-col items-center px-4">
        <img src="images_new/categories_pastry.png" class="rounded-full w-40 h-40 sm:w-[220px] sm:h-[220px] shadow-lg">
        <h3 class="font-semibold mt-4">Pastry</h3>
        <p class="text-xs sm:text-sm mt-2">Flaky croissants and buttery pastries.</p>
      </div>
      <div class="swiper-slide flex flex-col items-center px-4">
        <img src="images_new/categories_savory.png" class="rounded-full w-40 h-40 sm:w-[220px] sm:h-[220px] shadow-lg">
        <h3 class="font-semibold mt-4">Savory</h3>
        <p class="text-xs sm:text-sm mt-2">Delicious savory bakes.</p>
      </div>
      <div class="swiper-slide flex flex-col items-center px-4">
        <img src="images_new/categories_tartCakes.png" class="rounded-full w-40 h-40 sm:w-[220px] sm:h-[220px] shadow-lg">
        <h3 class="font-semibold mt-4">Tart Cakes</h3>
        <p class="text-xs sm:text-sm mt-2">Classic tart cakes with rich filling.</p>
      </div>
    </div>
  </div>
</section>

<!-- What we do -->
<section class="py-20 px-6 sm:px-10 lg:px-20">
  <div class="flex flex-col md:flex-row items-start gap-12">
    <div class="flex-shrink-0 w-full md:w-auto" data-aos="fade-right">
      <img src="images_new/what_weDo.png" 
           class="rounded-lg w-full max-w-sm mx-auto md:mx-0 
                  transition-transform duration-500 hover:scale-105 hover:shadow-2xl">
    </div>
    <div class="flex-1" data-aos="fade-left">
      <h2 class="text-2xl sm:text-3xl font-bold text-[#7b5550] mb-6">What do we do?</h2>
      <p class="text-sm sm:text-base text-[#7b5550] leading-relaxed">
        At Aurora Patisserie, every loaf and pastry is crafted with care.  
        We combine traditional baking techniques with high-quality ingredients  
        to bring you bread, cakes, and pastries that are fresh, flavorful,  
        and filled with passion. 
      </p>
      <p class="text-sm sm:text-base text-[#7b5550] leading-relaxed mt-4">
        Whether you’re stopping by for a quick bite, ordering in bulk, or looking for special discounts, we make sure every experience is warm and satisfying.
      </p>
    </div>
  </div>

  <!-- Grid 2x2 fitur -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-10 mt-16">
    <div class="flex gap-4 items-start p-4 rounded-lg bg-[#f9ece8] 
                hover:scale-105 hover:shadow-lg transition-transform duration-500" 
         data-aos="zoom-in" data-aos-delay="100">
      <div class="w-12 h-12 bg-white flex items-center justify-center rounded-lg text-[#7b5550] text-2xl">
        <i class="bi bi-clock-history"></i>
      </div>
      <div>
        <h3 class="font-semibold">24 Hours Service</h3>
        <p class="text-xs sm:text-sm text-[#a07d79]">Freshly baked goods available around the clock.</p>
      </div>
    </div>
    <div class="flex gap-4 items-start p-4 rounded-lg bg-[#f9ece8] 
                hover:scale-105 hover:shadow-lg transition-transform duration-500" 
         data-aos="zoom-in" data-aos-delay="200">
      <div class="w-12 h-12 bg-white flex items-center justify-center rounded-lg text-[#7b5550] text-2xl">
        <i class="bi bi-box-seam"></i>
      </div>
      <div>
        <h3 class="font-semibold">Bulk Orders</h3>
        <p class="text-xs sm:text-sm text-[#a07d79]">Large orders with consistent quality.</p>
      </div>
    </div>
    <div class="flex gap-4 items-start p-4 rounded-lg bg-[#f9ece8] 
                hover:scale-105 hover:shadow-lg transition-transform duration-500" 
         data-aos="zoom-in" data-aos-delay="300">
      <div class="w-12 h-12 bg-white flex items-center justify-center rounded-lg text-[#7b5550] text-2xl">
        <i class="bi bi-percent"></i>
      </div>
      <div>
        <h3 class="font-semibold">Special Discount</h3>
        <p class="text-xs sm:text-sm text-[#a07d79]">Seasonal promotions and exclusive deals.</p>
      </div>
    </div>
    <div class="flex gap-4 items-start p-4 rounded-lg bg-[#f9ece8] 
                hover:scale-105 hover:shadow-lg transition-transform duration-500" 
         data-aos="zoom-in" data-aos-delay="400">
      <div class="w-12 h-12 bg-white flex items-center justify-center rounded-lg text-[#7b5550] text-2xl">
        <i class="bi bi-truck"></i>
      </div>
      <div>
        <h3 class="font-semibold">Fast Deliveries</h3>
        <p class="text-xs sm:text-sm text-[#a07d79]">Quick delivery service to your door.</p>
      </div>
    </div>
  </div>
</section>


<!-- Reviews -->
<section id="reviews" class="py-16 text-center px-4">
  <h2 class="text-2xl sm:text-3xl font-bold mb-12">Customer Reviews</h2>
  <div class="swiper reviewSwiper px-4 sm:px-10">
    <div class="swiper-wrapper">
      <!-- Dummy -->
      <div class="swiper-slide bg-[#f9ece8] rounded-lg w-64 p-6 shadow-md"><img src="images_new/reviews_taylorSwift.png" class="w-16 h-16 rounded-full mx-auto"><p class="mt-4 font-semibold">Taylor Swift</p><p class="text-xs mt-2">The croissants are so buttery!</p><p class="text-yellow-500 mt-2">★★★★★</p></div>
      <div class="swiper-slide bg-[#f9ece8] rounded-lg w-64 p-6 shadow-md"><img src="images_new/reviews_arianaGrande.png" class="w-16 h-16 rounded-full mx-auto"><p class="mt-4 font-semibold">Ariana Grande</p><p class="text-xs mt-2">Loaf cakes moist and tasty.</p><p class="text-yellow-500 mt-2">★★★★★</p></div>
      <!-- DB -->
      <?php foreach ($reviews as $r): ?>
        <div class="swiper-slide bg-[#f9ece8] rounded-lg w-64 p-6 shadow-md">
          <img src="<?= htmlspecialchars($r['profile_pic'] ?? 'images_new/default.png') ?>" class="w-16 h-16 rounded-full mx-auto">
          <p class="mt-4 font-semibold"><?= htmlspecialchars($r['username']) ?></p>
          <p class="text-xs mt-2"><?= htmlspecialchars($r['comment']) ?></p>
          <p class="text-yellow-500 mt-2"><?= str_repeat("★", (int)$r['rating']) . str_repeat("☆", 5 - (int)$r['rating']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="mt-8"><button onclick="openModal()" class="bg-[#7b5550] text-white px-6 py-2 rounded">Add Review</button></div>
</section>

<!-- Modal -->
<div id="reviewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded-lg w-96 relative">
    <button onclick="closeModal()" class="absolute top-2 right-2">✕</button>
    <h3 class="text-xl font-bold mb-4">Add Your Review</h3>
    <form action="add_review.php" method="POST">
      <input type="text" name="username" placeholder="Your Name" required class="w-full mb-4 px-4 py-2 border rounded">
      <textarea name="comment" placeholder="Your Review" required class="w-full mb-4 px-4 py-2 border rounded"></textarea>
      <select name="rating" class="w-full mb-4 px-4 py-2 border rounded">
        <option value="5">★★★★★</option><option value="4">★★★★☆</option>
        <option value="3">★★★☆☆</option><option value="2">★★☆☆☆</option><option value="1">★☆☆☆☆</option>
      </select>
      <button type="submit" class="bg-[#7b5550] text-white px-6 py-2 rounded w-full">Submit</button>
    </form>
  </div>
</div>

<footer class="bg-[#f2e4df] text-center py-10 text-sm">
  <p>Contact Us: 08123456789 | aurorapatisserie@gmail.com</p>
  <p class="mt-2">Quick Links: Menu | Categories | Cart</p>
  <div class="mt-4 flex justify-center items-center gap-6">
    <span>Available on:</span>
    <img src="images_new/gofood-logo.png" class="h-8">
    <img src="images_new/grabFood_logo.png" class="h-8">
    <img src="images_new/spfood_logo.png" class="h-8">
    <img src="images_new/maxim_logo.png" class="h-8">
  </div>
  <p class="mt-4">Aurora Patisserie Bakery Ltd, registered in Indonesia</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  AOS.init({ duration: 1000 });
  const searchInput = document.getElementById("search");
const suggestionBox = document.getElementById("suggestions");

searchInput.addEventListener("input", () => {
  const query = searchInput.value;
  if (query.length > 0) {
    fetch("search.php?q=" + query)
      .then(res => res.json())
      .then(data => {
        suggestionBox.innerHTML = "";
        if (data.length > 0) {
          data.forEach(item => {
            const li = document.createElement("li");
            li.textContent = item;
            li.className = "px-4 py-2 hover:bg-gray-200 cursor-pointer";
            li.onclick = () => {
              searchInput.value = item;
              suggestionBox.classList.add("hidden");
              // otomatis submit form ke categories_menu.php
              searchInput.form.submit();
            };
            suggestionBox.appendChild(li);
          });
          suggestionBox.classList.remove("hidden");
        } else {
          suggestionBox.classList.add("hidden");
        }
      });
  } else {
    suggestionBox.classList.add("hidden");
  }
});

  var reviewSwiper = new Swiper(".reviewSwiper", {
    slidesPerView: 1, spaceBetween: 20,
    breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 }},
    loop: true, autoplay: { delay: 3000, disableOnInteraction: false },
    speed: 800, grabCursor: true
  });

  var catSwiper = new Swiper(".mySwiper", {
    slidesPerView: 1, centeredSlides: true, spaceBetween: 20,
    breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 }},
    loop: true, autoplay: { delay: 3000, disableOnInteraction: false },
    effect: "coverflow",
    coverflowEffect: { rotate: 0, stretch: 0, depth: 100, modifier: 2, slideShadows: false }
  });

  var typed = new Typed("#hero-typing", {
    strings: [
      "<?php echo $loggedIn ? 'Hello, ' . htmlspecialchars($username) : 'Welcome to Aurora Patisserie'; ?>",
      "Where Every Loaf Tells a Story","Freshly Baked, Every Day"
    ],
    typeSpeed: 60, backSpeed: 40, loop: true
  });

  function openModal(){document.getElementById("reviewModal").classList.remove("hidden");document.getElementById("reviewModal").classList.add("flex");}
  function closeModal(){document.getElementById("reviewModal").classList.add("hidden");}
  window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  const links = navbar.querySelectorAll("a");
  const actions = navbar.querySelectorAll("a, button, span"); // tombol & username

  if (window.scrollY > 50) {
    navbar.classList.add("bg-[#e9a79c]/90", "shadow-md");

    links.forEach(link => link.classList.add("text-white"));
    actions.forEach(el => el.classList.add("text-white"));
  } else {
    navbar.classList.remove("bg-[#e9a79c]/90", "shadow-md");

    links.forEach(link => link.classList.remove("text-white"));
    actions.forEach(el => el.classList.remove("text-white"));
  }
});
</script>
</body>
</html>
