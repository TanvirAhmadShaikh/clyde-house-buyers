<?php
/**
 * _auth.php — PHP session password gate for internal tools.
 *
 * Fasthosts shared hosting blocks .htaccess Basic Auth, so we gate access in PHP.
 *
 * Usage: put  require __DIR__ . '/_auth.php';  at the very top of any tool file
 * (before any output). If the user isn't authenticated, this renders a login form
 * and exits. If they are, execution continues to the tool.
 *
 * The password hash lives in /private/secrets.php under 'tools_password_hash'.
 * Generate it once (see setup notes) — the plaintext password is never stored.
 */

session_start();

$SECRETS_PATH = __DIR__ . '/../private/secrets.php';
$secrets = is_readable($SECRETS_PATH) ? (require $SECRETS_PATH) : [];
$HASH = $secrets['tools_password_hash'] ?? '';

// --- Handle logout ---
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// --- Handle login submission ---
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tools_password'])) {
    if ($HASH && password_verify($_POST['tools_password'], $HASH)) {
        session_regenerate_id(true);
        $_SESSION['tools_authed'] = true;
    } else {
        $login_error = 'Incorrect password.';
        // small delay to slow brute-force attempts
        usleep(500000);
    }
}

// --- If not authenticated, show login form and stop ---
if (empty($_SESSION['tools_authed'])) {
    $config_missing = ($HASH === '');
    ?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Internal Tools — Sign in</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: #0B1F3B; color: #2B2F36; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
  .login-card { background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,0.3); padding: 2.5rem; max-width: 380px; width: 100%; }
  .eyebrow { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.12em; color: #a8862f; font-weight: 600; }
  h1 { font-family: 'Fraunces', serif; font-size: 1.5rem; color: #0B1F3B; margin: 0.3rem 0 1.5rem; font-weight: 600; }
  label { display: block; font-size: 0.85rem; font-weight: 600; color: #0B1F3B; margin-bottom: 0.4rem; }
  input { width: 100%; padding: 0.75rem 0.85rem; border: 1px solid #cdd4dc; border-radius: 8px; font-size: 1rem; font-family: inherit; }
  input:focus { outline: none; border-color: #C8A24A; box-shadow: 0 0 0 3px #FBF4E1; }
  button { width: 100%; margin-top: 1.25rem; padding: 0.85rem; background: #C8A24A; color: #0B1F3B; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; }
  button:hover { background: #d6b56b; }
  .err { background: #FFF0EE; border-left: 3px solid #FF6B5A; padding: 0.6rem 0.85rem; border-radius: 6px; color: #8a2c22; font-size: 0.85rem; margin-bottom: 1rem; }
  .cfg { background: #FBF4E1; border-left: 3px solid #C8A24A; padding: 0.6rem 0.85rem; border-radius: 6px; color: #6b5418; font-size: 0.8rem; margin-bottom: 1rem; line-height: 1.5; }
</style>
</head>
<body>
  <div class="login-card">
    <span class="eyebrow">Clyde Housebuyers</span>
    <h1>Internal Tools</h1>
    <?php if ($config_missing): ?>
      <div class="cfg"><strong>Setup needed:</strong> no password is configured yet. Set <code>tools_password_hash</code> in <code>/private/secrets.php</code> on the server.</div>
    <?php endif; ?>
    <?php if ($login_error): ?>
      <div class="err"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <label for="tools_password">Password</label>
      <input type="password" id="tools_password" name="tools_password" autofocus required>
      <button type="submit">Sign in</button>
    </form>
  </div>
</body>
</html>
    <?php
    exit;
}
// Authenticated — execution continues to the tool that included this file.
