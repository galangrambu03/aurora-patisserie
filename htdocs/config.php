<?php
$servername = "sql208.infinityfree.com"; // host InfinityFree
$username   = "if0_39913039";            // user database
$password   = "Meisasi01";               // password database
$dbname     = "if0_39913039_bakery";     // nama database (harus sama di cPanel)

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
