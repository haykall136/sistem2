<?php
session_start();
include 'dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch user leave data for graph
$sql = "SELECT u.username, 
               COUNT(hr.id) AS total_applications, 
               SUM(CASE WHEN hr.status = 'Approved' THEN 1 ELSE 0 END) AS total_approved 
        FROM users u
        LEFT JOIN holiday_requests hr ON u.id = hr.user_id
        GROUP BY u.id
        ORDER BY total_applications DESC";
$result = mysqli_query($condb, $sql);

$user_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $user_data[] = $row;
}

// Split user data into chunks of 4 for multiple graphs
$user_chunks = array_chunk($user_data, 4);

// Check if username exists in session
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Index</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .menu-bar a,
        .menu-bar button {
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

        .menu-bar a:hover,
        .menu-bar button:hover {
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

        .holiday-table-container {
            width: 90%;
            margin: 20px auto;
            text-align: center;
        }

        .holiday-table-container h2 {
            color: #4A628A;
            margin-bottom: 10px;
        }

        table {
            width: 85%;
            border-collapse: collapse;
            margin: 10px auto;
        }

        thead th {
            background-color: #7AB2D3;
            color: white;
            padding: 10px;
            text-align: center;
        }

        tbody td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .leave-info p {
            text-align: left;
            color: #4A628A;
            margin: 10px auto;
            font-size: 1.2em;
            width: 90%;
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
        <br><br><br>
        <div class="holiday-table-container">
            <h2>Public Holidays & Leave Information</h2>
            <table>
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>TARIKH</th>
                        <th>HARI</th>
                        <th>PERKARA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>31/3/2025</td>
                        <td>ISNIN</td>
                        <td rowspan="4">HARI RAYA AIDILFITRI</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>1/4/2025</td>
                        <td>SELASA</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>2/4/2025</td>
                        <td>RABU</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>3/4/2025</td>
                        <td>KHAMIS</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>27/4/2025</td>
                        <td>AHAD</td>
                        <td>KEPUTERAAN SULTAN TERENGGANU</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>1/5/2025</td>
                        <td>KHAMIS</td>
                        <td>HARI PEKERJA</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>2/6/2025</td>
                        <td>ISNIN</td>
                        <td>HARI AGONG</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>8/6/2025</td>
                        <td>AHAD</td>
                        <td rowspan="2">HARI RAYA AIDILADHA</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>9/6/2025</td>
                        <td>ISNIN</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>31/8/2025</td>
                        <td>AHAD</td>
                        <td>HARI KEMERDEKAAN</td>
                    </tr>
                    <tr>
                        <td>11</td>
                        <td>16/9/2025</td>
                        <td>SELASA</td>
                        <td>HARI MALAYSIA</td>
                    </tr>
                </tbody>
            </table>
            <div class="leave-info">
                <br>
                <table>
                    <thead>
                        <tr>
                            <th>NOTA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <p>* <strong>CUTI TAHUNAN:</strong><br><br>
                                    12 HARI</p><br>
                                <p>* <strong>CUTI SAKIT (MC):</strong><br><br>
                                    14 HARI - jika bekerja kurang 2 tahun<br>18 HARI - jika bekerja 2 tahun tetapi
                                    kurang 5 tahun
                            </td>
                            </p>
                        </tr>
                    </tbody>
                </table>
            </div>
            <br><br><br>
            <h1>Admin Dashboard</h1>
            <p>Below are charts of leave applications and approvals by users.</p>

            <?php foreach ($user_chunks as $index => $chunk): ?>
                <div class="chart-container">
                    <canvas id="userChart<?= $index; ?>"></canvas>
                </div>
            <?php endforeach; ?>
        </div>
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

        // Chart.js script for rendering multiple bar charts
        <?php foreach ($user_chunks as $index => $chunk): ?>
            const ctx<?= $index; ?> = document.getElementById('userChart<?= $index; ?>').getContext('2d');
            new Chart(ctx<?= $index; ?>, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($chunk, 'username')); ?>,
                    datasets: [
                        {
                            label: 'Total Applications',
                            data: <?= json_encode(array_column($chunk, 'total_applications')); ?>,
                            backgroundColor: '#4A628A',
                        },
                        {
                            label: 'Total Approved',
                            data: <?= json_encode(array_column($chunk, 'total_approved')); ?>,
                            backgroundColor: '#7AB2D3',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                },
            });
        <?php endforeach; ?>
    </script>
</body>

</html>