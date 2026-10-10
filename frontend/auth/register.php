<?php
require_once __DIR__ . '/../includes/config.php';
$pageTitle = 'Create Account';
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
      <div class="quote" data-i18n="auth_register_quote">"Whichever role you play in the harvest journey, you start here."</div>
    </div>
    <div style="font-size:12.5px;color:rgba(255,255,255,.55);">© <?= date('Y') ?> Govi Paura.</div>
  </div>

  <div class="auth-form-wrap">
    <form class="auth-form" id="registerForm" data-validate style="max-width:480px;">
        <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="72" height="53" style="margin-bottom:22px;">
        <h1 style="margin-bottom:4px;" data-i18n="auth_register_title"></h1>
        <p class="muted" data-i18n="auth_register_sub"></p>
        <div class="field">
            <label data-i18n="auth_role_label">I am a...</label>
            <div class="radio-card-group" style="grid-template-columns:repeat(3,1fr);">
            <label class="radio-card selected" style="text-align:center;padding:12px 8px;">
                <input type="radio" name="role" value="farmer" checked style="display:block;margin:0 auto 6px;">
                <i class="bi bi-basket" style="font-size:18px;color:var(--sage);"></i>
                <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="role_farmer">Farmer</div>
            </label>
            <label class="radio-card" style="text-align:center;padding:12px 8px;">
                <input type="radio" name="role" value="buyer" style="display:block;margin:0 auto 6px;">
                <i class="bi bi-cart3" style="font-size:18px;color:var(--rust);"></i>
                <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="role_buyer">Buyer</div>
            </label>
            <label class="radio-card" style="text-align:center;padding:12px 8px;">
                <input type="radio" name="role" value="rider" style="display:block;margin:0 auto 6px;">
                <i class="bi bi-truck" style="font-size:18px;color:var(--gold);"></i>
                <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="role_rider">Rider</div>
            </label>
            </div>
        </div>

        <div class="field" role="group" aria-labelledby="preferredLanguageLabel">
            <label id="preferredLanguageLabel" data-i18n="auth_preferred_language">Preferred language</label>
            <div class="radio-card-group" style="grid-template-columns:repeat(2,1fr);">
                <label class="radio-card selected" style="text-align:center;padding:12px 8px;">
                    <input type="radio" name="preferred_lang" value="english" checked required style="display:block;margin:0 auto 6px;">
                    <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="language_english">English</div>
                </label>
                <label class="radio-card" style="text-align:center;padding:12px 8px;">
                    <input type="radio" name="preferred_lang" value="sinhala" required style="display:block;margin:0 auto 6px;">
                    <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="language_sinhala">Sinhala</div>
                </label>
            </div>
        </div>

        <div class="field-row">
            <div class="field">
            <label for="fullName" data-i18n="auth_full_name">Full name</label>
            <input type="text" id="fullName" name="full_name" placeholder="Your name" required>
            </div>
            <div class="field">
            <label for="phone" data-i18n="auth_phone">Mobile number</label>
            <input type="tel" id="phone" name="phone" data-format="phone" placeholder="077 123 4567" required>
            </div>
        </div>
        <div class="field">
            <label for="email" data-i18n="auth_email">Email</label>
            <input type="email" id="email" name="email" data-format="email" placeholder="you@example.com">
        </div>

        <!-- Farmer + Rider only: NIC -->
        <div data-role-section="farmer,rider">
            <div class="field">
            <label for="nic" data-i18n="auth_nic">NIC number</label>
            <input type="text" id="nic" name="nic_number" data-format="nic" data-required-for-role placeholder="200012345678 or 881234567V">
            </div>
        </div>

        <!-- Farmer only -->
        <div data-role-section="farmer">
            <div class="field">
            <label for="farmLocation" data-i18n="auth_farm_location">Farm location</label>
            <input type="text" id="farmLocation" name="farm_location" data-required-for-role placeholder="e.g. Kottawa, Colombo District">
            </div>
        </div>

        <!-- Rider only -->
        <div data-role-section="rider">
        <div class="field">
            <label data-i18n="auth_rider_category">What will you be delivering?</label>
            <div class="radio-card-group" style="grid-template-columns:repeat(2,1fr);">
            <label class="radio-card selected" style="text-align:center;padding:12px 8px;">
                <input type="radio" name="category" value="vegetables" checked data-required-for-role style="display:block;margin:0 auto 6px;">
                <i class="bi bi-flower1" style="font-size:18px;color:var(--sage);"></i>
                <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="category_vegetables">Vegetables</div>
            </label>
            <label class="radio-card" style="text-align:center;padding:12px 8px;">
                <input type="radio" name="category" value="paddy" data-required-for-role style="display:block;margin:0 auto 6px;">
                <i class="bi bi-grid-3x3" style="font-size:18px;color:var(--gold);"></i>
                <div style="font-size:12.5px;font-weight:700;margin-top:4px;" data-i18n="category_paddy">Paddy</div>
            </label>
            </div>
        </div>

        <div class="field-row">
            <div class="field">
            <label for="vehicleType" data-i18n="auth_vehicle_type">Vehicle type</label>
            <select id="vehicleType" name="vehicle_type" data-required-for-role>
                <option value="">Select vehicle</option>
                <option value="bike">Motorbike</option>
                <option value="three_wheeler">Three-wheeler</option>
                <option value="lorry">Mini lorry</option>
                <option value="lorry">Van</option>
            </select>
            </div>
            <div class="field">
            <label for="vehicleNumber" data-i18n="auth_vehicle_number">Vehicle registration no.</label>
            <input type="text" id="vehicleNumber" name="vehicle_number" data-required-for-role placeholder="e.g. CAB-1234">
            </div>
        </div>
        </div>

        <!-- Buyer only -->
        <div data-role-section="buyer">
            <div class="field">
            <label for="businessName" data-i18n="auth_business_name">Business name</label>
            <input type="text" id="businessName" name="business_name" data-required-for-role placeholder="e.g. Green Grocer PVT">
            </div>
            <div class="field">
            <label for="businessType" data-i18n="auth_business_type">Business type</label>
            <select id="businessType" name="business_type" data-required-for-role>
                <option value="">Select type</option>
                <option value="retailer">Retailer</option>
                <option value="restaurant">Restaurant</option>
                <option value="other">Other</option>
            </select>
            </div>
        </div>

        <div class="field-row">
            <div class="field">
            <label for="password" data-i18n="auth_password_label">Password</label>
            <input type="password" id="password" name="password" minlength="8" placeholder="••••••••" required>
            </div>
            <div class="field">
            <label for="confirmPassword" data-i18n="auth_confirm_password">Confirm password</label>
            <input type="password" id="confirmPassword" name="confirm_password" placeholder="••••••••" required>
            </div>
        </div>

        <label style="display:flex;align-items:flex-start;gap:8px;font-size:12.5px;color:var(--ink-soft);font-weight:400;margin-bottom:18px;">
            <input type="checkbox" name="terms" required style="width:auto;margin-top:2px;">
            <span data-i18n="auth_terms">I agree to the Govi Paura Terms of Service and Privacy Policy.</span>
        </label>

        <p id="registerError" class="error-text" style="display:none;color:#c0392b;font-size:13px;margin-bottom:12px;"></p>

        <button type="submit" class="btn btn-primary btn-block" data-i18n="btn_create_account">Create Account</button>

        <div class="auth-switch">
            <span data-i18n="auth_have_account">Already have an account?</span> <a href="<?= BASE_URL ?>/auth/login.php" data-i18n="btn_login">Log in</a>
        </div>
    </form>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/api.js"></script>
<script src="<?= BASE_URL ?>/assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
</body>
</html>
