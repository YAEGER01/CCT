<?php
$servername = "localhost";
$username = "root";
$password = "";   // must match the MariaDB root password you set
$dbname = "food_ordering_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>