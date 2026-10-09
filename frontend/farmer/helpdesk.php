<?php
$portal = 'farmer'; $active = 'helpdesk'; $pageTitle = 'Helpdesk';
$crumb = ''; $userRole = 'Farmer'; $needsHelpdesk = true;
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

  <section class="helpdesk-section" aria-labelledby="farmerFaqTitle">
    <div class="helpdesk-section__head"><h2 id="farmerFaqTitle">Frequently asked questions</h2></div>
    <div class="helpdesk-faq">
      <details><summary>How do I switch a listing from fixed price to bidding?</summary><p>Open the listing from "My Listings", choose Edit, and select "Bidding" as the selling method. You'll be asked to set a starting bid and closing time.</p></details>
      <details><summary>When do I get paid after an order is delivered?</summary><p>Payments are released to your account within 24 hours of the rider marking an order as "Delivered" and the buyer confirming receipt.</p></details>
      <details><summary>I don't have reliable internet — can I still get updates?</summary><p>Yes. Enable SMS alerts in your profile settings to receive bid, order and verification updates by text message.</p></details>
    </div>
  </section>

  <section class="helpdesk-section helpdesk-manual" aria-labelledby="farmerManualTitle">
    <div class="helpdesk-section__head"><h2 id="farmerManualTitle">How to Use?</h2></div>
    <ol class="helpdesk-steps">
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 1</span><h3>Verify your account</h3><ol class="helpdesk-substeps"><li>Open Verification.</li><li>Upload a clear photo or scan of your NIC.</li><li>Wait for approval before publishing listings.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 2</span><h3>List your produce</h3><ol class="helpdesk-substeps"><li>Open My Listings and create a listing.</li><li>Add crop, quantity, harvest date, location, and a clear photo.</li><li>Choose fixed price or bidding, then publish.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 3</span><h3>Review bids and orders</h3><ol class="helpdesk-substeps"><li>Open Bids Received to compare offers.</li><li>Accept or reject each pending bid.</li><li>Check My Orders for new purchases.</li></ol></li>
      <li class="helpdesk-step"><span class="helpdesk-step__number">Step 4</span><h3>Fulfill the sale</h3><ol class="helpdesk-substeps"><li>Open the order and review its items.</li><li>Prepare the produce for pickup or delivery.</li><li>Update the order as it progresses.</li></ol></li>
    </ol>
  </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
