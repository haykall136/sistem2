<?php
session_start();
include 'dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users
$sql = "SELECT id, username, phone_number, position, remaining_leave_days FROM users";
$result = mysqli_query($condb, $sql);

// Handle update for remaining leave days
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $remaining_leave_days = intval($_POST['remaining_leave_days']);

    $update_sql = "UPDATE users SET remaining_leave_days = '$remaining_leave_days' WHERE id = '$user_id'";
    if (mysqli_query($condb, $update_sql)) {
        $message = "Remaining leave days updated successfully for User ID: $user_id.";
    } else {
        $message = "Error updating remaining leave days: " . mysqli_error($condb);
    }

    // Redirect to avoid form resubmission
    header("Location: admin_manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5F6F8;
            color: #4A628A;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #4A628A;
            margin-top: 20px;
        }

        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: #FFFFFF;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #DDD;
        }

        th {
            background-color: #7AB2D3;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #B9E5E8;
        }

        tr:hover {
            background-color: #DFF2EB;
        }

        form {
            display: inline-block;
        }

        input[type="number"] {
            padding: 5px;
            width: 60px;
            border: 1px solid #DDD;
            border-radius: 5px;
        }

        button {
            background-color: #4A628A;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #7AB2D3;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: white;
            background-color: #4A628A;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        a:hover {
            background-color: #7AB2D3;
        }

        .success {
            color: green;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
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
            padding: 12px 24.9px;
            margin: 10px 0;
            width: 70%;
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
            color: #4A628A;
            font-size: 2em;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2em;
            color: #4A628A;
        }

        .chart-container {
            width: 60%;
            max-width: 800px;
            margin: 20px auto;
        }

        @media (max-width: 768px) {
            .menu-bar {
                width: 200px;
                left: -200px;
            }

            .menu-bar.active {
                left: 0;
            }

            .content.active {
                margin-left: 200px;
            }

            .chart-container {
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
        <a href="admin_index.php">Home</a>
        <br>
        <a href="admin_panel.php">Staff Applications</a>
        <a href="admin_manage_users.php">Manage Staff</a>
        <a href="admin_login.php">Admin Login</a>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content" id="content">
    <h1>Manage Staff</h1>

    <!-- Display Message -->
    <?php if (isset($message)): ?>
        <p class="<?= strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <!-- User List Table -->
    <table>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Phone Number</th>
            <th>Position</th>
            <th>Remaining Leave Days</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['username']); ?></td>
                <td><?= htmlspecialchars($row['phone_number']); ?></td>
                <td><?= htmlspecialchars($row['position']); ?></td>
                <td><?= $row['remaining_leave_days']; ?></td>
                <td>
                    <!-- Update Remaining Leave Days Form -->
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                        <input type="number" name="remaining_leave_days" value="<?= $row['remaining_leave_days']; ?>" min="0" required>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    </div>

    <div style="text-align: center;">
        <a href="admin_index.php">Back</a>
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
