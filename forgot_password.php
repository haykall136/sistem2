<?php
session_start();
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number'];
    $recovery_code = $_POST['recovery_code'];

    // Fetch user by phone number
    $sql = "SELECT recovery_codes FROM users WHERE phone_number = '$phone_number'";
    $result = mysqli_query($condb, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $stored_codes = json_decode($user['recovery_codes'], true);
        $is_valid = false;

        // Check if the recovery code matches any of the hashed codes
        foreach ($stored_codes as $hashed_code) {
            if (password_verify($recovery_code, $hashed_code)) {
                $is_valid = true;
                break;
            }
        }

        if ($is_valid) {
            $_SESSION['phone_number'] = $phone_number; // Store phone number in session
            header("Location: reset_password.php"); // Redirect to reset password
            exit();
        } else {
            $error_message = "Invalid recovery code. Please try again.";
        }
    } else {
        $error_message = "User not found. Please check the phone number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        .forgot-container {
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
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }
        p {
            color: #4A628A;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h1>Forgot Password</h1>
        <?php if (isset($error_message)): ?>
            <p class="error"><?= $error_message ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" id="phone_number" required>
            <label for="recovery_code">Recovery Code:</label>
            <input type="text" name="recovery_code" id="recovery_code" required>
            <button type="submit">Verify Recovery Code</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
