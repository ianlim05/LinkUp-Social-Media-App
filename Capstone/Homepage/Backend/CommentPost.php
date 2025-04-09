<?php
session_start();
include "../../!! General/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Homepage/Homepage.php?comment_error=not_logged_in");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../Homepage/Homepage.php?comment_error=invalid_method");
    exit();
}

if (!isset($_POST['postId']) || !isset($_POST['comment'])) {
    header("Location: ../../Homepage/Homepage.php?comment_error=missing_data");
    exit();
}

$userId = $_SESSION['user_id'];
$postId = $_POST['postId'];
$comment = trim($_POST['comment']);

// Check if comment is empty
if (empty($comment)) {
    header("Location: ../../Homepage/Homepage.php?comment_error=empty");
    exit();
}

// Get all blacklisted words from filter table
$filter_query = $conn->prepare("SELECT BlacklistedWord FROM filter WHERE BlacklistedWord IS NOT NULL");
$filter_query->execute();
$filter_result = $filter_query->get_result();

$blacklistedWords = [];
while ($row = $filter_result->fetch_assoc()) {
    if (!empty($row['BlacklistedWord'])) {
        $blacklistedWords[] = strtolower($row['BlacklistedWord']);
    }
}

// Check comment against blacklisted words
$commentLower = strtolower($comment);
foreach ($blacklistedWords as $word) {
    if (preg_match("/\b" . preg_quote($word, '/') . "\b/i", $comment)) {
        header("Location: ../../Homepage/Homepage.php?comment_error=inappropriate");
        exit();
    }
}

// Check if post exists
$check_post = $conn->prepare("SELECT UserID FROM post WHERE PostID = ?");
$check_post->bind_param("i", $postId);
$check_post->execute();
$post_result = $check_post->get_result();

if ($post_result->num_rows === 0) {
    header("Location: ../../Homepage/Homepage.php?comment_error=post_not_found");
    exit();
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (PostID, UserID, comment, date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $postId, $userId, $comment);

if ($stmt->execute()) {
    // Success - redirect back to homepage
    header("Location: ../../Homepage/Homepage.php?comment_success=true");
} else {
    header("Location: ../../Homepage/Homepage.php?comment_error=db_error");
}

$stmt->close();
$filter_query->close();
$conn->close();
?>