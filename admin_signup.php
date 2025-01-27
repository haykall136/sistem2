<?php
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists
    $check_sql = "SELECT * FROM admin WHERE username = '$username'";
    $check_result = mysqli_query($condb, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<p class='error'>Username already exists. Please choose another one.</p>";
    } else {
        // Insert the admin into the database
        $sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
        if (mysqli_query($condb, $sql)) {
            echo "<p class='success'>Admin account created successfully. <a href='admin_login.php'>Login here</a></p>";
        } else {
            echo "<p class='error'>Error: " . mysqli_error($condb) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-Up</title>
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

        .signup-container {
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

        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }

        p {
            color: #4A628A;
        }

        a {
            color: #7AB2D3;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .signup-container {
                padding: 20px;
            }

            input {
                font-size: 14px;
            }

            button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Admin Sign-Up</h1>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="admin_login.php">Login here</a></p>
    </div>
</body>
</html>
