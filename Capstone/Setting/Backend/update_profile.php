<?php
session_start();
// ASSISTED BY IAN LIM, gotta give credit where its due

// Include the database connection file
include '../../!! General/database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userid = $_SESSION['user_id'];

// Get other form data
$username = $_POST['username'] ?? '';
$bio = $_POST['bio'] ?? '';

// Validate username is not empty
if (empty($username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username cannot be empty']);
    exit();
}

// Check for duplicate username, excluding the current user
$check_username_query = "SELECT id FROM users WHERE username = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_username_query);
mysqli_stmt_bind_param($check_stmt, "si", $username, $userid);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username already exists. Please choose a different username.']);
    exit();
}
mysqli_stmt_close($check_stmt);

// Handle file upload first
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../../!! Images/";
    
    // Get file extension
    $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    
    // Keep the original filename but prefix with user ID for uniqueness
    $new_filename = $userid . '_' . $_FILES['profile_picture']['name'];
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES['profile_picture']['tmp_name']);
    if ($check === false) {
        echo json_encode(['status' => 'error', 'message' => 'File is not an image']);
        exit();
    }
    
    // Allow certain file formats
    if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed']);
        exit();
    }
    
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $profile_picture = $new_filename;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
        exit();
    }
}

// Prepare SQL query
$query = "UPDATE users SET ";
$params = [];
$types = "";

if ($profile_picture !== null) {
    $query .= "profile_picture = ?, ";
    $params[] = $profile_picture;
    $types .= "s";
}

$query .= "username = ?, bio = ? WHERE id = ?";
$params[] = $username;
$params[] = $bio;
$params[] = $userid;
$types .= "ssi";

// Create prepared statement
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    // Bind parameters
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Update session variables
        $_SESSION['username'] = $username;
        if ($profile_picture !== null) {
            // Store just the filename in the session
            $_SESSION['profile_picture'] = $profile_picture;
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Profile updated successfully',
            'newUsername' => $username,
            'newProfilePicture' => $profile_picture
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
} 