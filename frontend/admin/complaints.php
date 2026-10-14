<?php
$portal = 'admin'; $active = 'complaints'; $pageTitle = 'Helpdesk';
$crumb = 'Maintain the support contact shown to platform users'; $userRole = 'Admin';
$pageScript = 'admin-management';
include __DIR__ . '/../includes/header.php';
?>

<section class="gp-card" style="max-width:720px;">
  <h2>Administrator contact details</h2>
  <p>These details are used by users who need direct support, including when connectivity prevents them from using the platform.</p>
  <form id="adminSupportContactForm">
    <div class="field"><label for="adminSupportEmail">Support email</label><input id="adminSupportEmail" type="email" maxlength="255" required></div>
    <div class="field"><label for="adminSupportPhone">Support phone</label><input id="adminSupportPhone" type="tel" maxlength="30" required></div>
    <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save contact details</button>
  </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>