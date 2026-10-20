<?php
$portal = 'rider'; $active = 'assigned_deliveries'; $pageTitle = 'Update Delivery Status';
$crumb = 'Add notes or advance the delivery stage'; $userRole = 'Delivery Rider';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-card" style="max-width:560px;">
  <h3 id="updateStatusOrderHeading" style="margin-bottom:4px;">Loading delivery…</h3>
  <p class="muted" style="margin-bottom:20px;">Update the current stage and optionally attach a note.</p>

  <form id="updateStatusForm" novalidate>
    <div class="field">
      <label for="status">Current status</label>
      <select id="status" name="status" required>
        <option value="assigned">Assigned</option>
        <option value="picked_up">Picked Up</option>
        <option value="in_transit" selected>In Transit</option>
        <option value="delivered">Delivered</option>
      </select>
    </div>
    <div class="field">
      <label for="note">Note (optional)</label>
      <textarea id="note" name="note" rows="3" placeholder="e.g. Buyer requested drop-off at gate"></textarea>
    </div>
    <p id="updateStatusError" class="field-error" style="display:none;margin-bottom:14px;"></p>
    <button type="submit" class="btn btn-gold btn-block">Save Update</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
