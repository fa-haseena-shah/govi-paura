<?php
$portal = 'admin'; $active = 'manage_users'; $pageTitle = 'Manage Users';
$crumb = 'Review account activity and access'; $userRole = 'Admin';
$pageScript = 'admin-management';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar" style="gap:12px;flex-wrap:wrap;">
  <div class="field" style="margin:0;flex:1;min-width:220px;"><label class="visually-hidden" for="adminUserSearch">Search users</label><input id="adminUserSearch" type="search" placeholder="Search name, email or phone"></div>
  <select id="adminUserRole" aria-label="Filter by role"><option value="">All roles</option><option value="farmer">Farmers</option><option value="buyer">Buyers</option><option value="rider">Riders</option><option value="admin">Admins</option></select>
  <select id="adminUserStatus" aria-label="Filter by account status"><option value="">All statuses</option><option value="active">Active</option><option value="pending_verification">Pending verification</option><option value="suspended">Suspended</option><option value="rejected">Rejected</option></select>
</div>

<div class="gp-card">
  <div class="table-wrap"><table class="gp-table">
    <thead><tr><th>Account</th><th>Role</th><th>Contact</th><th>Status</th><th>Registered</th><th></th></tr></thead>
    <tbody id="adminUsersBody"><tr><td colspan="6" class="muted">Loading users…</td></tr></tbody>
  </table></div>
  <div id="adminUsersPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>