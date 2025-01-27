<?php
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT * FROM users WHERE phone_number = '$phone_number'";
    $result = mysqli_query($condb, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // Storing username for reference
        $_SESSION['position'] = $user['position']; // Store user's position
        header("Location: index.php"); // Redirect to dashboard
    } else {
        echo "<script>alert('Invalid phone number or password.');</script>";
    }
}
?>