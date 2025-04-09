<?php
require "../!! General/database.php"; // Include database connection

$user_id = $_GET['user_id'];

// Fetch visibility and blocked status
$query = "SELECT manage_visibility, blocked_user FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode([
        "success" => true,
        "visibility" => $data["manage_visibility"], // "public" or "private"
        "blocked" => $data["blocked_user"] === "1" // Assume blocked_user is a boolean column
    ]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
?>
