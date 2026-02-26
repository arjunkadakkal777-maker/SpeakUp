<?php
$phpIni = 'C:\xampp\php\php.ini';
$content = file_get_contents($phpIni);

// Uncomment sendmail_path and set it
$content = preg_replace(
    '/^;?sendmail_path\s*=.*$/m', 
    'sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"', 
    $content
);

file_put_contents($phpIni, $content);
echo "php.ini updated.\n";

$sendmailIni = 'C:\xampp\sendmail\sendmail.ini';
$sContent = file_get_contents($sendmailIni);

// Ensure SMTP settings are correct
// Check if smtp_server exists, if not prepend it? 
// The regex replace earlier might have failed if multiple occurrences.
// Let's force set the [sendmail] block values.

$replacements = [
    '/^smtp_server=.*$/m' => 'smtp_server=smtp.gmail.com',
    '/^smtp_port=.*$/m' => 'smtp_port=587',
    '/^auth_password=.*$/m' => 'auth_password=wdsnsiovezfpcggy', // Removing spaces
];

foreach ($replacements as $pattern => $replace) {
    $sContent = preg_replace($pattern, $replace, $sContent);
}

file_put_contents($sendmailIni, $sContent);
echo "sendmail.ini updated (removed spaces from password, checked server).\n";
?>
