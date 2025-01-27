<?php
include 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_sebenar = $_POST['nama_sebenar'];
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    $position = $_POST['position'];

    // Check if phone number already exists
    $check_sql = "SELECT * FROM users WHERE phone_number = '$phone_number'";
    $result = mysqli_query($condb, $check_sql);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>
                alert('Phone number is already registered. Please use a different phone number.');
                window.history.back(); // Redirects the user back to the form
              </script>";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate 6 random recovery codes
    $recovery_codes = [];
    for ($i = 0; $i < 6; $i++) {
        $recovery_codes[] = strtoupper(bin2hex(random_bytes(3))); // 6-character alphanumeric codes
    }

    // Hash the recovery codes for validation
    $hashed_codes = json_encode(array_map(fn($code) => password_hash($code, PASSWORD_DEFAULT), $recovery_codes));

    // Store plain-text recovery codes (encrypting for security)
    $encryption_key = 'your_encryption_key';
    $iv = 'your_iv_here'; // Ensure the IV is 16 bytes
    $encrypted_plain_codes = openssl_encrypt(json_encode($recovery_codes), 'aes-256-cbc', $encryption_key, 0, $iv);

    // Insert user into database
    $sql = "INSERT INTO users (nama_sebenar, username, phone_number, password, position, remaining_leave_days, recovery_codes, plain_recovery_codes) 
            VALUES ('$nama_sebenar', '$username', '$phone_number', '$hashed_password', '$position', '20', '$hashed_codes', '$encrypted_plain_codes')";

    if (mysqli_query($condb, $sql)) {
        header("Location: recovery_codes.php");
        exit();
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
    <title>Sign Up</title>
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
        <h1>Sign Up</h1>
        <br>
        <form method="POST">
            <label for="nama_sebenar">Name:</label>
            <input type="text" name="nama_sebenar" id="nama_sebenar" required>

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" id="phone_number" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="position">Position:</label>
            <input type="text" name="position" id="position" required>
            <br><br>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>

</html>