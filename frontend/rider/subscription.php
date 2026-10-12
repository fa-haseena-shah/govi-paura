<?php
$portal = 'rider'; $active = 'subscription'; $pageTitle = 'Subscription';
$crumb = 'Manage your monthly route plan'; $userRole = 'Delivery Rider'; $pageScript = 'subscription';
include __DIR__ . '/../includes/header.php';
?>

<div id="subscriptionLoading" class="gp-empty"><i class="bi bi-hourglass-split"></i><h4>Loading subscription…</h4></div>
<div id="subscriptionContent" style="display:none;">
  <div class="gp-card" style="margin-bottom:20px;">
    <div class="section-head">
      <div><h3 id="subscriptionStateTitle" style="margin-bottom:4px;"></h3><span id="subscriptionStateDetail" class="muted"></span></div>
      <span id="subscriptionStateBadge" class="badge"></span>
    </div>
    <p id="subscriptionNotice" class="muted" style="margin:10px 0 0;display:none;"></p>
  </div>

  <section aria-labelledby="subscriptionPlansTitle">
    <h3 id="subscriptionPlansTitle" style="margin-bottom:14px;">Your subscription plan</h3>
    <div id="subscriptionPlans" class="gp-grid"></div>
  </section>

  <div class="gp-card" style="margin-top:20px;">
    <h3 style="margin-bottom:14px;">Subscription history</h3>
    <div class="table-wrap">
      <table class="gp-table">
        <thead><tr><th>Plan</th><th>Period</th><th>Amount</th><th>Payment</th><th>Status</th><th>Receipt</th></tr></thead>
        <tbody id="subscriptionHistory"><tr><td colspan="6" class="muted">Loading…</td></tr></tbody>
      </table>
    </div>
  </div>
</div>

<div id="subscriptionError" class="gp-empty" style="display:none;"><i class="bi bi-exclamation-circle"></i><h4 id="subscriptionErrorMessage">Could not load subscription.</h4></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
