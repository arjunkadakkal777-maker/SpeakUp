<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$to = "aashnaraj2512006@gmail.com"; // Sending to self to test
$subject = "Test Email from XAMPP";
$message = "If you see this, XAMPP email is working!";
$headers = "From: aashnaraj2512006@gmail.com";

echo "Attempting to send email to $to...<br>";

if (mail($to, $subject, $message, $headers)) {
    echo "<strong>Success:</strong> PHP mail() function returned TRUE.<br>";
    echo "Check your inbox (and spam folder).<br>";
    echo "If it didn't arrive, check C:\\xampp\\sendmail\\error.log";
} else {
    echo "<strong>Failure:</strong> PHP mail() function returned FALSE.<br>";
}
?>
