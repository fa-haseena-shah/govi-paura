/**
 * Govi Paura — ratings.js
 * Loaded via $pageScript = 'ratings' on farmer/ratings.php and buyer/ratings.php.
 *
 * Farmer view: received reviews list + summary stats  (GET /ratings/received)
 * Buyer view:  submit a rating for a completed order  (POST /orders/{id}/ratings)
 *              list of reviews the buyer has given    (GET /ratings/given)
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc } = gp;

  /* ── shared helpers ───────────────────────────────────────────────── */

  function starsMarkup(score) {
    const filled = Math.max(0, Math.min(5, Math.round(Number(score) || 0)));
    return `<span class="stars" aria-label="${filled} out of 5 stars">${'★'.repeat(filled)}<span class="off">${'★'.repeat(5 - filled)}</span></span>`;
  }

  function reviewDate(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return 'Date unavailable';
    const days = Math.floor((Date.now() - date.getTime()) / 86400000);
    if (days <= 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 30) return `${days} days ago`;
    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(date);
  }

  /* ══════════════ FARMER — received reviews ═══════════════════════════ */

  const ratingsList   = document.getElementById('ratingsList');
  const average       = document.getElementById('ratingAverage');
  const averageStars  = document.getElementById('ratingAverageStars');
  const count         = document.getElementById('ratingCount');
  const fiveStarCount = document.getElementById('fiveStarCount');
  const pagination    = document.getElementById('ratingsPagination');
  const pageLabel     = document.getElementById('ratingsPageLabel');
  const previousButton = document.getElementById('ratingsPrevious');
  const nextButton    = document.getElementById('ratingsNext');

  if (ratingsList) {
    const pageSize = 10;
    let page    = 1;
    let total   = 0;
    let loading = false;

    function updatePagination() {
      const pageCount = Math.ceil(total / pageSize);
      pagination.hidden = total <= pageSize;
      pageLabel.textContent = `Showing ${(page - 1) * pageSize + 1}–${Math.min(page * pageSize, total)} of ${total}`;
      previousButton.disabled = loading || page <= 1;
      nextButton.disabled     = loading || page >= pageCount;
    }

    function renderReceivedReviews(reviews) {
      if (!reviews.length) {
        ratingsList.innerHTML = '<p class="muted">You have not received any reviews yet.</p>';
        return;
      }
      ratingsList.innerHTML = reviews.map((r, i) => `
        <article style="padding:14px 0;${i < reviews.length - 1 ? 'border-bottom:1px solid var(--line);' : ''}">
          <div class="section-head" style="margin-bottom:4px;">
            <strong>${esc(r.raterName || 'Buyer')}</strong>
            ${starsMarkup(r.score)}
          </div>
          ${r.comment ? `<p style="margin-bottom:4px;">${esc(r.comment)}</p>` : '<p class="muted" style="margin-bottom:4px;">No written comment.</p>'}
          <span class="muted" style="font-size:12px;">Order #GP-${esc(String(r.orderId))} &middot; ${esc(reviewDate(r.createdAt))}</span>
        </article>`).join('');
    }

    async function loadReceivedReviews() {
      if (loading) return;
      loading = true;
      ratingsList.innerHTML = '<p class="muted">Loading reviews...</p>';
      updatePagination();
      try {
        await gp.session;
        const result      = await apiFetch(`/ratings/received?page=${page}&per_page=${pageSize}`);
        const summary     = result.summary ?? {};
        const averageValue = summary.average == null ? null : Number(summary.average);
        if (average)      average.textContent   = averageValue == null || !Number.isFinite(averageValue) ? '—' : averageValue.toFixed(1);
        if (averageStars) averageStars.innerHTML = averageValue == null ? '' : starsMarkup(averageValue);
        if (count)        count.textContent      = summary.count ?? result.total ?? 0;
        if (fiveStarCount) fiveStarCount.textContent = summary.distribution?.[5] ?? 0;
        total = Number(result.total) || 0;
        renderReceivedReviews(result.data ?? []);
      } catch (err) {
        ratingsList.innerHTML = `<p class="muted">Could not load reviews: ${esc(err.message)} <button class="btn btn-outline btn-sm" type="button" data-ratings-retry>Retry</button></p>`;
      } finally {
        loading = false;
        updatePagination();
      }
    }

    ratingsList.addEventListener('click', (e) => { if (e.target.closest('[data-ratings-retry]')) loadReceivedReviews(); });
    previousButton?.addEventListener('click', () => { if (page > 1) { page--; loadReceivedReviews(); } });
    nextButton?.addEventListener('click',     () => { if (page < Math.ceil(total / pageSize)) { page++; loadReceivedReviews(); } });

    loadReceivedReviews();
  }

  /* ══════════════ BUYER — submit a rating ════════════════════════════ */

  const orderSelect    = document.getElementById('rateOrderSelect');
  const ratingForm     = document.getElementById('ratingForm');
  const ratingFormWrap = document.getElementById('ratingFormWrap');
  const submitMsg      = document.getElementById('ratingSubmitMsg');

  /** Wire interactive star-input clicks inside a container element. */
  function initStarInput(container) {
    const hidden = container.querySelector('input[type="hidden"][name="score"]');
    const stars  = [...container.querySelectorAll('i.bi')];

    function paint(n) {
      stars.forEach((s, i) => {
        s.classList.toggle('bi-star-fill', i < n);
        s.classList.toggle('bi-star', i >= n);
      });
    }

    stars.forEach((star, idx) => {
      star.addEventListener('click', () => {
        const val = idx + 1;
        if (hidden) hidden.value = val;
        paint(val);
      });
      star.addEventListener('mouseenter', () => paint(idx + 1));
    });
    container.addEventListener('mouseleave', () => paint(Number(hidden?.value) || 0));
  }

  // Populate the order select with completed orders that haven't been rated yet
  async function loadRatableOrders() {
    if (!orderSelect) return;
    orderSelect.innerHTML = '<option value="">Loading orders…</option>';
    orderSelect.disabled  = true;
    try {
      await gp.session;
      // Fetch buyer's orders; filter to Completed on the client side
      const res    = await apiFetch('/orders?per_page=100');
      const orders = (res.data ?? []).filter((o) => o.status === 'completed');

      if (orders.length === 0) {
        orderSelect.innerHTML = '<option value="">No completed orders to rate</option>';
        if (ratingFormWrap) ratingFormWrap.style.display = 'none';
        return;
      }

      orderSelect.innerHTML = '<option value="">Select a completed order…</option>' +
        orders.map((o) => {
          const label = `#GP-${o.id}` + (o.items?.length ? ' — ' + o.items.map((i) => `${esc(i.cropType ?? i.crop_type ?? '?')} ${i.qty ?? i.quantity}kg`).join(', ') : '');
          return `<option value="${o.id}">${label}</option>`;
        }).join('');
      orderSelect.disabled = false;
    } catch (err) {
      orderSelect.innerHTML = `<option value="">Could not load orders: ${esc(err.message)}</option>`;
    }
  }

  if (orderSelect) {
    orderSelect.addEventListener('change', () => {
      if (ratingFormWrap) ratingFormWrap.style.display = orderSelect.value ? '' : 'none';
      if (submitMsg) submitMsg.style.display = 'none';
    });
    loadRatableOrders();
  }

  if (ratingForm) {
    const starContainer = ratingForm.querySelector('.star-input');
    if (starContainer) initStarInput(starContainer);

    ratingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const orderId = orderSelect?.value;
      if (!orderId) return;

      const score   = Number(ratingForm.elements.namedItem('score')?.value);
      const comment = ratingForm.elements.namedItem('comment')?.value?.trim() || null;

      if (!score || score < 1 || score > 5) {
        if (submitMsg) { submitMsg.textContent = 'Please select a star rating.'; submitMsg.className = 'form-message form-message--error'; submitMsg.style.display = ''; }
        return;
      }

      const btn = ratingForm.querySelector('[type="submit"]');
      if (btn) { btn.disabled = true; btn.textContent = 'Submitting…'; }

      try {
        await apiFetch(`/orders/${orderId}/ratings`, {
          method: 'POST',
          body:   JSON.stringify({ score, comment }),
        });

        if (submitMsg) { submitMsg.textContent = 'Review submitted — thank you!'; submitMsg.className = 'form-message form-message--success'; submitMsg.style.display = ''; }
        ratingForm.reset();
        if (starContainer) initStarInput(starContainer);  // reset star UI
        if (ratingFormWrap) ratingFormWrap.style.display = 'none';
        orderSelect.value = '';

        // Reload the "reviews you've given" section
        await loadGivenReviews(1);
        // Remove the rated order from the select
        await loadRatableOrders();
      } catch (err) {
        if (submitMsg) { submitMsg.textContent = err.message; submitMsg.className = 'form-message form-message--error'; submitMsg.style.display = ''; }
      } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Submit Review'; }
      }
    });
  }

  /* ══════════════ BUYER — reviews given ══════════════════════════════ */

  const givenList      = document.getElementById('givenList');
  const givenEmpty     = document.getElementById('givenEmpty');
  const givenPagination = document.getElementById('givenPagination');
  const givenPageLabel  = document.getElementById('givenPageLabel');
  const givenPrev      = document.getElementById('givenPrevious');
  const givenNext      = document.getElementById('givenNext');

  let givenPage  = 1;
  let givenTotal = 0;
  const givenPageSize = 10;

  function updateGivenPagination() {
    if (!givenPagination) return;
    const pages = Math.ceil(givenTotal / givenPageSize);
    givenPagination.hidden    = givenTotal <= givenPageSize;
    if (givenPageLabel) givenPageLabel.textContent = `Showing ${(givenPage - 1) * givenPageSize + 1}–${Math.min(givenPage * givenPageSize, givenTotal)} of ${givenTotal}`;
    if (givenPrev) givenPrev.disabled = givenPage <= 1;
    if (givenNext) givenNext.disabled = givenPage >= pages;
  }

  async function loadGivenReviews(pg = givenPage) {
    if (!givenList) return;
    givenPage = pg;
    givenList.innerHTML = '<p class="muted">Loading…</p>';
    try {
      await gp.session;
      const result = await apiFetch(`/ratings/given?page=${givenPage}&per_page=${givenPageSize}`);
      const reviews = result.data ?? [];
      givenTotal = Number(result.total) || 0;

      if (reviews.length === 0) {
        givenList.innerHTML = '';
        if (givenEmpty) givenEmpty.style.display = '';
      } else {
        if (givenEmpty) givenEmpty.style.display = 'none';
        givenList.innerHTML = reviews.map((r, i) => `
          <article style="padding:14px 0;${i < reviews.length - 1 ? 'border-bottom:1px solid var(--line);' : ''}">
            <div class="section-head" style="margin-bottom:4px;">
              <strong>${esc(r.rateeName || 'Farmer')}</strong>
              ${starsMarkup(r.score)}
            </div>
            ${r.comment ? `<p style="margin-bottom:4px;">${esc(r.comment)}</p>` : '<p class="muted" style="margin-bottom:4px;">No written comment.</p>'}
            <span class="muted" style="font-size:12px;">Order #GP-${esc(String(r.orderId))} &middot; ${esc(reviewDate(r.createdAt))}</span>
          </article>`).join('');
      }
      updateGivenPagination();
    } catch (err) {
      givenList.innerHTML = `<p class="muted">Could not load reviews: ${esc(err.message)}</p>`;
    }
  }

  givenPrev?.addEventListener('click', () => { if (givenPage > 1) loadGivenReviews(givenPage - 1); });
  givenNext?.addEventListener('click', () => { if (givenPage < Math.ceil(givenTotal / givenPageSize)) loadGivenReviews(givenPage + 1); });

  if (givenList) loadGivenReviews();
});