<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../../Auth/login.php");
    exit();
}

require_once(__DIR__ . '/../../!! General/database.php');
include __DIR__ . '/../../!! General/admin-header.php';

// Fetch reported users from the `reporteduser` table
$sql = "
    SELECT r.ReportedUserID, r.Reason, u.username, u.profile_picture, u.id as UserID
    FROM reporteduser r 
    JOIN users u ON r.UserID = u.id";
$result = mysqli_query($conn, $sql);
$reportedUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch reported posts from the `reportedpost` table
$sql = "
    SELECT r.ReportPostID, r.Reason, p.PostID, p.fileImage, p.captionText, p.titleText, u.username 
    FROM reportedpost r 
    JOIN post p ON r.PostID = p.PostID
    JOIN users u ON p.UserID = u.id";
$result = mysqli_query($conn, $sql);
$reportedPosts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(#0671B7 0%,#67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
            min-height: 100vh;
        }
        .header {
            background: #2c3e50;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }
        .logo img {
            width: 200px;
            height: auto;
            max-height: 80px;
        }
        .nav {
            display: flex;
            gap: 20px;
        }
        .nav button {
            padding: 10px 20px;
            border: none;
            background: #4a90e2;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .nav button:hover {
            background: #357ABD;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 10px;
            position: relative;
        }
        .profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile span {
            font-size: 18px;
            color: white;
        }
        .dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            display: none;
            flex-direction: column;
            min-width: 120px;
            z-index: 100;
        }
        .profile:hover .dropdown {
            display: flex;
        }
        .dropdown a {
            padding: 10px;
            text-decoration: none;
            color: #333;
            display: block;
            text-align: center;
            font-weight: bold;
        }
        .dropdown a:hover {
            background: #f0f0f0;
            border-radius: 10px;
        }
        .content {
            padding: 20px;
        }
        .reported-section {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: white;
        }
        .reported-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .reported-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        .reported-item .details {
            flex: 1;
        }
        .reported-item .details h3 {
            margin: 0;
            font-size: 18px;
        }
        .reported-item .details p {
            margin: 5px 0;
            color: #555;
        }
        .reported-item .details a {
            text-decoration: none;
            color: #4a90e2;
        }
        .reported-item .details a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
 

    <div class="content">
        <h1>Moderation</h1>

        <!-- Reported Users Section -->
        <div class="reported-section">
            <h2>Reported Users</h2>
            <?php if (empty($reportedUsers)): ?>
                <p>No reported users.</p>
            <?php else: ?>
                <?php foreach ($reportedUsers as $user): ?>
                    <div class="reported-item">
                        <img src="<?php echo !empty($user['profile_picture']) ? '../../!! Images/' . htmlspecialchars($user['profile_picture']) : '../../!! Images/DefaultUser.jpg'; ?>" alt="Profile Picture">
                        <div class="details">
                            <h3><a href="../AdminSearch/admin-viewprofile.php?user_id=<?php echo $user['UserID']; ?>"><?php echo htmlspecialchars($user['username']); ?></a></h3>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($user['Reason']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Reported Posts Section -->
        <div class="reported-section">
            <h2>Reported Posts</h2>
            <?php if (empty($reportedPosts)): ?>
                <p>No reported posts.</p>
            <?php else: ?>
                <?php foreach ($reportedPosts as $post): ?>
                    <div class="reported-item">
                        <img src="../../!! Images/<?php echo htmlspecialchars($post['fileImage']); ?>" alt="Post Image">
                        <div class="details">
                            <h3><a href="../AdminSearch/admin-viewpost.php?post_id=<?php echo $post['PostID']; ?>">Post by <?php echo htmlspecialchars($post['username']); ?></a></h3>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($post['Reason']); ?></p>
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($post['titleText']); ?></p>
                            <p><strong>Caption:</strong> <?php echo htmlspecialchars($post['captionText']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>