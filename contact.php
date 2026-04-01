<?php
// ══════════════════════════════════════════════════════════════════════
//  CONTACT FORM HANDLER — tammyseiler.com
//  File:    contact.php
//  Place:   public_html/ (same folder as all HTML files)
// ══════════════════════════════════════════════════════════════════════

// ── ONLY ACCEPT POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// ── CONFIGURATION ─────────────────────────────────────────────────────
$to_email   = 'tammy@tammyseiler.com';  // destination — Tammy's inbox
$from_email = 'forms@tammyseiler.com';       // must exist in Bluehost Email Accounts
$site_name  = 'TammySeiler.com';
$log_file   = __DIR__ . '/mail_errors.log'; // error log — delete after debugging

// ── HELPER ────────────────────────────────────────────────────────────
function clean($val) {
    return htmlspecialchars(strip_tags(trim((string)$val)), ENT_QUOTES, 'UTF-8');
}

// ── COLLECT FIELDS ────────────────────────────────────────────────────
$form_type = clean($_POST['form_type'] ?? 'General Contact');

// Handle both firstName/lastName and name field formats
$first     = clean($_POST['firstName']  ?? '');
$last      = clean($_POST['lastName']   ?? '');
$full_name = trim("$first $last");
if (empty($full_name)) {
    $full_name = clean($_POST['name'] ?? 'Not provided');
}

$email    = clean($_POST['email']    ?? '');
$phone    = clean($_POST['phone']    ?? '');
$address  = clean($_POST['address']  ?? clean($_POST['currentAddress'] ?? ''));
$timeline = clean($_POST['timeline'] ?? clean($_POST['movedate']       ?? ''));
$interest = clean($_POST['interest'] ?? clean($_POST['subject']        ?? ''));
$from_loc = clean($_POST['from_location'] ?? '');
$message  = clean($_POST['message']  ?? clean($_POST['reason']         ?? ''));

// ── VALIDATION ────────────────────────────────────────────────────────
if (empty($email) && empty($phone)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please provide at least an email or phone number.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── BUILD EMAIL BODY ──────────────────────────────────────────────────
$subject = "[$site_name] $form_type";

$body  = "$form_type\n";
$body .= str_repeat('=', 50) . "\n";
$body .= "Received: " . date('Y-m-d H:i:s T') . "\n\n";
$body .= "Name:     $full_name\n";
$body .= "Email:    " . ($email    ?: 'Not provided') . "\n";
$body .= "Phone:    " . ($phone    ?: 'Not provided') . "\n";

if ($address)  $body .= "Address:  $address\n";
if ($timeline) $body .= "Timeline: $timeline\n";
if ($interest) $body .= "Interest: $interest\n";
if ($from_loc) $body .= "From:     $from_loc\n";
if ($message)  $body .= "\nMessage:\n$message\n";

$body .= "\n" . str_repeat('-', 50) . "\n";
$body .= "Sent from $site_name";

// ── HEADERS ───────────────────────────────────────────────────────────
$reply_to = !empty($email) ? $email : $from_email;

$headers  = "From: $site_name <$from_email>\r\n";
$headers .= "Reply-To: $reply_to\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// ── ADDITIONAL PARAMETERS for Bluehost ───────────────────────────────
// The -f flag tells Bluehost's sendmail exactly which address to send from.
// This is the most common fix for mail() failing on Bluehost.
$additional_params = "-f $from_email";

// ── SEND ──────────────────────────────────────────────────────────────
$sent = mail($to_email, $subject, $body, $headers, $additional_params);

// ── LOG ERRORS ────────────────────────────────────────────────────────
if (!$sent) {
    $log_entry = date('Y-m-d H:i:s') . " | mail() failed"
               . " | to=$to_email"
               . " | from=$from_email"
               . " | form=$form_type"
               . " | php_version=" . phpversion()
               . " | error=" . error_get_last()['message'] ?? 'none'
               . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// ── RESPOND ───────────────────────────────────────────────────────────
header('Content-Type: application/json');

if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Tammy will be in touch within 24 hours.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was a problem sending your message. Please call 385-327-9225 directly.'
    ]);
}
?>