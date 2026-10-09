/**
 * Govi Paura — common.js
 * Loaded on every portal page (farmer / buyer / ...) after api.js and main.js.
 *   1. Shared helpers on window.gp (escaping, money, badges, countdowns)
 *   2. Session guard: sends logged-out users to the login page, sends users to
 *      their own portal, fills in the name in the top bar, wires "Log out"
 *   gp.session is a promise that resolves to the logged-in user (from GET /me)
 */
(function () {
  const ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };

  const gp = {
    /** Escape text before putting it into innerHTML. */
    esc: (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ESC[ch]),

    money: (amount) => `LKR ${Number(amount ?? 0).toLocaleString('en-LK', { maximumFractionDigits: 2 })}`,

    formatDateTime: (value) => value
      ? new Date(value).toLocaleString('en-LK', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })
      : '—',

    orderRef: (id) => `#${id}`,

    itemsSummary: (items) => (items ?? []).map((i) => `${gp.esc(i.cropType)} x${i.qty}`).join(', '),

    orderStatusBadge(status) {
      const map = {
        pending: ['badge-warning', 'Pending'],
        confirmed: ['badge-sage', 'Confirmed'],
        processing: ['badge-sage', 'Processing'],
        delivered: ['badge-success', 'Delivered'],
        completed: ['badge-success', 'Completed'],
      };
      const [cls, label] = map[status] ?? ['badge-muted', status];
      return `<span class="badge ${cls}">${gp.esc(label)}</span>`;
    },

    /** Paid / cash on delivery / unpaid, from the order's payment fields. */
    paymentBadge(order) {
      if (order.paymentStatus === 'paid') return '<span class="badge badge-success">Paid</span>';
      if (order.paymentMethod === 'cash_on_delivery') return '<span class="badge badge-warning">Cash on delivery</span>';
      return '<span class="badge badge-muted">Unpaid</span>';
    },

    /** Start "3h 20m 5s left" timers on [data-countdown] elements (also for elements added later). */
    startCountdowns(root = document) {
      root.querySelectorAll('[data-countdown]').forEach((el) => {
        if (el.dataset.countdownStarted) return;
        el.dataset.countdownStarted = '1';
        const end = new Date(el.dataset.countdown).getTime();
        const tick = () => {
          if (!document.body.contains(el)) return;
          const diff = end - Date.now();
          if (diff <= 0) { el.textContent = 'Closed'; return; }
          const h = Math.floor(diff / 3600000);
          const m = Math.floor((diff % 3600000) / 60000);
          const s = Math.floor((diff % 60000) / 1000);
          el.textContent = `${h}h ${m}m ${s}s left`;
          setTimeout(tick, 1000);
        };
        tick();
      });
    },

    /** "2026-12-31T18:00:00+05:30" -> "2026-12-31T18:00" for <input type="datetime-local">. */
    toLocalInput(iso) {
      if (!iso) return '';
      const d = new Date(iso);
      const pad = (n) => String(n).padStart(2, '0');
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    },
  };

  window.gp = gp;

  /* ---------------- session guard ---------------- */
  const portal = document.body.dataset.portal;
  const loginUrl = `${BASE_URL_JS}/auth/login.php`;

  if (!localStorage.getItem('token')) {
    window.location.replace(loginUrl);
    gp.session = new Promise(() => {}); // page scripts wait forever while we redirect
    return;
  }

  gp.session = apiFetch('/me').then((res) => {
    const user = res.data;

    if (portal && user.role !== portal) {
      window.location.replace(`${BASE_URL_JS}/${user.role}/dashboard.php`);
      return new Promise(() => {});
    }

    const nameEl = document.querySelector('.gp-user__name');
    const roleEl = document.querySelector('.gp-user__sub');
    const avatarEl = document.querySelector('.gp-user__avatar');
    if (nameEl) nameEl.textContent = user.full_name;
    if (roleEl) roleEl.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    if (avatarEl) {
      avatarEl.textContent = user.full_name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('');
    }
    return user;
  });
  gp.session.catch(() => {}); // apiFetch already redirects on 401; pages handle other errors themselves

  document.querySelectorAll('a[data-i18n="logout"]').forEach((link) => {
    link.addEventListener('click', async (e) => {
      e.preventDefault();
      try { await apiFetch('/logout', { method: 'POST' }); } catch (err) { /* token may already be invalid */ }
      localStorage.removeItem('token');
      localStorage.removeItem('gp_cart');
      window.location.href = loginUrl;
    });
  });
})();
