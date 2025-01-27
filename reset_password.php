<?php
session_start();
include 'dbconnect.php';

// Check if user has verified their recovery code
if (!isset($_SESSION['phone_number'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_SESSION['phone_number'];
    $new_password = $_POST['new_password'];

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $sql = "UPDATE users SET password = '$hashed_password' WHERE phone_number = '$phone_number'";
    if (mysqli_query($condb, $sql)) {
        echo "<script>alert('Password reset successful!'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($condb);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5F6F8;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-container {
            background-color: #FFFFFF;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            color: #4A628A;
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #4A628A;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #DDD;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #4A628A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #7AB2D3;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>Reset Password</h1>
        <form method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
