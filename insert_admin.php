<?php
include 'dbconnect.php';

// Admin credentials
$username = 'admin'; // Set the desired admin username
$password = 'admin123'; // Set the desired admin password

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert admin account into the database
$sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
if (mysqli_query($condb, $sql)) {
    echo "Admin account created successfully.";
} else {
    echo "Error: " . mysqli_error($condb);
}

mysqli_close($condb);
?>
