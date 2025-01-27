<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "holidaysystem";

// Database connection
$condb = mysqli_connect($host, $dbusername, $dbpassword, $dbname);
if (!$condb) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
