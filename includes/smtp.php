<?php
/**
 * Minimal native-PHP SMTP client.
 *
 * Sends one email per call, authenticating against a remote SMTP server
 * (designed for Fasthosts smtp.livemail.co.uk:587 STARTTLS).
 *
 * Usage:
 *   $r = smtp_send([
 *     'host' => 'smtp.livemail.co.uk',
 *     'port' => 587,
 *     'user' => 'no-reply@clydehousebuyers.co.uk',
 *     'pass' => 'YOUR-MAILBOX-PASSWORD',
 *     'secure' => 'starttls',         // 'starttls' or 'ssl' or 'none'
 *     'from_email' => 'no-reply@clydehousebuyers.co.uk',
 *     'from_name'  => 'Clyde Housebuyers',
 *     'to_email'   => 'info@clydehousebuyers.co.uk',
 *     'to_name'    => 'Clyde Housebuyers',
 *     'subject'    => 'Test',
 *     'body'       => "Hello, this is the message body.\nThanks.",
 *     'reply_to'   => 'someone@example.com',  // optional
 *   ]);
 *   // $r is ['ok' => bool, 'log' => 'transcript', 'error' => 'msg if !ok']
 */
function smtp_send(array $cfg) {
    $log = '';
    $logf = function ($line) use (&$log) { $log .= rtrim($line) . "\n"; };

    $host   = $cfg['host'];
    $port   = (int)($cfg['port'] ?? 587);
    $secure = strtolower($cfg['secure'] ?? 'starttls');
    $user   = $cfg['user']       ?? '';
    $pass   = $cfg['pass']       ?? '';
    $fromE  = $cfg['from_email'];
    $fromN  = $cfg['from_name'] ?? '';
    $toE    = $cfg['to_email'];
    $toN    = $cfg['to_name'] ?? '';
    $subj   = $cfg['subject']    ?? '(no subject)';
    $body   = $cfg['body']       ?? '';
    $replyTo = $cfg['reply_to'] ?? null;
    $timeout = (int)($cfg['timeout'] ?? 15);

    // Connect (ssl:// for port 465, tcp:// for STARTTLS or plain)
    $scheme = ($secure === 'ssl') ? 'ssl://' : 'tcp://';
    $errno = 0; $errstr = '';
    $sock = @stream_socket_client($scheme . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
    if (!$sock) {
        return ['ok' => false, 'log' => $log, 'error' => "Connect failed: $errstr ($errno)"];
    }
    stream_set_timeout($sock, $timeout);

    // Helper to read a complete SMTP response (handles multi-line e.g. "250-...\r\n250 ...\r\n")
    $read = function () use ($sock, $logf) {
        $reply = '';
        while (($line = fgets($sock, 1024)) !== false) {
            $reply .= $line;
            $logf('S: ' . $line);
            // Multi-line replies have a hyphen at position 4 ("250-foo"). Final line uses space ("250 foo").
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        return $reply;
    };
    $write = function ($cmd) use ($sock, $logf) {
        // Don't log the password — substitute when AUTH PLAIN is detected
        $shown = (stripos($cmd, 'AUTH PLAIN') === 0 || stripos($cmd, 'AUTH LOGIN') === 0) ? "C: [auth credentials redacted]" : "C: $cmd";
        $logf($shown);
        fwrite($sock, $cmd . "\r\n");
    };
    $expect = function ($prefix, $reply) use ($logf) {
        return (strpos($reply, $prefix) === 0);
    };

    // 1. Read greeting
    $r = $read();
    if (!$expect('220', $r)) {
        fclose($sock);
        return ['ok' => false, 'log' => $log, 'error' => 'Bad greeting: ' . trim($r)];
    }

    // Detect local hostname for EHLO
    $ehloName = $_SERVER['SERVER_NAME'] ?? $host;

    // 2. EHLO
    $write('EHLO ' . $ehloName);
    $r = $read();
    if (!$expect('250', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'EHLO failed: ' . trim($r)]; }

    // 3. STARTTLS if requested
    if ($secure === 'starttls') {
        $write('STARTTLS');
        $r = $read();
        if (!$expect('220', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'STARTTLS failed: ' . trim($r)]; }

        // Configure TLS context with peer verification.
        // The expected peer name is the SMTP host (e.g. smtp.livemail.co.uk).
        $ctx = stream_context_get_options($sock);
        stream_context_set_option($sock, 'ssl', 'verify_peer', true);
        stream_context_set_option($sock, 'ssl', 'verify_peer_name', true);
        stream_context_set_option($sock, 'ssl', 'peer_name', $host);
        // Use the OS CA bundle on Linux. If unavailable, PHP falls back to its bundled list.
        if (is_readable('/etc/ssl/certs/ca-certificates.crt')) {
            stream_context_set_option($sock, 'ssl', 'cafile', '/etc/ssl/certs/ca-certificates.crt');
        }

        if (!@stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $err = error_get_last();
            fclose($sock);
            return ['ok' => false, 'log' => $log, 'error' => 'TLS handshake failed' . ($err ? ': ' . $err['message'] : '')];
        }
        // EHLO again after TLS
        $write('EHLO ' . $ehloName);
        $r = $read();
        if (!$expect('250', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'Post-TLS EHLO failed: ' . trim($r)]; }
    }

    // 4. AUTH LOGIN (most compatible with Fasthosts and others)
    if ($user !== '') {
        $write('AUTH LOGIN');
        $r = $read();
        if (!$expect('334', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'AUTH LOGIN rejected: ' . trim($r)]; }
        $write(base64_encode($user));
        $r = $read();
        if (!$expect('334', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'Username rejected: ' . trim($r)]; }
        $write(base64_encode($pass));
        $r = $read();
        if (!$expect('235', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'Auth failed: ' . trim($r)]; }
    }

    // 5. MAIL FROM
    $write('MAIL FROM:<' . $fromE . '>');
    $r = $read();
    if (!$expect('250', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'MAIL FROM rejected: ' . trim($r)]; }

    // 6. RCPT TO
    $write('RCPT TO:<' . $toE . '>');
    $r = $read();
    if (!$expect('250', $r) && !$expect('251', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'RCPT TO rejected: ' . trim($r)]; }

    // 7. DATA
    $write('DATA');
    $r = $read();
    if (!$expect('354', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'DATA rejected: ' . trim($r)]; }

    // Build the message headers + body
    $msgid = sprintf('<%s.%s@%s>', date('YmdHis'), bin2hex(random_bytes(6)), $host);
    $date  = date('r');
    $encFromN = '=?UTF-8?B?' . base64_encode($fromN) . '?=';
    $encToN   = '=?UTF-8?B?' . base64_encode($toN) . '?=';
    $encSubj  = '=?UTF-8?B?' . base64_encode($subj) . '?=';

    $headers  = "Date: $date\r\n";
    $headers .= "From: \"$encFromN\" <$fromE>\r\n";
    $headers .= "To: \"$encToN\" <$toE>\r\n";
    if ($replyTo) $headers .= "Reply-To: <$replyTo>\r\n";
    $headers .= "Subject: $encSubj\r\n";
    $headers .= "Message-ID: $msgid\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "X-Mailer: Clyde-Housebuyers/1.0\r\n";

    // Body normalisation: use CRLF and dot-stuff any line starting with .
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = str_replace("\n", "\r\n", $body);
    $body = preg_replace('/^\./m', '..', $body);

    fwrite($sock, $headers . "\r\n" . $body . "\r\n.\r\n");
    $logf('C: [message body sent]');
    $r = $read();
    if (!$expect('250', $r)) { fclose($sock); return ['ok' => false, 'log' => $log, 'error' => 'Message rejected: ' . trim($r)]; }

    // 8. QUIT
    $write('QUIT');
    $read();
    fclose($sock);
    return ['ok' => true, 'log' => $log, 'error' => null];
}
