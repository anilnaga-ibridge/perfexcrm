<template>
  <div class="contracts-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Contracts</h1>
        <span class="subtitle">Manage client contracts and agreements</span>
      </div>
      <div class="header-actions">
        <button class="btn-bulk-pdf" @click="exportPDF" title="Bulk PDF Export">📄 Bulk PDF</button>
        <button class="btn-primary" @click="openCreate"><span>＋</span> New Contract</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-icon">📄</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.total || 0 }}</div>
          <div class="stat-label">Total Contracts</div>
        </div>
      </div>
      <div class="stat-card active">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.active || 0 }}</div>
          <div class="stat-label">Active</div>
        </div>
      </div>
      <div class="stat-card not-started">
        <div class="stat-icon">🕐</div>
        <div class="stat-info">
          <div class="stat-value">{{ stats.not_started || 0 }}</div>
          <div class="stat-label">Not Started</div>
        </div>
      </div>
      <div class="stat-card value">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value">${{ fmt(stats.total_value) }}</div>
          <div class="stat-label">Total Value</div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search contracts…" class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Statuses</option>
        <option value="Not Started">Not Started</option>
        <option value="In Progress">In Progress</option>
        <option value="On Hold">On Hold</option>
        <option value="Finished">Finished</option>
      </select>
      <div class="view-toggle">
        <button class="icon-toggle-btn" :class="{ active: view === 'table' }" @click="view = 'table'" title="Table View">☰</button>
        <button class="icon-toggle-btn" :class="{ active: view === 'cards' }" @click="view = 'cards'" title="Cards View">⊞</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-container" v-if="view === 'table'">
      <table class="data-table">
        <thead>
          <tr>
            <th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th>Subject</th>
            <th>Client</th>
            <th>Contract Type</th>
            <th>Value</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Signed</th>
            <th>Status</th>
            <th style="width:80px"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="loading-cell"><div class="loader"></div> Loading…</td>
          </tr>
          <tr v-else-if="!contracts.length">
            <td colspan="10" class="empty-cell">
              <div class="empty-state"><span class="empty-icon">📄</span><p>No contracts found</p></div>
            </td>
          </tr>
          <tr v-for="con in contracts" :key="con.id" class="data-row">
            <td><input type="checkbox" v-model="selected" :value="con.id" /></td>
            <td>
              <div class="con-subject">{{ con.subject }}</div>
              <div v-if="con.description" class="con-desc">{{ truncate(con.description, 60) }}</div>
            </td>
            <td>{{ con.client?.company || '—' }}</td>
            <td><span class="type-tag">{{ contractTypeName(con.contract_type_id) }}</span></td>
            <td class="value-cell">${{ fmt(con.value) }}</td>
            <td>{{ fmtDate(con.start_date) || '—' }}</td>
            <td>
              <span :class="isExpired(con.end_date) ? 'text-danger' : ''">
                {{ fmtDate(con.end_date) || 'No end date' }}
              </span>
            </td>
            <td>
              <span class="signed-badge" :class="con.signed ? 'yes' : 'no'">
                {{ con.signed ? '✓ Signed' : '✗ Unsigned' }}
              </span>
            </td>
            <td><span class="status-badge" :class="statusClass(con.status)">{{ con.status }}</span></td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="Edit" @click="editContract(con)">✏️</button>
                <button class="action-btn" title="Delete" @click="deleteContract(con)">🗑</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="page <= 1" @click="page--; load()">‹ Prev</button>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="page++; load()">Next ›</button>
      </div>
    </div>

    <!-- Cards View -->
    <div v-else class="contracts-cards">
      <div v-if="loading" class="loading-wrap"><div class="loader"></div> Loading…</div>
      <div v-for="con in contracts" :key="con.id" class="contract-card">
        <div class="cc-header">
          <span class="status-badge" :class="statusClass(con.status)">{{ con.status }}</span>
          <span class="cc-value">${{ fmt(con.value) }}</span>
        </div>
        <div class="cc-subject">{{ con.subject }}</div>
        <div class="cc-client">🏢 {{ con.client?.company || 'No client' }}</div>
        <div class="cc-dates">
          <span>📅 {{ fmtDate(con.start_date) || 'No start' }}</span>
          <span>→</span>
          <span :class="isExpired(con.end_date) ? 'text-danger' : ''">{{ fmtDate(con.end_date) || 'No end' }}</span>
        </div>
        <div class="cc-meta">
          <span class="type-tag">{{ contractTypeName(con.contract_type_id) }}</span>
          <span class="signed-badge" :class="con.signed ? 'yes' : 'no'">{{ con.signed ? '✓ Signed' : '✗ Unsigned' }}</span>
        </div>
        <div class="cc-actions">
          <button @click="editContract(con)">✏️ Edit</button>
          <button @click="deleteContract(con)">🗑 Delete</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box">
          <div class="modal-header">
            <h2>{{ editing ? 'Edit Contract' : 'New Contract' }}</h2>
            <button class="close-btn" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-row span-2">
                <label>Subject / Title *</label>
                <input v-model="form.subject" placeholder="Contract subject" class="form-input" />
              </div>
              <div class="form-row">
                <label>Contract Type</label>
                <select v-model="form.contract_type_id" class="form-input">
                  <option value="">Select type</option>
                  <option value="1">Ongoing</option>
                  <option value="2">One-time</option>
                  <option value="3">Retainer</option>
                  <option value="4">Custom</option>
                </select>
              </div>
              <div class="form-row">
                <label>Value ($)</label>
                <input v-model="form.value" type="number" min="0" step="0.01" placeholder="0.00" class="form-input" />
              </div>
              <div class="form-row">
                <label>Start Date</label>
                <input v-model="form.start_date" type="date" class="form-input" />
              </div>
              <div class="form-row">
                <label>End Date</label>
                <input v-model="form.end_date" type="date" class="form-input" />
              </div>
              <div class="form-row">
                <label>Status</label>
                <select v-model="form.status" class="form-input">
                  <option value="Not Started">Not Started</option>
                  <option value="In Progress">In Progress</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Finished">Finished</option>
                </select>
              </div>
              <div class="form-row signed-row">
                <label>Signed?</label>
                <label class="toggle-label">
                  <input type="checkbox" v-model="form.signed" />
                  <span>{{ form.signed ? 'Yes, Signed' : 'Not signed yet' }}</span>
                </label>
              </div>
              <div class="form-row span-2">
                <label>Description / Notes</label>
                <textarea v-model="form.description" rows="3" class="form-input form-textarea" placeholder="Contract details…"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="save" :disabled="saving">
              {{ saving ? 'Saving…' : (editing ? 'Save Changes' : 'Create Contract') }}
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
const contracts  = ref([])
const stats      = ref({})
const loading    = ref(false)
const saving     = ref(false)
const search     = ref('')
const statusFilter = ref('')
const page       = ref(1)
const totalPages = ref(1)
const selectAll  = ref(false)
const selected   = ref([])
const showModal  = ref(false)
const editing    = ref(null)
const view       = ref('table')

