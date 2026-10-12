/**
 * Govi Paura — notification center for buyer, farmer, and rider portals.
 * Uses the authenticated Notification API and the shared gp:filterchange event.
 */
document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('notificationsList');
  if (!list) return;

  const filters = document.getElementById('notificationFilters');
  const unreadLabel = document.getElementById('notificationUnreadCount');
  const markAllButton = document.getElementById('markAllNotificationsRead');
  const pagination = document.getElementById('notificationsPagination');
  const { esc } = gp;
  const pageSize = 20;
  const categories = {
    bid_placed: 'bids',
    bid_rejected: 'bids',
    bidding_closed: 'bids',
    order_created: 'orders',
    order_status_updated: 'orders',
    payment_completed: 'orders',
    delivery_status_updated: 'orders',
    low_stock: 'system',
  };
  let notifications = [];
  let filter = 'all';
  let page = 1;
  let unread = 0;
  let total = 0;

  function render() {
    const visible = filter === 'all'
      ? notifications
      : notifications.filter((notification) => categories[notification.type] === filter);

    unreadLabel.textContent = `${unread} unread`;
    markAllButton.disabled = unread === 0;

    if (!visible.length) {
      list.innerHTML = `<p class="muted">${notifications.length ? 'No notifications in this category on this page.' : 'You have no notifications yet.'}</p>`;
    } else {
      list.innerHTML = visible.map((notification) => {
        const created = notification.createdAt && !Number.isNaN(Date.parse(notification.createdAt))
          ? new Date(notification.createdAt).toLocaleString('en-LK', { dateStyle: 'medium', timeStyle: 'short' })
          : '—';
        const action = notification.isRead
          ? ''
          : `<button type="button" class="btn btn-outline btn-sm" data-mark-read="${notification.id}">Mark as read</button>`;

        return `<article class="notif-item ${notification.isRead ? '' : 'unread'}" data-notification-id="${notification.id}">
          <div style="flex:1;min-width:0;">
            <strong>${esc(notification.title)}</strong>
            <div>${esc(notification.message)}</div>
          </div>
          <time class="notif-time" datetime="${esc(notification.createdAt)}">${esc(created)}</time>
          ${action}
        </article>`;
      }).join('');
    }

    renderPagination();
  }

  function renderPagination() {
    pagination.innerHTML = '';
    const pageCount = Math.ceil(total / pageSize);
    if (pageCount <= 1) return;

    for (let pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `btn btn-sm ${pageNumber === page ? 'btn-accent' : 'btn-outline'}`;
      button.textContent = pageNumber;
      button.setAttribute('aria-current', pageNumber === page ? 'page' : 'false');
      button.addEventListener('click', () => {
        page = pageNumber;
        load();
      });
      pagination.appendChild(button);
    }
  }

  async function load() {
    list.innerHTML = '<p class="muted">Loading notifications…</p>';
    try {
      await gp.session;
      const result = await apiFetch(`/notifications?page=${page}&per_page=${pageSize}`);
      notifications = result.data ?? [];
      total = Number(result.total ?? 0);
      unread = Number(result.unread ?? 0);
      render();
    } catch (error) {
      list.innerHTML = `<p class="muted">Could not load notifications: ${esc(error.message)}</p>`;
      pagination.innerHTML = '';
      unreadLabel.textContent = 'Unread count unavailable';
      window.gpToast('Could not load notifications: ' + error.message, 'error');
    }
  }

  filters?.addEventListener('gp:filterchange', (event) => {
    filter = event.detail;
    render();
  });

  list.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-mark-read]');
    if (!button) return;

    button.disabled = true;
    try {
      await apiFetch(`/notifications/${button.dataset.markRead}/read`, { method: 'POST' });
      const notification = notifications.find((item) => item.id === Number(button.dataset.markRead));
      if (notification && !notification.isRead) {
        notification.isRead = true;
        unread = Math.max(0, unread - 1);
      }
      render();
    } catch (error) {
      button.disabled = false;
      window.gpToast(error.message, 'error');
    }
  });

  markAllButton.addEventListener('click', async () => {
    markAllButton.disabled = true;
    try {
      await apiFetch('/notifications/read-all', { method: 'POST' });
      notifications.forEach((notification) => { notification.isRead = true; });
      unread = 0;
      render();
      window.gpToast('All notifications marked as read.');
    } catch (error) {
      markAllButton.disabled = false;
      window.gpToast(error.message, 'error');
    }
  });

  load();
});
