<?php /* The applicant's verification page, shared by the farmer and rider portals. */ ?>
<div id="verificationLoading" class="gp-empty">
  <i class="bi bi-hourglass-split"></i>
  <h4>Loading your verification status…</h4>
</div>

<div id="verificationApp" style="display:none;max-width:820px;">
  <div id="verificationBanner" class="gp-card" style="margin-bottom:18px;"></div>

  <div class="gp-card">
    <div class="section-head" style="margin-bottom:6px;">
      <h3 style="margin:0;">Identification documents</h3>
    </div>
    <p class="muted" style="margin-bottom:14px;font-size:13px;">Upload a clear photo or scan (JPG, PNG or PDF, up to 5 MB each). Only you and our verification team can see these files.</p>
    <div id="documentList"></div>
  </div>
</div>
