<?php
include 'dbconnect.php';

// Get user input
$user_name = $_POST['user_name'];
$holiday_date = $_POST['holiday_date'];

// Insert into database
$sql = "INSERT INTO holiday_requests (user_name, holiday_date, status) VALUES ('$user_name', '$holiday_date', 'Pending')";
if (mysqli_query($condb, $sql)) {
    echo "Holiday request submitted successfully.";
    echo "<br><a href='index.php'>Back</a>";
} else {
    echo "Error: " . mysqli_error($condb);
}

mysqli_close($condb);
?>
