<?php
session_start();
// ASSISTED BY IAN LIM, gotta give credit where its due

// Include the database connection file
include '../../!! General/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userid = $_SESSION['user_id'];

// Prepare SQL query
$query = "SELECT username, bio, profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, "i", $userid);
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        
        if ($user_data) {
            echo json_encode([
                'status' => 'success',
                'data' => $user_data
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch profile']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?> 