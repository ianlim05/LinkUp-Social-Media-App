<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";

$mail = new PHPMailer(true);

$mail->SMTPDebug = 0;

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = "smtp.gmail.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = "ianlim575@gmail.com";
$mail->Password = "jteg circ ggxu hkmk";

$mail->setFrom("noreply@example.com", "LinkUp");
$mail->isHtml(true);

return $mail;