/* global bootstrap, $ */

window.APP = window.APP || {};

// Toast helper (Bootstrap 5)
APP.toast = function (type, message) {
  const stack = document.getElementById('toastStack');
  if (!stack) return;

  const el = document.createElement('div');
  el.className = 'toast show';
  el.setAttribute('role', 'alert');
  el.setAttribute('aria-live', 'assertive');
  el.setAttribute('aria-atomic', 'true');

  el.innerHTML = `
    <div class="toast-header">
      <strong class="me-auto">${APP.escapeHtml('AI Assistant')}</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body text-bg-${APP.escapeHtml(type || 'info')} rounded-2">
      ${APP.escapeHtml(message || '')}
    </div>
  `;

  stack.appendChild(el);
  try {
    const t = bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 });
    t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  } catch (_) {
    setTimeout(() => el.remove(), 3500);
  }
};

APP.escapeHtml = function (s) {
  return String(s).replace(/[&<>"']/g, function (c) {
    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
  });
};

// Loading overlay untuk proses AI (UX seperti SaaS)
APP.showLoader = function (show) {
  const el = document.getElementById('appLoader');
  if (!el) return;
  el.classList.toggle('d-none', !show);
};

APP.copyToClipboard = function (text) {
  const t = String(text || '');
  if (!t) {
    APP.toast('warning', 'Tidak ada teks untuk dicopy.');
    return;
  }

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(t).then(
      () => APP.toast('success', 'Berhasil dicopy.'),
      () => APP.fallbackCopy(t)
    );
    return;
  }

  APP.fallbackCopy(t);
};

APP.fallbackCopy = function (text) {
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed';
  ta.style.left = '-9999px';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    APP.toast('success', 'Berhasil dicopy.');
  } catch (_) {
    APP.toast('danger', 'Gagal copy. Silakan copy manual.');
  } finally {
    ta.remove();
  }
};

APP.ajaxFail = function (xhr) {
  try {
    const status = xhr && typeof xhr.status === 'number' ? xhr.status : 0;
    const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
    const msg = json && json.error ? json.error : '';

    if (msg) {
      APP.toast('danger', msg);
      return;
    }

    if (status === 401) {
      APP.toast('warning', 'Session habis. Silakan login ulang.');
      return;
    }

    if (status === 419) {
      APP.toast('warning', 'CSRF token tidak valid. Refresh halaman lalu coba lagi.');
      return;
    }

    const text = xhr && typeof xhr.responseText === 'string' ? xhr.responseText : '';
    if (text) {
      APP.toast('danger', 'Server error (HTTP ' + status + '). Buka Network tab untuk lihat detail.');
      return;
    }
    APP.toast('danger', 'Request gagal. Cek koneksi/setting OpenRouter.');
  } catch (_) {
    APP.toast('danger', 'Request gagal. Cek koneksi/setting OpenRouter.');
  }
};

// Auto-save draft: simpan input form ke localStorage (untuk fitur tambahan TA)
APP.saveDraft = function ($form, key) {
  try {
    const data = $form.serializeArray();
    const map = {};
    data.forEach((x) => { map[x.name] = x.value; });
    localStorage.setItem(key, JSON.stringify(map));
  } catch (_) {}
};

APP.restoreDraft = function ($form, key) {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return;
    const map = JSON.parse(raw);
    Object.keys(map || {}).forEach((name) => {
      const $el = $form.find(`[name="${name}"]`);
      if ($el.length) $el.val(map[name]);
    });
  } catch (_) {}
};

APP.applyTheme = function (mode) {
  const theme = (mode === 'dark') ? 'dark' : 'light';
  document.documentElement.setAttribute('data-bs-theme', theme);
  localStorage.setItem('theme', theme);
};

$(function () {
  const saved = localStorage.getItem('theme') || 'light';
  APP.applyTheme(saved);

  $('#btnToggleTheme').on('click', function () {
    const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
    APP.applyTheme(current === 'light' ? 'dark' : 'light');
  });

  $('#btnToggleSidebar').on('click', function () {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.classList.toggle('open');
  });

  $(document).on('click', function (e) {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar || !sidebar.classList.contains('open')) return;
    const isClickInside = sidebar.contains(e.target) || (e.target && e.target.id === 'btnToggleSidebar');
    if (!isClickInside) sidebar.classList.remove('open');
  });

  // Default header CSRF untuk semua request AJAX
  if ($ && $.ajaxSetup && APP.csrfToken) {
    $.ajaxSetup({ headers: { 'X-CSRF-Token': APP.csrfToken } });
  }
});
