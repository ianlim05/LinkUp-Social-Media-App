<?php
// unblock_user.php

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
$loggedInUserId = $_SESSION['user_id'];

// Get the blocked user ID from the POST request
$blockedUserId = $_POST['blockedUserId'];

// Validate the blocked user ID
if (empty($blockedUserId)) {
    echo "Blocked user ID is required";
    exit();
}

// Delete both rows from the block table
$query = "
    DELETE FROM block 
    WHERE (UserID = ? AND BlockedUserId = ?)
    OR (UserID = ? AND BlockedUserId = ?)
";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    // If statement preparation fails, return an error message
    echo "Failed to prepare the SQL statement";
    exit();
}

// Bind the parameters to the statement
mysqli_stmt_bind_param($stmt, "iiii", $loggedInUserId, $blockedUserId, $blockedUserId, $loggedInUserId);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    // Check if any rows were affected
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Return success message
        echo "User unblocked successfully";
    } else {
        // If no rows were affected, return an error message
        echo "Failed to unblock user: No matching records found";
    }
} else {
    // If execution fails, return an error message
    echo "Failed to execute the SQL statement";
}

// Close the statement
mysqli_stmt_close($stmt);
?>