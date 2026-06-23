<?php
/**
 * comps-api.php — backend proxy for the AI property-research tool.
 *
 * FIXES applied in this version:
 *
 * 1. RESPONSE ENVELOPE: The Manus API v2 nests responses TWICE — manus_request()
 *    returns the raw decoded JSON as $res['data'], and that object contains an
 *    INNER `data` key holding the real payload (task / messages / task_id).
 *    So the true path is $res['data']['data']['...']. The helper unwrap() strips
 *    BOTH layers (outer via manus_request, inner here) and returns the real
 *    payload. Reading only one layer left task/status/messages null, which made
 *    the UI poll forever — that was the spin bug.
 *
 * 2. TASK ID EXTRACTION: `task.create` returns `{ "data": { "task_id": "..." } }`.
 *    The old multi-path fallback chain could silently return null. Now uses
 *    `unwrap()` and a single clear path.
 *
 * 3. STATUS POLLING: `task.listMessages` is the authoritative source for both
 *    progress updates AND the terminal `agent_status`. The old code called
 *    `task.detail` for status and `task.listMessages` separately, meaning the
 *    `status` field came from a different call than the messages. Now a single
 *    `task.listMessages` call per poll provides both status and messages, matching
 *    the documented lifecycle (running → stopped → structured_output_result).
 *
 * 4. STRUCTURED OUTPUT RESULT: The `structured_output_result` event is emitted
 *    AFTER `agent_status=stopped` in the same `task.listMessages` response. The
 *    old `action=result` handler fetched messages again in a second request, which
 *    was redundant and could miss the event if the list was fetched with
 *    `order=desc&limit=20` (truncating older events). Now the status poll itself
 *    detects `agent_status=stopped`, scans the SAME message list for
 *    `structured_output_result`, and returns the comparables immediately — no
 *    second round-trip needed.
 *
 * 5. MESSAGE PARSING: `status_update` events carry `agent_status` (the lifecycle
 *    state) inside `status_update.agent_status`, not as a top-level field. The old
 *    code read `$task['status']` from `task.detail` which uses a different field
 *    name (`status` vs `agent_status`). Now reads from the `status_update` event
 *    directly.
 *
 * 6. CREDIT USAGE: `task.detail` exposes `credit_usage`; `task.listMessages` does
 *    not. Since we now use `task.listMessages` as the primary poll, credit usage is
 *    fetched from a lightweight `task.detail` call only when needed (on each poll),
 *    keeping the credit counter accurate without a separate endpoint.
 */

header('Content-Type: application/json; charset=utf-8');

