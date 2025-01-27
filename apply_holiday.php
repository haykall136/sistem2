<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $holiday_date = $_POST['holiday_date'];

    // Insert holiday request into the database
    $sql = "INSERT INTO holiday_requests (user_id, holiday_date, status) VALUES ('$user_id', '$holiday_date', 'Pending')";
    if (mysqli_query($condb, $sql)) {
        echo "Holiday request submitted successfully.";
    } else {
        echo "Error: " . mysqli_error($condb);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Holiday</title>
</head>
<body>
    <h1>Apply for a Holiday</h1>
    <p>Welcome, <?= $_SESSION['username']; ?>!</p>
    <form method="POST">
        <label for="holiday_date">Select Holiday Date:</label>
        <input type="date" name="holiday_date" id="holiday_date" required>
        <br><br>
        <button type="submit">Apply</button>
    </form>
</body>
</html>
 