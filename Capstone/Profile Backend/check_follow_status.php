<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include "../!! General/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

if (!isset($_GET['following_id'])) {
    echo json_encode(["success" => false, "message" => "No user specified"]);
    exit;
}

$follower_id = $_SESSION['user_id'];
$following_id = $_GET['following_id'];

// 检查用户是否为私密账号
$visibility_query = $conn->prepare("SELECT visibility FROM users WHERE id = ?");
$visibility_query->bind_param("i", $following_id);
$visibility_query->execute();
$visibility_result = $visibility_query->get_result();
$visibility_data = $visibility_result->fetch_assoc();
$is_private = $visibility_data['visibility'] === 'private';

// 检查关注状态
$status_query = $conn->prepare("SELECT FollowStatus FROM follow WHERE FollowerID = ? AND FollowingID = ?");
$status_query->bind_param("ii", $follower_id, $following_id);
$status_query->execute();
$status_result = $status_query->get_result();
$status_data = $status_result->fetch_assoc();

$is_following = false;
$is_requested = false;

if ($status_data) {
    $is_following = $status_data['FollowStatus'] === 'approved';
    $is_requested = $status_data['FollowStatus'] === 'pending';
}

echo json_encode([
    "success" => true,
    "is_following" => $is_following,
    "is_requested" => $is_requested,
    "is_private" => $is_private
]); 