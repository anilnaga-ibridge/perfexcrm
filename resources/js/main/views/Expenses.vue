<template>
  <div class="expenses-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage business expenses</p>
      </div>
      <button class="btn-primary" @click="openCreate">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Record Expense
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="exp-stats-row">
      <div class="exp-stat-card" v-for="card in summaryCards" :key="card.label">
        <div class="exp-stat-icon" :style="{ background: card.bg, color: card.color }" v-html="card.icon"></div>
        <div class="exp-stat-info">
          <div class="exp-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="exp-stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="exp-filters-bar">
      <div class="exp-filters-left">
        <select class="exp-filter-select" v-model="perPage" @change="load">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <button class="exp-toolbar-btn" @click="exportPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export
        </button>
      </div>
      <div class="exp-filters-right">
        <select class="exp-filter-select" v-model="statusFilter" @change="load">
          <option value="">All Status</option>
          <option value="billed">Billed</option>
          <option value="unbilled">Unbilled</option>
        </select>
        <div class="exp-search-wrap">
          <svg class="exp-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" placeholder="Search expenses..." class="exp-search-input" @input="onSearch" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="exp-table-wrap">
      <table class="exp-table">
        <thead>
          <tr>
            <th style="width:44px;">#</th>
            <th>Category</th>
            <th style="width:100px;text-align:right;">Amount</th>
            <th>Name</th>
            <th>Receipt</th>
            <th style="width:110px;">Date</th>
            <th>Project</th>
            <th>Customer</th>
            <th>Invoice</th>
            <th>Reference #</th>
            <th>Payment Mode</th>
            <th style="width:70px;" class="no-print">Options</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="12" class="exp-empty-cell">
              <svg class="animate-spin h-5 w-5 text-slate-300 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </td>
          </tr>
          <tr v-for="(exp, idx) in expenses" :key="exp.id" class="exp-row">
            <td class="exp-cell-muted">{{ idx + 1 + (page - 1) * perPage }}</td>
            <td><span class="exp-category-tag">{{ categoryName(exp.category_id) }}</span></td>
            <td class="exp-amount-cell">${{ fmt(exp.amount) }}</td>
            <td>
              <div class="exp-name-cell">
                <span class="exp-name">{{ exp.name }}</span>
                <span v-if="exp.note" class="exp-note">{{ truncate(exp.note, 50) }}</span>
              </div>
            </td>
            <td class="exp-cell-muted">
              <span v-if="exp.receipt" class="exp-receipt-link" title="View receipt">📎</span>
              <span v-else class="text-slate-300">—</span>
            </td>
            <td class="exp-cell-muted">{{ fmtDate(exp.date) }}</td>
            <td class="exp-cell-muted">{{ exp.project?.name || '—' }}</td>
            <td class="exp-cell-muted">{{ exp.client?.company || '—' }}</td>
            <td class="exp-cell-muted">{{ exp.invoice?.number || '—' }}</td>
            <td class="exp-cell-muted"><span class="exp-ref">{{ exp.reference || '—' }}</span></td>
            <td>
              <span class="exp-pm-badge">{{ exp.payment_mode || '—' }}</span>
            </td>
            <td class="no-print">
              <div class="exp-action-group">
                <button @click="editExpense(exp)" class="exp-action-icon" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                </button>
                <button @click="deleteExpense(exp)" class="exp-action-icon hover:text-rose-600" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !expenses.length">
            <td colspan="12" class="exp-empty-cell">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="36" height="36" class="mb-2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
              <p class="text-slate-400 text-sm">No expenses found</p>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="exp-pagination" v-if="totalPages > 1">
        <span class="exp-pagination-info">Page {{ page }} of {{ totalPages }}</span>
        <div class="exp-pagination-btns">
          <button class="exp-pg-btn" :disabled="page <= 1" @click="page--; load()">Previous</button>
          <button class="exp-pg-btn" :disabled="page >= totalPages" @click="page++; load()">Next</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="exp-modal-overlay" @click.self="closeModal">
        <div class="exp-modal-box">
          <div class="exp-modal-header">
            <h3>{{ editing ? 'Edit Expense' : 'Record Expense' }}</h3>
            <button class="exp-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="exp-modal-body">
            <div class="exp-form-grid">
              <div class="exp-form-row span-2">
                <label class="exp-form-label">Expense Name / Description <span class="text-red-500">*</span></label>
                <input v-model="form.name" placeholder="e.g. AWS Monthly Invoice" class="exp-form-input" />
              </div>
              <div class="exp-form-row">
                <label class="exp-form-label">Amount ($) <span class="text-red-500">*</span></label>
                <input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0.00" class="exp-form-input" />
              </div>
              <div class="exp-form-row">
                <label class="exp-form-label">Date <span class="text-red-500">*</span></label>
                <input v-model="form.date" type="date" class="exp-form-input" />
              </div>
              <div class="exp-form-row">
                <label class="exp-form-label">Category</label>
                <select v-model="form.category_id" class="exp-form-input">
                  <option value="">General</option>
                  <option value="1">Hosting Services</option>
                  <option value="2">Office Rent</option>
                  <option value="3">Travel & Fuel</option>
                  <option value="4">Marketing</option>
                  <option value="5">Consulting Fees</option>
                  <option value="6">Software Licenses</option>
                  <option value="7">Utilities</option>
                </select>
              </div>
              <div class="exp-form-row">
                <label class="exp-form-label">Payment Mode</label>
                <select v-model="form.payment_mode" class="exp-form-input">
                  <option value="Credit Card">Credit Card</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Cash">Cash</option>
                  <option value="PayPal">PayPal</option>
                  <option value="Cheque">Cheque</option>
                </select>
              </div>
              <div class="exp-form-row">
                <label class="exp-form-label">Reference #</label>
                <input v-model="form.reference" placeholder="e.g. INV-9901" class="exp-form-input" />
              </div>
              <div class="exp-form-row span-2">
                <label class="exp-form-label">Note</label>
                <textarea v-model="form.note" rows="3" placeholder="Additional notes..." class="exp-form-input exp-form-textarea"></textarea>
              </div>
            </div>
          </div>
          <div class="exp-modal-footer">
            <button class="exp-btn-cancel" @click="closeModal">Cancel</button>
            <button class="exp-btn-save" @click="save" :disabled="saving">
              <svg v-if="saving" class="animate-spin h-3 w-3 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Record Expense') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'

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
const showModal  = ref(false)
const editing    = ref(null)

