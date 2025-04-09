<?php
// fetch_blocked_users.php

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

// Fetch the blocked users from the database
$query = "
    SELECT u.id, u.username 
    FROM block b
    JOIN users u ON b.BlockedUserId = u.id
    WHERE b.UserID = ?
";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    // If statement preparation fails, return an error message
    echo "Failed to prepare the SQL statement";
    exit();
}

// Bind the parameters to the statement
mysqli_stmt_bind_param($stmt, "i", $userid);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    // Bind the result to variables
    mysqli_stmt_bind_result($stmt, $blockedUserId, $blockedUsername);

    // Fetch the results and store them in an array
    $blockedUsers = [];
    while (mysqli_stmt_fetch($stmt)) {
        $blockedUsers[] = "$blockedUsername:$blockedUserId"; // Format: "username:id"
    }

    // Return the blocked users as a comma-separated string
    echo implode(",", $blockedUsers);
} else {
    // If execution fails, return an error message
    echo "Failed to fetch blocked users";
}

// Close the statement
mysqli_stmt_close($stmt);
?>