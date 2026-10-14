<?php
$portal = 'rider'; $active = 'subscription'; $pageTitle = 'Subscription Payment';
$crumb = 'Complete your rider plan payment'; $userRole = 'Delivery Rider';
$pageScript = 'subscription-payment';
$subscriptionId = (int) ($_GET['id'] ?? 0);
include __DIR__ . '/../includes/header.php';
?>
<script>window._paymentSubscriptionId = <?= $subscriptionId ?>;</script>

<div id="subscriptionPaymentLoading" class="gp-empty"><i class="bi bi-hourglass-split"></i><h4>Loading subscription…</h4></div>
<div id="subscriptionPaymentUnavailable" class="gp-empty" style="display:none;">
  <i class="bi bi-exclamation-circle"></i>
  <h4 id="subscriptionPaymentUnavailableMessage">Payment details could not be loaded.</h4>
  <a href="<?= BASE_URL ?>/rider/subscription.php" class="btn btn-outline" style="margin-top:12px;">Back to subscription</a>
</div>

<div id="subscriptionPaymentLayout" class="gp-grid cols-2" style="align-items:start;display:none;">
  <section class="gp-card">
    <div class="section-head" style="margin-bottom:18px;">
      <div><h3 style="margin-bottom:3px;">Card details</h3><p class="muted" style="margin:0;">Demo payment; card details are not stored.</p></div>
    </div>
    <form id="subscriptionPaymentForm" novalidate>
      <div class="field"><label for="subscriptionCardholder">Name on card</label><input id="subscriptionCardholder" autocomplete="cc-name" maxlength="100"></div>
      <div class="field"><label for="subscriptionCardNumber">Card number</label><input id="subscriptionCardNumber" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" maxlength="23"></div>
      <div class="field-row">
        <div class="field"><label for="subscriptionCardExpiry">Expiry date</label><input id="subscriptionCardExpiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM / YY" maxlength="7"></div>
        <div class="field"><label for="subscriptionCardCvc">Security code</label><input id="subscriptionCardCvc" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="CVV" maxlength="4"></div>
      </div>
      <p id="subscriptionPaymentError" class="field-error" style="display:none;margin:0 0 14px;"></p>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;">
        <a href="<?= BASE_URL ?>/rider/subscription.php" class="btn btn-outline" style="flex:1;">Cancel</a>
        <button type="submit" class="btn btn-gold" id="subscriptionPayNow" style="flex:1;min-width:220px;">Pay now</button>
      </div>
    </form>
  </section>

  <aside class="gp-card" aria-live="polite">
    <h3 style="margin-bottom:16px;">Subscription summary</h3>
    <p class="muted">Subscription <strong id="subscriptionPaymentNumber" style="color:var(--ink);"></strong></p>
    <div id="subscriptionPaymentPlan" class="section-head" style="margin:0 0 12px;"></div>
    <hr class="divider">
    <div class="section-head" style="font-size:18px;margin:0;"><span>Total due</span><strong id="subscriptionPaymentTotal" style="color:var(--forest);"></strong></div>
  </aside>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>