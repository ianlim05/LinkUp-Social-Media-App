<?php
header('Content-Type: application/json'); // Set response header to JSON

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "linkup";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$current_user_id = $_SESSION['id']; // Get logged-in user ID dynamically

// Fetch Followers
$followers_query = "
    SELECT u.username, u.full_name AS fullName, u.profile_picture AS profilePic
    FROM follow f
    JOIN users u ON f.FollowerID = u.id
    WHERE f.FollowingID = ? AND f.FollowStatus = 'approved'
";
$stmt = $conn->prepare($followers_query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$followers_result = $stmt->get_result();
$followers = $followers_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch Following
$following_query = "
    SELECT u.username, u.full_name AS fullName, u.profile_picture AS profilePic
    FROM follow f
    JOIN users u ON f.FollowingID = u.id
    WHERE f.FollowerID = ? AND f.FollowStatus = 'approved'
";
$stmt = $conn->prepare($following_query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$following_result = $stmt->get_result();
$following = $following_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

echo json_encode(['followers' => $followers, 'following' => $following]);

?>