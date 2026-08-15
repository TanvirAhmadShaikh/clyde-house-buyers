<?php
/**
 * private/secrets.example.php — template for private/secrets.php.
 *
 * private/secrets.php is gitignored and holds the real values, both
 * locally and on the live server. It's also excluded from deploy.sh's
 * rsync, so the server's real values are never overwritten by a deploy.
 *
 * To set up a new environment: copy this file to private/secrets.php
 * and fill in the real values.
 *
 * Expected keys:
 *   smtp_pass           → Fasthosts mailbox password for no-reply@clydehousebuyers.co.uk
 *                         (read by submit-lead.php at runtime)
 *   manus_api_key       → API key for the AI comps tool (if used)
 *   tools_password_hash → bcrypt hash gating /tools/ pages (if used)
 *   fb_pixel_id         → Facebook Pixel ID (visible in Events Manager URL).
 *                         Read by includes/fb-capi.php to send Lead events.
 *   fb_capi_token       → Facebook Conversions API access token.
 *                         Generated in Events Manager → Settings → CAPI.
 *   fb_test_event_code  → OPTIONAL. Only set during testing — paste from
 *                         Events Manager → Test Events. Remove once live.
 */
return [
    'smtp_pass'           => 'REPLACE-WITH-MAILBOX-PASSWORD',
    'manus_api_key'       => 'REPLACE-WITH-YOUR-NEW-MANUS-API-KEY',
    'tools_password_hash' => 'REPLACE-WITH-BCRYPT-HASH',
    'fb_pixel_id'         => 'REPLACE-WITH-PIXEL-ID',
    'fb_capi_token'       => 'REPLACE-WITH-CAPI-ACCESS-TOKEN',
    // 'fb_test_event_code' => 'TEST12345',  // uncomment while testing only
];
