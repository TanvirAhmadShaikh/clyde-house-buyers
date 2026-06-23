<?php
/**
 * Lead form handler — Clyde Housebuyers (Fasthosts SMTP version)
 *
 * Sends two emails per submission:
 *   1. Lead notification to LEAD_RECIPIENT (you)
 *   2. Auto-reply to the seller (the form-filler)
 *
 * Both go via authenticated SMTP through Fasthosts (smtp.livemail.co.uk:587).
 *
 * Also: validates server-side, appends to /private/leads.csv, optional HighLevel
 * webhook, optional reCAPTCHA v3.
 *
 * --- CONFIGURATION ---
 * 1. Create a mailbox 'no-reply@clydehousebuyers.co.uk' in Fasthosts control panel
 *    (set any password — you'll never log in interactively).
 * 2. Set SMTP_USER and SMTP_PASS below to that mailbox's credentials.
 * 3. Set LEAD_RECIPIENT to the email you actually monitor.
 */

require_once __DIR__ . '/includes/smtp.php';

/* ============================================================
   CONFIG — edit these
   ============================================================ */

// Where the lead notification goes (your real inbox)
$LEAD_RECIPIENT      = 'info@clydehousebuyers.co.uk';

// The mailbox that SENDS the emails (must exist on Fasthosts)
$FROM_EMAIL          = 'no-reply@clydehousebuyers.co.uk';
$FROM_NAME           = 'Clyde Housebuyers Website';

// SMTP host config (non-secret — safe in version control)
$SMTP_HOST           = 'smtp.livemail.co.uk';
$SMTP_PORT           = 587;
$SMTP_SECURE         = 'starttls';                // starttls | ssl | none
$SMTP_USER           = 'no-reply@clydehousebuyers.co.uk';

// SMTP password — loaded from /private/secrets.php so this file can be
// committed and deployed without leaking credentials. secrets.php is
// excluded from rsync, so the live server keeps its real password and
// each deploy of submit-lead.php just re-uses whatever's already there.
$SMTP_PASS = '';  // populated below from secrets.php
$_secrets_file = __DIR__ . '/private/secrets.php';
if (is_readable($_secrets_file)) {
    $_secrets = require $_secrets_file;
    if (is_array($_secrets) && !empty($_secrets['smtp_pass'])) {
        $SMTP_PASS = $_secrets['smtp_pass'];
    }
}
if ($SMTP_PASS === '') {
    // Don't crash the form — saving the lead still works without SMTP.
    // The submission will land in private/leads.csv but no notification
    // email goes out. The lead is not lost. error_log so we know.
    error_log('submit-lead.php: SMTP_PASS not configured — emails will not send. Add smtp_pass to private/secrets.php.');
}

// Optional integrations
$HL_WEBHOOK          = '';   // HighLevel inbound webhook URL (leave blank to disable)
$RECAPTCHA_SECRET    = '';   // reCAPTCHA v3 secret (leave blank to disable)

// Debug mode — set true ONLY while testing. Writes SMTP transcripts to /private/smtp-debug.log.
// Set back to false once you're confident emails are flowing.
$DEBUG_SMTP          = false;

/* ============================================================
   END CONFIG
   ============================================================ */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

/* --- Honeypot (silent bot trap) --- */
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);  // pretend success to the bot
    exit;
}

/* --- Helpers --- */

/**
 * Sanitises a single POST field.
 *
 * - Coerces arrays to comma-joined strings
 * - Strips HTML tags
 * - Strips ALL control characters except \n if $allow_newlines is true
 * - Trims whitespace and truncates to max length
 *
 * @param string $key            POST key to read
 * @param int    $max            Max length (multibyte-safe)
 * @param bool   $allow_newlines If true, keep \n (after normalising \r\n→\n). For textareas.
 */
