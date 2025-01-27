<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $position = $_POST['position'];
    $password = $_POST['password'];

    // Start building the update query
    $update_sql = "UPDATE users 
                   SET username = '$username',
                       phone_number = '$phone_number',
                       position = '$position'";

    // Append password update only if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql .= ", password = '$hashed_password'";
    }

    // Complete the query with the WHERE clause
    $update_sql .= " WHERE id = '$user_id'";

    // Execute the query
    if (mysqli_query($condb, $update_sql)) {
        $message = "Profile updated successfully.";
    } else {
        $message = "Error: " . mysqli_error($condb);
    }

    // Redirect to avoid form resubmission
    header("Location: profile.php");
    exit();
}

// Fetch user details
$sql = "SELECT username, phone_number, position, remaining_leave_days FROM users WHERE id = '$user_id'";
$result = mysqli_query($condb, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: row;
            min-height: 100vh;
            background-color: #E5F6F8;
            color: #4A628A;
        }

        .menu-bar {
            width: 250px;
            background-color: #4A628A;
            color: white;
            position: fixed;
            top: 0;
            left: -250px;
            height: 100%;
            transition: left 1.4s;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            z-index: 1000;
        }

        .menu-bar.active {
            left: 0;
        }

        .menu-bar h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .menu-bar a, .menu-bar button {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            margin: 10px 0;
            width: 90%;
            text-align: center;
            background-color: #7AB2D3;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .menu-bar a:hover, .menu-bar button:hover {
            background-color: #B9E5E8;
            color: #4A628A;
        }

        .close-btn-outside {
        position: absolute;
        /* Positioned relative to the viewport */
        top: 20px;
        /* 20px from the top of the screen */
        left: 270px;
        /* Placed next to the menu bar (250px + margin) */
        background-color: #4A628A;
        /* Background color */
        color: white;
        /* Text color */
        border: none;
        /* Removes border */
        border-radius: 5px;
        /* Rounds the corners */
        padding: 10px 15px;
        /* Adds padding */
        cursor: pointer;
        /* Pointer cursor on hover */
        font-size: 16px;
        /* Adjusted font size */
        font-weight: bold;
        /* Bold font */
        z-index: 1100;
        /* Ensures it appears on top */
        opacity: 0;
        /* Initially hidden */
        transition: opacity 1s;
        /* Smooth transition for visibility */
    }

    .close-btn-outside.active {
        opacity: 1;
        /* Makes the button visible when the menu is active */
        pointer-events: auto;
    }

    .close-btn-outside:hover {
        background-color: #7AB2D3;
        /* Changes background color on hover */
    }
        .content {
            flex: 1;
            padding: 20px;
            margin-left: 0;
            transition: margin-left 1.4s;
        }

        .content.active {
            margin-left: 250px;
        }

        .toggle-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #4A628A;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            z-index: 1100;
            transition: opacity 0.3s;
        }

        .toggle-btn.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .toggle-btn:hover {
            background-color: #7AB2D3;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        form {
            width: 35%;
            margin: 20px auto;
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #DDD;
            border-radius: 5px;
        }

        button {
            background-color: #4A628A;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #7AB2D3;
        }

        p {
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        @media (max-width: 768px) {
            form {
                width: 80%;
            }
        }
    </style>
</head>
<body>
<button class="toggle-btn" id="toggleBtn" onclick="toggleMenu()">☰</button>
<button class="close-btn-outside" id="closeBtnOutside" onclick="toggleMenu()">&times;</button>
<div class="menu-bar" id="menuBar">
    <br>
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="user_dashboard.php">Leave Applications</a>
    <a href="admin_login.php">Admin Login</a>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    <a href="logout.php">Logout</a>
</div>

    <div class="content" id="content">
        <h1>User Profile</h1>

        <?php if (!empty($message)): ?>
            <p class="<?= strpos($message, 'Error') === false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number']); ?>" required>

            <label>Position:</label>
            <input type="text" name="position" value="<?= htmlspecialchars($user['position']); ?>" required>

            <label>Password (leave blank if unchanged):</label>
            <input type="password" name="password">

            <label>Remaining Leave Days:</label>
            <input type="number" value="<?= htmlspecialchars($user['remaining_leave_days']); ?>" disabled>

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <script>
    function toggleMenu() {
        const menuBar = document.getElementById('menuBar');
        const content = document.getElementById('content');
        const toggleBtn = document.getElementById('toggleBtn');
        const closeBtnOutside = document.getElementById('closeBtnOutside');
        const isMenuActive = menuBar.classList.toggle('active');

        content.classList.toggle('active', isMenuActive);
        toggleBtn.classList.toggle('hidden', isMenuActive);
        closeBtnOutside.classList.toggle('active', isMenuActive);
    }
</script>

</body>
</html>

<?php
mysqli_close($condb);
?>
