<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "linkuptest.php"; // Database connection

$user_id = null; // Initialize

// Check if ID is in URL
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// If still no user ID, show an error
if (!$user_id) {
    echo "Profile not found. <a href='login.php'>Login here</a>";
    exit();
}

// Fetch user details
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$users = $result->fetch_assoc();

if (!$users) {
    echo "User not found.";
    exit();
}

// Get followers count
$followers_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowingID = ? AND FollowStatus = 'approved'");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();
$followers_count = $followers_result->fetch_assoc()['count'];

// Get following count
$following_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowerID = ?");
$following_query->bind_param("i", $user_id);
$following_query->execute();
$following_result = $following_query->get_result();
$following_count = $following_result->fetch_assoc()['count'];

// Get posts count
$posts_query = $conn->prepare("SELECT COUNT(*) AS count FROM post WHERE UserID = ?");
$posts_query->bind_param("i", $user_id);
$posts_query->execute();
$posts_result = $posts_query->get_result();
$posts_count = $posts_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ViewProfile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu" />
    <title>View Profile</title>
</head>
<body>
<header>
    <div class="logo">
        <img src="images/LinkUp-logo1.png" alt="logo">
    </div>

    <nav class="nav">
        <button>Home</button>
        <button>Explore</button>
        <button>Post</button>
        <button class="Profile">
            <a href="ViewOwnProfile.php?id=<?php echo htmlspecialchars($user_id); ?>" style="text-decoration: none; color: inherit;">
                Profile
            </a>
        </button>
        <button>Settings</button>
    </nav>

    <!-- Show User Profile if logged in --> 
    <?php if (isset($username) && isset($profilepicture)): ?>
        <div class="profile" onclick="toggleDropdown()">
            <img src="<?php echo htmlspecialchars($profilepicture); ?>" alt="Profile Picture">
            <span><?php echo htmlspecialchars($username); ?></span>
            <div class="dropdown">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    <?php endif; ?>
</header>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="ProfilePic">
                <?php
                        if (!empty($users['profile_picture'])) {
                          $profile_picture = $users['profile_picture'];
                                } else {
                                   $profile_picture = 'images/DefaultUser.jpg'; // Set default profile picture
                                }
                        ?>
                        <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                            </div>       
                <div class="profile-details">
                <div class="top-section">
                <h2 class="profile-name"><?php echo $users['username']; ?></h2> 
                <button class="follow-btn" data-user-id="<?php echo $user_id; ?>">Follow</button>
    
                <!-- Report Icon -->
                <span class="material-symbols-outlined report-icon" onclick="toggleReportMenu()">
                 menu
                </span>

                <!-- Background Overlay -->
                <div class="overlay" id="overlay" onclick="toggleReportMenu()"></div>

                <!-- Report Menu (OUTSIDE overlay) -->
                <div class="report-menu" id="report-menu">
                    <ul>
                        <li class="red" onclick="blockUser(<?php echo $user_id; ?>)">Block</li>
                        <li class="red" onclick="openReportModal('user', <?php echo $user_id; ?>)">Report</li>
                        <li onclick="toggleReportMenu()">Cancel</li>
                    </ul>
                </div>
            </div>

                    <div class="profile-stats">
                    <button class="Post"><?php echo $posts_count; ?> Posts</button>
                    <button class="follower-btn"><?php echo $followers_count; ?> Followers</button>
                    <button class="following-btn"><?php echo $following_count; ?> Following</button>
                    </div>
                    <div class="profile-bio">
                    <p><?php echo nl2br($users['bio']); ?></p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    
    <div class="post-section">
    <?php
    $posts_query = $conn->prepare("SELECT * FROM post WHERE UserID = ?");
    $posts_query->bind_param("i", $user_id);
    $posts_query->execute();
    $posts_result = $posts_query->get_result();

    if ($posts_result->num_rows > 0) {
        echo '<div class="post-grid">';
        while ($post = $posts_result->fetch_assoc()) {
            echo '<div class="post-item">
                    <a href="viewpost.php?post_id='.htmlspecialchars($post['PostID']).'">
                        <img src="./images/' . htmlspecialchars($post['fileImage']).'" alt="Post">
                    </a>
                  </div>';
        }
        echo '</div>'; // Close .post-grid
    } else {
        echo '<div class="no-posts">
        <img src="images/DefaultPost.png" alt="No posts yet">
        <p class="no-post-text">No posts yet</p>
      </div>';
}
?>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReportModal()">&times;</span>
        <h2>Report User</h2>
        <form id="reportForm" method="POST">
            <input type="hidden" id="reportType" name="reportType" value="user">
            <input type="hidden" id="reportId" name="reportId" value="">
            <textarea id="reportReason" name="reportReason" placeholder="Enter reason for report..." required></textarea>
            <button type="submit">Submit Report</button>
        </form>
    </div>
</div>

<script>
function toggleReportMenu() {
    var reportMenu = document.getElementById("report-menu");
    var overlay = document.getElementById("overlay");

    // Toggle visibility
    if (overlay.style.display === "flex") {
        overlay.style.display = "none";
        reportMenu.style.display = "none";
    } else {
        overlay.style.display = "flex";
        reportMenu.style.display = "block";
    }
}

// Close menu when clicking outside
document.getElementById("overlay").addEventListener("click", function() {
    toggleReportMenu();
});

// Prevent closing when clicking inside the menu
document.getElementById("report-menu").addEventListener("click", function(event) {
    event.stopPropagation();
});

function openReportModal(type, id) {
    document.getElementById('reportType').value = type;
    document.getElementById('reportId').value = id;
    document.getElementById('reportModal').style.display = 'block';
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}

function blockUser(userId) {
    if (confirm("Are you sure you want to block this user?")) {
        fetch('block_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("User blocked successfully");
            } else {
                alert("Error blocking user");
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

document.getElementById('reportForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Report submitted successfully');
            closeReportModal();
        } else {
            alert('Error submitting report');
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
</body>
</html>