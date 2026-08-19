<?php
/**
 * Yearnsoft Solutions — Form Mailer
 * Handles: Contact form  +  Job Application form
 * Sends to: sandeep.mishra318@gmail.com
 *
 * Place this file in the SAME folder as index.html (your main HTML file).
 * Requires PHP 7.4+ with mail() enabled on the server,
 * OR swap mail() for PHPMailer/SMTP if your host blocks PHP mail.
 */

// ── CONFIG ──────────────────────────────────────────────────────────────────
define('TO_EMAIL',   'sandeep.mishra318@gmail.com');
define('TO_NAME',    'Sandeep Mishra');
define('FROM_EMAIL', 'noreply@yearnsoft.neowiseindia.com');   // must match your domain
define('FROM_NAME',  'Yearnsoft Solutions');
define('SITE_NAME',  'Yearnsoft Solutions');

// ── CORS / HEADERS ──────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// ── HELPER FUNCTIONS ────────────────────────────────────────────────────────

/** Sanitise a plain-text field */
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

/** Validate email address */
function validEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/** Validate URL (allow empty) */
function validUrl(string $url): bool {
    if ($url === '') return true;
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
}

/** Send email using PHP mail() */
function sendMail(string $subject, string $body, string $replyTo, string $replyName): bool {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: {$replyName} <{$replyTo}>\r\n";
    $headers .= "X-Mailer: PHP/" . PHP_VERSION . "\r\n";

    return mail(TO_EMAIL, $subject, $body, $headers);
}

/** Wrap content in a branded HTML email shell */
function emailWrapper(string $title, string $content): string {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#0a0a0a;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0a0a;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

      <!-- Header -->
      <tr>
        <td style="background:linear-gradient(135deg,#020d08,#0a1f10);border:1px solid #1a4028;border-radius:12px 12px 0 0;padding:30px 36px;text-align:center;">
          <div style="font-family:'Segoe UI',Arial,sans-serif;font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">
            Yearn<span style="color:#00ff88;">soft</span>
          </div>
          <div style="color:#5a8a65;font-size:12px;margin-top:4px;letter-spacing:2px;text-transform:uppercase;">Solutions</div>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="background:#0d1a10;border-left:1px solid #1a4028;border-right:1px solid #1a4028;padding:36px;">
          <h2 style="font-family:'Segoe UI',Arial,sans-serif;font-size:20px;font-weight:700;color:#ffffff;margin:0 0 24px 0;padding-bottom:16px;border-bottom:1px solid #1a4028;">{$title}</h2>
          {$content}
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#020d08;border:1px solid #1a4028;border-top:none;border-radius:0 0 12px 12px;padding:20px 36px;text-align:center;">
          <p style="color:#5a8a65;font-size:12px;margin:0;">
            © 2026 Yearnsoft Solutions &nbsp;·&nbsp;
            House No. 337, Chanakyapuri, Saharsa, Bihar 852201 &nbsp;·&nbsp;
            <a href="mailto:sandeep.mishra318@gmail.com" style="color:#00ff88;text-decoration:none;">sandeep.mishra318@gmail.com</a>
          </p>
          <p style="color:#3a5a40;font-size:11px;margin:8px 0 0 0;">
            This email was sent from the Yearnsoft Solutions contact form.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}

/** Build one field row for the email body */
function row(string $label, string $value, bool $highlight = false): string {
    $bg    = $highlight ? '#0a2010' : '#071208';
    $color = $highlight ? '#00ff88' : '#d4f0dd';
    return <<<HTML
<tr>
  <td style="padding:10px 0;border-bottom:1px solid #1a3020;vertical-align:top;">
    <span style="display:block;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#5a8a65;margin-bottom:4px;">{$label}</span>
    <span style="display:block;background:{$bg};border:1px solid #1a3020;border-radius:6px;padding:10px 14px;color:{$color};font-size:14px;line-height:1.6;word-break:break-word;">{$value}</span>
  </td>
</tr>
HTML;
}

