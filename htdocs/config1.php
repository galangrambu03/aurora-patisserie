<?php
$servername = "localhost"; // BUKAN pakai host InfinityFree
$username   = "root";      // default user XAMPP
$password   = "";          // default XAMPP kosong
$dbname     = "bakery";    // nama database kamu

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
