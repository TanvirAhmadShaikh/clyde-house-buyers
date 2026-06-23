<?php
/**
 * private/secrets.php — runtime secrets, NOT committed to deploys.
 *
 * This file is excluded from rsync (see deploy.sh exclude list) so the
 * live server's real values are never overwritten by this placeholder.
 *
 * On the live server, this file already contains the real values.
 * If you ever need to seed it on a new server, copy this template and
 * fill in the placeholders with real values.
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
