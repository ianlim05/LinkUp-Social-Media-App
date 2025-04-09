<?php
// if (!isset($_GET["token"])) {
//     die("Token is missing.");
// }
if (!isset($_GET["token"])) {
    header("Location: ../Auth/password-recovery.php?error=invalid");
    exit;
}
$token = trim($_GET["token"]);

$token_hash = hash("sha256", $token);

$conn = require __DIR__ . "/../!! General/database.php";

// if (!$conn) {
//     die("Failed to connect to database.");
// }
if (!$conn) {
    header("Location: ../Auth/password-recovery.php?error=db");
    exit;
}

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";

$stmt = mysqli_prepare($conn, $sql);
// if (!$stmt) {
//     die("Failed to prepare statement: " . mysqli_error($conn));
// }
if (!$stmt) {
    header("Location: ../Auth/password-recovery.php?error=db");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $token_hash);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

// if (!$user) {
//     die("Token not found.");
// }

// if (strtotime($user["reset_token_expires_at"]) <= time()) {
//     die("token has expired");
// }
if (!$user || strtotime($user["reset_token_expires_at"]) <= time()) {
    header("Location: ../Auth/password-recovery.php?error=expired");
    exit;
}

// Add error message handling
$error_message = "";
if (isset($_GET["error"])) {
    switch($_GET["error"]) {
        case "length":
            $error_message = "Password must be at least 8 characters.";
            break;
        case "letter":
            $error_message = "Password must contain at least one letter.";
            break;
        case "number":
            $error_message = "Password must contain at least one number.";
            break;
        case "match":
            $error_message = "Passwords must match.";
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Reset Password</title>
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
        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            width: 100%;
            font-size: 14px;
        }
        .container {
            display: flex;
            align-items: center;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            width: 500px;
            text-align: center;
        }
        .reset-container {
            background-color: #ffffff;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0px 8px 10px rgba(0, 0, 0, 0.4);
            text-align: center;
            transition: transform 0.3s;
            width: 100%;
        }
        .reset-container:hover {
            transform: translateY(-10px);
        }

        h2 {
            margin-bottom: 30px;
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
            margin-bottom: 10px;
            color:#555;
            font-weight: bold;
        }
        .input-group input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .input-group input:focus {
            border-color: #74ebd5;
            outline: none;
            box-shadow: 0 0 8px rgba(116, 235, 213, 0.5);
        }
        .reset-button {
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
        .reset-button:hover {
            background: linear-gradient(90deg, #57c6a9, #74ebd5);
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
        .error-message {
            color: red;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid red;
            background-color: #ffe6e6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <h2>Reset Password</h2>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="../Auth/process-reset-password.php">
                <div class="input-group">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <label for="password">New password</label>
                    <input type="password" id="password" name="password">
                    <i class="fa-regular fa-eye-slash" id="togglePassword"></i>

                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation">
                    <i class="fa-regular fa-eye-slash" id="toggleConfirmPassword"></i>
                </div>
                <button type="submit" class="reset-button">Reset Password</button>
            </form>
        </div>
    </div>
    <script>
        // Toggle for password
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const isPasswordVisible = passwordInput.type === 'text';
            
            passwordInput.type = isPasswordVisible ? 'password' : 'text';
            this.classList.toggle('fa-eye-slash', isPasswordVisible);
            this.classList.toggle('fa-eye', !isPasswordVisible);
        });

        // Toggle for confirm password
        document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const isPasswordVisible = confirmPasswordInput.type === 'text';
            
            confirmPasswordInput.type = isPasswordVisible ? 'password' : 'text';
            this.classList.toggle('fa-eye-slash', isPasswordVisible);
            this.classList.toggle('fa-eye', !isPasswordVisible);
        });
    </script>
</body>
</html>