function clean($key, $max = 500, $allow_newlines = false) {
    $v = $_POST[$key] ?? '';
    if (is_array($v)) $v = implode(', ', $v);
    $v = (string)$v;

    // Strip control characters: \x00-\x08, \x0B-\x0C, \x0E-\x1F, \x7F.
    // We deliberately keep \x09 (tab — will be neutralised by csv_safe if present at start)
    // and \x0A (newline — handled by $allow_newlines below).
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);

    if ($allow_newlines) {
        // Normalise line endings to \n. Cap consecutive newlines at 2 (one blank line).
        $v = str_replace(["\r\n", "\r"], "\n", $v);
        $v = preg_replace("/\n{3,}/", "\n\n", $v);
    } else {
        // Single-line field — neutralise CR/LF to prevent SMTP header injection.
        $v = str_replace(["\r", "\n"], ' ', $v);
    }

    $v = trim(strip_tags($v));
    return mb_substr($v, 0, $max);
}

/**
 * Defang CSV formula injection.
 * Excel/Sheets interpret values starting with =, +, -, @, tab or CR as formulas — including
 * dangerous ones like =HYPERLINK or DDE-based command execution. Prefix any such value with
 * a single quote so it's treated as text. Reference: OWASP Formula Injection (CWE-1236).
 */
function csv_safe($v) {
    if ($v === '' || $v === null) return $v;
    $first = substr($v, 0, 1);
    if (in_array($first, ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $v;
    }
    return $v;
}

function valid_postcode($pc) {
    $pc = strtoupper(preg_replace('/\s+/', '', $pc));
    return preg_match('/^[A-Z]{1,2}[0-9R][0-9A-Z]?[0-9][A-Z]{2}$/', $pc);
}
function valid_phone($p) {
    $p = preg_replace('/[\s\-\(\)]/', '', $p);
    return preg_match('/^(\+?44|0)[0-9]{9,11}$/', $p);
}

/**
 * Rate-limit by client IP. Tracks recent submission timestamps in a JSON file
 * inside /private/. Allows MAX_PER_WINDOW submissions per WINDOW_SECONDS.
 * Returns ['allow' => bool, 'reason' => 'message'].
 */
function rate_limit_check($ip) {
    $MAX_PER_WINDOW = 3;
    $WINDOW_SECONDS = 300;  // 5 minutes
    $DAILY_MAX = 12;        // hard daily cap per IP
    $DAY_SECONDS = 86400;

    $rl_dir = __DIR__ . '/private';
    if (!is_dir($rl_dir)) @mkdir($rl_dir, 0700, true);
    $rl_file = $rl_dir . '/ratelimit.json';

    $now = time();
    $data = [];
    if (is_readable($rl_file)) {
        $raw = @file_get_contents($rl_file);
        $data = $raw ? (json_decode($raw, true) ?: []) : [];
    }

    // Purge entries older than DAY_SECONDS to keep file small
    foreach ($data as $k => $entry) {
        $data[$k] = array_values(array_filter($entry, fn($t) => ($now - $t) < $DAY_SECONDS));
        if (empty($data[$k])) unset($data[$k]);
    }

    // Hash the IP so the file never stores raw IPs (mild privacy improvement)
    $key = substr(hash('sha256', $ip . '|cdhb-rl'), 0, 16);
    $hits = $data[$key] ?? [];

    // Window check
    $recent = array_filter($hits, fn($t) => ($now - $t) < $WINDOW_SECONDS);
    if (count($recent) >= $MAX_PER_WINDOW) {
        return ['allow' => false, 'reason' => 'Too many submissions. Please try again in a few minutes, or call us directly on 0141 530 1430.'];
    }
    if (count($hits) >= $DAILY_MAX) {
        return ['allow' => false, 'reason' => 'Submission limit reached for today. Please call us on 0141 530 1430.'];
    }

    // Record this attempt (use exclusive lock to avoid races)
    $hits[] = $now;
    $data[$key] = array_values($hits);
    $fp = @fopen($rl_file, 'c');
    if ($fp) {
        if (@flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    return ['allow' => true, 'reason' => ''];
}

/* --- Rate limit by IP (M2) --- */
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rl = rate_limit_check($client_ip);
if (!$rl['allow']) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => $rl['reason']]);
    exit;
}

