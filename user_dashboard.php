<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the remaining leave days
$user_sql = "SELECT remaining_leave_days FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($condb, $user_sql);
$user_data = mysqli_fetch_assoc($user_result);
$remaining_leave_days = $user_data['remaining_leave_days'];

// Handle holiday application submission
if (isset($_POST['apply_leave'])) {
    if ($remaining_leave_days > 0) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $reason = mysqli_real_escape_string($condb, $_POST['reason']);

        // Validate dates
        if (strtotime($start_date) && strtotime($end_date) && $start_date <= $end_date) {
            // Insert new holiday request
            $insert_sql = "INSERT INTO holiday_requests (user_id, start_date, end_date, reason, status, result) 
                           VALUES ('$user_id', '$start_date', '$end_date', '$reason', 'Pending', '')";
            if (mysqli_query($condb, $insert_sql)) {
                echo "<script>alert('Application submitted successfully!'); window.location='user_dashboard.php';</script>";
            } else {
                echo "Error: " . mysqli_error($condb);
            }
        } else {
            echo "<script>alert('Invalid start or end date.');</script>";
        }
    } else {
        echo "<script>alert('You do not have enough remaining leave days to apply for a leave.');</script>";
    }
}

// Handle form submission for updating reason
if (isset($_POST['update_reason'])) {
    $request_id = $_POST['request_id'];
    $new_reason = mysqli_real_escape_string($condb, $_POST['new_reason']);

    $update_sql = "UPDATE holiday_requests SET reason = '$new_reason' WHERE id = '$request_id' AND user_id = '$user_id'";
    if (mysqli_query($condb, $update_sql)) {
        echo "<script>alert('Reason updated successfully!'); window.location='user_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($condb);
    }
}

// Handle deletion of an application
if (isset($_POST['delete_application'])) {
    $request_id = $_POST['request_id'];

    $delete_sql = "DELETE FROM holiday_requests WHERE id = '$request_id' AND user_id = '$user_id'";
    if (mysqli_query($condb, $delete_sql)) {
        echo "<script>alert('Application deleted successfully!'); window.location='user_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($condb);
    }
}

// Fetch user's holiday application history grouped by month
$sql = "SELECT id, start_date, end_date, application_date, reason, status, result 
        FROM holiday_requests 
        WHERE user_id = '$user_id' 
        ORDER BY start_date DESC";
$result = mysqli_query($condb, $sql);

