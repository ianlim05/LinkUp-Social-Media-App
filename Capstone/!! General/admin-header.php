<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $username = $_SESSION['username'];  
    $profilepicture = '../../!! Images/user_icon.jpg';
    $email = $_SESSION['email'];  
    $role = $_SESSION['role'];  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../!! Images/LinkUp-logo.png">
    <style>
        .h-body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .h-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #2c3e50;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-wrap: wrap;
        }
        .h-logo img {
            width: 200px;
            height: auto;
            max-height: 80px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.2));
        }
        .h-nav {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
        }
        .h-nav a {
            padding: 15px 30px;
            border: none;
            background: #0671B7;
            transition: background 0.3s ease-in-out, transform 0.1s ease-in-out;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            font-size: 20px;
            text-decoration: none;
            color: white;
        }
        .h-nav a:hover {
            background: #67A3D9;
            color: black;
        }
        .h-nav a:active {
            transform: scale(0.95);
        }
        .h-user-menu {
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .h-login-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            font-weight: bold;
        }
        .h-login-btn:hover {
            color: #f6ACC5;
        }
        .h-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 10px;
            position: relative;
        }
        .h-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .h-span {
            font-size: 20px;
            color: white;
        }
        .h-dropdown {
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
        .h-user-menu:hover .h-dropdown {
            display: flex;
        }
        .h-dropdown a {
            padding: 10px;
            text-decoration: none;
            color: black;
            display: block;
            text-align: center;
            font-weight: bold;
            transition: background 0.3s ease;
            border-radius: 10px;
        }
        .h-dropdown a:hover {
            background: #f6ACC5;
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .h-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .h-nav {
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body class="h-body">
    <header class="h-header">
        <div class="h-logo"><a href='../Moderation/moderation.php'><img src="../../!! Images/LinkUp-logo1.png" alt="logo"></a></div>
        <nav class="h-nav">
            <a href='../AdminHome/admin-dashboard.php'>Home</a>
            <a href='../Moderation/moderation.php'>Moderation</a>
            <a href='../AdminSearch/search.php'>Search</a>

        </nav>

        <div class="h-user-menu">
            <?php if (!$isLoggedIn): ?>
                <!-- Show Login Button if NOT logged in -->
                <button class="h-login-btn" onclick="location.href='../../Auth/login.php'">LOGIN</button>
            <?php else: ?>
                <!-- Show User Profile if logged in -->
                <div class="h-profile" onclick="toggleDropdown()">
                    <img src="<?php echo htmlspecialchars($profilepicture); ?>">
                    <span class="h-span"><?php echo htmlspecialchars($username); ?></span>
                    <div class="h-dropdown">
                        <a href="../../Auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>
</body>
</html>