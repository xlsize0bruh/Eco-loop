function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  const icon = type === 'success' ? 'fa-check' : 'fa-circle-exclamation';

  toast.className = 'toast-enter pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border bg-neutral-900 text-neutral-100 border-neutral-700 shadow-2xl max-w-xs';
  toast.innerHTML = `
    <span class="w-6 h-6 rounded-full ${type === 'success' ? 'bg-white text-neutral-950' : 'bg-red-500 text-white'} flex items-center justify-center">
      <i class="fa-solid ${icon} text-[11px]"></i>
    </span>
    <span class="font-medium text-sm">${message}</span>
  `;

  if (container) {
    container.appendChild(toast);
    setTimeout(() => {
      toast.classList.replace('toast-enter', 'toast-exit');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
}

function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('hidden');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('hidden');
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[<>&"']/g, (s) => ({
    '<': '&lt;',
    '>': '&gt;',
    '&': '&amp;',
    '"': '&quot;',
    "'": '&#39;'
  }[s]));
}

function fmtDate(ts) {
  if (!ts) return '';
  return new Date(ts * 1000).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function dueInfo(ts) {
  if (!ts) return { text: 'Not started', overdue: false };

  const now = Math.floor(Date.now() / 1000);
  const diff = ts - now;
  const days = Math.ceil(diff / 86400);

  if (diff <= 0) {
    const overdueDays = Math.max(0, Math.floor((-diff) / 86400));
    return { text: `Overdue by ${overdueDays} day${overdueDays === 1 ? '' : 's'}`, overdue: true };
  }

  return { text: `Due in ${days} day${days === 1 ? '' : 's'}`, overdue: false };
}

function reviewBadge(pos = 0, neg = 0) {
  return `
    <span class='inline-flex items-center gap-1.5 text-xs text-neutral-300'>
      <span class='inline-flex items-center gap-1 text-green-400'><i class='fa-solid fa-thumbs-up text-[10px]'></i>${pos}</span>
      <span class='inline-flex items-center gap-1 text-red-400'><i class='fa-solid fa-thumbs-down text-[10px]'></i>${neg}</span>
    </span>
  `;
}

let allItems = [];
let myItems = [];
let allRequests = [];
let allChampions = [];
let currentTab = 'market';
let currentUserId = null;
let currentUser = null;
let globalPollInterval = null;

function setActiveTab(tabName) {
  currentTab = tabName;
  document.querySelectorAll('.tab-btn').forEach((btn) => {
    const on = btn.getAttribute('data-tab') === tabName;
    btn.classList.toggle('text-white', on);
    btn.classList.toggle('bg-neutral-800', on);
    btn.classList.toggle('text-neutral-400', !on);
  });

  document.querySelectorAll('.tab-content').forEach((el) => {
    const show = el.id === `${tabName}-tab`;
    el.classList.toggle('hidden', !show);
  });
}

document.querySelectorAll('.tab-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    const tab = btn.getAttribute('data-tab');
    setActiveTab(tab);
    if (tab === 'market') loadMarketplace(true);
    if (tab === 'my-items') loadMyItems(true);
    if (tab === 'requests') loadRequests(true);
    if (tab === 'champion') loadChampion(true);
    if (tab === 'profile') loadProfile(true);
  });
});

document.getElementById('logout-btn')?.addEventListener('click', async () => {
  await fetch('api/auth.php?action=logout', { method: 'POST' });
  window.location.href = 'login.php';
});

async function init() {
  setActiveTab('market');
  await loadProfile(true);
  await loadMarketplace(true);
  await loadMyItems(true);
  await loadRequests(true);
  await loadChampion(true);
  await loadDonations(true);
  startPolling();
}

