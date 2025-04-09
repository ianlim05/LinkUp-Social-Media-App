<?php
session_start();
include "../!! General/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Prepare SQL query to get warnings for the user
    $query = $conn->prepare("SELECT w.WarningID, w.UserID, w.WarningDate, w.WarningReason 
                            FROM warning w 
                            WHERE w.UserID = ? 
                            ORDER BY w.WarningDate DESC");
    
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    
    $warnings = [];
    
    while ($row = $result->fetch_assoc()) {
        $warnings[] = [
            'warning_id' => $row['WarningID'],
            'warning_date' => $row['WarningDate'],
            'warning_reason' => $row['WarningReason']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'warnings' => $warnings
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching warnings: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 