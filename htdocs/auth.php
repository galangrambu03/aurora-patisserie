<?php
session_start();
include "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            } else {
                $message = "❌ Password salah!";
            }
        } else {
            $message = "❌ Email tidak ditemukan!";
        }
    }

    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="images_new/logo.png">

  <meta charset="UTF-8">
  <title>Aurora Auth</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fdf6f2] flex justify-center items-center min-h-screen px-4">

  <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8 relative">
    <!-- Logo -->
    <div class="flex justify-center mb-6">
      <img src="images_new/logo.png" alt="Aurora Logo" class="w-20 h-20">
    </div>

    <!-- Error Message -->
    <?php if ($message): ?>
      <div class="text-center text-red-600 mb-4 text-sm sm:text-base font-medium">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="flex mb-6 border rounded-lg overflow-hidden text-sm sm:text-base">
      <button id="btnLoginTab" onclick="showLogin()" 
              class="w-1/2 py-2 bg-[#7b5550] text-white font-semibold transition">
        Login
      </button>
      <button id="btnRegisterTab" onclick="showRegister()" 
              class="w-1/2 py-2 bg-gray-200 text-[#7b5550] font-semibold transition">
        Sign Up
      </button>
    </div>

    <!-- Login Form -->
    <form id="loginForm" method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" 
             class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#e9a79c]" required>
      <input type="password" name="password" placeholder="Password" 
             class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#e9a79c]" required>
      <button type="submit" name="login" 
              class="w-full bg-[#7b5550] text-white py-3 rounded hover:bg-[#5a3f3b] transition">
        Login
      </button>
    </form>

    <!-- Register Form -->
    <form id="registerForm" method="POST" class="hidden space-y-4">
      <input type="text" name="username" placeholder="Username" 
             class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#e9a79c]" required>
      <input type="email" name="email" placeholder="Email" 
             class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#e9a79c]" required>
      <input type="password" name="password" placeholder="Password" 
             class="w-full p-3 border rounded focus:outline-none focus:ring-2 focus:ring-[#e9a79c]" required>
      <button type="submit" name="register" 
              class="w-full bg-[#7b5550] text-white py-3 rounded hover:bg-[#5a3f3b] transition">
        Sign Up
      </button>
    </form>
  </div>

  <script>
  function showLogin() {
    document.getElementById("loginForm").classList.remove("hidden");
    document.getElementById("registerForm").classList.add("hidden");

    document.getElementById("btnLoginTab").classList.add("bg-[#7b5550]", "text-white");
    document.getElementById("btnLoginTab").classList.remove("bg-gray-200", "text-[#7b5550]");

    document.getElementById("btnRegisterTab").classList.add("bg-gray-200", "text-[#7b5550]");
    document.getElementById("btnRegisterTab").classList.remove("bg-[#7b5550]", "text-white");
  }

  function showRegister() {
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("registerForm").classList.remove("hidden");

    document.getElementById("btnRegisterTab").classList.add("bg-[#7b5550]", "text-white");
    document.getElementById("btnRegisterTab").classList.remove("bg-gray-200", "text-[#7b5550]");

    document.getElementById("btnLoginTab").classList.add("bg-gray-200", "text-[#7b5550]");
    document.getElementById("btnLoginTab").classList.remove("bg-[#7b5550]", "text-white");
  }
  </script>

</body>
</html>
