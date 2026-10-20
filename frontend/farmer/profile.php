<?php
$portal = 'farmer'; $active = 'profile'; $pageTitle = 'My Profile';
$crumb = 'Manage your account details'; $userRole = 'Farmer';
$pageScript = 'profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid" style="align-items:start;">
  <div class="gp-card">
    <h3 style="margin-bottom:16px;">Account Details</h3>
    <form id="profileForm" data-role="farmer">
    <div class="field">
        <label for="fullName">Full name</label>
        <input
            type="text"
            id="fullName"
            name="full_name"
            maxlength="191"
            required
        >
    </div>

    <div class="field">
        <label for="address">Farm address</label>
        <textarea
            id="address"
            name="address"
            maxlength="191"
            required
        ></textarea>
    </div>

    <div class="field">
        <label for="region">Region</label>
        <input
            type="text"
            id="region"
            name="region"
            maxlength="191"
            required
        >
    </div>

    <div id="profileMessage" style="display:none;margin-top:12px;" role="alert"></div>

    <button type="submit" class="btn btn-accent">
        Save Changes
    </button>
</form>

  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
