<?php
$portal = 'admin'; $active = 'verify_users'; $pageTitle = 'Verify Users';
$crumb = 'Review identification documents from farmers and riders'; $userRole = 'Admin';
$pageScript = 'verification';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="filter-pills" id="reviewPills">
    <span class="pill active" data-value="pending">Pending</span>
    <span class="pill" data-value="rejected">Rejected</span>
    <span class="pill" data-value="approved">Approved</span>
  </div>
</div>

<div class="gp-card">
  <div class="table-wrap">
    <table class="gp-table">
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Details</th>
          <th>Documents</th>
          <th>Submitted</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="reviewBody">
        <tr><td colspan="5" class="muted">Loading…</td></tr>
      </tbody>
    </table>
  </div>
  <div id="reviewPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<!-- Reject with a reason -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none;">
      <div class="modal-header" style="border-bottom:1px solid var(--line);">
        <h3 style="margin:0;" id="rejectTitle">Reject request</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <div class="field">
          <label for="rejectReason">Reason (the applicant will see this)</label>
          <textarea id="rejectReason" rows="3" maxlength="500" placeholder="e.g. The NIC photo is too blurry to read."></textarea>
        </div>
        <p id="rejectError" style="display:none;color:#c0392b;font-size:13px;margin:0 0 12px;"></p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="rejectConfirmBtn">Reject request</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
