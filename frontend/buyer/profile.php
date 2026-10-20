<?php
$portal = 'buyer'; $active = 'profile'; $pageTitle = 'My Profile';
$crumb = 'Manage your account details'; $userRole = 'Buyer';
$pageScript = 'profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid" style="align-items:start;">
  <div class="gp-card">
    <h3 style="margin-bottom:16px;">Account Details</h3>
    <form id="profileForm" data-role="buyer">
        <div class="field">
            <label for="fullName">Buyer name</label>
            <input
                type="text"
                id="fullName"
                name="full_name"
                maxlength="191"
                required
            >
        </div>

        <div class="field">
            <label for="businessName">Business name</label>
            <input
                type="text"
                id="businessName"
                name="business_name"
                maxlength="191"
                required
            >
        </div>

        <div class="field">
            <label for="businessType">Business type</label>
            <select id="businessType" name="business_type" required>
                <option value="">Select business type</option>
                <option value="retailer">Retailer</option>
                <option value="restaurant">Restaurant</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="field">
            <label for="address">Business address</label>
            <textarea
                id="address"
                name="address"
                maxlength="500"
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

        <button type="submit" class="btn btn-primary">
            Save Changes
        </button>
    </form>

  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
