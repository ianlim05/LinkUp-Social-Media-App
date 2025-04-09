<?php
session_start();
include "../../!! General/database.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../Homepage/Homepage.php?report=invalid_method");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Homepage/Homepage.php?report=not_logged_in");
    exit();
}

$postId = intval($_POST['postId']);
$reason = trim($_POST['reason']);
$userId = $_SESSION['user_id']; // Get the reporting user's ID

if (empty($reason)) {
    header("Location: ../../Homepage/Homepage.php?report=empty_reason&postId=".$postId);
    exit();
}

// Check if this user has already reported this post
$checkQuery = "SELECT ReportPostID FROM reportedpost WHERE PostID = ? AND UserID = ? LIMIT 1";
$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
    error_log("Check prepare failed: ".mysqli_error($conn));
    header("Location: ../../Homepage/Homepage.php?report=db_error");
    exit();
}

mysqli_stmt_bind_param($checkStmt, "ii", $postId, $userId);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    mysqli_stmt_close($checkStmt);
    header("Location: ../../Homepage/Homepage.php?report=already_reported&postId=".$postId);
    exit();
}
mysqli_stmt_close($checkStmt);

// Insert new report with all required fields
$query = "INSERT INTO reportedpost (PostID, UserID, Reason, ReportDate) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    error_log("Report prepare failed: ".mysqli_error($conn));
    header("Location: ../../Homepage/Homepage.php?report=db_error");
    exit();
}

mysqli_stmt_bind_param($stmt, "iis", $postId, $userId, $reason);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: ../../Homepage/Homepage.php?report=success");
} else {
    error_log("Report execute failed: ".mysqli_error($conn));
    mysqli_stmt_close($stmt);
    header("Location: ../../Homepage/Homepage.php?report=db_error");
}
exit();
?>