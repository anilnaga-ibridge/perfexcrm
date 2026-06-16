<template>
  <div class="projects-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Projects</h1>
        <span class="subtitle">Manage your active and archived projects</span>
      </div>
      <div class="header-actions">
        <button class="btn-bulk-pdf" @click="exportPDF" title="Bulk PDF Export">📄 Bulk PDF</button>
        <button class="btn-primary" @click="openCreate"><span>＋</span> New Project</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card" v-for="s in statCards" :key="s.label"
           @click="filterByStatus(s.filter)" :class="{ active: statusFilter === s.filter }">
        <div class="stat-icon">{{ s.icon }}</div>
        <div class="stat-info">
          <div class="stat-value" :style="{ color: s.color }">{{ s.value }}</div>
          <div class="stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- Filters + Toggle -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search projects…" class="search-input" @input="onSearch" />
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
        <button class="icon-toggle-btn" :class="{ active: view === 'kanban' }" @click="view = 'kanban'" title="Kanban View">⊞</button>
      </div>
    </div>

    <!-- Table View -->
    <div class="table-container" v-if="view === 'table'">
      <table class="data-table">
        <thead>
          <tr>
            <th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th>Project Name</th>
            <th>Client</th>
            <th>Billing Type</th>
            <th>Members</th>
            <th>Start Date</th>
            <th>Deadline</th>
            <th>Status</th>
            <th style="width:80px"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="9" class="loading-cell"><div class="loader"></div> Loading…</td>
          </tr>
          <tr v-else-if="!projects.length">
            <td colspan="9" class="empty-cell">
              <div class="empty-state"><span class="empty-icon">🗂️</span><p>No projects found</p></div>
            </td>
          </tr>
          <tr v-for="proj in projects" :key="proj.id" class="data-row">
            <td><input type="checkbox" v-model="selected" :value="proj.id" /></td>
            <td>
              <div class="proj-name">{{ proj.name }}</div>
              <div v-if="proj.description" class="proj-desc">{{ truncate(proj.description, 60) }}</div>
            </td>
            <td>{{ proj.client?.company || '—' }}</td>
            <td><span class="billing-tag">{{ proj.billing_type }}</span></td>
            <td>
              <div class="avatars">
                <div class="avatar" v-for="m in (proj.members || []).slice(0,4)" :key="m.id" :title="m.name">
                  {{ m.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div v-if="(proj.members || []).length > 4" class="avatar more">+{{ proj.members.length - 4 }}</div>
              </div>
            </td>
            <td>{{ fmtDate(proj.start_date) }}</td>
            <td>
              <span :class="isOverdue(proj.deadline) ? 'text-danger' : ''">{{ fmtDate(proj.deadline) || 'No Deadline' }}</span>
            </td>
            <td><span class="status-badge" :class="statusClass(proj.status)">{{ proj.status }}</span></td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="Edit" @click="editProject(proj)">✏️</button>
                <button class="action-btn" title="Delete" @click="deleteProject(proj)">🗑</button>
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

    <!-- Kanban View -->
    <div class="kanban-board" v-else>
      <div class="kanban-col" v-for="col in kanbanCols" :key="col.status">
        <div class="kanban-col-header" :style="{ borderTopColor: col.color }">
          <span class="col-icon">{{ col.icon }}</span>
          <span class="col-title">{{ col.status }}</span>
          <span class="col-count">{{ projectsByStatus(col.status).length }}</span>
        </div>
        <div class="kanban-cards">
          <div v-if="!projectsByStatus(col.status).length" class="kanban-empty">No projects</div>
          <div v-for="proj in projectsByStatus(col.status)" :key="proj.id" class="kanban-card">
            <div class="kc-name">{{ proj.name }}</div>
            <div class="kc-client">{{ proj.client?.company || 'No client' }}</div>
            <div class="kc-meta">
              <span class="billing-tag">{{ proj.billing_type }}</span>
              <span v-if="proj.deadline" class="kc-deadline" :class="isOverdue(proj.deadline) ? 'overdue' : ''">
                📅 {{ fmtDate(proj.deadline) }}
              </span>
            </div>
            <div class="kc-members">
              <div class="avatar sm" v-for="m in (proj.members || []).slice(0,3)" :key="m.id" :title="m.name">
                {{ m.name?.charAt(0)?.toUpperCase() }}
              </div>
            </div>
            <div class="kc-actions">
              <button @click="editProject(proj)">✏️</button>
              <button @click="deleteProject(proj)">🗑</button>
            </div>
          </div>
        </div>
        <button class="kanban-add-btn" @click="openCreateForStatus(col.status)">＋ Add Project</button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box">
          <div class="modal-header">
            <h2>{{ editing ? 'Edit Project' : 'New Project' }}</h2>
            <button class="close-btn" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-row span-2">
                <label>Project Name *</label>
                <input v-model="form.name" placeholder="Project name" class="form-input" />
              </div>
              <div class="form-row">
                <label>Billing Type</label>
                <select v-model="form.billing_type" class="form-input">
                  <option value="Fixed Rate">Fixed Rate</option>
                  <option value="Project Hours">Project Hours</option>
                  <option value="Task Hours">Task Hours</option>
                </select>
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
              <div class="form-row">
                <label>Start Date</label>
                <input v-model="form.start_date" type="date" class="form-input" />
              </div>
              <div class="form-row">
                <label>Deadline</label>
                <input v-model="form.deadline" type="date" class="form-input" />
              </div>
              <div class="form-row">
                <label>Budget ($)</label>
                <input v-model="form.budget" type="number" min="0" placeholder="0.00" class="form-input" />
              </div>
              <div class="form-row span-2">
                <label>Description</label>
                <textarea v-model="form.description" rows="3" class="form-input form-textarea" placeholder="Project description…"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="save" :disabled="saving">
              {{ saving ? 'Saving…' : (editing ? 'Save Changes' : 'Create Project') }}
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

const BASE = '/api'
const projects   = ref([])
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
  name: '', description: '', billing_type: 'Fixed Rate', status: 'Not Started',
  start_date: '', deadline: '', budget: '',
})