/* --- Timing check: detect bot-fast submissions (M2/M3) --- */
// Form renders a hidden `form_started` timestamp via JS. If under 3 seconds, treat as suspicious.
// We don't reject on this alone (some users are genuinely fast); we just skip the auto-reply
// and flag the lead so you can decide whether to call back.
$submitted_too_fast = false;
$form_started = isset($_POST['form_started']) ? (int)$_POST['form_started'] : 0;
if ($form_started > 0) {
    $elapsed_ms = (int)(microtime(true) * 1000) - $form_started;
    if ($elapsed_ms < 3000) $submitted_too_fast = true;
}

/* --- Required fields --- */
$postcode     = clean('postcode', 12);
$prop_type    = clean('property_type');
$condition    = clean('condition');
$situation    = clean('situation', 1000);
$timeline     = clean('timeline');
$first        = clean('first_name', 80);
$phone        = clean('phone', 30);
$email        = clean('email', 200);
$contact_pref = clean('contact_preference', 100);
$notes        = clean('notes', 2000, true);  // allow_newlines: textarea field

/* --- Detect submission type ---
   Four variants currently:
     postcode = 'N/A-FUNNEL'   → funnel landing pages: name + phone only (lowest-friction)
     postcode = 'N/A-CONTACT'  → contact page: name + phone + email + message
     postcode = '<real>' AND tags include 'quiz' (via _quiz hidden field)
                               → quiz funnels: real postcode + qualification fields, but no email
     postcode = '<real>'       → valuation form: full property + contact details
   The funnel and quiz variants are intentionally minimal — paid traffic on
   mobile, every extra field measurably costs conversions. Email is collected
   on the follow-up call instead. */
$is_funnel       = ($postcode === 'N/A-FUNNEL');
$is_contact_form = ($postcode === 'N/A-CONTACT');
$is_quiz         = !empty($_POST['_quiz']);

$errors = [];
if ($is_funnel) {
    // Funnel form: just name + phone. Everything else gathered on the call.
    if (!$first || strlen($first) < 2) $errors[] = 'Name required.';
    if (!$phone || !valid_phone($phone)) $errors[] = 'Valid UK phone number required.';
} elseif ($is_contact_form) {
    // Contact form: only require name, phone, email, and a message
    if (!$first || strlen($first) < 2) $errors[] = 'Name required.';
    if (!$phone || !valid_phone($phone)) $errors[] = 'Valid UK phone number required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!$notes) $errors[] = 'Please include a message.';
} elseif ($is_quiz) {
    // Quiz funnel: postcode + type + condition + timeline (from the quiz steps) + name + phone. Email not required.
    if (!$postcode || !valid_postcode($postcode)) $errors[] = 'Valid postcode required.';
    if (!$prop_type) $errors[] = 'Property type required.';
    if (!$condition) $errors[] = 'Condition required.';
    if (!$timeline) $errors[] = 'Timeline required.';
    if (!$first || strlen($first) < 2) $errors[] = 'First name required.';
    if (!$phone || !valid_phone($phone)) $errors[] = 'Valid UK phone number required.';
} else {
    // Valuation form: full property + contact details required
    if (!$postcode || !valid_postcode($postcode)) $errors[] = 'Valid postcode required.';
    if (!$prop_type) $errors[] = 'Property type required.';
    if (!$condition) $errors[] = 'Condition required.';
    if (!$timeline) $errors[] = 'Timeline required.';
    if (!$first || strlen($first) < 2) $errors[] = 'First name required.';
    if (!$phone || !valid_phone($phone)) $errors[] = 'Valid UK phone number required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
}

if ($errors) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => implode(' ', $errors)]);
    exit;
}

/* --- Optional reCAPTCHA v3 check --- */
if ($RECAPTCHA_SECRET && !empty($_POST['recaptcha_token'])) {
    $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($RECAPTCHA_SECRET) . '&response=' . urlencode($_POST['recaptcha_token']));
    if ($resp) {
        $data = json_decode($resp, true);
        if (empty($data['success']) || ($data['score'] ?? 0) < 0.4) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Verification failed. Please try calling us on 0141 530 1430.']);
            exit;
        }
    }
}

