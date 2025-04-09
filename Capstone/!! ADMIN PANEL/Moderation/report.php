<?php
session_start();
include "../!! General/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['reportType'];
    $reportId = $_POST['reportId'];
    $reason = $_POST['reportReason'];

    if ($reportType === 'user') {
        $query = $conn->prepare("INSERT INTO reporteduser (UserID, Reason) VALUES (?, ?)");
    } else if ($reportType === 'post') {
        $query = $conn->prepare("INSERT INTO reportedpost (PostID, Reason) VALUES (?, ?)");
    }

    $query->bind_param("is", $reportId, $reason);
    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>