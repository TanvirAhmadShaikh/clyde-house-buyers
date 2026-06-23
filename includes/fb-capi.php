<?php
/**
 * includes/fb-capi.php — Facebook Conversions API (CAPI) helper.
 *
 * Why server-side: cold paid traffic on mobile increasingly has ad-blockers
 * (uBlock, Brave's Shield, iOS Safari ITP, Firefox's strict tracking
 * protection) that kill the browser Pixel before it can fire. Server-side
 * CAPI bypasses all of that — the event POSTs from PHP directly to
 * Facebook's API, with no dependency on the visitor's browser.
 *
 * What it sends: the standard `Lead` event with hashed contact details
 * (Facebook requires SHA-256 hashes for PII), plus the source funnel name
 * as content_name so we can segment in Ads Manager.
 *
 * Failure mode: SILENT. CAPI failures never break form submission. If
 * Facebook's API is down, slow, or we have bad credentials, the lead
 * still saves to CSV and the user still sees the success state. CAPI is
 * fire-and-forget; the only side-effect of failure is missing Ads-Manager
 * attribution for that one lead.
 *
 * Credentials live in private/secrets.php:
 *   fb_pixel_id    → the Pixel/Dataset ID (visible in Events Manager URL)
 *   fb_capi_token  → access token, generated in Events Manager → Settings
 *   fb_test_event_code → optional, used while testing. Remove when live.
 */

if (!function_exists('fb_send_event')) {

/**
 * Hash a single PII value the way Facebook requires:
 *   - trim
 *   - lowercase
 *   - strip non-alphanumerics for phone (just digits, including country code)
 *   - SHA-256 hex
 * Returns null for empty input so we don't send hash-of-empty-string.
 */
function fb_hash($value, $type = 'text') {
    if (!$value) return null;
    $v = trim((string) $value);
    if ($v === '') return null;

    if ($type === 'phone') {
        // Strip everything except digits; UK numbers should include country
        // code (447...) for best match rate, but Facebook will also accept
        // national format. We do a best-effort normalisation.
        $digits = preg_replace('/[^0-9]/', '', $v);
        // UK national-format mobile (07...) → convert to international (447...)
        if (strlen($digits) === 11 && substr($digits, 0, 2) === '07') {
            $digits = '44' . substr($digits, 1);
        }
        $v = $digits;
    } else {
        $v = strtolower($v);
    }
    return hash('sha256', $v);
}

/**
 * Send an event to Facebook Conversions API.
 *
 * @param string $event_name   Standard event name (e.g. 'Lead')
 * @param array  $user_data    Plain (unhashed) PII fields. Keys we recognise:
 *                               email, phone, first_name, last_name, ip, user_agent
 *                             Anything else is ignored.
 * @param array  $custom_data  Custom event parameters. We use:
 *                               content_name   → funnel identifier for segmentation
 *                               event_source_url → page URL the lead came from
 * @param string $event_id     Optional dedup ID. If you later add browser Pixel,
 *                             passing the same event_id from both sides lets
 *                             Facebook merge them.
 * @return array  ['ok' => bool, 'error' => string|null]
 */
function fb_send_event($event_name, $user_data = [], $custom_data = [], $event_id = null) {
    // Load credentials from private/secrets.php. Done inside the function
    // (not at module load) so calling code never accidentally exposes them.
    $secrets_file = __DIR__ . '/../private/secrets.php';
    if (!is_readable($secrets_file)) {
        return ['ok' => false, 'error' => 'secrets.php not readable'];
    }
    $secrets = require $secrets_file;
    if (!is_array($secrets) || empty($secrets['fb_pixel_id']) || empty($secrets['fb_capi_token'])) {
        // CAPI not configured — silent skip (this is the "not set up yet" state).
        return ['ok' => false, 'error' => 'fb_pixel_id or fb_capi_token not configured'];
    }
    $pixel_id   = $secrets['fb_pixel_id'];
    $token      = $secrets['fb_capi_token'];
    $test_code  = !empty($secrets['fb_test_event_code']) ? $secrets['fb_test_event_code'] : null;

    // Build hashed user_data block per Facebook's spec.
    $hashed_user = [];
    if (!empty($user_data['email']))      $hashed_user['em'] = [fb_hash($user_data['email'], 'email')];
    if (!empty($user_data['phone']))      $hashed_user['ph'] = [fb_hash($user_data['phone'], 'phone')];
    if (!empty($user_data['first_name'])) $hashed_user['fn'] = [fb_hash($user_data['first_name'])];
    if (!empty($user_data['last_name']))  $hashed_user['ln'] = [fb_hash($user_data['last_name'])];
    // IP and user_agent are NOT hashed — Facebook requires plain values for those
    // and uses them for browser-side dedup / fraud signals.
    if (!empty($user_data['ip']))         $hashed_user['client_ip_address']  = $user_data['ip'];
    if (!empty($user_data['user_agent'])) $hashed_user['client_user_agent']  = $user_data['user_agent'];

    // Action source: 'website' is the standard for form submissions on a website.
    // 'system_generated' or 'business_messaging' have specific other meanings.
    $event = [
        'event_name'    => $event_name,
        'event_time'    => time(),
        'action_source' => 'website',
        'user_data'     => $hashed_user,
    ];
    if (!empty($custom_data)) $event['custom_data'] = $custom_data;
    if ($event_id)            $event['event_id']    = $event_id;
    // Helps Facebook attribute the event back to the page URL.
    if (!empty($custom_data['event_source_url'])) {
        $event['event_source_url'] = $custom_data['event_source_url'];
    }

    $payload = ['data' => [$event]];
    if ($test_code) $payload['test_event_code'] = $test_code;

    // POST to Facebook's CAPI endpoint. v19.0 is current stable as of Jan 2026;
    // bump it when Facebook deprecates (they give ~2 years' notice).
    $url = "https://graph.facebook.com/v19.0/{$pixel_id}/events?access_token=" . urlencode($token);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        // Short timeout — if Facebook's slow we don't make the user wait.
        // The lead is already saved by the time we call this.
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        error_log("FB CAPI: cURL error — {$curl_err}");
        return ['ok' => false, 'error' => "cURL: {$curl_err}"];
    }
    if ($http_code !== 200) {
        // Don't dump the whole response into error_log — it can contain echoed
        // payload data. Just enough to debug.
        error_log("FB CAPI: HTTP {$http_code} from Facebook — first 200 chars: " . substr($response, 0, 200));
        return ['ok' => false, 'error' => "HTTP {$http_code}", 'response' => $response];
    }

    // Successful response looks like: {"events_received":1,"messages":[],"fbtrace_id":"..."}
    return ['ok' => true, 'response' => $response];
}

}  // function_exists guard
