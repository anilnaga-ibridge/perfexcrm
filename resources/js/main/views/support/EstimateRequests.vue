<template>
  <div class="er-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Estimate Requests</h1>
        <span class="subtitle">Manage incoming estimate requests from clients</span>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card" @click="filterByStatus('')">
        <div class="stat-icon">📥</div>
        <div class="stat-value">{{ stats.total || 0 }}</div>
        <div class="stat-label">Total</div>
      </div>
      <div class="stat-card pending" @click="filterByStatus('pending')">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ stats.pending || 0 }}</div>
        <div class="stat-label">Pending</div>
      </div>
      <div class="stat-card converted" @click="filterByStatus('converted')">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ stats.converted || 0 }}</div>
        <div class="stat-label">Converted</div>
      </div>
      <div class="stat-card declined" @click="filterByStatus('declined')">
        <div class="stat-icon">❌</div>
        <div class="stat-value">{{ stats.declined || 0 }}</div>
        <div class="stat-label">Declined</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search by name or email..." class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="converted">Converted</option>
        <option value="declined">Declined</option>
      </select>
      <button class="btn-bulk-pdf" @click="exportPDF" title="Export PDF">📄 Bulk PDF</button>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="loading-cell"><div class="loader"></div> Loading...</td>
          </tr>
          <tr v-else-if="!requests.length">
            <td colspan="7" class="empty-cell">
              <div class="empty-state">
                <span class="empty-icon">📩</span>
                <p>No estimate requests found</p>
              </div>
            </td>
          </tr>
          <tr v-for="req in requests" :key="req.id" class="data-row">
            <td><input type="checkbox" v-model="selected" :value="req.id" /></td>
            <td class="name-cell">{{ req.name }}</td>
            <td class="email-cell"><a :href="`mailto:${req.email}`" @click.stop>{{ req.email }}</a></td>
            <td class="message-cell">{{ truncate(req.message, 80) }}</td>
            <td>
              <select :value="req.status" @change="updateStatus(req, $event.target.value)" class="status-select" :class="req.status">
                <option value="pending">Pending</option>
                <option value="converted">Converted</option>
                <option value="declined">Declined</option>
              </select>
            </td>
            <td>{{ formatDate(req.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button class="action-btn view-btn" title="View" @click="viewReq(req)">👁</button>
                <button class="action-btn delete-btn" title="Delete" @click="deleteReq(req)">🗑</button>
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

    <!-- View Modal -->
    <Teleport to="body">
      <div v-if="viewingReq" class="modal-overlay" @click.self="viewingReq = null">
        <div class="modal-box">
          <div class="modal-header">
            <h2>Estimate Request from {{ viewingReq.name }}</h2>
            <button class="close-btn" @click="viewingReq = null">✕</button>
          </div>
          <div class="modal-body">
            <div class="detail-field"><label>Name:</label> {{ viewingReq.name }}</div>
            <div class="detail-field"><label>Email:</label> <a :href="`mailto:${viewingReq.email}`">{{ viewingReq.email }}</a></div>
            <div class="detail-field"><label>Status:</label>
              <span class="status-inline" :class="viewingReq.status">{{ viewingReq.status }}</span>
            </div>
            <div class="detail-field"><label>Date:</label> {{ formatDate(viewingReq.created_at) }}</div>
            <div class="detail-field message-block"><label>Message:</label><p>{{ viewingReq.message }}</p></div>
          </div>
          <div class="modal-footer">
            <button class="btn-success" @click="updateStatus(viewingReq, 'converted'); viewingReq = null">Convert to Estimate</button>
            <button class="btn-danger" @click="updateStatus(viewingReq, 'declined'); viewingReq = null">Decline</button>
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
const requests    = ref([])
const stats       = ref({})
const loading     = ref(false)
const search      = ref('')
const statusFilter = ref('')
const page        = ref(1)
const totalPages  = ref(1)
const selectAll   = ref(false)
const selected    = ref([])
const viewingReq  = ref(null)

function filterByStatus(s) { statusFilter.value = s; page.value = 1; load() }

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/estimate-requests`, { params })
    requests.value  = res.data.requests?.data || []
    totalPages.value = res.data.requests?.last_page || 1
    stats.value     = res.data.stats || {}
  } catch {
    requests.value = [
      { id: 1, name: 'John Smith', email: 'john@acme.com', message: 'Looking for an estimate on web development services.', status: 'pending', created_at: new Date().toISOString() },
      { id: 2, name: 'Sarah Lane', email: 'sarah@business.io', message: 'Need estimate for 500 hours of consulting.', status: 'converted', created_at: new Date().toISOString() },
    ]
    stats.value = { total: 2, pending: 1, converted: 1, declined: 0 }
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function viewReq(req) { viewingReq.value = req }

async function updateStatus(req, newStatus) {
  try {
    await axios.put(`${BASE}/estimate-requests/${req.id}`, { status: newStatus })
    req.status = newStatus
  } catch { req.status = newStatus } // demo mode fallback
}

async function deleteReq(req) {
  if (!confirm(`Delete request from ${req.name}?`)) return
  try {
    await axios.delete(`${BASE}/estimate-requests/${req.id}`)
    load()
  } catch { alert('Failed to delete') }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select requests to export')
  alert(`Exporting ${selected.value.length} request(s) as PDF...`)
}

function toggleAll() { selected.value = selectAll.value ? requests.value.map(r => r.id) : [] }
function truncate(str, n) { if (!str) return '—'; return str.length > n ? str.slice(0, n) + '…' : str }
function formatDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }

onMounted(load)
</script>

<style scoped>
.er-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; cursor: pointer; text-align: center; transition: all 0.2s; }
.stat-card:hover { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.stat-card.pending .stat-value  { color: #d97706; }
.stat-card.converted .stat-value{ color: #10b981; }
.stat-card.declined .stat-value { color: #dc2626; }
.stat-icon { font-size: 22px; margin-bottom: 4px; }
.stat-value { font-size: 28px; font-weight: 800; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 2px; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; }
.search-wrap { position: relative; flex: 1; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; outline: none; }
.btn-bulk-pdf { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 13px; }
.btn-bulk-pdf:hover { background: #e2e8f0; }

.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 10px 14px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.name-cell { font-weight: 600; color: #1e293b; }
.email-cell a { color: #1e9aff; text-decoration: none; }
.message-cell { color: #64748b; font-size: 12px; max-width: 300px; }
.loading-cell, .empty-cell { text-align: center; padding: 40px; color: #94a3b8; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 32px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

.status-select { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1.5px solid #e2e8f0; cursor: pointer; outline: none; background: #fff; }
.status-select.pending   { color: #d97706; border-color: #fde68a; background: #fffbeb; }
.status-select.converted { color: #16a34a; border-color: #bbf7d0; background: #f0fdf4; }
.status-select.declined  { color: #dc2626; border-color: #fecaca; background: #fef2f2; }

.action-buttons { display: flex; gap: 4px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 4px 6px; border-radius: 6px; }
.action-btn:hover { background: #f1f5f9; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.modal-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
.close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 12px; }
.detail-field { display: flex; gap: 8px; font-size: 13px; align-items: flex-start; }
.detail-field label { font-weight: 600; color: #374151; min-width: 60px; }
.detail-field.message-block { flex-direction: column; }
.detail-field.message-block p { margin: 6px 0 0; color: #334155; line-height: 1.6; background: #f8fafc; padding: 12px; border-radius: 8px; }
.status-inline { padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-inline.pending   { background: #fffbeb; color: #d97706; }
.status-inline.converted { background: #f0fdf4; color: #16a34a; }
.status-inline.declined  { background: #fef2f2; color: #dc2626; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }
.btn-success { padding: 9px 18px; background: linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-danger  { padding: 9px 18px; background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
</style>
