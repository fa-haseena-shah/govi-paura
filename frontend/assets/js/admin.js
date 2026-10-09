/**
 * Govi Paura — admin.js
 * Handles all API interactions for the Admin portal.
 * Loaded by footer.php on every /admin/* page after api.js and main.js.
 *
 * Pages handled:
 *   - /admin/manage_listings.php  → view, filter, remove listings
 */

document.addEventListener('DOMContentLoaded', () => {

  const totalUsers = document.getElementById('adminTotalUsers');
  if (totalUsers) {
    (async () => {
      try {
        await gp.session;
        const { data } = await apiFetch('/admin/dashboard');
        totalUsers.textContent = Number(data.total_users).toLocaleString();
        document.getElementById('adminActiveListings').textContent = Number(data.active_listings).toLocaleString();
        document.getElementById('adminOrdersThisMonth').textContent = Number(data.orders_this_month).toLocaleString();
        document.getElementById('adminPaidOrderVolume').textContent = gp.money(data.paid_order_volume);
      } catch (err) {
        totalUsers.textContent = '—';
        document.getElementById('adminActiveListings').textContent = '—';
        document.getElementById('adminOrdersThisMonth').textContent = '—';
        document.getElementById('adminPaidOrderVolume').textContent = '—';
        window.gpToast('Could not load dashboard metrics: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════════════════════════════════════════════
     MANAGE LISTINGS PAGE  (/admin/manage_listings.php)
  ══════════════════════════════════════════════════════ */
  const tbody          = document.getElementById('adminListingsTbody');
  const loadingState   = document.getElementById('adminListingsLoading');
  const emptyState     = document.getElementById('adminListingsEmpty');
  const paginationWrap = document.getElementById('adminListingsPagination');
  const filterPills    = document.getElementById('adminListingPills');

  if (!tbody) return; // Not on manage listings page

  let currentPage   = 1;
  let currentFilter = 'all';

  function statusBadge(status, sellingMethod) {
    if (status === 'active' && sellingMethod === 'bidding') {
      return '<span class="badge badge-warning">Bidding</span>';
    }
    if (status === 'active') return '<span class="badge badge-success">Active</span>';
    if (status === 'sold')   return '<span class="badge badge-muted">Sold out</span>';
    if (status === 'closed') return '<span class="badge badge-muted">Closed</span>';
    return `<span class="badge badge-muted">${status}</span>`;
  }

  function priceCell(listing) {
    if (listing.sellingMethod === 'fixed_price' && listing.pricePerUnit != null) {
      return `LKR ${Number(listing.pricePerUnit).toLocaleString()}/kg`;
    }
    if (listing.sellingMethod === 'bidding' && listing.startingPrice != null) {
      return `from LKR ${Number(listing.startingPrice).toLocaleString()}`;
    }
    return '—';
  }

  function renderRow(listing) {
    const tr = document.createElement('tr');
    tr.dataset.status = listing.status;
    tr.innerHTML = `
      <td>${listing.cropType}</td>
      <td>Farmer #${listing.farmerId}</td>
      <td>${listing.qty} kg</td>
      <td>${listing.sellingMethod === 'fixed_price' ? 'Fixed' : 'Bidding'}</td>
      <td>${priceCell(listing)}</td>
      <td>${statusBadge(listing.status, listing.sellingMethod)}</td>
      <td class="no-export">
        <a href="${BASE_URL_JS}/buyer/listing_details.php?id=${listing.id}" class="btn btn-outline btn-sm" target="_blank">View</a>
        ${listing.status !== 'sold' && listing.status !== 'closed'
          ? `<button class="btn btn-danger btn-sm" data-admin-close="${listing.id}">Remove</button>`
          : ''
        }
      </td>
    `;
    return tr;
  }

  function renderPagination(total, page, perPage) {
    if (!paginationWrap) return;
    paginationWrap.innerHTML = '';
    const totalPages = Math.ceil(total / perPage);
    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.className = `btn btn-sm ${i === page ? 'btn-accent' : 'btn-outline'}`;
      btn.textContent = i;
      btn.addEventListener('click', () => { currentPage = i; fetchListings(); });
      paginationWrap.appendChild(btn);
    }
  }

  async function fetchListings() {
    loadingState.style.display = '';
    emptyState.style.display   = 'none';

    const params = new URLSearchParams({ page: currentPage, per_page: 20 });
    // Note: backend browse() only returns 'active'. For admin moderation a dedicated
    // endpoint with all statuses would be ideal. We fetch active and supplement as the
    // backend evolves.
    if (currentFilter !== 'all') {
      // Status filter applied client-side after fetch until backend supports status param
    }

    try {
      const result = await apiFetch(`/listings?${params.toString()}`);
      let listings = result.data ?? [];

      if (currentFilter !== 'all') {
        listings = listings.filter(l => {
          if (currentFilter === 'active') return l.status === 'active';
          if (currentFilter === 'sold')   return l.status === 'sold';
          if (currentFilter === 'closed') return l.status === 'closed';
          return true;
        });
      }

      loadingState.style.display = 'none';
      tbody.innerHTML = '';

      if (listings.length === 0) {
        emptyState.style.display = '';
        renderPagination(0, 1, 20);
        return;
      }

      listings.forEach(l => tbody.appendChild(renderRow(l)));
      renderPagination(result.total, result.page, result.perPage);

    } catch (err) {
      loadingState.style.display = 'none';
      emptyState.style.display   = '';
      window.gpToast('Could not load listings: ' + err.message, 'error');
    }
  }

  /* ---------- Filter pills ---------- */
  if (filterPills) {
    filterPills.addEventListener('gp:filterchange', (e) => {
      currentFilter = e.detail;
      currentPage   = 1;
      fetchListings();
    });
  }

  /* ---------- Admin close (remove) listing ---------- */
  tbody.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-admin-close]');
    if (!btn) return;
    const listingId = parseInt(btn.dataset.adminClose, 10);
    if (!confirm(`Remove listing #${listingId}? The farmer will no longer be able to receive orders on it.`)) return;

    try {
      await apiFetch(`/listings/${listingId}/close`, { method: 'POST' });
      window.gpToast('Listing removed.');
      fetchListings();
    } catch (err) {
      window.gpToast(err.message, 'error');
    }
  });

  /* ---------- Init ---------- */
  fetchListings();
});

