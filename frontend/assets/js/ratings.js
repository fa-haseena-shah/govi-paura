document.addEventListener('DOMContentLoaded', () => {
  const ratingsList = document.getElementById('ratingsList');
  if (!ratingsList) return;

  const average = document.getElementById('ratingAverage');
  const averageStars = document.getElementById('ratingAverageStars');
  const count = document.getElementById('ratingCount');
  const fiveStarCount = document.getElementById('fiveStarCount');
  const pagination = document.getElementById('ratingsPagination');
  const pageLabel = document.getElementById('ratingsPageLabel');
  const previousButton = document.getElementById('ratingsPrevious');
  const nextButton = document.getElementById('ratingsNext');
  const pageSize = 10;
  let page = 1;
  let total = 0;
  let loading = false;

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

  function updatePagination() {
    const pageCount = Math.ceil(total / pageSize);
    pagination.hidden = total <= pageSize;
    pageLabel.textContent = `Showing ${(page - 1) * pageSize + 1}-${Math.min(page * pageSize, total)} of ${total}`;
    previousButton.disabled = loading || page <= 1;
    nextButton.disabled = loading || page >= pageCount;
  }

  function renderReviews(reviews) {
    if (!reviews.length) {
      ratingsList.innerHTML = '<p class="muted">You have not received any reviews yet.</p>';
      return;
    }

    ratingsList.innerHTML = reviews.map((review, index) => `
      <article style="padding:14px 0;${index < reviews.length - 1 ? 'border-bottom:1px solid var(--line);' : ''}">
        <div class="section-head" style="margin-bottom:4px;">
          <strong>${gp.esc(review.raterName || 'Buyer')}</strong>
          ${starsMarkup(review.score)}
        </div>
        ${review.comment ? `<p style="margin-bottom:4px;">${gp.esc(review.comment)}</p>` : '<p class="muted" style="margin-bottom:4px;">No written comment.</p>'}
        <span class="muted" style="font-size:12px;">Order #${gp.esc(review.orderId)} &middot; ${gp.esc(reviewDate(review.createdAt))}</span>
      </article>`).join('');
  }

  async function loadReviews() {
    if (loading) return;
    loading = true;
    ratingsList.innerHTML = '<p class="muted">Loading reviews...</p>';
    updatePagination();

    try {
      await gp.session;
      const result = await apiFetch(`/ratings/received?page=${page}&per_page=${pageSize}`);
      const summary = result.summary ?? {};
      const averageValue = summary.average == null ? null : Number(summary.average);

      average.textContent = averageValue == null || !Number.isFinite(averageValue) ? '—' : averageValue.toFixed(1);
      averageStars.innerHTML = averageValue == null ? '' : starsMarkup(averageValue);
      count.textContent = summary.count ?? result.total ?? 0;
      fiveStarCount.textContent = summary.distribution?.[5] ?? 0;
      total = Number(result.total) || 0;
      renderReviews(result.data ?? []);
    } catch (error) {
      ratingsList.innerHTML = `<p class="muted">Could not load reviews: ${gp.esc(error.message)} <button class="btn btn-outline btn-sm" type="button" data-ratings-retry>Retry</button></p>`;
    } finally {
      loading = false;
      updatePagination();
    }
  }

  ratingsList.addEventListener('click', (event) => {
    if (event.target.closest('[data-ratings-retry]')) loadReviews();
  });
  previousButton.addEventListener('click', () => {
    if (page > 1) {
      page -= 1;
      loadReviews();
    }
  });
  nextButton.addEventListener('click', () => {
    if (page < Math.ceil(total / pageSize)) {
      page += 1;
      loadReviews();
    }
  });

  loadReviews();
});