/* --- Tag strategy hint --- */
$tags = [];
$sit_lower = strtolower($situation);
if (strpos($sit_lower, 'inherited') !== false || strpos($sit_lower, 'probate') !== false) $tags[] = 'probate';
if (strpos($sit_lower, 'divorce') !== false) $tags[] = 'divorce';
if (strpos($sit_lower, 'repossession') !== false || strpos($sit_lower, 'mortgage') !== false || strpos($sit_lower, 'financial') !== false) $tags[] = 'repossession-urgent';
if (strpos($sit_lower, 'tenant') !== false || strpos($sit_lower, 'landlord') !== false) $tags[] = 'tenanted';
if (strpos($sit_lower, 'work') !== false || strpos($sit_lower, 'repair') !== false) $tags[] = 'assisted-sale-candidate';
if (strpos($sit_lower, 'non-standard') !== false || strpos($sit_lower, 'construction') !== false) $tags[] = 'non-standard-construction';
if ($timeline === 'Within 4 weeks') $tags[] = 'priority';
$tag_string = $tags ? implode(', ', $tags) : 'general';

/* --- Suspicion flag (M3) --- */
// A submission is "suspicious" if it came in unrealistically fast.
// We still send YOU the lead (you can ignore it), but we skip the auto-reply to the seller's
// email — because if it's a spoofed third-party email, we don't want to spam them on the
// attacker's behalf.
$is_suspicious = $submitted_too_fast;

/* --- Build lead notification body --- */
$ts = date('Y-m-d H:i:s');
$body = "New lead from clydehousebuyers.co.uk\n";
$body .= "Received: {$ts}\n";
if ($is_suspicious) $body .= "⚠️ FLAGGED: submission timing suggests bot. Verify by phone before replying by email.\n";
$body .= "\n";
$body .= "PROPERTY\n";
$body .= "Postcode: {$postcode}\n";
$body .= "Type: {$prop_type}\n";
$body .= "Condition: {$condition}\n\n";
$body .= "SITUATION\n";
$body .= "Situation: {$situation}\n";
$body .= "Timeline: {$timeline}\n";
$body .= "Notes: " . ($notes ?: '(none)') . "\n\n";
$body .= "CONTACT\n";
$body .= "Name: {$first}\n";
$body .= "Phone: {$phone}\n";
$body .= "Email: {$email}\n";
$body .= "Prefers: " . ($contact_pref ?: 'any') . "\n\n";
$body .= "STRATEGY TAGS: {$tag_string}\n\n";
$body .= "User agent: " . substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250) . "\n";
$body .= "IP: " . $client_ip . "\n";

if ($is_suspicious) {
    $subject_prefix = '[FLAGGED] ';
} elseif ($is_contact_form) {
    $subject_prefix = '[Enquiry] ';
} else {
    $subject_prefix = '[Lead] ';
}
$subject_suffix = $is_contact_form ? 'contact form' : $postcode . ' — ' . ($tags ? implode(',', $tags) : 'general');
$subject = $subject_prefix . mb_substr($first, 0, 40) . ' — ' . $subject_suffix;

/* --- Email 1: lead notification to you --- */
$smtp_base = [
    'host' => $SMTP_HOST, 'port' => $SMTP_PORT, 'secure' => $SMTP_SECURE,
    'user' => $SMTP_USER, 'pass' => $SMTP_PASS,
    'from_email' => $FROM_EMAIL, 'from_name' => $FROM_NAME,
];

$r1 = smtp_send(array_merge($smtp_base, [
    'to_email' => $LEAD_RECIPIENT,
    'to_name'  => 'Clyde Housebuyers',
    'subject'  => $subject,
    'body'     => $body,
    'reply_to' => $email ?: $FROM_EMAIL,  // funnel leads have no email; reply-to falls back to from-address
]));

