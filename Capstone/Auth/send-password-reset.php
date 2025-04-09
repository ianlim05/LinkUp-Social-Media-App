<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if email is provided
if (!isset($_POST["email"]) || empty($_POST["email"])) {
    die("❌ Error: No email provided.");
}

$email = $_POST["email"];

// Generate token
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
// $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$conn = require __DIR__ . "/../!! General/database.php";

if (!$conn) {
    die("Database connection failed.");
}

// Prepare the SQL query
$sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
// Check if statement was prepared successfully
if (!$stmt) {
    die("Statement preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $token_hash, $email);
mysqli_stmt_execute($stmt);

// Check if any row was updated
if (mysqli_stmt_affected_rows($stmt) == 0) {
    die("No account found with this email.");
}

// Load mailer
$mail = require __DIR__ . "/mailer.php";
$mail->setFrom("noreply@example.com", "LinkUp");
$mail->addAddress($email);
$mail->Subject = "Password Reset";
$mail->isHTML(true);
$mail->Body = "<p> Click <a href='http://localhost/LinkUp/Capstone/Auth/reset-password.php?token=$token'>here</a> to reset your password. </p>";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Message Sent</title>
</head>
<body>
<?php
try {
    if (!$mail->send()) {
        echo "<div style='color: red; font-size: 18px; font-weight: bold; padding: 10px; border: 2px solid red; background-color: #ffe6e6; text-align: center; border-radius: 5px;'>
        ❌ Mailer Error: " . htmlspecialchars($mail->ErrorInfo). "
        </div>";
    } else {
        echo "<div style='color: green; font-size: 20px; font-weight: bold; padding: 10px; border: 2px solid green; background-color: #e6ffe6; text-align: center; border-radius: 5px;'>
                ✅ Message sent successfully! Please check your inbox.
              </div>";
    }
} catch (Exception $e) {
    echo "<div style='color: red; font-size: 18px; font-weight: bold; padding: 10px; border: 2px solid red; background-color: #ffe6e6; text-align: center; border-radius: 5px;'>
            ❌ Error sending email: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
</body>
</html>