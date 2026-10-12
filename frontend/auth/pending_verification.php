<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Account Created';
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
      <div class="quote">"Verified farmers and riders keep the marketplace trusted for everyone."</div>
      <div class="quote-sub">It only takes a few minutes to upload your documents.</div>
    </div>
    <div style="font-size:12.5px;color:rgba(255,255,255,.55);">© <?= date('Y') ?> Govi Paura.</div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-form">
      <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="72" height="53" style="margin-bottom:22px;">
      <h1 style="margin-bottom:4px;">Account created</h1>
      <p class="muted">Your account needs to be verified before you can sell or deliver. Log in, upload your identification documents, and our team will review them.</p>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary btn-block" style="margin-top:12px;">Log in to upload documents</a>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