// Organize data by month
$history_by_month = [];
while ($row = mysqli_fetch_assoc($result)) {
    $month_year = date('F Y', strtotime($row['start_date'])); // Get month and year (e.g., January 2023)
    if (!isset($history_by_month[$month_year])) {
        $history_by_month[$month_year] = [];
    }
    $history_by_month[$month_year][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E5F6F8;
            color: #4A628A;
            margin: 0;
            padding: 0;
        }

        h1,
        h2,
        h3 {
            text-align: center;
            color: #4A628A;
        }

        .month-container {
            width: 30%;
            margin: 20px auto;
            padding: 15px;
            background-color: #4A628A;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, background-color 0.3s;
        }

        .month-container:hover {
            background-color:rgb(255, 255, 255);
            transform: scale(1.02);
        }

        .month-header {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            text-align: center;
        }

        .history-table {
            display: none;
            width: 90%;
            margin: 10px auto;
            border-collapse: collapse;
            background-color: #FFFFFF;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
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

        .toggle-icon {
            float: right;
            font-size: 16px;
            transition: transform 0.3s;
        }

        .month-container.collapsed .toggle-icon {
            transform: rotate(90deg);
        }

        .form-container {
            background-color:rgb(227, 254, 247);
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-containerHistory {
            background-color: #B9E5E8;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-container input,
        .form-container textarea,
        .form-container button {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #4A628A;
            width: 80%;
            max-width: 400px;
        }

        .form-container button {
            background-color: #4A628A;
            color: white;
            cursor: pointer;
            border: none;
        }

        .form-container button:hover {
            background-color: #7AB2D3;
        }

        .month-header {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 30px;
            color: #4A628A;
        }

        .alert {
            text-align: center;
            color: red;
            font-weight: bold;
        }

        .logout {
            text-align: center;
            margin: 20px;
        }

        .logout a {
            color: white;
            background-color: #4A628A;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .logout a:hover {
            background-color: #7AB2D3;
        }

        textarea {
            resize: none;
        }

        .menu-bar {
            width: 250px;
            /* Width of the menu bar */
            background-color: #4A628A;
            /* Menu bar background color */
            color: white;
            /* Text color inside the menu bar */
            position: fixed;
            /* Sticks the menu to the side */
            top: 0;
            /* Aligns to the top */
            left: -250px;
            /* Initially hides the menu by positioning it off-screen */
            height: 100%;
            /* Full height of the viewport */
            transition: left 1.4s;
            /* Smooth transition when menu slides in/out */
            display: flex;
            /* Aligns items inside the menu vertically */
            flex-direction: column;
            /* Stacks items vertically */
            align-items: center;
            /* Centers items horizontally */
            padding: 20px 0;
            /* Adds vertical padding */
            z-index: 1000;
            /* Ensures menu appears on top */
        }

        .menu-bar.active {
            left: 0;
            /* Moves the menu bar into view when active */
        }

        .menu-bar h2 {
            margin-bottom: 20px;
            /* Adds spacing below the header text */
            font-size: 1.5em;
            /* Increases the font size of the header */
        }

        .menu-bar a,
        .menu-bar button {
            display: block;
            /* Makes links and buttons block elements */
            color: white;
            /* Text color */
            text-decoration: none;
            /* Removes underline from links */
            padding: 12px 24.9px;
            /* Padding inside the buttons/links */
            margin: 10px 0;
            /* Adds spacing between links/buttons */
            width: 69.9%;
            /* Makes buttons/links fill 90% of the menu width */
            text-align: center;
            /* Centers the text inside buttons/links */
            background-color: #7AB2D3;
            /* Background color of links/buttons */
            border: none;
            /* Removes border from buttons */
            border-radius: 5px;
            /* Rounds the corners of buttons */
            font-weight: bold;
            /* Makes text bold */
            cursor: pointer;
            /* Changes cursor to pointer on hover */
            transition: background-color 0.3s;
            /* Smooth transition for hover effect */
        }

        .menu-bar a:hover,
        .menu-bar button:hover {
            background-color: #B9E5E8;
            /* Lighter background color on hover */
            color: #4A628A;
            /* Changes text color on hover */
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

        .toggle-btn {
            position: absolute;
            /* Positions the button relative to the viewport */
            top: 20px;
            /* Positions it 20px from the top */
            left: 20px;
            /* Positions it 20px from the left */
            background-color: #4A628A;
            /* Background color of the button */
            color: white;
            /* Text color */
            border: none;
            /* Removes border */
            border-radius: 5px;
            /* Rounds the corners */
            padding: 10px 20px;
            /* Adds padding inside the button */
            cursor: pointer;
            /* Changes cursor to pointer on hover */
            z-index: 1100;
            /* Ensures it appears above other elements */
            transition: opacity 0.3s;
            /* Smooth transition for opacity changes */
        }

        .toggle-btn.hidden {
            opacity: 0;
            /* Hides the button when the menu is active */
            pointer-events: none;
            /* Disables interactions with the button */
        }

        .toggle-btn:hover {
            background-color: #7AB2D3;
            /* Changes background color on hover */
        }

        .content {
            flex: 1;
            /* Makes content take up remaining space */
            padding: 20px;
            /* Adds padding around content */
            margin-left: 0;
            /* No margin by default */
            transition: margin-left 1.4s;
            /* Smooth transition for content shift */
        }

        .content.active {
            margin-left: 250px;
            /* Shifts content to the right when menu is active */
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
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></h1>
        <h2>Your Position: <?= htmlspecialchars($_SESSION['position']); ?></h2>
        <h3>Remaining Leave Days: <?= $remaining_leave_days; ?></h3>

        <!-- Holiday Application Form -->
        <div class="form-container">
            <h2>Apply for a Leave</h2>
            <form method="POST">
                <label for="start_date">Start Date:</label><br>
                <input type="date" name="start_date" id="start_date" required><br><br>

                <label for="end_date">End Date:</label><br>
                <input type="date" name="end_date" id="end_date" required><br><br>

                <label for="reason">Reason for Leave:</label><br>
                <textarea name="reason" id="reason" rows="4" placeholder="Explain why you need this leave"
                    required></textarea><br><br>

                <button type="submit" name="apply_leave" <?= $remaining_leave_days <= 0 ? 'disabled' : '' ?>>Apply</button>
            </form>
            <?php if ($remaining_leave_days <= 0): ?>
                <p class="alert">You do not have enough remaining leave days to apply for a leave.</p>
            <?php endif; ?>
        </div>

        <div class="form-containerHistory">
        <!-- Holiday Application History -->
        <h2>Your Leave Application History</h2>
        <br>

        <?php foreach ($history_by_month as $month_year => $applications): ?>
            <div class="month-container collapsed"
                onclick="toggleHistory('<?= str_replace(' ', '_', $month_year); ?>', this)">
                <span class="month-header"><?= $month_year; ?></span>
                <span class="toggle-icon">▶</span>
            </div>
            <table class="history-table" id="<?= str_replace(' ', '_', $month_year); ?>">
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Application Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Result</th>
                    <th>Delete</th>
                    <th>Download PDF</th>
                </tr>
                <?php foreach ($applications as $row): ?>
                    <tr>
                        <td><?= date('l, d F Y', strtotime($row['start_date'])); ?></td>
                        <td><?= date('l, d F Y', strtotime($row['end_date'])); ?></td>
                        <td><?= date('l, d F Y', strtotime($row['application_date'])); ?></td>
                        <td><?= htmlspecialchars($row['reason']); ?></td>
                        <td><?= $row['status']; ?></td>
                        <td><?= htmlspecialchars($row['result']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this application?');">
                                <input type="hidden" name="request_id" value="<?= $row['id']; ?>">
                                <button type="submit" name="delete_application" style="color:red;">Delete</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'Approved'): ?>
                                <a href="generate_pdf.php?request_id=<?= $row['id']; ?>" target="_blank">Download PDF</a>
                            <?php else: ?>
                                Not Available
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
        </div>              
        <br>          

        <!-- Logout Button -->
        <div class="logout">
            <a href="index.php">Back</a>
        </div>
    </div>

    <!-- JavaScript for confirmation dialog -->
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

        function toggleHistory(monthId, container) {
            const table = document.getElementById(monthId);
            const isCollapsed = table.style.display === "none" || table.style.display === "";

            // Toggle display of the table
            table.style.display = isCollapsed ? "table" : "none";

            // Toggle class for the container
            container.classList.toggle('collapsed', !isCollapsed);
        }
    </script>

</body>

</html>

<?php
mysqli_close($condb);
?>