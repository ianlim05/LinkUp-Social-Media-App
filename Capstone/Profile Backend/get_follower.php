<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json'); // Ensure JSON response
include "../!! General/database.php"; // Database connection file

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$currentUserId = $_GET['user_id'] ?? $_SESSION['user_id']; 

error_log("Fetching followers for user ID: " . $currentUserId);

// get follower（who followed current user）
$query = $conn->prepare("SELECT u.id, u.username, u.profile_picture 
                        FROM users u 
                        JOIN follow f ON f.FollowerID = u.id 
                        WHERE f.FollowingID = ? AND f.FollowStatus = 'approved'
                        ORDER BY u.username");

if (!$query) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Database query failed"]);
    exit;
}

$query->bind_param("i", $currentUserId);
if (!$query->execute()) {
    error_log("Query execution failed: " . $query->error);
    echo json_encode(["success" => false, "message" => "Query execution failed"]);
    exit;
}

$result = $query->get_result();
$followers = [];

while ($row = $result->fetch_assoc()) {
    $followers[] = $row;
}


error_log("Found " . count($followers) . " followers for user ID: " . $currentUserId);
error_log("Followers data: " . json_encode($followers));

echo json_encode([
    "success" => true,
    "followers" => $followers,
    "debug_info" => [
        "user_id" => $currentUserId,
        "follower_count" => count($followers)
    ],
    "message" => empty($followers) ? "No followers found" : null
]);

