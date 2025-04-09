<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Password Recovery</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient( #0671B7 0%, #67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
        .recovery-container {
            background-color: #ffffff;
            padding: 20px 25px;
            border-radius: 16px;
            box-shadow: 0px 8px 10px rgba(0, 0, 0, 0.4);
            text-align: center;
            transition: transform 0.3s;
            width: 100%;
        }
        .recovery-container:hover {
            transform: translateY(-10px);
        }
        p{
            margin-bottom: 30px;
            text-align: left;
            font-weight: 100;
            color:#908c8c;
            font-size: 15px;
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
        }
        .input-group input:focus {
            border-color: #74ebd5;
            outline: none;
            box-shadow: 0 0 8px rgba(116, 235, 213, 0.5);
        }
        .send-button {
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
        .send-button:hover {
            background: linear-gradient(90deg, #57c6a9, #74ebd5);
        }
        .login-link {
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
        .login-link a {
            color: #57c6a9;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="recovery-container">
        <h2>Password Recovery</h2>
        <?php
        if (isset($_GET["error"]) && $_GET["error"] === "expired") {
            echo "<div style='color: red; font-size: 18px; font-weight: bold; padding: 15px;
                        border: 2px solid red; background-color: #ffe6e6; text-align: center;
                        border-radius: 5px; width: 80%; margin: 20px auto;'>
                    ❌ Invalid or expired reset link. Please 
                    <a href='password-recovery.php' style='color: blue; font-weight: bold; text-decoration: none;'>request a new one</a>.
                  </div>";
        }
        ?>
        <p>
            <span style="display: block; margin-bottom: 7px;">Enter your email to reset your password</span>
            <span style="display: block;">You will receive a link to create a new password</span>
        </p>
        <form method="post" id="recoveryForm" action="../Auth/send-password-reset.php">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="send-button">Send Reset Link</button>
        </form>
        <p class="login-link">Remember your password? <a href="../Auth/login.php">Back to Login</a></p>
    </div>
</div>

<script>
    document.getElementById("recoveryForm").addEventListener("submit", function(event) {
        
        let email = document.getElementById("email").value;
        
        // Simulate sending email (Replace this with actual backend logic)
        alert("A password reset link has been sent to " + email);
    });
</script>

</body>
</html>
