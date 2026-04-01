<?php
// ══════════════════════════════════════════════════════════════════════
//  RENTAL APPLICATION HANDLER — tammyseiler.com
//  File:    rental-application.php
//  Place:   public_html/ (same folder as rental-application.html)
//  Handles: rentalForm on rental-application.html
// ══════════════════════════════════════════════════════════════════════

// ── ONLY ACCEPT POST REQUESTS ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// ── CONFIGURATION — update these as needed ────────────────────────────
$to_email   = 'tammy@tammyseiler.com';   // where applications are sent
$from_email = 'forms@tammyseiler.com';        // must match your Bluehost domain
$cc_email   = '';                             // optional CC address — leave blank if not needed
$site_name  = 'TammySeiler.com';

// ── HELPER: sanitise a single value ──────────────────────────────────
function clean($value) {
    return htmlspecialchars(strip_tags(trim((string)$value)), ENT_QUOTES, 'UTF-8');
}

// ── HELPER: Yes / No for radio buttons ───────────────────────────────
function yesno($value) {
    $v = strtolower(clean($value));
    if ($v === 'yes') return 'Yes';
    if ($v === 'no')  return 'No';
    return 'Not answered';
}

// ══════════════════════════════════════════════════════════════════════
//  COLLECT & SANITISE ALL FORM FIELDS
// ══════════════════════════════════════════════════════════════════════

// ── Section 1: Personal Information ──────────────────────────────────
$first_name      = clean($_POST['firstName']      ?? '');
$last_name       = clean($_POST['lastName']       ?? '');
$full_name       = trim("$first_name $last_name");
$email           = clean($_POST['email']          ?? '');
$phone           = clean($_POST['phone']          ?? '');
$dob             = clean($_POST['dob']            ?? '');
$income          = clean($_POST['income']         ?? '');
$current_address = clean($_POST['currentAddress'] ?? '');

// ── Section 2: Property & Move-In ────────────────────────────────────
$property        = clean($_POST['property']       ?? '');
$movedate        = clean($_POST['movedate']       ?? '');
$occupants       = clean($_POST['occupants']      ?? '');
$reason          = clean($_POST['reason']         ?? '');

// ── Section 3: Background Questions (Yes/No radio buttons) ───────────
$has_pet         = yesno($_POST['pet']        ?? '');
$was_evicted     = yesno($_POST['evicted']    ?? '');
$has_felonies    = yesno($_POST['felonies']   ?? '');
$has_bankruptcy  = yesno($_POST['bankruptcy'] ?? '');
$is_smoker       = yesno($_POST['smoke']      ?? '');

// ══════════════════════════════════════════════════════════════════════
//  VALIDATION
// ══════════════════════════════════════════════════════════════════════

$errors = [];

if (empty($first_name) || empty($last_name)) {
    $errors[] = 'Full name is required.';
}

if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required.';
}

if (!empty($errors)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ══════════════════════════════════════════════════════════════════════
//  BUILD EMAIL
// ══════════════════════════════════════════════════════════════════════

$subject = "[$site_name] Rental Application — $full_name";

// Format dates for readability
$dob_fmt      = $dob      ? date('F j, Y', strtotime($dob))      : 'Not provided';
$movedate_fmt = $movedate ? date('F j, Y', strtotime($movedate)) : 'Not provided';

$body  = "══════════════════════════════════════════════\n";
$body .= "  RENTAL APPLICATION — $site_name\n";
$body .= "══════════════════════════════════════════════\n";
$body .= "Received: " . date('l, F j, Y \a\t g:i A T') . "\n\n";

$body .= "── PERSONAL INFORMATION ──────────────────────\n";
$body .= "Name:              $full_name\n";
$body .= "Email:             $email\n";
$body .= "Phone:             $phone\n";
$body .= "Date of Birth:     $dob_fmt\n";
$body .= "Monthly Income:    " . ($income ?: 'Not provided') . "\n";
$body .= "Current Address:   " . ($current_address ?: 'Not provided') . "\n\n";

$body .= "── PROPERTY & MOVE-IN ───────────────────────\n";
$body .= "Property Requested: " . ($property   ?: 'Not specified') . "\n";
$body .= "Move-In Date:       $movedate_fmt\n";
$body .= "Number of Occupants:" . ($occupants  ?: 'Not provided') . "\n";
$body .= "Reason for Moving:  " . ($reason     ?: 'Not provided') . "\n\n";

$body .= "── BACKGROUND QUESTIONS ─────────────────────\n";
$body .= "Has Pets:           $has_pet\n";
$body .= "Prior Eviction:     $was_evicted\n";
$body .= "Felony Conviction:  $has_felonies\n";
$body .= "Prior Bankruptcy:   $has_bankruptcy\n";
$body .= "Smoker:             $is_smoker\n\n";

$body .= "══════════════════════════════════════════════\n";
$body .= "Reply directly to this email to contact the applicant.\n";
$body .= "Sent from tammyseiler.com\n";

// ══════════════════════════════════════════════════════════════════════
//  EMAIL HEADERS
// ══════════════════════════════════════════════════════════════════════

$headers  = "From: $site_name <$from_email>\r\n";
$headers .= "Reply-To: $full_name <$email>\r\n";  // reply goes straight to applicant
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Add CC if configured
if (!empty($cc_email)) {
    $headers .= "Cc: $cc_email\r\n";
}

// ══════════════════════════════════════════════════════════════════════
//  SEND
// ══════════════════════════════════════════════════════════════════════

$sent = mail($to_email, $subject, $body, $headers);

// ══════════════════════════════════════════════════════════════════════
//  RESPOND TO THE BROWSER
// ══════════════════════════════════════════════════════════════════════

header('Content-Type: application/json');

if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your application has been received. Tammy will be in touch within 1–2 business days.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was a problem submitting your application. Please call Tammy directly at 385-327-9225.'
    ]);
}
?>