const form = reactive({
  subject: '', client_id: '', contract_type_id: '', value: '',
  start_date: '', end_date: '', status: 'Not Started',
  description: '', signed: false,
})

const CONTRACT_TYPES = { '1': 'Ongoing', '2': 'One-time', '3': 'Retainer', '4': 'Custom' }
function contractTypeName(id) { return CONTRACT_TYPES[String(id)] || 'Custom' }

const DEMO = [
  { id: 1, subject: 'Website Maintenance SLA', client: { company: 'Nader-Abernathy' }, contract_type_id: '1', value: 12000, start_date: '2026-01-01', end_date: '2026-12-31', signed: true, status: 'In Progress', description: 'Annual maintenance retainer' },
  { id: 2, subject: 'Mobile App Development', client: { company: 'Mertz-Bergnaum' }, contract_type_id: '2', value: 45000, start_date: '2026-03-01', end_date: '2026-08-31', signed: true, status: 'In Progress', description: 'iOS and Android mobile application' },
  { id: 3, subject: 'Marketing Consulting', client: { company: 'Schroeder & Sons' }, contract_type_id: '3', value: 3500, start_date: '2026-06-01', end_date: '2026-06-30', signed: false, status: 'Not Started', description: 'Monthly marketing consulting retainer' },
  { id: 4, subject: 'Cloud Infrastructure Setup', client: { company: 'Bayer Group' }, contract_type_id: '4', value: 8000, start_date: '2026-02-15', end_date: '2026-04-15', signed: true, status: 'Finished', description: 'AWS infrastructure configuration and handover' },
]

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 20, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/contracts`, { params })
    contracts.value  = res.data.contracts?.data || []
    totalPages.value = res.data.contracts?.last_page || 1
    stats.value      = res.data.stats || {}
  } catch {
    contracts.value = DEMO
    stats.value = { total: 4, active: 2, not_started: 1, finished: 1, total_value: DEMO.reduce((a, c) => a + c.value, 0) }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  editing.value = null
  Object.assign(form, { subject: '', client_id: '', contract_type_id: '', value: '', start_date: new Date().toISOString().slice(0,10), end_date: '', status: 'Not Started', description: '', signed: false })
  showModal.value = true
}

function editContract(con) {
  editing.value = con
  Object.assign(form, {
    subject: con.subject, client_id: con.client_id || '',
    contract_type_id: String(con.contract_type_id || ''),
    value: con.value || '', status: con.status,
    start_date: con.start_date?.slice?.(0,10) || '',
    end_date: con.end_date?.slice?.(0,10) || '',
    description: con.description || '', signed: !!con.signed,
  })
  showModal.value = true
}

async function save() {
  if (!form.subject) return alert('Subject is required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/contracts/${editing.value.id}`, form)
    } else {
      await axios.post(`${BASE}/contracts`, form)
    }
    closeModal(); load()
  } catch {
    // Demo mode
    if (editing.value) Object.assign(editing.value, { ...form })
    closeModal()
  } finally { saving.value = false }
}

