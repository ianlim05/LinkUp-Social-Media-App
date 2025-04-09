<?php
// Start the session to access user data
session_start();

// Include the database connection file
include '../../!! General/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, return an error message
    echo "User not logged in";
    exit(); // Stop the script
}

// Get the user ID from the session
$userid = $_SESSION['user_id'];
error_log("User ID: " . $userid); // Debug log

// Get the visibility value from the POST request
$visibility = $_POST['visibility'] ?? '';
error_log("Received visibility: " . $visibility); // Debug log

// Check if the visibility value is valid (either 'public' or 'private')
if ($visibility !== 'public' && $visibility !== 'private') {
    // If invalid, return an error message even though it shouldn't have happen since is hard coded
    echo "Invalid visibility value";
    exit(); // Stop the script
}

// Prepare the SQL query to update the visibility setting
$query = "UPDATE users SET manage_visibility = ? WHERE id = ?";
error_log("SQL Query: " . $query); // Debug log

// Create a prepared statement to prevent SQL injection
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($conn)); // Debug log
    echo "Database error: " . mysqli_error($conn);
    exit();
}

// Bind the parameters to the statement
mysqli_stmt_bind_param($stmt, "si", $visibility, $userid);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    // If successful, return a success message
    error_log("Update successful"); // Debug log
    echo "success";
} else {
    // If failed, return an error message
    error_log("Update failed: " . mysqli_stmt_error($stmt)); // Debug log
    echo "Failed to update visibility setting: " . mysqli_stmt_error($stmt);
}

// Close the statement
mysqli_stmt_close($stmt);
?>