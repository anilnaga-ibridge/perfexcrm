<template>
  <div class="expenses-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Expenses</h1>
        <span class="subtitle">Track and manage business expenses</span>
      </div>
      <div class="header-actions">
        <button class="btn-bulk-pdf" @click="exportPDF" title="Bulk PDF Export">
          <span>📄</span> Bulk PDF
        </button>
        <button class="btn-primary" @click="openCreate">
          <span>＋</span> Record Expense
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon total-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value">${{ fmt(stats.total_amount) }}</div>
          <div class="stat-label">Total Expenses</div>
        </div>
      </div>
      <div class="stat-card billed">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value">${{ fmt(stats.billed) }}</div>
          <div class="stat-label">Billed</div>
        </div>
      </div>
      <div class="stat-card unbilled">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <div class="stat-value">${{ fmt(stats.unbilled) }}</div>
          <div class="stat-label">Unbilled</div>
        </div>
      </div>
      <div class="stat-card count">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total || 0 }}</div>
          <div class="stat-label">Total Records</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search expenses…" class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Status</option>
        <option value="billed">Billed</option>
        <option value="unbilled">Unbilled</option>
      </select>
      <select v-model="perPage" @change="load" class="filter-select">
        <option value="15">15 / page</option>
        <option value="25">25 / page</option>
        <option value="50">50 / page</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-container">
      <!-- Table Toggle Buttons -->
      <div class="table-toggle-bar">
        <div class="toggle-left">
          <input type="checkbox" v-model="selectAll" @change="toggleAll" id="chk-all" />
          <label for="chk-all" style="font-size:12px;color:#64748b;margin-left:6px">
            {{ selected.length > 0 ? `${selected.length} selected` : 'Select all' }}
          </label>
        </div>
        <div class="toggle-right">
          <button class="icon-toggle-btn" :class="{ active: view === 'table' }" @click="view = 'table'" title="Table view">☰</button>
          <button class="icon-toggle-btn" :class="{ active: view === 'grid' }" @click="view = 'grid'" title="Grid view">⊞</button>
        </div>
      </div>

      <!-- Table View -->
      <table v-if="view === 'table'" class="data-table">
        <thead>
          <tr>
            <th style="width:36px"></th>
            <th @click="sort('name')" class="sortable">Name <span class="sort-arrow">{{ sortArrow('name') }}</span></th>
            <th @click="sort('date')" class="sortable">Date <span class="sort-arrow">{{ sortArrow('date') }}</span></th>
            <th>Category</th>
            <th>Client</th>
            <th @click="sort('amount')" class="sortable">Amount <span class="sort-arrow">{{ sortArrow('amount') }}</span></th>
            <th>Payment Mode</th>
            <th>Reference</th>
            <th>Status</th>
            <th style="width:90px"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="loading-cell"><div class="loader"></div> Loading…</td>
          </tr>
          <tr v-else-if="!expenses.length">
            <td colspan="10" class="empty-cell">
              <div class="empty-state"><span class="empty-icon">💸</span><p>No expenses found</p></div>
            </td>
          </tr>
          <tr v-for="exp in expenses" :key="exp.id" class="data-row">
            <td><input type="checkbox" v-model="selected" :value="exp.id" /></td>
            <td>
              <div class="expense-name">{{ exp.name }}</div>
              <div v-if="exp.note" class="expense-note">{{ truncate(exp.note, 60) }}</div>
            </td>
            <td>{{ fmtDate(exp.date) }}</td>
            <td><span class="category-tag">{{ exp.category_id ? `Cat #${exp.category_id}` : 'General' }}</span></td>
            <td>{{ exp.client?.company || '—' }}</td>
            <td class="amount-cell">${{ fmt(exp.amount) }}</td>
            <td>{{ exp.payment_mode }}</td>
            <td><span class="ref-text">{{ exp.reference || '—' }}</span></td>
            <td>
              <span class="status-badge" :class="exp.status">{{ exp.status }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="Edit" @click="editExpense(exp)">✏️</button>
                <button class="action-btn" title="Delete" @click="deleteExpense(exp)">🗑</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Grid View -->
      <div v-else class="expense-grid">
        <div v-if="loading" class="loading-cell"><div class="loader"></div> Loading…</div>
        <div v-for="exp in expenses" :key="exp.id" class="expense-card">
          <div class="ec-header">
            <span class="status-badge" :class="exp.status">{{ exp.status }}</span>
            <span class="ec-amount">${{ fmt(exp.amount) }}</span>
          </div>
          <div class="ec-name">{{ exp.name }}</div>
          <div class="ec-meta">
            <span>📅 {{ fmtDate(exp.date) }}</span>
            <span>💳 {{ exp.payment_mode }}</span>
          </div>
          <div class="ec-client">{{ exp.client?.company || 'No client' }}</div>
          <div class="ec-actions">
            <button @click="editExpense(exp)">✏️ Edit</button>
            <button @click="deleteExpense(exp)">🗑 Delete</button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="page <= 1" @click="page--; load()">‹ Prev</button>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="page++; load()">Next ›</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box">
          <div class="modal-header">
            <h2>{{ editing ? 'Edit Expense' : 'Record Expense' }}</h2>
            <button class="close-btn" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-row span-2">
                <label>Expense Name / Description *</label>
                <input v-model="form.name" placeholder="e.g. AWS Monthly Invoice" class="form-input" />
              </div>
              <div class="form-row">
                <label>Amount ($) *</label>
                <input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0.00" class="form-input" />
              </div>
              <div class="form-row">
                <label>Date *</label>
                <input v-model="form.date" type="date" class="form-input" />
              </div>
              <div class="form-row">
                <label>Category</label>
                <select v-model="form.category_id" class="form-input">
                  <option value="">General</option>
                  <option value="1">Hosting Services</option>
                  <option value="2">Office Rent</option>
                  <option value="3">Travel &amp; Fuel</option>
                  <option value="4">Marketing</option>
                  <option value="5">Consulting Fees</option>
                  <option value="6">Software Licenses</option>
                  <option value="7">Utilities</option>
                </select>
              </div>
              <div class="form-row">
                <label>Payment Mode</label>
                <select v-model="form.payment_mode" class="form-input">
                  <option value="Credit Card">Credit Card</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Cash">Cash</option>
                  <option value="PayPal">PayPal</option>
                  <option value="Cheque">Cheque</option>
                </select>
              </div>
              <div class="form-row">
                <label>Status</label>
                <select v-model="form.status" class="form-input">
                  <option value="unbilled">Unbilled</option>
                  <option value="billed">Billed</option>
                </select>
              </div>
              <div class="form-row">
                <label>Reference #</label>
                <input v-model="form.reference" placeholder="e.g. INV-9901" class="form-input" />
              </div>
              <div class="form-row span-2">
                <label>Note</label>
                <textarea v-model="form.note" rows="3" placeholder="Additional notes…" class="form-input form-textarea"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="save" :disabled="saving">
              {{ saving ? 'Saving…' : (editing ? 'Save Changes' : 'Record Expense') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const BASE = '/api'
const expenses   = ref([])
const stats      = ref({})
const loading    = ref(false)
const saving     = ref(false)
const search     = ref('')
const statusFilter = ref('')
const perPage    = ref('25')
const page       = ref(1)
const totalPages = ref(1)
const selectAll  = ref(false)
const selected   = ref([])
const showModal  = ref(false)
const editing    = ref(null)
const view       = ref('table')
const sortKey    = ref('date')
const sortDir    = ref('desc')

const form = reactive({
  name: '', amount: '', date: '', category_id: '', client_id: '',
  payment_mode: 'Credit Card', status: 'unbilled', reference: '', note: '',
})

const DEMO_DATA = [
  { id: 1, name: 'AWS Production Server', note: 'Monthly cloud hosting', amount: 450, date: '2026-06-10', category_id: 1, client: { company: 'Bayer Group' }, reference: 'AWS-88291', payment_mode: 'Credit Card', status: 'billed' },
  { id: 2, name: 'HQ Office Space', note: 'Rent payment for main office', amount: 2500, date: '2026-06-01', category_id: 2, client: null, reference: 'RENT-0626', payment_mode: 'Bank Transfer', status: 'unbilled' },
  { id: 3, name: 'Client Visit Transport', note: 'Uber & train tickets', amount: 120, date: '2026-06-08', category_id: 3, client: { company: 'Halvorson LLC' }, reference: 'TX-9901', payment_mode: 'Cash', status: 'billed' },
  { id: 4, name: 'Google Ads Campaign', note: 'Search engine marketing', amount: 1200, date: '2026-05-25', category_id: 4, client: null, reference: 'G-ADS-7729', payment_mode: 'Credit Card', status: 'unbilled' },
  { id: 5, name: 'Adobe Creative Suite', note: 'Annual license subscription', amount: 350, date: '2026-05-18', category_id: 6, client: { company: 'Mertz Corp' }, reference: 'ADOBE-921', payment_mode: 'PayPal', status: 'billed' },
]

function sortArrow(k) { return sortKey.value === k ? (sortDir.value === 'asc' ? '↑' : '↓') : '' }
function sort(k) {
  if (sortKey.value === k) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = k; sortDir.value = 'asc' }
  load()
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/expenses`, { params })
    expenses.value  = res.data.expenses?.data || []
    totalPages.value = res.data.expenses?.last_page || 1
    stats.value     = res.data.stats || {}
  } catch {
    expenses.value = DEMO_DATA
    const tot = DEMO_DATA.reduce((a, r) => a + r.amount, 0)
    stats.value = {
      total_amount: tot,
      billed:   DEMO_DATA.filter(r => r.status === 'billed').reduce((a, r) => a + r.amount, 0),
      unbilled: DEMO_DATA.filter(r => r.status === 'unbilled').reduce((a, r) => a + r.amount, 0),
      total:    DEMO_DATA.length,
    }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  editing.value = null
  Object.assign(form, { name: '', amount: '', date: new Date().toISOString().slice(0,10), category_id: '', client_id: '', payment_mode: 'Credit Card', status: 'unbilled', reference: '', note: '' })
  showModal.value = true
}

function editExpense(exp) {
  editing.value = exp
  Object.assign(form, { name: exp.name, amount: exp.amount, date: exp.date?.slice?.(0,10) || exp.date, category_id: exp.category_id || '', client_id: exp.client_id || '', payment_mode: exp.payment_mode || 'Credit Card', status: exp.status, reference: exp.reference || '', note: exp.note || '' })
  showModal.value = true
}

async function save() {
  if (!form.name || !form.amount || !form.date) return alert('Name, amount and date are required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/expenses/${editing.value.id}`, form)
    } else {
      await axios.post(`${BASE}/expenses`, form)
    }
    closeModal(); load()
  } catch { alert('Failed to save expense') }
  finally { saving.value = false }
}