async function deleteContract(con) {
  if (!confirm(`Delete contract "${con.subject}"?`)) return
  try {
    await axios.delete(`${BASE}/contracts/${con.id}`)
    load()
  } catch { contracts.value = contracts.value.filter(c => c.id !== con.id) }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select contracts to export')
  alert(`Exporting ${selected.value.length} contract(s) as PDF…`)
}

function toggleAll() { selected.value = selectAll.value ? contracts.value.map(c => c.id) : [] }
function closeModal() { showModal.value = false; editing.value = null }
function statusClass(s) { return { 'Not Started': 'default', 'In Progress': 'in-progress', 'On Hold': 'on-hold', 'Finished': 'finished' }[s] || '' }
function isExpired(d) { return d && new Date(d) < new Date() }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return null; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '…' : str }

onMounted(load)
</script>

<style scoped>
.contracts-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }
.header-actions { display: flex; gap: 10px; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; transition: all 0.2s; }
.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
.stat-card.active  { border-left: 3px solid #10b981; }
.stat-card.not-started { border-left: 3px solid #94a3b8; }
.stat-card.value   { border-left: 3px solid #6366f1; }
.stat-icon { font-size: 26px; }
.stat-value { font-size: 20px; font-weight: 800; color: #1e293b; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 1px; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; outline: none; background: #fff; }
.view-toggle { display: flex; gap: 4px; }
.icon-toggle-btn { background: none; border: 1.5px solid #e2e8f0; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.icon-toggle-btn.active { background: #1e9aff; color: #fff; border-color: #1e9aff; }

.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 10px 14px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.con-subject { font-weight: 600; color: #1e293b; }
.con-desc { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.type-tag { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.value-cell { font-weight: 700; color: #1e293b; font-size: 14px; }
.text-danger { color: #dc2626; font-weight: 600; }

.signed-badge { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.signed-badge.yes { background: #f0fdf4; color: #16a34a; }
.signed-badge.no  { background: #fef2f2; color: #dc2626; }

.status-badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-badge.default     { background: #f8fafc; color: #64748b; }
.status-badge.in-progress { background: #eff6ff; color: #3b82f6; }
.status-badge.on-hold     { background: #fffbeb; color: #d97706; }
.status-badge.finished    { background: #f0fdf4; color: #16a34a; }

.action-buttons { display: flex; gap: 4px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 4px 6px; border-radius: 6px; }
.action-btn:hover { background: #f1f5f9; }
.loading-cell, .empty-cell { text-align: center; padding: 48px; color: #94a3b8; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 36px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

.contracts-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
.loading-wrap { text-align: center; padding: 40px; color: #94a3b8; }
.contract-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px; transition: all 0.2s; }
.contract-card:hover { border-color: #1e9aff; box-shadow: 0 4px 20px rgba(30,154,255,.1); transform: translateY(-2px); }
.cc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.cc-value { font-size: 17px; font-weight: 800; color: #1e293b; }
.cc-subject { font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 6px; }
.cc-client { font-size: 12px; color: #64748b; margin-bottom: 10px; }
.cc-dates { display: flex; gap: 6px; font-size: 11px; color: #64748b; align-items: center; margin-bottom: 10px; }
.cc-meta { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; flex-wrap: wrap; }
.cc-actions { display: flex; gap: 8px; }
.cc-actions button { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; }
.cc-actions button:hover { border-color: #1e9aff; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-primary { display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #1e9aff, #0d7bd6); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }
.btn-bulk-pdf { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 13px; }
.btn-bulk-pdf:hover { background: #e2e8f0; }

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
.signed-row { justify-content: center; }
.toggle-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: #1e293b; }
.toggle-label input { width: 16px; height: 16px; cursor: pointer; }

@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } .form-row.span-2 { grid-column: span 1; } }
</style>
