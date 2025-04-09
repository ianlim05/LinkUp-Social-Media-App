<?php
session_start();
require_once "../!! General/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = $_POST["username-email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE (email = ? OR username = ?) AND username = 'admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $input, $input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $adminUser = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($adminUser && password_verify($password, $adminUser["password"])) {
        $_SESSION['user_id'] = $adminUser['id'];
        $_SESSION['username'] = $adminUser['username'];
        $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? $user['profile_picture'] : '../!! Images/user_icon.jpg';
        $_SESSION['email'] = $adminUser['email'];
        $_SESSION['role'] = 'admin';
        header("Location: ../!! ADMIN PANEL/AdminHome/admin-dashboard.php");
        exit();
    }

    $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $input, $input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // Check if user is banned
        $ban_check = mysqli_prepare($conn, "SELECT * FROM ban WHERE UserID = (SELECT id FROM users WHERE email = ? OR username = ?)");
        mysqli_stmt_bind_param($ban_check, "ss", $input, $input);
        mysqli_stmt_execute($ban_check);
        $ban_result = mysqli_stmt_get_result($ban_check);

        if (mysqli_num_rows($ban_result) > 0) {
            $_SESSION['error'] = "Your account has been banned. Please contact support for more information.";
            header("Location: login.php");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? $user['profile_picture'] : '../!! Images/user_icon.jpg';
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = 'user';
        header("Location: ../Homepage/Homepage.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username/email or password.";
        header("Location: login.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Login</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(#0671B7 0%,#67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            display: flex;
            flex-direction:column;
            align-items:center;
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            overflow: hidden;      
            height: auto;         
            max-width: 800px; 
        }
        .alert-overlay {
            position: fixed;
            top: 5px;
            transform: translateX(-50%);
            background-color: #ff4d4d;
            color: white;
            text-align: center;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index:1000;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .login-container {
            background-color: #ffffff;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0px 8px 10px rgba(0, 0, 0, 0.4);
            text-align: center;
            transition: transform 0.3s;
            width: 400px;
            margin-top: 5px;
            
        }
        .login-container:hover {
            transform: translateY(-10px);
        }
        .login-container h2 {
            margin-bottom: 20px;
            color:#333333;
            font-weight: bold;
            font-family: cursive;
        }
        .input-group {
            text-align: left;
            margin-bottom: 15px;
            position: relative;
        }
        .input-group label {
            display: block;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 10px;
            color:#555555;
        }
        .input-group input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            border-color: #74ebd5;
            outline: none;
            box-shadow: 0 0 8px rgba(116, 235, 213, 0.5);
        }
        .password-group {
            position: relative;
        }
        #togglePassword {
            position: absolute;
            top: 38px;
            right: 15px;
            cursor: pointer;
            color: #aaa;
        }
        #togglePassword:hover {
            color: #57c6a9;
        }
        .password-recovery {
            display: block;
            text-align: right;
            font-size: 15px;
            color: #57c6a9;
            text-decoration: none;
            margin-top: -10px;
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .password-recovery:hover {
            color:rgb(12, 107, 89);
            
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #74ebd5, #57c6a9);
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-button:hover {
            background: linear-gradient(90deg, #57c6a9, #74ebd5);
        }
        .signup-link {
            display: block;
            text-align: center;
            font-size: 15px;
            color: #333;
            text-decoration: none;
            margin-top: 15px;
        }
        .signup-link span {
            color: #57c6a9;
            font-weight: bold;
            transition: color 0.3s;
        }
        .signup-link span:hover {
            color: #74ebd5;
            text-decoration: underline;
        }
        .logo-img{
            height: 90px;
            display: block;
            margin: 0 auto 15px;
            width: auto;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 20px;
                height: auto;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-overlay">
                <?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="login-container">
            <img src="../!! Images/LinkUp-logo1.png" alt="LinkUp Logo" class="logo-img">
            <h2>LOGIN</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username-email">Username/Email</label>
                <input type="text" id="username-email" name="username-email" placeholder="Enter your username or email">
            </div>

            <div class="input-group password-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password">
                <i class="fa-regular fa-eye-slash" id="togglePassword"></i>
            </div>

            <a href="../Auth/password-recovery.php" class="password-recovery">Forgot Password?</a>

            <button type="submit" class="login-button">LOGIN</button>
            <a href="../Auth/sign-up.php" class="signup-link">Don't have an account? <span>Sign Up</span></a>
        </form>
        </div>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const PasswordVisible = passwordInput.type === 'text';
            
            passwordInput.type = PasswordVisible ? 'password' : 'text';

            this.classList.toggle('fa-eye-slash', PasswordVisible);
            this.classList.toggle('fa-eye', !PasswordVisible);
            
        });

        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                let alertBox = document.querySelector(".alert-overlay");
                if (alertBox) {
                    alertBox.classList.add("hidden"); // Apply fade-out transition
                    setTimeout(() => {
                        alertBox.remove(); // Remove it after fade-out completes
                    }, 1000); // Wait for fade-out animation to finish
                }
            }, 3000); // Display for 3 seconds before starting fade-out
        });
    </script>
</body>
</html>