// ── ROUTE BY FORM TYPE ───────────────────────────────────────────────────────

$formType = clean($_POST['form_type'] ?? '');

// ════════════════════════════════════════════════════════════════════════════
//  CONTACT FORM
// ════════════════════════════════════════════════════════════════════════════
if ($formType === 'contact') {

    // Collect & sanitise
    $name    = clean($_POST['name']    ?? '');
    $email   = clean($_POST['email']   ?? '');
    $phone   = clean($_POST['phone']   ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');

    // Validate
    $errors = [];
    if (!$name)              $errors[] = 'Name is required.';
    if (!validEmail($email)) $errors[] = 'A valid email address is required.';
    if (!$phone)             $errors[] = 'Phone number is required.';
    if (!$subject)           $errors[] = 'Subject is required.';
    if (!$message)           $errors[] = 'Message is required.';

    if ($errors) {
        echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
        exit;
    }

    // Build email body
    $content = '<table width="100%" cellpadding="0" cellspacing="0">'
        . row('From', "{$name}")
        . row('Email', "<a href=\"mailto:{$email}\" style=\"color:#00ff88;\">{$email}</a>")
        . row('Phone', $phone)
        . row('Subject', $subject, true)
        . row('Message', nl2br($message))
        . '</table>';

    $emailSubject = "[Contact] {$subject} — from {$name}";
    $body         = emailWrapper("New Contact Message", $content);

    if (sendMail($emailSubject, $body, $email, $name)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Mail server error. Please email us directly at sandeep.mishra318@gmail.com']);
    }
    exit;
}

// ════════════════════════════════════════════════════════════════════════════
//  JOB APPLICATION FORM
// ════════════════════════════════════════════════════════════════════════════
if ($formType === 'application') {

    // Collect & sanitise
    $position   = clean($_POST['position']   ?? '');
    $name       = clean($_POST['name']       ?? '');
    $email      = clean($_POST['email']      ?? '');
    $phone      = clean($_POST['phone']      ?? '');
    $experience = clean($_POST['experience'] ?? '');
    $portfolio  = clean($_POST['portfolio']  ?? '');
    $cover      = clean($_POST['cover']      ?? '');

    // Validate
    $errors = [];
    if (!$position)           $errors[] = 'Position is missing.';
    if (!$name)               $errors[] = 'Full name is required.';
    if (!validEmail($email))  $errors[] = 'A valid email address is required.';
    if (!$phone)              $errors[] = 'Phone number is required.';
    if (!$experience)         $errors[] = 'Years of experience is required.';
    if (!$portfolio)          $errors[] = 'Portfolio / LinkedIn URL is required.';
    elseif (!validUrl($portfolio)) $errors[] = 'Portfolio URL does not look valid.';
    if (!$cover)              $errors[] = 'Cover letter is required.';

    if ($errors) {
        echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
        exit;
    }

    // Portfolio as a link
    $portfolioLink = "<a href=\"{$portfolio}\" style=\"color:#00ff88;\">{$portfolio}</a>";

    // Build email body
    $content = '<table width="100%" cellpadding="0" cellspacing="0">'
        . row('Applied Position', $position, true)
        . row('Applicant Name', $name)
        . row('Email', "<a href=\"mailto:{$email}\" style=\"color:#00ff88;\">{$email}</a>")
        . row('Phone', $phone)
        . row('Years of Experience', $experience)
        . row('Portfolio / LinkedIn', $portfolioLink)
        . row('Cover Letter', nl2br($cover))
        . '</table>';

    $emailSubject = "[Job Application] {$position} — {$name}";
    $body         = emailWrapper("New Job Application — {$position}", $content);

    if (sendMail($emailSubject, $body, $email, $name)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Mail server error. Please email us directly at sandeep.mishra318@gmail.com']);
    }
    exit;
}

// ── Unknown form type ────────────────────────────────────────────────────────
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Unknown form type.']);
