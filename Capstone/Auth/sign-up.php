<?php
session_start();
require_once "../!! General/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" AND isset($_POST["signup-button"])) {
    
    $fullName = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $passwordConfirm = $_POST["confirm_password"];

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $errors = array();

    // 🔹 Validate Required Fields
    if (empty($fullName) OR empty($username) OR empty($email) OR empty($password) OR empty($passwordConfirm)) {
        $errors[] = "All fields are required";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[a-zA-Z]/', $password)) {
        $errors[] = "Password must contain at least one alphabet letter";
    }
    if ($password !== $passwordConfirm) {
        $errors[] = "Passwords do not match";
    }

    // 🔹 Check if email or username already exists
    if (empty($errors)) { 
        $sql = "SELECT email, username FROM users WHERE email = ? OR username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['email'] === $email) {
                $errors[] = "Email already exists!";
            }
            if ($row['username'] === $username) {
                $errors[] = "Username already exists!";
            }
        }
    }

    // 🔹 Display errors if found
    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        // Store validated input to repopulate form
        $_SESSION['old_input'] = [
            'fullname' => $fullName,
            'username' => $username,
            'email' => $email
        ];
        
    } else {
        $sql = "INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $fullName, $username, $email, $passwordHash);
            mysqli_stmt_execute($stmt);
            unset($_SESSION['old_input']);
            $_SESSION['success'] = "You are registered successfully.";
        } else {
            $_SESSION['errors'] = "Something went wrong";
        }
    }
    header("Location: sign-up.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Sign Up</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(#0671B7 0%, #67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            display: flex;
            flex-direction:column;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            overflow: hidden;
            height: auto;
            max-width: 800px;
            
        }
        /* 🔹 Alert Overlay */
        .alert-overlay {
            position: fixed;
            top: 5px;
            transform: translateX(-50%);
            width: 100%;
            max-width: 400px;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            display: none;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }
        .alert-danger { background-color: #ff4d4d; color: white; }
        .alert-success { background-color: #4CAF50; color: white; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .signup-container {
            background-color: #ffffff;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0px 8px 10px rgba(0, 0, 0, 0.4);
            text-align: center;
            transition: transform 0.3s;
            width: 400px;
            margin-top: 5px;
        }
        .signup-container:hover {
            transform: translateY(-10px);
        }
        .signup-container h2 {
            margin-bottom: 15px;
            color: #333;
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
            color:#555;
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
        #togglePassword, #toggleConfirmPassword {
            position: absolute;
            top: 40px;
            right: 15px;
            cursor: pointer;
            color: #aaa;
        }
        #togglePassword:hover, #toggleConfirmPassword:hover {
            color: #57c6a9;
        }
        .privacy-policy {
            display: flex;
            align-items: center;
            font-size: 14px;
            margin-bottom: 15px;
            color: #555;
        }
        .privacy-policy input {
            margin-right: 10px;
        }
        .signup-button {
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
        .signup-button:hover {
            background: linear-gradient(90deg, #57c6a9, #74ebd5);
        }
        .login-link {
            font-size: 14px;
            margin-top: 15px;
        }
        .login-link a {
            color: #57c6a9;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .login-link a:hover {
            text-decoration: underline;
            color: #74ebd5;
            
        }
        .logo-img {
            height: 90px;
            display: block;
            margin: 0 auto 15px;
            width: auto;
        }

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
        <div class="alert-overlay" id="alertBox"></div>
        <?php 
        $alertMessage = "";
        $alertClass = "";
        if (isset($_SESSION['signup_errors'])) {
            $alertMessage = implode("<br>", $_SESSION['signup_errors']);
            $alertClass = "alert-danger";
            unset($_SESSION['signup_errors']);
        } elseif (isset($_SESSION['success'])) {
            $alertMessage = $_SESSION['success'];
            $alertClass = "alert-success";
            unset($_SESSION['success']);
        }
        ?>

        <form action="sign-up.php" method="post">
        <div class="signup-container">
            <img src="../!! Images/LinkUp-logo1.png" alt="LinkUp Logo" class="logo-img">
            <h2>SIGN UP</h2>

            
            <div class="input-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" value="<?php echo isset($_SESSION['old_input']['fullname']) ? htmlspecialchars($_SESSION['old_input']['fullname']) : ''; ?>">
            </div>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" value="<?php echo isset($_SESSION['old_input']['username']) ? htmlspecialchars($_SESSION['old_input']['username']) : ''; ?>">
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_SESSION['old_input']['email']) ? htmlspecialchars($_SESSION['old_input']['email']) : ''; ?>">
            </div>

            <div class="input-group password-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password">
                <i class="fa-regular fa-eye-slash" id="togglePassword"></i>
            </div>

            <div class="input-group password-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password">
                <i class="fa-regular fa-eye-slash" id="toggleConfirmPassword"></i>
            </div>

            <div class="privacy-policy">
                <input type="checkbox" id="privacy" required>
                <label for="privacy">I agree to the <a href="privacy-policy.php">Privacy Policy</a></label>
            </div>

            <button class="signup-button" name="signup-button">SIGN UP</button>

            <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
        </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility(passwordFieldId, toggleIconId) {
            const passwordInput = document.getElementById(passwordFieldId);
            const toggleIcon = document.getElementById(toggleIconId);
            const isPasswordVisible = passwordInput.type === 'text';

            passwordInput.type = isPasswordVisible ? 'password' : 'text';

            toggleIcon.classList.toggle('fa-eye-slash', isPasswordVisible);
            toggleIcon.classList.toggle('fa-eye', !isPasswordVisible);
        }

        document.getElementById('togglePassword').addEventListener('click', function () {
            togglePasswordVisibility('password', 'togglePassword');
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            togglePasswordVisibility('confirm-password', 'toggleConfirmPassword');
        });


        document.addEventListener("DOMContentLoaded", function() {
                let alertBox = document.getElementById("alertBox");
                let alertMessage = `<?php echo $alertMessage; ?>`;
                let alertClass = "<?php echo $alertClass; ?>";

                if (alertMessage.trim() !== "") {
                    alertBox.innerHTML = alertMessage;
                    alertBox.classList.add(alertClass);
                    alertBox.style.display = "block";
                    alertBox.style.opacity = "1";

                    // Set timeout to fade out the alert
                    setTimeout(() => {
                        alertBox.style.opacity = "0";
                        setTimeout(() => { alertBox.style.display = "none"; }, 1000);
                    }, 5000);
                }
            });
    </script>
</body>
</html>