const form = reactive({
  name: '', amount: '', date: '', category_id: '', client_id: '',
  payment_mode: 'Credit Card', status: 'unbilled', reference: '', note: '',
})

const summaryCards = computed(() => [
  {
    label: 'Total',
    value: '$' + fmt(stats.value.total_amount || 0),
    color: '#1e293b',
    bg: '#f1f5f9',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><path d="M12 6v12"/><path d="M8 10c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v.5"/><path d="M16 14c0 1.1-.9 2-2 2h-4a2 2 0 0 1-2-2v-.5"/></svg>',
  },
  {
    label: 'Billable',
    value: '$' + fmt(stats.value.billable || 0),
    color: '#16a34a',
    bg: '#f0fdf4',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>',
  },
  {
    label: 'Non Billable',
    value: '$' + fmt(stats.value.non_billable || 0),
    color: '#64748b',
    bg: '#f8fafc',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  },
  {
    label: 'Not Invoiced',
    value: '$' + fmt(stats.value.not_invoiced || 0),
    color: '#dc2626',
    bg: '#fef2f2',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  },
  {
    label: 'Billed',
    value: '$' + fmt(stats.value.billed || 0),
    color: '#16a34a',
    bg: '#f0fdf4',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  },
])

const expenseCategories = {
  1: 'Hosting Services',
  2: 'Office Rent',
  3: 'Travel & Fuel',
  4: 'Marketing',
  5: 'Consulting Fees',
  6: 'Software Licenses',
  7: 'Utilities',
}

