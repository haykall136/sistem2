<?php
include 'dbconnect.php';

// Fetch holiday requests with user details
$sql = "SELECT holiday_requests.id, users.username, holiday_requests.holiday_date, holiday_requests.status
        FROM holiday_requests
        JOIN users ON holiday_requests.user_id = users.id";
$result = mysqli_query($condb, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Holiday Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['username'] ?></td>
                <td><?= $row['holiday_date'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <form action="update_status.php" method="POST" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="Approved">Approve</button>
                        <button type="submit" name="action" value="Rejected">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
