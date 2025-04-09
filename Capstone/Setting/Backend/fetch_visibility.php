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

// Fetch the saved screen time reminder from the database
$query = "SELECT manage_visibility FROM users WHERE id = ?";
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
    // Bind the result to a variable
    mysqli_stmt_bind_result($stmt, $visibility);

    // Fetch the result
    if (mysqli_stmt_fetch($stmt)) {
        // Return "public" or "private"
        echo $visibility;
    } else {
        // Default value if no data is found
        echo "public"; 
    }
} else {
    echo "Failed to fetch visibility setting";
}

mysqli_stmt_close($stmt);
?>