async function loadProfile(silent = false) {
  try {
    const res = await fetch('api/users.php?action=profile');
    const data = await res.json();
    if (!data.success) return;
    currentUser = data.profile;
    currentUserId = data.profile.id;

    const posEl = document.getElementById('header-pos');
    const negEl = document.getElementById('header-neg');
    if (posEl) posEl.innerHTML = `${data.profile.positive_reviews ?? 0} <i class="fa-solid fa-thumbs-up"></i>`;
    if (negEl) negEl.innerHTML = `${data.profile.negative_reviews ?? 0} <i class="fa-solid fa-thumbs-down"></i>`;

    const tradeEl = document.getElementById('prof-trades');
    if (tradeEl) tradeEl.textContent = data.profile.completed_trades ?? 0;
  } catch (error) {
    if (!silent) showToast('Unable to load profile.', 'error');
  }
}

async function loadMarketplace(silent = false) {
  try {
    const res = await fetch('api/items.php?action=list');
    const data = await res.json();
    if (!data.success) return;
    allItems = data.items ?? [];
    renderMarketplace();
  } catch (error) {
    if (!silent) showToast('Unable to refresh marketplace.', 'error');
  }
}

function renderMarketplace() {
  const grid = document.getElementById('market-grid');
  if (!grid) return;

  const query = document.getElementById('search-tags')?.value.toLowerCase() ?? '';
  const items = allItems.filter((item) => {
    if (!query) return item.owner_id !== currentUserId && !item.in_trade;
    return item.owner_id !== currentUserId && !item.in_trade && (
      item.title.toLowerCase().includes(query) ||
      (item.tags || []).some((tag) => tag.toLowerCase().includes(query))
    );
  });

  if (!items.length) {
    grid.innerHTML = '<div class="col-span-full rounded-2xl border border-dashed border-neutral-700 p-8 text-center text-neutral-400">No items match your search.</div>';
    return;
  }

  grid.innerHTML = items.map((item) => `
    <article class="group rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden">
      <div class="aspect-[4/3] bg-gradient-to-br from-neutral-800 to-neutral-900 flex items-center justify-center text-neutral-500">
        ${item.image ? `<img src="${item.image}" alt="${escapeHtml(item.title)}" class="w-full h-full object-cover">` : '<i class="fa-solid fa-box-open text-3xl"></i>'}
      </div>
      <div class="p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
          <div>
            <h3 class="font-semibold text-white">${escapeHtml(item.title)}</h3>
            <p class="text-xs text-neutral-500">by ${escapeHtml(item.owner_username || 'Unknown')}</p>
          </div>
          ${reviewBadge(item.owner_pos ?? 0, item.owner_neg ?? 0)}
        </div>
        <p class="text-sm text-neutral-400 line-clamp-3">${escapeHtml(item.description || 'No description available.')}</p>
        <div class="flex flex-wrap gap-2">
          ${(item.tags || []).slice(0, 4).map((tag) => `<span class="rounded-full border border-neutral-700 bg-neutral-950 px-2 py-1 text-[10px] text-neutral-300">#${escapeHtml(tag)}</span>`).join('')}
        </div>
        <div class="flex gap-2 pt-2">
          <button class="flex-1 rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm font-medium text-neutral-200 hover:bg-neutral-800" onclick="openModal('trade-modal');">Offer trade</button>
          <button class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-neutral-950" onclick="showToast('Trade request drafted.', 'success')">Swap</button>
        </div>
      </div>
    </article>
  `).join('');
}

async function loadMyItems(silent = false) {
  try {
    const res = await fetch('api/items.php?action=list');
    const data = await res.json();
    if (!data.success) return;
    myItems = data.items ?? [];
    renderMyItems();
  } catch (error) {
    if (!silent) showToast('Unable to read your items.', 'error');
  }
}

function renderMyItems() {
  const container = document.getElementById('my-items-list');
  if (!container) return;

  if (!myItems.length) {
    container.innerHTML = '<div class="rounded-2xl border border-dashed border-neutral-700 p-6 text-neutral-400">You have no listed items yet.</div>';
    return;
  }

  container.innerHTML = myItems.map((item) => `
    <div class="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h4 class="font-semibold text-white">${escapeHtml(item.title)}</h4>
          <p class="text-xs text-neutral-500">${escapeHtml(item.owner_username || 'You')}</p>
        </div>
        <span class="rounded-full ${item.is_locked ? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30'} px-2 py-1 text-[10px] font-medium">${item.is_locked ? 'Locked' : 'Available'}</span>
      </div>
      <p class="mt-2 text-sm text-neutral-400">${escapeHtml(item.description || 'No description available')}</p>
      <div class="mt-3 flex flex-wrap gap-2">
        ${(item.tags || []).map((tag) => `<span class="rounded-full bg-neutral-950 px-2 py-1 text-[10px] text-neutral-300">${escapeHtml(tag)}</span>`).join('')}
      </div>
    </div>
  `).join('');
}

async function loadRequests(silent = false) {
  try {
    const res = await fetch('api/trades.php?action=list');
    const data = await res.json();
    if (!data.success) return;
    allRequests = data.trades ?? [];
    renderRequests();
  } catch (error) {
    if (!silent) showToast('Unable to load requests.', 'error');
  }
}

function renderRequests() {
  const container = document.getElementById('requests-list');
  if (!container) return;

  if (!allRequests.length) {
    container.innerHTML = '<div class="rounded-2xl border border-dashed border-neutral-700 p-6 text-neutral-400">No trades yet.</div>';
    return;
  }

  container.innerHTML = allRequests.map((t) => `
    <div class="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h4 class="font-semibold text-white">${escapeHtml(t.offered_item_title)} → ${escapeHtml(t.wanted_item_title)}</h4>
          <p class="text-xs text-neutral-500">${escapeHtml(t.proposer_username)} ↔ ${escapeHtml(t.receiver_username)}</p>
        </div>
        <span class="rounded-full border border-neutral-700 bg-neutral-950 px-2 py-1 text-[10px] text-neutral-300 uppercase">${escapeHtml(t.status)}</span>
      </div>
    </div>
  `).join('');
}

async function loadChampion(silent = false) {
  try {
    const res = await fetch('api/users.php?action=leaderboard');
    const data = await res.json();
    if (!data.success) return;
    allChampions = data.topUsers ?? [];
    renderChampion();
  } catch (error) {
    if (!silent) showToast('Unable to load leaderboard.', 'error');
  }
}

function renderChampion() {
  const container = document.getElementById('champion-list');
  if (!container) return;

  if (!allChampions.length) {
    container.innerHTML = '<div class="rounded-2xl border border-dashed border-neutral-700 p-6 text-neutral-400">No leaderboard data yet.</div>';
    return;
  }

  container.innerHTML = allChampions.map((u, idx) => `
    <div class="flex items-center justify-between rounded-2xl border border-neutral-800 bg-neutral-900/60 p-3">
      <div class="flex items-center gap-3">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-xs font-bold text-neutral-950">${idx + 1}</div>
        <div>
          <p class="font-medium text-white">${escapeHtml(u.username)}</p>
          <p class="text-[11px] text-neutral-500">${escapeHtml(u.pincode)}</p>
        </div>
      </div>
      ${reviewBadge(u.positive_reviews ?? 0, u.negative_reviews ?? 0)}
    </div>
  `).join('');
}

async function loadDonations(silent = false) {
  try {
    const res = await fetch('api/items.php?action=list');
    const data = await res.json();
    if (!data.success) return;
    const items = data.items ?? [];
    const donations = document.getElementById('donation-list');
    if (!donations) return;

    donations.innerHTML = items.slice(0, 3).map((item) => `
      <div class="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4">
        <p class="font-medium text-white">${escapeHtml(item.title)}</p>
        <p class="text-xs text-neutral-500">${escapeHtml(item.owner_username || 'Community')}</p>
      </div>
    `).join('');
  } catch (error) {
    if (!silent) showToast('Unable to load donations.', 'error');
  }
}

function startPolling() {
  if (globalPollInterval) clearInterval(globalPollInterval);
  globalPollInterval = setInterval(() => {
    loadProfile(true);
    loadMarketplace(true);
    loadMyItems(true);
    loadRequests(true);
    loadChampion(true);
    loadDonations(true);
  }, 10000);
}

document.addEventListener('DOMContentLoaded', init);