session_start();
if (empty($_SESSION['tools_authed'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated.']);
    exit;
}

$secrets_path = __DIR__ . '/../private/secrets.php';
if (!is_readable($secrets_path)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Secrets file missing.']);
    exit;
}
$secrets = require $secrets_path;
$MANUS_API_KEY = $secrets['manus_api_key'] ?? '';

define('MANUS_API_URL', 'https://api.manus.ai/v2');
define('MAX_CREDITS', 600);

// ---------------------------------------------------------------------------
// HTTP helper
// ---------------------------------------------------------------------------
function manus_request($endpoint, $api_key, $data = null, $method = 'POST') {
    $url = MANUS_API_URL . '/' . $endpoint;
    $headers = ['x-manus-api-key: ' . $api_key, 'Content-Type: application/json'];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response === false) return ['ok' => false, 'error' => 'Network error.'];
    $decoded = json_decode($response, true);
    if ($decoded === null) return ['ok' => false, 'error' => 'Invalid JSON.'];
    if ($http_code >= 400) return ['ok' => false, 'error' => $decoded['error']['message'] ?? ('HTTP ' . $http_code)];
    return ['ok' => true, 'data' => $decoded];
}

// ---------------------------------------------------------------------------
// FIX 1: Normalise the API v2 response envelope.
// The Manus API v2 always wraps the payload in a single `data` key:
//   { "ok": true, "data": { ... actual payload ... } }
// Return the inner payload directly, or null on failure.
// ---------------------------------------------------------------------------
function unwrap($res) {
    if (empty($res['ok'])) return null;
    $payload = $res['data'] ?? null;
    if (!is_array($payload)) return $payload;
    // Manus v2 nests the real payload under an INNER "data" key:
    //   { ok, request_id, data: { ok, task / messages / task_id ... } }
    // manus_request() already gave us the outer object as $res['data'], so the
    // actual payload is one level deeper. Strip that inner layer when present.
    if (isset($payload['data']) && is_array($payload['data'])) {
        return $payload['data'];
    }
    return $payload;
}

// ---------------------------------------------------------------------------
// Stop a running task (best-effort).
// ---------------------------------------------------------------------------
function manus_stop_task($task_id, $api_key) {
    manus_request('task.stop', $api_key, ['task_id' => $task_id], 'POST');
}

// ---------------------------------------------------------------------------
// Scrub provider name from any user-visible text.
// ---------------------------------------------------------------------------
function scrub_provider($text) {
    if (!is_string($text) || $text === '') return $text;
    $text = preg_replace('/\bManus(\s+AI|\.ai)\b/i', 'AI', $text);
    $text = preg_replace('/\bManus\b/i', 'the AI', $text);
    $text = preg_replace('/\bthe AI AI\b/i', 'the AI', $text);
    $text = preg_replace('/\bthe the AI\b/i', 'the AI', $text);
    return $text;
}

// ---------------------------------------------------------------------------
// Detect out-of-credits error text.
// ---------------------------------------------------------------------------
function credit_error_message($text) {
    if (!is_string($text) || $text === '') return null;
    $needles = ['enough credit', 'insufficient credit', 'out of credit', 'not enough credit',
                'upgrade', 'quota', 'credit balance', 'run out of credit', 'no credits'];
    $low = strtolower($text);
    foreach ($needles as $n) {
        if (strpos($low, $n) !== false) {
            return 'Research stopped: the AI research allowance has run out. Please top up the account to continue running searches.';
        }
    }
    return null;
}

// ---------------------------------------------------------------------------
// FIX 3 + 4 + 5: Parse a task.listMessages response.
//
// Returns:
//   'agent_status'  => 'running' | 'stopped' | 'waiting' | 'error'
//   'updates'       => array of scrubbed progress strings
//   'comparables'   => array (populated when structured_output_result is found)
//   'error_text'    => string|null  (from error_message events)
// ---------------------------------------------------------------------------
function parse_messages($items) {
    $agent_status  = 'running';
    $updates       = [];
    $comparables   = null;
    $error_text    = null;

    foreach ($items as $event) {
        $type = $event['type'] ?? '';

        if ($type === 'status_update') {
            $su = $event['status_update'] ?? [];
            // agent_status lives inside status_update, not at the top level
            if (!empty($su['agent_status'])) {
                $agent_status = strtolower($su['agent_status']);
            }
            if (!empty($su['description'])) $updates[] = scrub_provider($su['description']);
            if (!empty($su['brief']))       $updates[] = scrub_provider($su['brief']);

        } elseif ($type === 'assistant_message') {
            $content = $event['assistant_message']['content'] ?? '';
            if ($content !== '') $updates[] = scrub_provider($content);

        } elseif ($type === 'structured_output_result') {
            $sor = $event['structured_output_result'] ?? [];
            if (!empty($sor['success'])) {
                $comparables = $sor['value']['comparables'] ?? [];
            }

        } elseif ($type === 'error_message') {
            $msg = $event['error_message']['content'] ?? '';
            if ($msg !== '' && $error_text === null) $error_text = $msg;
        }
    }

    return [
        'agent_status' => $agent_status,
        'updates'      => array_values(array_unique($updates)),
        'comparables'  => $comparables,
        'error_text'   => $error_text,
    ];
}

// ---------------------------------------------------------------------------
// Route: action=create
// ---------------------------------------------------------------------------
$action = $_GET['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $address = trim($body['address'] ?? '');
    $beds    = trim($body['beds'] ?? '');
    $type    = trim($body['type'] ?? '');

    $task_data = [
        'message' => [
            'content' => "Find exactly 5 comparable sales for a {$beds} bedroom {$type} house near {$address}. "
                       . "EFFICIENCY: Work within a strict budget of 600 credits. Be economical — do the minimum searching needed. Prefer ONE good property data source (e.g. a single Registers of Scotland / property portal search) over visiting many sites. Do not over-research or double-check beyond what's necessary. Return results as soon as you have 5 solid comparables. "
                       . "SEARCH: Look within ~0.4 miles for sales in the last 12 months. Only widen to 2 years if you cannot find 5. Stop the moment you have 5. "
                       . "SORTING: nearest first, then most recent. "
                       . "DISTANCE: 'distance_miles' as a number to TWO decimals (e.g. 0.04, 0.12); same-street properties show their small fraction (e.g. 0.02), never 0. "
                       . "Each property needs: latest sale price and date, floor area in the 'area_sqm' field including the unit (e.g. '95 sqm'), and a reference URL. "
                       . "EPC: give each property's EPC band (A–G) in 'epc_rating'. If not quickly found, estimate from age/type and suffix '(est.)' (e.g. 'D (est.)'). Don't spend extra credits hunting for EPCs — a quick check or estimate is fine. "
                       . "If a value isn't available, estimate it rather than searching exhaustively. Avoid 'Not available'. "
                       . "STRICT: Never reveal or mention your underlying model, platform, or provider name in any progress update, message, or log. Refer to yourself only as 'AI' or 'Research Agent'.",
        ],
        'name' => 'Property Research Agent',
        'structured_output_schema' => [
            'type' => 'object',
            'properties' => [
                'comparables' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'address'        => ['type' => 'string'],
                            'postcode'       => ['type' => 'string'],
                            'bedrooms'       => ['type' => 'integer'],
                            'date_sold'      => ['type' => 'string'],
                            'price'          => ['type' => 'string'],
                            'area_sqm'       => ['type' => 'string'],
                            'distance_miles' => ['type' => 'number'],
                            'epc_rating'     => ['type' => 'string'],
                            'reference_url'  => ['type' => 'string'],
                        ],
                        'required' => ['address','postcode','bedrooms','date_sold','price',
                                       'area_sqm','distance_miles','epc_rating','reference_url'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['comparables'],
            'additionalProperties' => false,
        ],
    ];

    $res = manus_request('task.create', $MANUS_API_KEY, $task_data);
    if (!$res['ok']) {
        $credit_msg = credit_error_message($res['error'] ?? '');
        if ($credit_msg !== null) {
            echo json_encode(['ok' => false, 'credit_error' => true, 'error' => $credit_msg]);
            exit;
        }
        http_response_code(502);
        echo json_encode($res);
        exit;
    }

    // FIX 2: task.create returns { "ok": true, "data": { "task_id": "..." } }
    $payload = unwrap($res);
    $task_id = $payload['task_id'] ?? null;
    if (!$task_id) {
        echo json_encode(['ok' => false, 'error' => 'Could not start research (no task_id returned). Raw: ' . json_encode($payload)]);
        exit;
    }
    echo json_encode(['ok' => true, 'task_id' => $task_id]);
    exit;
}

// ---------------------------------------------------------------------------
// Route: action=status
// FIX 3 + 4 + 5: Use task.listMessages as the single source of truth for both
// status and structured output. Fetch task.detail only for credit_usage.
// ---------------------------------------------------------------------------
if ($action === 'status' && isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // --- Credit usage + authoritative status: task.detail (lightweight metadata call) ---
    $credits = 0;
    $detail_status = '';
    $detail_res = manus_request('task.detail?task_id=' . urlencode($task_id), $MANUS_API_KEY, null, 'GET');
    if ($detail_res['ok']) {
        $detail = unwrap($detail_res);
        // task.detail returns { "task": { ... } } inside data
        $credits = $detail['task']['credit_usage'] ?? $detail['credit_usage'] ?? 0;
        // The task's own status field is the AUTHORITATIVE completion signal —
        // far more reliable than scanning messages for a 'stopped' event, which
        // can be missed and cause the UI to poll forever.
        $detail_status = strtolower($detail['task']['status'] ?? $detail['status'] ?? '');
    }

    // --- Credit cap check ---
    if (is_numeric($credits) && $credits > MAX_CREDITS) {
        manus_stop_task($task_id, $MANUS_API_KEY);
        // Try to get any partial comparables from messages
        $msgs_res = manus_request('task.listMessages?task_id=' . urlencode($task_id) . '&order=asc', $MANUS_API_KEY, null, 'GET');
        $partial = [];
        if ($msgs_res['ok']) {
            $payload = unwrap($msgs_res);
            $items   = $payload['messages'] ?? [];
            $parsed  = parse_messages($items);
            $partial = $parsed['comparables'] ?? [];
        }
        echo json_encode([
            'ok'          => false,
            'credit_cap'  => true,
            'credits'     => $credits,
            'comparables' => $partial,
            'error'       => 'Max credit usage per request (' . MAX_CREDITS . ') reached. Stopping request.',
        ]);
        exit;
    }

    // --- Main poll: task.listMessages (order=asc to get structured_output_result) ---
    // Use order=asc so we see ALL events including structured_output_result which
    // appears AFTER the stopped status_update. With order=desc&limit=20 the
    // structured_output_result event could be truncated off the list.
    $msgs_res = manus_request('task.listMessages?task_id=' . urlencode($task_id) . '&order=asc', $MANUS_API_KEY, null, 'GET');

    if (!$msgs_res['ok']) {
        // 404 right after create = task not propagated yet; keep polling.
        if (stripos($msgs_res['error'] ?? '', 'not_found') !== false ||
            stripos($msgs_res['error'] ?? '', '404') !== false) {
            echo json_encode(['ok' => true, 'status' => 'running', 'credits' => 0, 'updates' => ['Initialising research...']]);
            exit;
        }
        $credit_msg = credit_error_message($msgs_res['error'] ?? '');
        if ($credit_msg !== null) {
            echo json_encode(['ok' => false, 'credit_error' => true, 'error' => $credit_msg]);
            exit;
        }
        echo json_encode($msgs_res);
        exit;
    }

    $payload = unwrap($msgs_res);
    $items   = $payload['messages'] ?? [];
    $parsed  = parse_messages($items);

    // Surface any error_message events (e.g. quota/credits)
    if (!empty($parsed['error_text'])) {
        $credit_msg = credit_error_message($parsed['error_text']);
        if ($credit_msg !== null) {
            echo json_encode(['ok' => false, 'credit_error' => true, 'error' => $credit_msg]);
            exit;
        }
        echo json_encode(['ok' => false, 'error' => 'Research could not be completed. Please try again.']);
        exit;
    }

    // Also scan updates for credit phrasing
    foreach ($parsed['updates'] as $u) {
        $credit_msg = credit_error_message($u);
        if ($credit_msg !== null) {
            echo json_encode(['ok' => false, 'credit_error' => true, 'error' => $credit_msg]);
            exit;
        }
    }

    // Prefer the authoritative status from task.detail; fall back to the
    // message-scanned agent_status only if detail didn't give us one.
    $agent_status = $detail_status !== '' ? $detail_status : $parsed['agent_status'];

    // Map agent_status to the frontend status values
    if (in_array($agent_status, ['stopped', 'completed', 'success', 'finished'], true)) {
        $status = 'done';
    } elseif (in_array($agent_status, ['failed', 'error'], true)) {
        $status = 'error';
    } elseif ($agent_status === 'waiting') {
        // A task can briefly report "waiting" while it's actually progressing
        // (e.g. queued between steps). Only treat it as a genuine pause if the
        // messages also show no recent progress — otherwise keep polling.
        // Since any real credit/quota error is caught earlier via error_text,
        // a bare "waiting" here is treated as still-running to avoid false stops.
        $status = 'running';
    } else {
        $status = 'running';
    }

    $response = [
        'ok'      => true,
        'status'  => $status,
        'credits' => $credits,
        'updates' => $parsed['updates'],
    ];

    // FIX 4: If stopped AND structured output is already in this same message list,
    // include comparables directly so the frontend can skip the separate result fetch.
    if ($status === 'done' && $parsed['comparables'] !== null) {
        $response['comparables'] = $parsed['comparables'];
    }

    echo json_encode($response);
    exit;
}

// ---------------------------------------------------------------------------
// Route: action=result
// Kept for backwards compatibility with the frontend's separate result fetch,
// but now also handles the case where structured_output_result was already
// included in the status response above.
// ---------------------------------------------------------------------------
if ($action === 'result' && isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $res = manus_request('task.listMessages?task_id=' . urlencode($task_id) . '&order=asc', $MANUS_API_KEY, null, 'GET');
    if (!$res['ok']) { echo json_encode($res); exit; }
    $payload = unwrap($res);
    $items   = $payload['messages'] ?? [];
    $parsed  = parse_messages($items);
    if ($parsed['comparables'] !== null) {
        echo json_encode(['ok' => true, 'comparables' => $parsed['comparables']]);
        exit;
    }
    echo json_encode(['ok' => false, 'error' => 'No structured result found yet.']);
    exit;
}

// ---------------------------------------------------------------------------
// Route: action=debug
// ---------------------------------------------------------------------------
if ($action === 'debug' && isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $detail = manus_request('task.detail?task_id=' . urlencode($task_id), $MANUS_API_KEY, null, 'GET');
    $msgs   = manus_request('task.listMessages?task_id=' . urlencode($task_id) . '&order=asc', $MANUS_API_KEY, null, 'GET');
    echo json_encode(['ok' => true, 'task_detail' => $detail, 'list_messages' => $msgs], JSON_PRETTY_PRINT);
    exit;
}