async function deleteExpense(exp) {
  if (!confirm(`Delete "${exp.name}"?`)) return
  try {
    await axios.delete(`${BASE}/expenses/${exp.id}`)
    load()
  } catch {
    // Demo mode: remove from local list
    expenses.value = expenses.value.filter(e => e.id !== exp.id)
  }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select expenses to export')
  alert(`Exporting ${selected.value.length} expense(s) as PDF…`)
}

function toggleAll() { selected.value = selectAll.value ? expenses.value.map(e => e.id) : [] }
function closeModal() { showModal.value = false; editing.value = null }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '…' : str }

onMounted(load)
</script>

<style scoped>
.expenses-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }
.header-actions { display: flex; gap: 10px; }

/* Stats */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; transition: all 0.2s; }
.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); transform: translateY(-1px); }
.stat-card.billed   { border-left: 3px solid #10b981; }
.stat-card.unbilled { border-left: 3px solid #f59e0b; }
.stat-card.count    { border-left: 3px solid #6366f1; }
.stat-icon { font-size: 26px; }
.stat-value { font-size: 22px; font-weight: 800; color: #1e293b; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 1px; }

/* Filters */
.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; outline: none; background: #fff; }

/* Table */
.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.table-toggle-bar { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; background: #fafbfc; }
.toggle-right { display: flex; gap: 4px; }
.icon-toggle-btn { background: none; border: 1.5px solid #e2e8f0; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.icon-toggle-btn:hover { border-color: #1e9aff; }
.icon-toggle-btn.active { background: #1e9aff; color: #fff; border-color: #1e9aff; }

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 10px 14px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; }
.sortable { cursor: pointer; user-select: none; }
.sortable:hover { color: #1e9aff; }
.sort-arrow { font-size: 10px; color: #1e9aff; }
.data-row { transition: background 0.15s; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

.expense-name { font-weight: 600; color: #1e293b; }
.expense-note { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.category-tag { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.amount-cell { font-weight: 700; color: #1e293b; font-size: 14px; }
.ref-text { font-family: monospace; font-size: 12px; color: #64748b; }

.status-badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-badge.billed   { background: #f0fdf4; color: #16a34a; }
.status-badge.unbilled { background: #fefce8; color: #ca8a04; }

.action-buttons { display: flex; gap: 4px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 4px 6px; border-radius: 6px; transition: background 0.15s; }
.action-btn:hover { background: #f1f5f9; }

.loading-cell, .empty-cell { text-align: center; padding: 48px; color: #94a3b8; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 36px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Grid view */
.expense-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; padding: 14px; }
.expense-card { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px; transition: all 0.2s; }
.expense-card:hover { border-color: #1e9aff; box-shadow: 0 4px 16px rgba(30,154,255,.1); }
.ec-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.ec-amount { font-size: 18px; font-weight: 800; color: #1e293b; }
.ec-name { font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 14px; }
.ec-meta { display: flex; gap: 10px; font-size: 11px; color: #64748b; margin-bottom: 6px; }
.ec-client { font-size: 12px; color: #94a3b8; margin-bottom: 12px; }
.ec-actions { display: flex; gap: 8px; }
.ec-actions button { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; }
.ec-actions button:hover { border-color: #1e9aff; color: #1e9aff; }

/* Pagination */
.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

/* Buttons */
.btn-primary { display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #1e9aff, #0d7bd6); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }
.btn-bulk-pdf { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 13px; }
.btn-bulk-pdf:hover { background: #e2e8f0; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; color: #1e293b; }
.close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
.modal-body { padding: 20px 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-row { display: flex; flex-direction: column; gap: 5px; }
.form-row.span-2 { grid-column: span 2; }
.form-row label { font-size: 12px; font-weight: 600; color: #374151; }
.form-input { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; width: 100%; box-sizing: border-box; }
.form-input:focus { border-color: #1e9aff; }
.form-textarea { resize: vertical; min-height: 80px; font-family: inherit; }

@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr 1fr; } .form-grid { grid-template-columns: 1fr; } .form-row.span-2 { grid-column: span 1; } }
</style>
