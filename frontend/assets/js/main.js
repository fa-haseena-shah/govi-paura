/**
 * Govi Paura — shared frontend behaviour
 * Sidebar toggle, dropdowns, tabs, filter pills, star inputs, countdown timers,
 * client-side validation helpers. Loaded on every portal page after Bootstrap JS.
 */
document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Mobile sidebar toggle ---------- */
  const hamburger = document.querySelector('.gp-hamburger');
  const sidebar = document.querySelector('.gp-sidebar');
  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 980 && sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) && !hamburger.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* ---------- Filter pills (single-select toggle groups) ---------- */
  document.querySelectorAll('.filter-pills').forEach((group) => {
    group.querySelectorAll('.pill').forEach((pill) => {
      pill.addEventListener('click', () => {
        group.querySelectorAll('.pill').forEach((p) => p.classList.remove('active'));
        pill.classList.add('active');
        group.dispatchEvent(new CustomEvent('gp:filterchange', { detail: pill.dataset.value }));
      });
    });
  });

  /* ---------- Radio card groups (selling method, plans, etc.) ---------- */
  document.querySelectorAll('.radio-card-group').forEach((group) => {
    group.querySelectorAll('.radio-card').forEach((card) => {
      const input = card.querySelector('input[type="radio"]');
      const sync = () => {
        group.querySelectorAll('.radio-card').forEach((c) => c.classList.remove('selected'));
        if (input && input.checked) card.classList.add('selected');
      };
      card.addEventListener('click', () => { if (input) { input.checked = true; sync(); } });
      if (input) input.addEventListener('change', sync);
      sync();
    });
  });

  /* ---------- Live table / card search filter (with empty-state toggle) ---------- */
  document.querySelectorAll('[data-gp-search]').forEach((input) => {
    const targetSelector = input.dataset.gpSearch;
    // Optional: data-gp-empty="#selector" points to a .gp-empty block to show when 0 matches
    const emptyEl = input.dataset.gpEmpty ? document.querySelector(input.dataset.gpEmpty) : null;
    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      const rows = document.querySelectorAll(targetSelector);
      let visibleCount = 0;
      rows.forEach((row) => {
        const match = row.textContent.toLowerCase().includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visibleCount++;
      });
      if (emptyEl) emptyEl.style.display = visibleCount === 0 ? '' : 'none';
    });
  });

  /* ---------- Filter pills also respect empty-state targets ---------- */
  document.querySelectorAll('.filter-pills[data-gp-empty]').forEach((group) => {
    const emptyEl = document.querySelector(group.dataset.gpEmpty);
    const targetSelector = group.dataset.gpFilterTarget;
    if (!emptyEl || !targetSelector) return;
    group.addEventListener('gp:filterchange', () => {
      const anyVisible = [...document.querySelectorAll(targetSelector)].some((el) => el.style.display !== 'none');
      emptyEl.style.display = anyVisible ? 'none' : '';
    });
  });

  /* ---------- Countdown timers (bidding closing time, subscription expiry) ---------- */
  document.querySelectorAll('[data-countdown]').forEach((el) => {
    const end = new Date(el.dataset.countdown).getTime();
    const tick = () => {
      const diff = end - Date.now();
      if (diff <= 0) { el.textContent = 'Closed'; return; }
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.textContent = `${h}h ${m}m ${s}s left`;
      requestAnimationFrame(() => setTimeout(tick, 1000));
    };
    tick();
  });

  /* ---------- Star rating input widget ---------- */
  document.querySelectorAll('.star-input').forEach((wrap) => {
    const hidden = wrap.querySelector('input[type="hidden"]');
    const stars = [...wrap.querySelectorAll('i')];
    stars.forEach((star, idx) => {
      star.addEventListener('click', () => {
        hidden.value = idx + 1;
        stars.forEach((s, i) => s.classList.toggle('bi-star-fill', i <= idx));
        stars.forEach((s, i) => s.classList.toggle('bi-star', i > idx));
      });
    });
  });

  /* ---------- Quantity stepper (cart) ---------- */
  document.querySelectorAll('.qty-stepper').forEach((stepper) => {
    const input = stepper.querySelector('input');
    stepper.querySelector('[data-step="down"]').addEventListener('click', () => {
      input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
      input.dispatchEvent(new Event('change'));
    });
    stepper.querySelector('[data-step="up"]').addEventListener('click', () => {
      input.value = parseInt(input.value || '1', 10) + 1;
      input.dispatchEvent(new Event('change'));
    });
  });

  /* ---------- Form validation: required fields + phone/email/NIC format + password match ----------
     Note: /auth/login.php and /auth/register.php load assets/js/auth.js instead, which has this
     same validation logic (kept self-contained there since those pages don't include main.js). */
  const GP_PATTERNS = {
    phone: /^(?:\+94|0)?7\d{1}[\s-]?\d{3}[\s-]?\d{4}$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    nic: /^(?:\d{9}[VvXx]|\d{12})$/,
  };
  const GP_MESSAGES = {
    phone: 'Enter a valid Sri Lankan mobile number, e.g. 077 123 4567',
    email: 'Enter a valid email address',
    nic: 'Enter a valid NIC — 9 digits + V/X (old) or 12 digits (new)',
  };
  function gpFieldError(field, message) {
    field.style.borderColor = 'var(--rust)';
    let hint = field.parentElement.querySelector('.field-error');
    if (!hint) {
      hint = document.createElement('div');
      hint.className = 'field-error';
      hint.style.cssText = 'color:var(--rust);font-size:12px;margin-top:4px;';
      field.insertAdjacentElement('afterend', hint);
    }
    hint.textContent = message;
  }
  function gpClearFieldError(field) {
    field.style.borderColor = '';
    const hint = field.parentElement.querySelector('.field-error');
    if (hint) hint.remove();
  }

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      let ok = true;

      form.querySelectorAll('[required]').forEach((field) => {
        if (field.offsetParent === null) return;
        if (!field.value.trim()) {
          ok = false;
          gpFieldError(field, 'This field is required');
        } else {
          gpClearFieldError(field);
        }
      });

      form.querySelectorAll('[data-format]').forEach((field) => {
        if (field.offsetParent === null || !field.value.trim()) return;
        const pattern = GP_PATTERNS[field.dataset.format];
        if (pattern && !pattern.test(field.value.trim())) {
          ok = false;
          gpFieldError(field, GP_MESSAGES[field.dataset.format]);
        } else {
          gpClearFieldError(field);
        }
      });

      const pw = form.querySelector('[name="password"], #newPw');
      const confirmPw = form.querySelector('[name="confirm_password"]');
      if (pw && confirmPw && confirmPw.value) {
        if (pw.value !== confirmPw.value) {
          ok = false;
          gpFieldError(confirmPw, 'Passwords do not match');
        } else {
          gpClearFieldError(confirmPw);
        }
      }

      if (!ok) e.preventDefault();
    });
  });

  /* ---------- Toast helper (call gpToast('Saved changes')) ---------- */
  window.gpToast = function gpToast(message, tone = 'success') {
    let host = document.querySelector('.gp-toast-host');
    if (!host) {
      host = document.createElement('div');
      host.className = 'gp-toast-host';
      host.style.cssText = 'position:fixed;bottom:22px;right:22px;z-index:999;display:flex;flex-direction:column;gap:8px;';
      document.body.appendChild(host);
    }
    const toast = document.createElement('div');
    const bg = tone === 'error' ? '#C0433E' : '#0C3B2E';
    toast.textContent = message;
    toast.style.cssText = `background:${bg};color:#fff;padding:12px 18px;border-radius:10px;font-size:13.5px;box-shadow:0 6px 20px rgba(0,0,0,.15);`;
    host.appendChild(toast);
    setTimeout(() => toast.remove(), 3200);
  };

  /* ---------- CSV export (client-side; used by admin list/report pages) ----------
   * Reads an on-screen <table>, skips any cell/row marked .no-export or hidden
   * by the search/filter helpers above, and downloads the rest as a .csv file.
   */
  window.gpExportTableToCSV = function gpExportTableToCSV(tableSelector, filename) {
    const table = document.querySelector(tableSelector);
    if (!table) {
      window.gpToast('Nothing to export', 'error');
      return false;
    }
    const rows = Array.from(table.querySelectorAll('tr')).filter((row) => row.style.display !== 'none');
    const lines = rows
      .map((row) => {
        const cells = Array.from(row.querySelectorAll('th, td')).filter((cell) => !cell.classList.contains('no-export'));
        return cells
          .map((cell) => {
            let text = (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            return `"${text}"`;
          })
          .join(',');
      })
      .filter((line) => line.length > 0);

    if (lines.length <= 1) {
      window.gpToast('No rows to export', 'error');
      return false;
    }

    const csvContent = lines.join('\r\n');
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const safeName = (filename || 'export').replace(/[^a-z0-9-_]+/gi, '_');
    const link = document.createElement('a');
    link.href = url;
    link.download = `${safeName}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
    window.gpToast('CSV file downloaded');
    return true;
  };

  /* Generic array-of-objects -> CSV download, used where there is no on-screen
   * table to read from (e.g. a report generated for a custom date range). */
  window.gpDownloadCSV = function gpDownloadCSV(rows, filename) {
    if (!rows || !rows.length) {
      window.gpToast('No data to export', 'error');
      return false;
    }
    const headers = Object.keys(rows[0]);
    const escape = (val) => `"${String(val ?? '').replace(/"/g, '""')}"`;
    const lines = [headers.map(escape).join(',')];
    rows.forEach((row) => lines.push(headers.map((h) => escape(row[h])).join(',')));
    const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const safeName = (filename || 'export').replace(/[^a-z0-9-_]+/gi, '_');
    const link = document.createElement('a');
    link.href = url;
    link.download = `${safeName}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
    window.gpToast('CSV file downloaded');
    return true;
  };

  /* Wire up any [data-export-csv] button site-wide: reads the table in the
   * selector given by the attribute and downloads it under data-export-filename. */
  document.querySelectorAll('[data-export-csv]').forEach((btn) => {
    btn.addEventListener('click', () => {
      window.gpExportTableToCSV(btn.dataset.exportCsv, btn.dataset.exportFilename || 'export');
    });
  });
});
