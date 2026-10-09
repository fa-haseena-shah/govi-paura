<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Page Not Found · Govi Paura</title>
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
</head>
<body style="background:var(--canvas);">
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
  <div style="text-align:center;max-width:440px;">
    <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="Govi Paura" width="56" height="56" style="border-radius:14px;margin-bottom:22px;">
    <div style="font-family:var(--font-display);font-size:64px;color:var(--sage);line-height:1;">404</div>
    <h1 style="margin:10px 0 8px;">This page isn't in the harvest</h1>
    <p class="muted" style="margin-bottom:24px;">
      The page you're looking for may have been moved, renamed, or the link might be out of date.
      Here's how to get back on track:
    </p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary">Go to Login</a>
      <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
    </div>
    <p class="muted" style="margin-top:26px;font-size:12.5px;">
      Still stuck? Reach our support team at
      <a href="mailto:support@govipaura.lk">support@govipaura.lk</a>
    </p>
  </div>
</div>
</body>
</html>
