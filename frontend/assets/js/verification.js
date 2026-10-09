/**
 * Govi Paura — verification.js
 * Loaded through $pageScript on:
 *   /farmer/verification.php and /rider/verification.php → upload documents, track status   (2.1, 2.2)
 *   /admin/verify_users.php                              → review, approve or reject         (2.3)
 *   /admin/dashboard.php                                 → request counts
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc } = gp;

  const STATUS = {
    pending: ['badge-warning', 'Pending review'],
    approved: ['badge-success', 'Approved'],
    rejected: ['badge-danger', 'Rejected'],
  };
  const statusBadge = (status) => {
    const [cls, label] = STATUS[status] ?? ['badge-muted', status];
    return `<span class="badge ${cls}">${esc(label)}</span>`;
  };

  // Documents are private, so they are fetched with the login token and opened through an object URL.
  async function openDocument(id) {
    const win = window.open('', '_blank');
    try {
      const url = URL.createObjectURL(await apiFetchBlob(`/verification/documents/${id}/file`));
      if (win) {
        win.location.href = url;
      } else {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.click();
      }
    } catch (err) {
      win?.close();
      window.gpToast(err.message, 'error');
    }
  }

  /* ══════════════ FARMER / RIDER: upload and track ══════════════ */
  const app = document.getElementById('verificationApp');
  if (app) {
    const loading = document.getElementById('verificationLoading');
    const banner = document.getElementById('verificationBanner');
    const list = document.getElementById('documentList');
    const MAX_BYTES = 5 * 1024 * 1024;

    function renderBanner(o) {
      let colour = '#d4a017';
      let icon = '';
      let title = 'Waiting for your documents';
      let text = 'Upload the documents below so our team can verify your account.';

      if (o.accountStatus === 'active') {
        colour = '#2e7d32'; icon = 'bi-patch-check-fill'; title = 'Your account is verified';
        text = 'You can use every feature of your portal.';
      } else if (o.accountStatus === 'rejected') {
        colour = '#c0392b'; icon = 'bi-x-octagon-fill'; title = 'Your verification was rejected';
        text = `${o.rejectionReason ? `Reason: ${esc(o.rejectionReason)}. ` : ''}Upload corrected documents below and your request will go back to our team.`;
      } else if (o.missing.length === 0) {
        icon = 'bi-hourglass-split'; title = 'Your documents are being reviewed';
        text = 'Our team will review them shortly. You can replace a document until it is reviewed.';
      }

      banner.style.borderLeft = `4px solid ${colour}`;
      banner.innerHTML = `
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <i class="bi ${icon}" style="font-size:24px;color:${colour};"></i>
          <div><h3 style="margin:0 0 4px;font-size:17px;">${title}</h3><p class="muted" style="margin:0;">${text}</p></div>
        </div>`;
    }

    function renderRows(o) {
      const types = [
        ...o.required.map((t) => ({ ...t, required: true })),
        ...o.optional.map((t) => ({ ...t, required: false })),
      ];

      list.innerHTML = types.map((t) => {
        const doc = o.documents.find((d) => d.type === t.type);
        const missing = o.missing.includes(t.type);

        const state = doc
          ? `${statusBadge(doc.status)} <span class="muted" style="font-size:12.5px;">${esc(doc.originalName)}</span>
             <a href="#" data-view="${doc.id}" style="font-size:12.5px;margin-left:6px;">View</a>`
          : `<span class="badge badge-muted">${t.required ? 'Required' : 'Optional'}</span>`;

        const reason = doc?.status === 'rejected' && doc.rejectionReason
          ? `<div style="font-size:12.5px;color:#c0392b;margin-top:4px;">${esc(doc.rejectionReason)}</div>` : '';

        const upload = o.canUpload ? `
          <div style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;">
            <input type="file" accept=".jpg,.jpeg,.png,.pdf" data-file style="max-width:260px;">
            <button type="button" class="btn ${missing ? 'btn-accent' : 'btn-outline'} btn-sm" data-upload="${t.type}">${doc && doc.status !== 'rejected' ? 'Replace' : 'Upload'}</button>
          </div>` : '';

        return `
          <div class="doc-row" data-type="${t.type}" style="padding:14px 0;border-bottom:1px solid var(--line);">
            <strong>${esc(t.label)}${t.required ? '' : ' <span class="muted" style="font-weight:400;">(optional)</span>'}</strong>
            <div style="margin-top:6px;">${state}</div>
            ${reason}
            ${upload}
          </div>`;
      }).join('');
    }

    async function load() {
      try {
        await gp.session;
        const overview = (await apiFetch('/verification')).data;
        renderBanner(overview);
        renderRows(overview);
        loading.style.display = 'none';
        app.style.display = '';
      } catch (err) {
        loading.innerHTML = '<i class="bi bi-exclamation-circle"></i><h4>Could not load your verification status</h4>';
        window.gpToast(err.message, 'error');
      }
    }

    list.addEventListener('click', async (e) => {
      const view = e.target.closest('[data-view]');
      if (view) { e.preventDefault(); openDocument(view.dataset.view); return; }

      const button = e.target.closest('[data-upload]');
      if (!button) return;

      const file = button.closest('.doc-row').querySelector('[data-file]').files[0];
      if (!file) { window.gpToast('Choose a file first.', 'error'); return; }
      if (file.size > MAX_BYTES) { window.gpToast('The file is larger than 5 MB.', 'error'); return; }
      if (!/\.(jpe?g|png|pdf)$/i.test(file.name)) { window.gpToast('Use a JPG, PNG or PDF file.', 'error'); return; }

      const formData = new FormData();
      formData.append('type', button.dataset.upload);
      formData.append('file', file);

      button.disabled = true;
      button.textContent = 'Uploading…';
      try {
        await apiFetch('/verification/documents', { method: 'POST', body: formData });
        window.gpToast('Document uploaded.');
        await load();
      } catch (err) {
        window.gpToast(err.message, 'error');
        button.disabled = false;
        button.textContent = 'Upload';
      }
    });

    load();
  }

  /* ══════════════ ADMIN: review queue ══════════════ */
  const reviewBody = document.getElementById('reviewBody');
  if (reviewBody) {
    const pills = document.getElementById('reviewPills');
    const pagination = document.getElementById('reviewPagination');
    let filter = 'pending';
    let page = 1;
    let rows = [];

    const LABELS = { pending: 'Pending', rejected: 'Rejected', approved: 'Approved' };
    const TYPE_LABELS = { nic: 'NIC', vehicle_registration: 'Vehicle registration', driving_license: 'Driving licence' };
    const date = (iso) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');

    function details(r) {
      if (r.role === 'farmer') {
        return `<div>Farmer · NIC ${esc(r.nic ?? '—')}</div>
                <div class="muted" style="font-size:12.5px;">${esc(r.address ?? '')}${r.region ? ` (${esc(r.region)})` : ''}</div>`;
      }
      const vehicles = r.vehicles.map((v) => `${esc(v.type.replace('_', ' '))} ${esc(v.number)}`).join(', ');
      return `<div>Rider · ${esc(r.riderCategory ?? '')}${r.region ? ` · ${esc(r.region)}` : ''}</div>
              <div class="muted" style="font-size:12.5px;">${vehicles || 'No vehicles'}</div>`;
    }

    function render() {
      if (rows.length === 0) {
        reviewBody.innerHTML = `<tr><td colspan="5" class="muted">No ${LABELS[filter].toLowerCase()} requests.</td></tr>`;
        return;
      }

      reviewBody.innerHTML = rows.map((r) => {
        const docs = r.documents.map((d) => `
          <div style="margin-bottom:4px;"><a href="#" data-view="${d.id}">${esc(d.typeLabel)}</a> ${statusBadge(d.status)}
          ${d.status === 'rejected' && d.rejectionReason ? `<div style="font-size:12px;color:#c0392b;">${esc(d.rejectionReason)}</div>` : ''}</div>`).join('');
        const missing = r.missing.length
          ? `<div style="font-size:12.5px;color:#c0392b;">Missing: ${r.missing.map((m) => esc(TYPE_LABELS[m] ?? m)).join(', ')}</div>` : '';

        const actions = filter === 'pending' ? `
          <button class="btn btn-accent btn-sm" data-approve="${r.userId}" ${r.missing.length ? 'disabled title="Required documents are missing"' : ''}>Approve</button>
          <button class="btn btn-danger btn-sm" data-reject="${r.userId}">Reject</button>` : '';

        return `
          <tr>
            <td><strong>${esc(r.fullName)}</strong>
                <div class="muted" style="font-size:12.5px;">${esc(r.phone)}${r.email ? ` · ${esc(r.email)}` : ''}</div></td>
            <td>${details(r)}</td>
            <td>${docs || '<span class="muted">No documents</span>'}${missing}</td>
            <td>${date(r.submittedAt)}</td>
            <td style="white-space:nowrap;">${actions}</td>
          </tr>`;
      }).join('');
    }

    function renderPagination(total, current, perPage) {
      pagination.innerHTML = '';
      const pages = Math.ceil(total / perPage);
      if (pages <= 1) return;
      for (let i = 1; i <= pages; i++) {
        const btn = document.createElement('button');
        btn.className = `btn btn-sm ${i === current ? 'btn-accent' : 'btn-outline'}`;
        btn.textContent = i;
        btn.addEventListener('click', () => { page = i; load(); });
        pagination.appendChild(btn);
      }
    }

    async function load() {
      try {
        await gp.session;
        const result = await apiFetch(`/admin/verifications?status=${filter}&page=${page}&per_page=10`);
        rows = result.data ?? [];
        Object.entries(result.counts ?? {}).forEach(([name, count]) => {
          const pill = pills.querySelector(`[data-value="${name}"]`);
          if (pill) pill.textContent = `${LABELS[name]} (${count})`;
        });
        render();
        renderPagination(result.total, result.page, result.perPage);
      } catch (err) {
        reviewBody.innerHTML = '<tr><td colspan="5" class="muted">Could not load requests.</td></tr>';
        window.gpToast(err.message, 'error');
      }
    }

    pills.addEventListener('gp:filterchange', (e) => { filter = e.detail; page = 1; load(); });

    /* ---------- approve / reject ---------- */
    const rejectModalEl = document.getElementById('rejectModal');
    const rejectReason = document.getElementById('rejectReason');
    const rejectError = document.getElementById('rejectError');
    const rejectBtn = document.getElementById('rejectConfirmBtn');
    let rejecting = null;

    reviewBody.addEventListener('click', async (e) => {
      const view = e.target.closest('[data-view]');
      if (view) { e.preventDefault(); openDocument(view.dataset.view); return; }

      const approve = e.target.closest('[data-approve]');
      if (approve) {
        const applicant = rows.find((r) => r.userId === parseInt(approve.dataset.approve, 10));
        if (!confirm(`Approve ${applicant.fullName}? Their account becomes active.`)) return;
        approve.disabled = true;
        try {
          await apiFetch(`/admin/verifications/${applicant.userId}/approve`, { method: 'POST' });
          window.gpToast(`${applicant.fullName} is now verified.`);
        } catch (err) {
          window.gpToast(err.message, 'error');
        }
        load();
        return;
      }

      const reject = e.target.closest('[data-reject]');
      if (reject) {
        rejecting = rows.find((r) => r.userId === parseInt(reject.dataset.reject, 10));
        document.getElementById('rejectTitle').textContent = `Reject ${rejecting.fullName}`;
        rejectReason.value = '';
        rejectError.style.display = 'none';
        bootstrap.Modal.getOrCreateInstance(rejectModalEl).show();
      }
    });

    rejectBtn.addEventListener('click', async () => {
      if (!rejecting) return;
      const reason = rejectReason.value.trim();
      if (reason.length < 5) {
        rejectError.textContent = 'Give the applicant a reason (at least 5 characters).';
        rejectError.style.display = '';
        return;
      }

      rejectBtn.disabled = true;
      try {
        await apiFetch(`/admin/verifications/${rejecting.userId}/reject`, { method: 'POST', body: JSON.stringify({ reason }) });
        bootstrap.Modal.getInstance(rejectModalEl)?.hide();
        window.gpToast('Request rejected.');
        load();
      } catch (err) {
        rejectError.textContent = err.message;
        rejectError.style.display = '';
      } finally {
        rejectBtn.disabled = false;
      }
    });

    load();
  }

  /* ══════════════ ADMIN dashboard counts ══════════════ */
  const pendingCount = document.getElementById('adminPendingCount');
  if (pendingCount) {
    (async () => {
      try {
        await gp.session;
        const { counts } = await apiFetch('/admin/verifications?status=pending&per_page=1');
        pendingCount.textContent = counts.pending;
        document.getElementById('adminApprovedCount').textContent = counts.approved;
        document.getElementById('adminRejectedCount').textContent = counts.rejected;
      } catch (err) {
        window.gpToast('Could not load counts: ' + err.message, 'error');
      }
    })();
  }
});