const DEMO = [
  { id: 1, name: 'E-commerce API Integration', description: 'Build REST endpoints for web store', client: { company: 'Nader-Abernathy' }, billing_type: 'Fixed Rate', members: [{ id: 1, name: 'Alexander' }, { id: 2, name: 'John Doe' }], start_date: '2026-06-01', deadline: '2026-07-15', status: 'In Progress' },
  { id: 2, name: 'Brand Strategy Redesign', description: 'Deliver brand manuals & logo vectors', client: { company: 'Mertz-Bergnaum' }, billing_type: 'Fixed Rate', members: [{ id: 3, name: 'Alice Smith' }], start_date: '2026-06-05', deadline: '2026-08-05', status: 'In Progress' },
  { id: 3, name: 'Legacy App Migration', description: 'Migrate Symfony 4 to Laravel 12', client: { company: 'Schroeder & Sons' }, billing_type: 'Project Hours', members: [{ id: 1, name: 'Alexander' }, { id: 4, name: 'Bob Johnson' }], start_date: '2026-05-10', deadline: '2026-09-30', status: 'On Hold' },
  { id: 4, name: 'SEO Audit & Content', description: 'Audit landing pages and write SEO blogs', client: { company: 'Bayer Group' }, billing_type: 'Fixed Rate', members: [{ id: 4, name: 'Bob Johnson' }], start_date: '2026-05-01', deadline: '2026-05-31', status: 'Finished' },
  { id: 5, name: 'DevOps CI/CD Automation', description: 'Configure GitHub Actions pipelines', client: { company: 'Halvorson LLC' }, billing_type: 'Project Hours', members: [{ id: 1, name: 'Alexander' }, { id: 5, name: 'Carol White' }], start_date: '2026-06-10', deadline: '2026-07-10', status: 'Not Started' },
]

const kanbanCols = [
  { status: 'Not Started', icon: '🕐', color: '#94a3b8' },
  { status: 'In Progress', icon: '🔄', color: '#3b82f6' },
  { status: 'On Hold',     icon: '⏸',  color: '#f59e0b' },
  { status: 'Finished',    icon: '✅', color: '#10b981' },
]

const statCards = computed(() => [
  { label: 'Total', icon: '📁', color: '#1e293b', value: stats.value.total || 0, filter: '' },
  { label: 'Not Started', icon: '🕐', color: '#94a3b8', value: stats.value.not_started || 0, filter: 'Not Started' },
  { label: 'In Progress', icon: '🔄', color: '#3b82f6', value: stats.value.in_progress || 0, filter: 'In Progress' },
  { label: 'On Hold', icon: '⏸', color: '#f59e0b', value: stats.value.on_hold || 0, filter: 'On Hold' },
  { label: 'Finished', icon: '✅', color: '#10b981', value: stats.value.finished || 0, filter: 'Finished' },
])

