<?php
// 开启错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
include "../!! General/database.php";

// 开启错误日志
error_log("Follow request received: " . json_encode($_POST));

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(["success" => false, "message" => "Please login first"]);
    exit;
}

// 检查必要参数
if (!isset($_POST['following_id']) || !isset($_POST['action'])) {
    error_log("Missing parameters: " . json_encode($_POST));
    error_log("POST data: " . print_r($_POST, true));
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content type: " . $_SERVER['CONTENT_TYPE']);
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

$follower_id = intval($_SESSION['user_id']);
$following_id = intval($_POST['following_id']);
$action = $_POST['action'];

error_log("Processing follow action: follower_id={$follower_id}, following_id={$following_id}, action={$action}");

// 验证用户ID
if ($follower_id <= 0 || $following_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// 检查是否试图关注自己
if ($follower_id == $following_id) {
    error_log("User tried to follow themselves");
    echo json_encode(["success" => false, "message" => "Cannot follow yourself"]);
    exit;
}

// 开始事务
$conn->begin_transaction();

try {
    // 检查目标用户是否存在
    $user_check = $conn->prepare("SELECT id, manage_visibility FROM users WHERE id = ?");
    $user_check->bind_param("i", $following_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    
    if ($user_result->num_rows === 0) {
        throw new Exception("User does not exist");
    }
    
    $user_data = $user_result->fetch_assoc();
    $is_private = $user_data['manage_visibility'] === 'private';

    if ($action === 'follow') {
        // 检查是否已存在关注关系
        $check_query = $conn->prepare("SELECT FollowID, FollowStatus FROM follow WHERE FollowerID = ? AND FollowingID = ?");
        $check_query->bind_param("ii", $follower_id, $following_id);
        $check_query->execute();
        $result = $check_query->get_result();

        if ($result->num_rows > 0) {
            $existing = $result->fetch_assoc();
            throw new Exception("Already " . ($existing['FollowStatus'] === 'pending' ? 'sent a follow request' : 'following this user'));
        }

        // 插入新的关注关系
        $status = $is_private ? 'pending' : 'approved';
        $insert_query = $conn->prepare("INSERT INTO follow (FollowerID, FollowingID, FollowStatus) VALUES (?, ?, ?)");
        $insert_query->bind_param("iis", $follower_id, $following_id, $status);
        
        if (!$insert_query->execute()) {
            error_log("Insert error: " . $insert_query->error);
            throw new Exception("Follow failed: " . $insert_query->error);
        }

        $conn->commit();
        echo json_encode([
            "success" => true,
            "is_private" => $is_private,
            "message" => $is_private ? "Follow request sent" : "Successfully followed"
        ]);

    } elseif ($action === 'unfollow') {
        // check and delete relationship
        $delete_query = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ?");
        $delete_query->bind_param("ii", $follower_id, $following_id);
        
        if (!$delete_query->execute()) {
            error_log("Delete error: " . $delete_query->error);
            throw new Exception("Unfollow failed: " . $delete_query->error);
        }

        if ($delete_query->affected_rows === 0) {
            throw new Exception("Follow relationship not found");
        }

        $conn->commit();
        echo json_encode([
            "success" => true,
            "message" => "Successfully unfollowed"
        ]);

    } else {
        throw new Exception("Invalid action type");
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Follow operation error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?> 