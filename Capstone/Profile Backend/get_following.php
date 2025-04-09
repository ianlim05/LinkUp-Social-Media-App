<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json'); // Ensure JSON response

include "../!! General/database.php";  // Database connection file

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$currentUserId = $_GET['user_id'] ?? $_SESSION['user_id']; 


error_log("Fetching following for user ID: " . $currentUserId);

// getting who u r following（current user followed who）
$query = $conn->prepare("SELECT u.id, u.username, u.profile_picture 
                        FROM users u 
                        JOIN follow f ON f.FollowingID = u.id 
                        WHERE f.FollowerID = ? AND f.FollowStatus = 'approved'
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
$following = [];

while ($row = $result->fetch_assoc()) {
    $following[] = $row;
}

error_log("Found " . count($following) . " following for user ID: " . $currentUserId);
error_log("Following data: " . json_encode($following));

echo json_encode([
    "success" => true,
    "following" => $following,
    "debug_info" => [
        "user_id" => $currentUserId,
        "following_count" => count($following)
    ],
    "message" => empty($following) ? "No following found" : null
]);
?>
