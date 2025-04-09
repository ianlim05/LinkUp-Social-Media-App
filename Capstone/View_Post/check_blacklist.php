<?php
session_start();
require_once '../!! General/database.php';

// Get the text to check from POST request
$text = isset($_POST['text']) ? $_POST['text'] : '';

// Get blacklisted words from database
$sql = "SELECT BlacklistedWord FROM filter";
$result = mysqli_query($conn, $sql);

$hasBlacklistedWord = false;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (stripos($text, $row['BlacklistedWord']) !== false) {
            $hasBlacklistedWord = true;
            break;
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['hasBlacklistedWord' => $hasBlacklistedWord]);
?> 