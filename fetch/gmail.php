<?php
$hostname = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';
$username = 'joicebarandon31@gmail.com';  // Your Gmail
$password = 'gmbviduachzzyazu';          // Use App Password (NOT your real password)

// Open IMAP connection
$inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

// Fetch all emails from Sent Mail
$emails = imap_search($inbox, 'ALL');

if ($emails) {
    rsort($emails); // Sort emails (newest first)

    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        $message = imap_fetchbody($inbox, $email_number, 1); // Fetch email body (plain text)

        echo "<h3>Subject: " . htmlspecialchars($overview[0]->subject) . "</h3>";
        echo "<p>From: " . htmlspecialchars($overview[0]->from) . "</p>";
        echo "<p>Date: " . htmlspecialchars($overview[0]->date) . "</p>";
        echo "<p>Message:</p><pre>" . htmlspecialchars($message) . "</pre>";
        echo "<hr>";
    }
} else {
    echo "No emails found in Sent Mail.";
}

// Close IMAP connection
imap_close($inbox);
?>
