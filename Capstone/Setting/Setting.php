<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
        <title>Login Required</title>
        <script src="../!! General/Countdown_Timer.js"></script>
        <style>
            body {
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background: #f0f2f5;
                font-family: Arial, sans-serif;
            }
            .modal {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                text-align: center;
                max-width: 400px;
                width: 90%;
            }
            .modal h2 {
                color: #2c3e50;
                margin-bottom: 20px;
            }
            .modal p {
                color: #666;
                margin-bottom: 25px;
            }
            .login-btn {
                background: #0671B7;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s ease;
            }
            .login-btn:hover {
                background: #67A3D9;
            }
            .cancel-btn {
                background: #f6ACC5;
                color: black;
                padding: 12px 20px;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s ease;
            }
            .cancel-btn:hover {
                background: #F3C4D6;
            }
            .icon {
                font-size: 48px;
                color: #0671B7;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>
        <div class="modal">
            <div class="icon">🔒</div>
            <h2>Login Required</h2>
            <p>Please log in to access your settings and customize your experience.</p>
            <button class="login-btn" onclick="window.location.href='../Auth/login.php'">Go to Login</button>
            <button class="cancel-btn" onclick="window.location.href='../Homepage/Homepage.php'">Cancel</button>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$userid = $_SESSION['user_id'];

// Include files after the checking if user logged in
include '../!! General/header.php';
include '../!! General/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="Setting.css">
</head>

<body>
    <div class="settings-container">
        <!-- Left Side: Settings Categories -->
        <div class="settings-menu">
            <h2>SETTING</h2>
            <ul>
                <li data-option="profile-settings">PROFILE SETTINGS</li>
                <li data-option="manage-visibility">MANAGE VISIBILITY</li>
                <li data-option="block-users">BLOCKED USERS</li>
                <li data-option="screen-time">SCREEN TIME REMINDERS</li>
                <li><a href="mental_health_resources.php" target="_blank">MENTAL HEALTH RESOURCES</a></li>
                <li><a href="../Auth/privacy-policy.php" target="_blank">PRIVACY POLICY</a></li>
            </ul>
        </div>

        <!-- Right Side: Content Display -->
        <div class="settings-content" id="content-box">
            <p>Select a setting option</p>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if we have category and show parameters in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        const show = urlParams.get('show');
        
        // If we have both parameters and show is true
        if (category === 'profile-settings' && show === 'true') {
            // Wait for a brief moment to ensure SettingCategory.js is fully loaded
            setTimeout(() => {
                // Find and click the profile settings option
                const profileSettingsOption = document.querySelector('[data-option="profile-settings"]');
                if (profileSettingsOption) {
                    // Remove active class from all menu items first
                    document.querySelectorAll(".settings-menu li").forEach(menuItem => {
                        menuItem.classList.remove('active');
                    });
                    
                    // Add active class to profile settings
                    profileSettingsOption.classList.add('active');
                    
                    // Trigger the click
                    profileSettingsOption.click();
                }
            }, 100); // Small delay to ensure everything is loaded
        }
    });
    </script>

    <script src="SettingCategory.js"></script>
</body>
</html>