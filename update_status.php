<?php
include 'dbconnect.php';

// Get inputs
$id = $_POST['id'];
$action = $_POST['action'];

// Update status
$sql = "UPDATE holiday_requests SET status='$action' WHERE id=$id";
if (mysqli_query($condb, $sql)) {
    echo "Request has been $action.";
    echo "<br><a href='admin.php'>Back to Admin Panel</a>";
} else {
    echo "Error updating status: " . mysqli_error($condb);
}

mysqli_close($condb);
?>
