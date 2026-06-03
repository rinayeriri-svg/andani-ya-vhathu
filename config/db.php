<?php
$servername = "localhost";
$username = "root";      // Default XAMPP username
$password = "";          // Default XAMPP password is blank
$dbname = "andani";      // Change this from 'andani_db' to 'andani'

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>