<?php

$token = trim($_POST["token"]);

$token_hash = hash("sha256", $token);

$conn = require __DIR__ . "/../!! General/database.php";
if (!$conn) {
    die("Failed to connect to database.");
}

$sql = "SELECT * FROM users
        WHERE reset_token_hash = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Failed to prepare statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $token_hash);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if ($user === null) {
    header("Location: reset-password.php?error=invalid&token=" . urlencode($token));
    exit;
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    header("Location: reset-password.php?error=expired&token=" . urlencode($token));
    exit;
}

if (empty($_POST["password"])) {
    header("Location: reset-password.php?error=empty&token=" . urlencode($token));
    exit;
}

$password = $_POST["password"];
$password_confirmation = $_POST["password_confirmation"];

if (strlen($password) < 8) {
    header("Location: reset-password.php?error=length&token=" . urlencode($token));
    exit;
}

if (!preg_match("/[a-z]/i", $password)) {
    header("Location: reset-password.php?error=letter&token=" . urlencode($token));
    exit;
}

if (!preg_match("/[0-9]/", $password)) {
    header("Location: reset-password.php?error=number&token=" . urlencode($token));
    exit;
}

if ($password !== $password_confirmation) {
    header("Location: reset-password.php?error=match&token=" . urlencode($token));
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "UPDATE users
        SET password = ?,
            reset_token_hash = NULL,
            reset_token_expires_at = NULL
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Failed to prepare update statement: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user["id"]);
mysqli_stmt_execute($stmt);

echo "<div style='color: green; font-size: 20px; font-weight: bold; padding: 15px; border: 2px solid green; background-color: #e6ffe6; text-align: center; border-radius: 5px; width: 50%; margin: 20px auto;'>
        ✅ Password updated successfully! You can now <a href='../Auth/login.php' style='color: blue; font-weight: bold; text-decoration: none;'>Login</a>.
      </div>";