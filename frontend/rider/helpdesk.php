<?php
$portal = 'rider'; $active = 'helpdesk'; $pageTitle = 'Helpdesk';
$crumb = ''; $userRole = 'Rider'; $needsHelpdesk = true;
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

  <section class="helpdesk-section" aria-labelledby="riderFaqTitle">
    <div class="helpdesk-section__head"><h2 id="riderFaqTitle">Frequently asked questions</h2></div>
    <div class="helpdesk-faq">
      <details><summary>How do I accept an available delivery?</summary><p>Open "Available Deliveries", review the pickup and drop-off points along your route, and tap Accept. It will then appear under "Assigned Deliveries" with pickup instructions.</p></details>
      <details><summary>How do I update a delivery's status?</summary><p>From "Assigned Deliveries", mark each order as Picked Up and then Delivered as you complete the route. Both the farmer and buyer are notified automatically at each step.</p></details>
      <details><summary>How does the monthly subscription work?</summary><p>A subscription covers one month and allows you to play a crucial role in Govi Paura's operations! It does not renew automatically. Call the support hotline to arrange or renew your monthly plan.</p></details>
      <details><summary>When and how am I paid for deliveries?</summary><p>Delivery fees are calculated per completed order and released to your registered account within 24 hours of the buyer confirming receipt.</p></details>
    </div>
  </section>

  <section class="helpdesk-section helpdesk-manual" aria-labelledby="riderManualTitle">
    <div class="helpdesk-section__head"><h2 id="riderManualTitle">How to Use?</h2></div>
    <ol class="helpdesk-steps">
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 1</span><h3>Manage your monthly subscription</h3><ol class="helpdesk-substeps"><li>Renew each month.</li><li>Call support or pay online to arrange or renew.</li></ol><a href="tel:+94112345678">Call support</a></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 2</span><h3>Find and accept a delivery</h3><ol class="helpdesk-substeps"><li>Open Available Deliveries.</li><li>Review pickup, destination, and route.</li><li>Accept a delivery that fits your route.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 3</span><h3>Pick up the order</h3><ol class="helpdesk-substeps"><li>Collect the produce from the farmer.</li><li>Confirm the items before leaving.</li><li>Update the status to Picked Up.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 4</span><h3>Complete delivery</h3><ol class="helpdesk-substeps"><li>Bring the order to the buyer.</li><li>Hand over the produce.</li><li>Mark the delivery as Delivered.</li></ol></li>
    </ol>
  </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
