<?php
include "../!! General/database.php";
session_start();

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("Report post request received");

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report a post']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['post_id']) || !isset($_POST['reason'])) {
    error_log("Missing fields. post_id: " . (isset($_POST['post_id']) ? $_POST['post_id'] : 'not set') . 
              ", reason: " . (isset($_POST['reason']) ? $_POST['reason'] : 'not set'));
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$reporter_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$reason = mysqli_real_escape_string($conn, $_POST['reason']);

error_log("Processing report - Reporter ID: $reporter_id, Post ID: $post_id, Reason: $reason");

// Check if user has already reported this post
$check_query = "SELECT * FROM reportedpost WHERE PostID = ? AND UserID = ?";
$stmt = mysqli_prepare($conn, $check_query);
if (!$stmt) {
    error_log("Error preparing check query: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $post_id, $reporter_id);
if (!mysqli_stmt_execute($stmt)) {
    error_log("Error executing check query: " . mysqli_stmt_error($stmt));
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    error_log("User has already reported this post");
    echo json_encode(['success' => false, 'message' => 'You have already reported this post']);
    exit();
}

// Insert the report
$insert_query = "INSERT INTO reportedpost (PostID, UserID, Reason) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
if (!$stmt) {
    error_log("Error preparing insert query: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

mysqli_stmt_bind_param($stmt, "iis", $post_id, $reporter_id, $reason);

if (mysqli_stmt_execute($stmt)) {
    error_log("Report successfully inserted");
    echo json_encode(['success' => true, 'message' => 'Post reported successfully']);
} else {
    error_log("Error inserting report: " . mysqli_stmt_error($stmt));
    echo json_encode(['success' => false, 'message' => 'Failed to report post: ' . mysqli_stmt_error($stmt)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?> 