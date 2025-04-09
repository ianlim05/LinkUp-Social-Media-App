<?php
include "../!! General/database.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report a user']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['user_id']) || !isset($_POST['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$reporter_id = $_SESSION['user_id'];
$reported_user_id = $_POST['user_id'];
$reason = mysqli_real_escape_string($conn, $_POST['reason']);

// Check if user has already reported this user
$check_query = "SELECT * FROM reporteduser WHERE UserID = ? AND ReportedUserID = ?";
$stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($stmt, "ii", $reporter_id, $reported_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reported this user']);
    exit();
}

// Insert the report
$insert_query = "INSERT INTO reporteduser (UserID, ReportedUserID, Reason) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $insert_query);
mysqli_stmt_bind_param($stmt, "iis", $reporter_id, $reported_user_id, $reason);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'User reported successfully']);
} else {
    // Log the error for debugging
    error_log("Error reporting user: " . mysqli_stmt_error($stmt));
    echo json_encode(['success' => false, 'message' => 'Failed to report user: ' . mysqli_stmt_error($stmt)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?> 