/* --- Email 2: auto-reply to the seller (skip if flagged as suspicious OR no email captured) --- */
if ($is_suspicious) {
    // Don't send the auto-reply — protects innocent third parties from being spammed
    // via a forged email address in our form. The lead still goes to you above.
    $r2 = ['ok' => false, 'log' => '', 'error' => 'skipped (suspicious submission)'];
} elseif (!$email) {
    // Funnel forms don't capture email — there's no auto-reply to send.
    // The lead is still saved and you still get the notification email above.
    $r2 = ['ok' => false, 'log' => '', 'error' => 'skipped (no email captured)'];
} else {
    $reply_subject = "We've got your message — Clyde Housebuyers";
    $reply_body  = "Hi {$first},\n\n";
    if ($is_contact_form) {
        $reply_body .= "Thanks for getting in touch. We've received your message and one of the team will reply within 1 working day.\n\n";
        $reply_body .= "If anything's urgent, please call us directly on 0141 530 1430 (Mon–Fri, 9am–6pm).\n\n";
    } else {
        $reply_body .= "Thanks for getting in touch about your property at {$postcode}.\n\n";
        $reply_body .= "Here's what happens next:\n\n";
        $reply_body .= "1. Within 1 working day, a member of our team will call you to learn more about your situation. No pressure, just a conversation.\n\n";
        $reply_body .= "2. Within 48 hours, you'll receive your free valuation and a proposal for the route that best fits your property — cash offer, assisted sale, or something tailored.\n\n";
        $reply_body .= "3. You decide what to do next. No pressure. No fees. No obligation.\n\n";
        if (in_array('repossession-urgent', $tags) || in_array('priority', $tags)) {
            $reply_body .= "Given the urgency you mentioned, we'll prioritise your call. If anything's time-sensitive, please call us directly on 0141 530 1430.\n\n";
        } else {
            $reply_body .= "If anything's urgent, please call us directly on 0141 530 1430.\n\n";
        }
    }
    $reply_body .= "Speak soon,\nClyde Housebuyers\n0141 530 1430\ninfo@clydehousebuyers.co.uk\nhttps://clydehousebuyers.co.uk\n";

    $r2 = smtp_send(array_merge($smtp_base, [
        'to_email' => $email,
        'to_name'  => $first,
        'subject'  => $reply_subject,
        'body'     => $reply_body,
        'reply_to' => 'info@clydehousebuyers.co.uk',
    ]));
}

