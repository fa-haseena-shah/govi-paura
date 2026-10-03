<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Log In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?> · Govi Paura</title>
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/buyer.css">
<script>const BASE_URL_JS = '<?= BASE_URL ?>';</script>

</head>
<body>
<div class="auth-shell">
  <div class="auth-visual">
    <div>
      <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="Govi Paura" width="72" height="53">
      <div style="font-family:var(--font-display);font-size:22px;color:#fff;margin-top:8px;">Govi Paura</div>
    </div>
    <div>
      <div class="quote" data-i18n="auth_login_quote">"One account, every role — farmers, buyers, riders and admins all sign in here."</div>
      <div class="quote-sub" data-i18n="auth_login_quote_sub">We'll take you straight to your dashboard once you're signed in.</div>
    </div>
    <div style="font-size:12.5px;color:rgba(255,255,255,.55);">© <?= date('Y') ?> Govi Paura.</div>
  </div>

  <div class="auth-form-wrap">
    <form class="auth-form" id="loginForm" data-validate>
      <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="72" height="53" style="margin-bottom:22px;">
      <h1 style="margin-bottom:4px;" data-i18n="auth_login_title">Welcome back</h1>
      <p class="muted" data-i18n="auth_login_sub">Log in with the account you registered — as a Farmer, Buyer, Rider or Admin.</p>

      <div class="field">
        <label for="identifier" data-i18n="auth_identifier_label">Mobile number or email</label>
        <input type="text" id="identifier" name="identifier" required>
      </div>
      <div class="field">
        <label for="password" data-i18n="auth_password_label">Password</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-soft);font-weight:400;">
          <input type="checkbox" name="remember" style="width:auto;"> <span data-i18n="auth_remember">Remember me</span>
        </label>
        <a href="#" style="font-size:13px;" data-i18n="auth_forgot">Forgot password?</a>
      </div>

      <p id="loginError" class="error-text" style="display:none;color:#c0392b;font-size:13px;margin-bottom:12px;"></p>

      <button type="submit" class="btn btn-primary btn-block" data-i18n="btn_login">Log In</button>

      <div class="auth-switch">
        <span data-i18n="auth_no_account">New to Govi Paura?</span> <a href="<?= BASE_URL ?>/auth/register.php" data-i18n="auth_create_account_link">Create an account</a>
      </div>
    </form>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/api.js"></script>
<script src="<?= BASE_URL ?>/assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
</body>
</html>