function filterByStatus(s) { statusFilter.value = s; page.value = 1; load() }
function projectsByStatus(s) { return projects.value.filter(p => p.status === s) }
function statusClass(s) { return { 'Not Started': 'default', 'In Progress': 'in-progress', 'On Hold': 'on-hold', 'Finished': 'finished' }[s] || '' }
function isOverdue(d) { return d && new Date(d) < new Date() }

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 20, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/projects`, { params })
    projects.value  = res.data.projects?.data || []
    totalPages.value = res.data.projects?.last_page || 1
    stats.value     = res.data.stats || {}
  } catch {
    projects.value = DEMO
    stats.value = { total: 5, not_started: 1, in_progress: 2, on_hold: 1, finished: 1 }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  editing.value = null
  Object.assign(form, { name: '', description: '', billing_type: 'Fixed Rate', status: 'Not Started', start_date: new Date().toISOString().slice(0,10), deadline: '', budget: '' })
  showModal.value = true
}

function openCreateForStatus(status) {
  openCreate()
  form.status = status
}

function editProject(proj) {
  editing.value = proj
  Object.assign(form, {
    name: proj.name, description: proj.description || '',
    billing_type: proj.billing_type || 'Fixed Rate', status: proj.status,
    start_date: proj.start_date?.slice?.(0,10) || proj.start_date || '',
    deadline: proj.deadline?.slice?.(0,10) || proj.deadline || '',
    budget: proj.budget || '',
  })
  showModal.value = true
}

async function save() {
  if (!form.name) return alert('Project name is required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/projects/${editing.value.id}`, form)
    } else {
      await axios.post(`${BASE}/projects`, form)
    }
    closeModal(); load()
  } catch {
    // Demo mode - update local
    if (editing.value) {
      Object.assign(editing.value, { ...form })
    }
    closeModal()
  } finally { saving.value = false }
}

async function deleteProject(proj) {
  if (!confirm(`Delete "${proj.name}"?`)) return
  try {
    await axios.delete(`${BASE}/projects/${proj.id}`)
    load()
  } catch { projects.value = projects.value.filter(p => p.id !== proj.id) }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select projects to export')
  alert(`Exporting ${selected.value.length} project(s) as PDF…`)
}

function toggleAll() { selected.value = selectAll.value ? projects.value.map(p => p.id) : [] }
function closeModal() { showModal.value = false; editing.value = null }
function fmtDate(d) { if (!d) return null; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '…' : str }

onMounted(load)
</script>

<style scoped>
.projects-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }

.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }
.header-actions { display: flex; gap: 10px; }

.stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.2s; }
.stat-card:hover, .stat-card.active { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.1); }
.stat-icon { font-size: 22px; }
.stat-value { font-size: 22px; font-weight: 800; }
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
.proj-name { font-weight: 600; color: #1e293b; }
.proj-desc { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.billing-tag { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.avatars { display: flex; }
.avatar { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #1e9aff, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; margin-left: -6px; border: 2px solid #fff; flex-shrink: 0; }
.avatar:first-child { margin-left: 0; }
.avatar.more { background: #e2e8f0; color: #64748b; font-size: 10px; }
.avatar.sm { width: 22px; height: 22px; font-size: 10px; }
.text-danger { color: #dc2626; font-weight: 600; }

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

/* Kanban */
.kanban-board { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.kanban-col { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px; min-height: 400px; display: flex; flex-direction: column; gap: 10px; }
.kanban-col-header { display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; border-top: 3px solid transparent; margin-top: -14px; padding-top: 14px; border-radius: 12px 12px 0 0; }
.col-title { font-weight: 700; font-size: 13px; color: #1e293b; flex: 1; }
.col-count { background: #e2e8f0; color: #64748b; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px; }
.kanban-cards { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.kanban-empty { text-align: center; color: #94a3b8; font-size: 12px; padding: 20px; }
.kanban-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 12px; transition: all 0.2s; }
.kanban-card:hover { border-color: #1e9aff; box-shadow: 0 4px 12px rgba(30,154,255,.1); }
.kc-name { font-weight: 700; color: #1e293b; font-size: 13px; margin-bottom: 4px; }
.kc-client { font-size: 11px; color: #64748b; margin-bottom: 8px; }
.kc-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
.kc-deadline { font-size: 11px; color: #64748b; }
.kc-deadline.overdue { color: #dc2626; font-weight: 600; }
.kc-members { display: flex; margin-bottom: 8px; }
.kc-actions { display: flex; gap: 6px; }
.kc-actions button { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px; font-size: 12px; cursor: pointer; }
.kc-actions button:hover { border-color: #1e9aff; }
.kanban-add-btn { background: none; border: 1.5px dashed #e2e8f0; border-radius: 8px; padding: 8px; color: #94a3b8; font-size: 13px; cursor: pointer; transition: all 0.15s; }
.kanban-add-btn:hover { border-color: #1e9aff; color: #1e9aff; }

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

@media (max-width: 1200px) { .kanban-board { grid-template-columns: repeat(2, 1fr); } .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .kanban-board { grid-template-columns: 1fr; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