/* --- CSV log (L1: csv_safe each field; L4: lock during write) --- */
$log_dir = __DIR__ . '/private';
if (!is_dir($log_dir)) @mkdir($log_dir, 0700, true);
$log_file = $log_dir . '/leads.csv';
$is_new = !file_exists($log_file);
$fp = @fopen($log_file, 'a');
if ($fp) {
    if (@flock($fp, LOCK_EX)) {
        if ($is_new) {
            fputcsv($fp, ['timestamp','postcode','type','condition','situation','timeline','name','phone','email','contact_pref','tags','notes','ip','suspicious','lead_email_sent','reply_email_sent']);
        }
        fputcsv($fp, array_map('csv_safe', [
            $ts, $postcode, $prop_type, $condition, $situation, $timeline, $first, $phone, $email,
            $contact_pref, $tag_string, $notes, $client_ip,
            $is_suspicious ? 'yes' : 'no',
            $r1['ok'] ? 'yes' : 'NO',
            $r2['ok'] ? 'yes' : ($r2['error'] === 'skipped (suspicious submission)' ? 'SKIPPED' : 'NO'),
        ]));
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

/* --- SMTP debug log (only when $DEBUG_SMTP is true) --- */
if ($DEBUG_SMTP) {
    $debug_file = $log_dir . '/smtp-debug.log';
    $entry = "\n========= " . $ts . " =========\n";
    $entry .= "LEAD EMAIL → {$LEAD_RECIPIENT}\n";
    $entry .= "Result: " . ($r1['ok'] ? 'OK' : 'FAILED — ' . $r1['error']) . "\n";
    $entry .= $r1['log'] . "\n";
    $entry .= "AUTO-REPLY → {$email}\n";
    $entry .= "Result: " . ($r2['ok'] ? 'OK' : 'FAILED — ' . $r2['error']) . "\n";
    $entry .= $r2['log'] . "\n";
    @file_put_contents($debug_file, $entry, FILE_APPEND);
}

/* --- Optional: post to HighLevel / Zapier webhook --- */
if ($HL_WEBHOOK) {
    $payload = [
        'postcode' => $postcode, 'property_type' => $prop_type, 'condition' => $condition,
        'situation' => $situation, 'timeline' => $timeline, 'first_name' => $first,
        'phone' => $phone, 'email' => $email, 'contact_preference' => $contact_pref,
        'notes' => $notes, 'tags' => $tags, 'source' => 'clydehousebuyers.co.uk',
        'received' => $ts
    ];
    $ch = curl_init($HL_WEBHOOK);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    @curl_exec($ch);
    curl_close($ch);
}

/* --- Facebook Conversions API: send Lead event ---
   This fires after the CSV write, so any failure here can't prevent the
   lead being saved. CAPI is fire-and-forget — silent on failure.
   See includes/fb-capi.php for the helper. */
require_once __DIR__ . '/includes/fb-capi.php';

// Derive a content_name that segments funnels in Ads Manager. Parse it from
// the situation field, which already carries the source URL.
// Examples:
//   "Lead from /funnels/free-listing.php (...)"      → 'free-listing-static'
//   "Lead from /funnels/free-listing-quiz.php · ..." → 'free-listing-quiz'
//   "Lead from /funnels/quick-cash-sale.php · ..."   → 'quick-cash-sale'
//   "Lead from /contact.php"                         → 'contact-form'
//   anything else (e.g. valuation form)              → 'valuation-form'
$content_name = 'valuation-form';
if (preg_match('#/funnels/([a-z0-9\-]+)\.php#i', $situation, $m)) {
    $slug = strtolower($m[1]);
    // Distinguish the static free-listing from the quiz variant. They have
    // the same prefix but the quiz has "-quiz" appended.
    $content_name = ($slug === 'free-listing') ? 'free-listing-static' : $slug;
} elseif (strpos($situation, '/contact.php') !== false) {
    $content_name = 'contact-form';
}

// Use the suspicious flag as a CAPI skip: don't tell Facebook about leads
// the honeypot caught — they're bots/spam and would muddy ad optimisation.
if (!$is_suspicious) {
    $event_url = 'https://clydehousebuyers.co.uk' .
        (isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) : '/funnels/' . $content_name);

    // Generate a deterministic event_id so if you later add the browser Pixel,
    // Facebook can deduplicate events from both sources. Using ts+phone is
    // stable enough — same submission won't generate a different ID on retry.
    $event_id = 'lead-' . hash('sha256', $ts . '|' . $phone);

    fb_send_event(
        'Lead',
        [
            'email'      => $email,
            'phone'      => $phone,
            'first_name' => $first,
            'ip'         => $client_ip,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
        ],
        [
            'content_name'     => $content_name,
            'content_category' => 'lead-funnel',
            'event_source_url' => $event_url,
            'currency'         => 'GBP',
            // Optional value: a notional value per lead helps Facebook optimise
            // bidding. £50 is a placeholder — adjust if you have real CPL data.
            'value'            => 50,
        ],
        $event_id
    );
}

/* --- Respond to the browser --- */
// We return success as long as the lead notification reached you. The CSV log is the
// authoritative record either way — you'll see the lead in /private/leads.csv even if
// SMTP failed.
if (!$r1['ok']) {
    // Internal-only log entry — no sensitive context, just enough to investigate.
    error_log('Clyde Housebuyers: lead-email SMTP send failed (see leads.csv + smtp-debug.log if enabled)');
}
echo json_encode(['ok' => true, 'firstName' => $first]);
