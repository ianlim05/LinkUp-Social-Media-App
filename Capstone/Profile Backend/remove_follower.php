<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include "../!! General/database.php";

// 开启错误日志
error_log("Remove follower request received: " . json_encode($_POST));

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(["success" => false, "message" => "Please log in first"]);
    exit;
}

// 检查必要参数
if (!isset($_POST['follower_id'])) {
    error_log("Missing follower_id parameter");
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

$user_id = $_SESSION['user_id']; // 当前登录用户（被关注者
$follower_id = $_POST['follower_id']; // 要移除的关注者

// 删除关注关系
$delete_query = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ?");
if (!$delete_query) {
    error_log("Error preparing delete query: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Database error"]);
    exit;
}

$delete_query->bind_param("ii", $follower_id, $user_id);
if (!$delete_query->execute()) {
    error_log("Error executing delete query: " . $delete_query->error);
    echo json_encode(["success" => false, "message" => "Failed to remove follower"]);
    exit;
}

if ($delete_query->affected_rows === 0) {
    error_log("No follower relationship found to remove");
    echo json_encode(["success" => false, "message" => "This user is not following you"]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Follower removed successfully"
]); 