<?php
session_start();
include 'dbconnect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Validate the user_id parameter
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "User ID is not specified.";
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user details
$user_sql = "SELECT username, remaining_leave_days FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($condb, $user_sql);
if (mysqli_num_rows($user_result) == 0) {
    echo "Invalid User ID.";
    exit();
}
$user = mysqli_fetch_assoc($user_result);

// Handle dropdown selection and result updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Handle dropdown selection
    if (isset($_POST['result'])) {
        $result = $_POST['result'];
        $admin_reason = isset($_POST['admin_reason']) ? mysqli_real_escape_string($condb, $_POST['admin_reason']) : '';

        if ($result === 'DILULUSKAN') {
            // Update the result and deduct leave days
            $update_result_sql = "UPDATE holiday_requests 
                                  SET result = 'DILULUSKAN' 
                                  WHERE id = '$request_id'";
            mysqli_query($condb, $update_result_sql);

            $update_user_sql = "UPDATE users 
                                SET remaining_leave_days = remaining_leave_days - 1 
                                WHERE id = '$user_id' AND remaining_leave_days > 0";
            mysqli_query($condb, $update_user_sql);
        } elseif ($result === 'TIDAK DILULUSKAN') {
            // Update the result and admin reason only
            $update_result_sql = "UPDATE holiday_requests 
                                  SET result = 'TIDAK DILULUSKAN', admin_reason = '$admin_reason' 
                                  WHERE id = '$request_id'";
            mysqli_query($condb, $update_result_sql);
        }
    }

    // Handle status updates via Approve, Reject, Reset buttons
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Update only the status without deducting leave days
        $update_request_sql = "UPDATE holiday_requests SET status = '$action' WHERE id = '$request_id'";
        mysqli_query($condb, $update_request_sql);
    }

    header("Location: view_user_applications.php?user_id=$user_id");
    exit();
}

// Fetch and group user's holiday applications by month
$applications_sql = "SELECT id, start_date, end_date, application_date, reason, status, result, admin_reason 
                     FROM holiday_requests 
                     WHERE user_id = '$user_id' 
                     ORDER BY start_date DESC";
$applications_result = mysqli_query($condb, $applications_sql);

$applications_by_month = [];
while ($row = mysqli_fetch_assoc($applications_result)) {
    $month_year = date('F Y', strtotime($row['start_date'])); // e.g., "January 2023"
    if (!isset($applications_by_month[$month_year])) {
        $applications_by_month[$month_year] = [];
    }
    $applications_by_month[$month_year][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Applications</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #DFF2EB;
            margin: 0;
            padding: 0;
        }

        h1, h3 {
            text-align: center;
            color: #4A628A;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #7AB2D3;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #B9E5E8;
        }

        tr:hover {
            background-color: #E6F7F8;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .approve {
            background-color: #4A628A;
            color: white;
        }

        .reject {
            background-color: #4A628A;
            color: white;
        }

        .reset {
            background-color: #577BC1;
            color: white;
        }

        .month-header {
            text-align: center;
            margin-top: 30px;
            font-size: 20px;
            font-weight: bold;
            color: #4A628A;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            text-align: center;
            text-decoration: none;
            font-size: 18px;
            color: white;
            background-color: #4A628A;
            padding: 10px 20px;
            border-radius: 5px;
        }

        a:hover {
            background-color: #7AB2D3;
        }

        textarea {
            width: 100%;
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px;
        }

        select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Applications of <?= htmlspecialchars($user['username']); ?></h1>
    <h3>Remaining Leave Days: <?= $user['remaining_leave_days']; ?></h3>

    <?php foreach ($applications_by_month as $month_year => $applications): ?>
    <div class="month-header"><?= $month_year; ?></div>
    <table>
        <tr>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Application Date</th>
            <th>Leave Reason</th>
            <th>By HOD</th>
            <th>HOD Status</th>
            <th>By CEO</th>
            <th>Reason</th>
        </tr>
        <?php foreach ($applications as $row): ?>
            <tr>
                <td><?= date('l, d/m/Y', strtotime($row['start_date'])); ?></td>
                <td><?= date('l, d/m/Y', strtotime($row['end_date'])); ?></td>
                <td><?= date('l, d/m/Y', strtotime($row['application_date'])); ?></td>
                <td><?= htmlspecialchars($row['reason']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="action" value="Approved">
                        <button type="submit" class="action-btn approve" <?= (!empty($row['result'])) ? 'disabled' : '' ?>>Approve</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="action" value="Rejected">
                        <button type="submit" class="action-btn reject" <?= (!empty($row['result'])) ? 'disabled' : '' ?>>Reject</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="action" value="Pending">
                        <button type="submit" class="action-btn reset" <?= (!empty($row['result'])) ? 'disabled' : '' ?>>Reset</button>
                    </form>
                </td>
                <td><?= $row['status'] ?></td>
                <td>
                    <?php if (empty($row['result'])): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                            <select name="result" required>
                                <option value="">Select</option>
                                <option value="DILULUSKAN">DILULUSKAN</option>
                                <option value="TIDAK DILULUSKAN">TIDAK DILULUSKAN</option>
                            </select>
                            <textarea name="admin_reason" placeholder="Reason if Rejected" rows="2" cols="20" style="display:none;"></textarea>
                            <button type="submit" class="action-btn" style="background-color: #FFA500; color: white;">Submit</button>
                        </form>
                    <?php else: ?>
                        <?= htmlspecialchars($row['result']); ?>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['admin_reason']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>


    <div style="text-align: center;">
        <a href="admin_panel.php">Back</a>
    </div>

    <script>
        // Toggle visibility of the admin reason textarea based on dropdown selection
        document.querySelectorAll('select[name="result"]').forEach(select => {
            select.addEventListener('change', function () {
                const reasonField = this.parentElement.querySelector('textarea[name="admin_reason"]');
                if (this.value === 'TIDAK DILULUSKAN') {
                    reasonField.style.display = 'block';
                } else {
                    reasonField.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
mysqli_close($condb);
?>
