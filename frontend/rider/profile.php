<?php
$portal = 'rider'; $active = 'profile'; $pageTitle = 'My Profile';
$crumb = 'Manage your account details'; $userRole = 'Delivery Rider';
$pageScript = 'profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid" style="align-items:start;">

  <!-- Editable account details -->
  <div class="gp-card">
    <h3 style="margin-bottom:16px;">Account Details</h3>
    <form id="profileForm" data-role="rider">

      <div class="field">
        <label for="fullName">Full name</label>
        <input type="text" id="fullName" name="full_name" maxlength="191" required>
      </div>

      <div class="field">
        <label for="category">Delivery category</label>
        <select id="category" name="category" required>
          <option value="">Select category</option>
          <option value="paddy">Paddy</option>
          <option value="vegetables">Vegetables</option>
        </select>
      </div>

      <div class="field">
        <label for="region">Operating region</label>
        <input type="text" id="region" name="region" maxlength="191" required>
      </div>

      <div id="profileMessage" style="display:none;margin-top:12px;" role="alert"></div>

      <button type="submit" class="btn btn-gold">Save Changes</button>
    </form>
  </div>

  <!-- Read-only vehicle details (set at registration) -->
  <div class="gp-card">
    <h3 style="margin-bottom:4px;">Vehicle Details</h3>
    <p class="muted" style="font-size:13px;margin-bottom:16px;">
      Set during registration. Contact support to change these.
    </p>

    <div class="field" style="margin-bottom:14px;">
      <label>Vehicle type</label>
      <input type="text" id="vehicleTypeDisplay" readonly
             style="background:var(--surface-2,#f4f6f4);cursor:default;" value="—">
    </div>

    <div class="field">
      <label>Registration number</label>
      <input type="text" id="vehicleNoDisplay" readonly
             style="background:var(--surface-2,#f4f6f4);cursor:default;" value="—">
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
