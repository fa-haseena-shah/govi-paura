<?php
$portal = 'buyer'; $active = 'helpdesk'; $pageTitle = 'Helpdesk';
$crumb = ''; $userRole = 'Buyer'; $needsHelpdesk = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="helpdesk-page">
  <section class="helpdesk-support" aria-labelledby="helpdeskContactTitle">
    <div class="helpdesk-support__copy">
      <h2 id="helpdeskContactTitle">Support hotline</h2>
      <p><a href="tel:+94112345678">011 234 5678</a> · Daily, 8:00 am–6:00 pm</p>
    </div>
    <a class="btn btn-gold helpdesk-call" href="tel:+94112345678">Call support</a>
  </div>

  <section class="helpdesk-section" aria-labelledby="buyerFaqTitle">
    <div class="helpdesk-section__head"><h2 id="buyerFaqTitle">Frequently asked questions</h2></div>
    <div class="helpdesk-faq">
      <details><summary>How do I place a bid on a listing?</summary><p>Open any listing marked "Bidding" from Browse Produce, enter your offer per kg, and submit. You'll be notified if the farmer accepts, rejects, or if you're outbid.</p></details>
      <details><summary>How do I track my order after checkout?</summary><p>Go to "My Orders" to see live status — Pending pickup, Out for delivery, or Delivered — along with the assigned rider once one is matched to your order.</p></details>
      <details><summary>What if the produce doesn't match what was listed?</summary><p>Rate and review the order honestly, then call the support hotline below within 24 hours of delivery so our team can follow up with the farmer.</p></details>
    </div>
  </section>

  <section class="helpdesk-section helpdesk-manual" aria-labelledby="buyerManualTitle">
    <div class="helpdesk-section__head"><h2 id="buyerManualTitle">How to Use?</h2></div>
    <ol class="helpdesk-steps">
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 1</span><h3>Find produce</h3><ol class="helpdesk-substeps"><li>Open Browse Produce.</li><li>Filter by crop, location, or selling method.</li><li>Open a listing to see its details.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 2</span><h3>Buy or bid</h3><ol class="helpdesk-substeps"><li>Add fixed-price produce to your cart.</li><li>For bidding, enter an offer per kg and submit.</li><li>Track offers from My Bids.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 3</span><h3>Checkout</h3><ol class="helpdesk-substeps"><li>Review items and quantities in your cart.</li><li>Choose card or cash on delivery.</li><li>Place your order.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 4</span><h3>Follow delivery</h3><ol class="helpdesk-substeps"><li>Open My Orders.</li><li>Check order status and payment details.</li><li>Contact support if an order needs attention.</li></ol></li>
    </ol>
  </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
