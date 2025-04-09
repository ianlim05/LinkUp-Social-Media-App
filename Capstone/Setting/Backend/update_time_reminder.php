<?php
// Start the session to access user data
session_start();

// Include the database connection file
include '../../!! General/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, return an error message
    echo "User not logged in";
    exit(); // Stop the script
}

// Get the user ID from the session
$userid = $_SESSION['user_id'];

// Get the screen time reminder value from the POST request
$screenTimeReminder = $_POST['screen_time_reminder'] ?? '';

// Validate the screen time reminder value
if (!is_numeric($screenTimeReminder) || $screenTimeReminder < 1 || $screenTimeReminder > 1440) {
    // If invalid, return an error message
    echo "Total time must be between 00:01:00 and 24:00:00.";
    exit(); // Stop the script
}

// Convert total minutes into HH:MM:SS format
$hours = floor($screenTimeReminder / 60);
$minutes = $screenTimeReminder % 60;
$timeValue = sprintf("%02d:%02d:00", $hours, $minutes); // Format as HH:MM:SS

// Prepare the SQL query to update the screen time reminder
$query = "UPDATE users SET screen_time_reminder = ? WHERE id = ?";

// Create a prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    // If statement preparation fails, return an error message
    echo "Failed to prepare the SQL statement";
    exit();
}

// Bind the parameters to the statement
mysqli_stmt_bind_param($stmt, "si", $timeValue, $userid);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    // If successful, return a success message
    echo "success";
} else {
    // If failed, return an error message
    echo "Failed to update screen time reminder";
}

// Close the statement
mysqli_stmt_close($stmt);
?>