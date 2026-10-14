<?php
$portal = 'rider'; $active = 'profile'; $pageTitle = 'My Profile';
$crumb = 'Manage your account details'; $userRole = 'Delivery Rider';
$pageScript = 'profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid" style="align-items:start;">
  <div class="gp-card">
    <h3 style="margin-bottom:16px;">Account Details</h3>
    <form id="profileForm" data-role="rider">
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
        <label for="category">Delivery category</label>
        <select id="category" name="category" required>
            <option value="">Select category</option>
            <option value="paddy">Paddy</option>
            <option value="vegetables">Vegetables</option>
        </select>
    </div>

    <div class="field">
        <label for="region">Operating region</label>
        <input
            type="text"
            id="region"
            name="region"
            maxlength="191"
            required
        >
    </div>

    <div id="profileMessage" style="display:none;margin-top:12px;" role="alert"></div>

    <button type="submit" class="btn btn-gold">
        Save Changes
    </button>
</form>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