function categoryName(id) {
  return id ? (expenseCategories[id] || `Category #${id}`) : 'General'
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
    expenses.value = []
    stats.value = { total_amount: 0, billable: 0, non_billable: 0, not_invoiced: 0, billed: 0, total: 0 }
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
  Object.assign(form, {
    name: exp.name, amount: exp.amount, date: exp.date?.slice?.(0,10) || exp.date,
    category_id: exp.category_id || '', client_id: exp.client_id || '',
    payment_mode: exp.payment_mode || 'Credit Card', status: exp.status,
    reference: exp.reference || '', note: exp.note || '',
  })
  showModal.value = true
}

async function save() {
  if (!form.name || !form.amount || !form.date) return alert('Name, amount and date are required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/expenses/${editing.value.id}`, form)
      message.success('Expense updated')
    } else {
      await axios.post(`${BASE}/expenses`, form)
      message.success('Expense created')
    }
    closeModal(); load()
  } catch {
    alert('Failed to save expense')
  } finally { saving.value = false }
}

async function deleteExpense(exp) {
  if (!confirm(`Delete "${exp.name}"?`)) return
  try {
    await axios.delete(`${BASE}/expenses/${exp.id}`)
    message.success('Expense deleted')
    load()
  } catch {
    expenses.value = expenses.value.filter(e => e.id !== exp.id)
  }
}

function exportPDF() {
  if (!expenses.value.length) return alert('No expenses to export')
  const headers = ['#', 'Category', 'Amount', 'Name', 'Date', 'Project', 'Customer', 'Invoice', 'Reference #', 'Payment Mode']
  const rows = expenses.value.map((e, i) => [
    i + 1, categoryName(e.category_id), e.amount, e.name,
    e.date, e.project?.name || '', e.client?.company || '',
    e.invoice?.number || '', e.reference || '', e.payment_mode || '',
  ])
  const csv = 'data:text/csv;charset=utf-8,' +
    [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n')
  const link = document.createElement('a')
  link.setAttribute('href', encodeURI(csv))
  link.setAttribute('download', 'expenses_export.csv')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function closeModal() { showModal.value = false; editing.value = null }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '...' : str }

onMounted(load)
</script>

<style scoped>
.expenses-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  padding: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
.page-subtitle {
  font-size: 13px;
  color: #94a3b8;
  margin: 2px 0 0;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
  font-family: inherit;
}
.btn-primary:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

/* Summary Cards */
.exp-stats-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}
@media (max-width: 900px) {
  .exp-stats-row { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 600px) {
  .exp-stats-row { grid-template-columns: repeat(2, 1fr); }
}
.exp-stat-card {
  background: #fff;
  border: 1px solid #f1f5f9;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,.02);
}
.exp-stat-card:hover {
  border-color: #e2e8f0;
  box-shadow: 0 4px 12px rgba(0,0,0,.04);
  transform: translateY(-1px);
}
.exp-stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.exp-stat-info { min-width: 0; }
.exp-stat-val {
  font-size: 17px;
  font-weight: 700;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
}
.exp-stat-label {
  font-size: 10.5px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 2px;
}

/* Filters */
.exp-filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}
.exp-filters-left, .exp-filters-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.exp-filter-select {
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 7px 10px;
  font-size: 12.5px;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
  outline: none;
  font-family: inherit;
}
.exp-filter-select:focus { border-color: #6366f1; }
.exp-toolbar-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 7px 12px;
  font-size: 12px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.exp-toolbar-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
.exp-search-wrap { position: relative; }
.exp-search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 13px;
  height: 13px;
  color: #94a3b8;
  pointer-events: none;
}
.exp-search-input {
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 7px 12px 7px 30px;
  font-size: 12.5px;
  color: #1e293b;
  background: #fff;
  width: 220px;
  outline: none;
  transition: border-color 0.12s;
  font-family: inherit;
}
.exp-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }

/* Table */
.exp-table-wrap {
  background: #fff;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  overflow: hidden;
}
.exp-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.exp-table thead th {
  background: #f8fafc;
  padding: 10px 14px;
  text-align: left;
  font-size: 10.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  border-bottom: 1.5px solid #e2e8f0;
}
.exp-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.exp-row:last-child td { border-bottom: none; }
.exp-row:hover { background: #fafbff; }
.exp-cell-muted { color: #64748b; }

.exp-category-tag {
  background: #f1f5f9;
  color: #475569;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.exp-amount-cell {
  font-weight: 700;
  color: #1e293b;
  font-size: 13px;
  text-align: right;
  font-variant-numeric: tabular-nums;
}
.exp-name-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.exp-name { font-weight: 600; color: #1e293b; }
.exp-note { font-size: 11px; color: #94a3b8; }
.exp-receipt-link { cursor: pointer; font-size: 16px; }
.exp-ref { font-family: monospace; font-size: 11.5px; color: #64748b; }
.exp-pm-badge {
  background: #f8fafc;
  color: #475569;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
}
.exp-action-group {
  display: flex;
  align-items: center;
  gap: 4px;
}
.exp-action-icon {
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  width: 30px;
  height: 30px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.12s;
}
.exp-action-icon:hover { background: #f8fafc; border-color: #cbd5e1; color: #6366f1; }

.exp-empty-cell {
  text-align: center;
  padding: 48px 20px;
  color: #94a3b8;
}

/* Pagination */
.exp-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-top: 1px solid #f1f5f9;
  font-size: 12px;
  color: #64748b;
}
.exp-pagination-info { color: #94a3b8; }
.exp-pagination-btns { display: flex; gap: 6px; }
.exp-pg-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 5px 12px;
  font-size: 12px;
  color: #475569;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.exp-pg-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.exp-pg-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Modal */
.exp-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.exp-modal-box {
  background: #fff; border-radius: 16px; width: 100%; max-width: 620px;
  max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25);
}
.exp-modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9;
}
.exp-modal-header h3 { font-size: 17px; font-weight: 700; margin: 0; color: #0f172a; }
.exp-modal-close {
  background: none; border: none; cursor: pointer; color: #94a3b8;
  width: 32px; height: 32px; border-radius: 8px; display: flex;
  align-items: center; justify-content: center; transition: all 0.12s;
}
.exp-modal-close:hover { background: #f1f5f9; color: #475569; }
.exp-modal-body { padding: 20px 24px; }
.exp-modal-footer {
  display: flex; justify-content: flex-end; gap: 10px;
  padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9;
}
.exp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.exp-form-row { display: flex; flex-direction: column; gap: 5px; }
.exp-form-row.span-2 { grid-column: span 2; }
.exp-form-label { font-size: 12px; font-weight: 600; color: #334155; }
.exp-form-input {
  padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: 13px; outline: none; width: 100%; box-sizing: border-box;
  font-family: inherit; transition: border-color 0.12s;
}
.exp-form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.exp-form-textarea { resize: vertical; min-height: 80px; }

.exp-btn-cancel {
  padding: 9px 20px; border: 1.5px solid #e2e8f0; border-radius: 10px;
  background: #fff; color: #64748b; font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.12s; font-family: inherit;
}
.exp-btn-cancel:hover { border-color: #cbd5e1; color: #334155; }
.exp-btn-save {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 22px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 10px; font-size: 13px;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99,102,241,.3); font-family: inherit;
}
.exp-btn-save:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(99,102,241,.4);
}
.exp-btn-save:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

@media (max-width: 1024px) {
  .expenses-page { padding: 16px; }
  .exp-table-wrap { overflow-x: auto; }
}
@media (max-width: 640px) {
  .exp-filters-bar { flex-direction: column; align-items: stretch; }
  .exp-filters-right { flex-direction: column; }
  .exp-search-input { width: 100%; }
  .exp-form-grid { grid-template-columns: 1fr; }
  .exp-form-row.span-2 { grid-column: span 1; }
}
</style>
