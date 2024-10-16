<?php
$servername = "localhost";  // The server hosting the MySQL database
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password (leave empty if using default settings)
$dbname = "food_ordering_db"; // The name of your database

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
