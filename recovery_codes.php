<?php
session_start();
include 'dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch recovery codes from the database
$sql = "SELECT plain_recovery_codes FROM users WHERE id = '$user_id'";
$result = mysqli_query($condb, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Error: User not found.";
    exit();
}

// Decrypt the plain recovery codes
$encrypted_codes = $user['plain_recovery_codes'];
$recovery_codes = json_decode(openssl_decrypt($encrypted_codes, 'aes-256-cbc', 'your_encryption_key', 0, 'your_iv_here'), true);

// If decryption fails or no codes are found
if (!$recovery_codes) {
    echo "<p>You do not have any recovery codes available. Please contact support.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Codes</title>
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

        .recovery-container {
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

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #7AB2D3;
            color: white;
            padding: 10px;
            margin: 5px 0;
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
            margin-top: 20px;
        }

        button:hover {
            background-color: #7AB2D3;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <h1>Your Recovery Codes</h1>
        <p>Below are your recovery codes. Save them securely!</p>
        <ul>
            <?php foreach ($recovery_codes as $code): ?>
                <li><?= htmlspecialchars($code) ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="POST" action="download_recovery_codes.php">
            <input type="hidden" name="codes" value="<?= htmlspecialchars(json_encode($recovery_codes)) ?>">
            <button type="submit">Download as PDF</button>
        </form>
    </div>
</body>
</html>
