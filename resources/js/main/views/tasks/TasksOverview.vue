<template>
  <div class="tk-ov-page">
    <div class="tk-ov-header">
      <div>
        <h1 class="tk-ov-title">Tasks Overview</h1>
        <p class="tk-ov-subtitle">
          <router-link :to="{ name: 'admin.tasks' }" class="tk-ov-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
            Back to tasks list
          </router-link>
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="tk-ov-filters">
      <select class="tk-ov-select" v-model="filters.staff" @change="load">
        <option value="">All Staff</option>
        <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
      </select>
      <select class="tk-ov-select" v-model="filters.month" @change="load">
        <option value="">All Months</option>
        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
      </select>
      <select class="tk-ov-select" v-model="filters.year" @change="load">
        <option value="">All Years</option>
        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
      </select>
      <select class="tk-ov-select" v-model="filters.progress" @change="load">
        <option value="">All Progress</option>
        <option value="Not Started">Not Started</option>
        <option value="In Progress">In Progress</option>
        <option value="Testing">Testing</option>
        <option value="Awaiting Feedback">Awaiting Feedback</option>
        <option value="Complete">Complete</option>
      </select>
    </div>

    <!-- Table -->
    <div class="tk-table-wrap">
      <table class="tk-table">
        <thead>
          <tr>
            <th>Name</th>
            <th style="width:100px;">Start Date</th>
            <th style="width:100px;">Due Date</th>
            <th style="width:120px;">Status</th>
            <th style="width:40px;">Attachments</th>
            <th style="width:40px;">Comments</th>
            <th style="width:80px;">Checklist</th>
            <th style="width:70px;">Logged</th>
            <th style="width:90px;">On Time?</th>
            <th>Assigned to</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="tk-empty-cell">
              <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </td>
          </tr>
          <tr v-for="t in tasks" :key="t.id" class="tk-row">
            <td>
              <div class="tk-name-cell">
                <span class="tk-name">{{ t.name }}</span>
                <span v-if="t.related_to_type" class="tk-rel">Related To: {{ t.related_to_type }} #{{ t.related_to_id }}</span>
              </div>
            </td>
            <td class="tk-cell-muted">{{ fmtDate(t.start_date) }}</td>
            <td :class="isOverdue(t) ? 'tk-overdue' : 'tk-cell-muted'">{{ fmtDate(t.due_date) }}</td>
            <td><span class="tk-status-badge" :class="statusClass(t.status)">{{ t.status }}</span></td>
            <td class="tk-cell-muted ta-c">0</td>
            <td class="tk-cell-muted ta-c">0</td>
            <td class="ta-c"><span class="tk-checklist">0/0</span></td>
            <td class="tk-cell-muted ta-c">00:00</td>
            <td class="ta-c"><span class="tk-cell-muted">—</span></td>
            <td class="tk-cell-muted">{{ t.assignee?.name || '—' }}</td>
          </tr>
          <tr v-if="!loading && !tasks.length">
            <td colspan="10" class="tk-empty-cell">No tasks found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

const BASE = '/api'
const tasks = ref([])
const staff = ref([])
const loading = ref(false)

const filters = reactive({ staff: '', month: '', year: '', progress: '' })

const months = [
  { label: 'January', value: '01' }, { label: 'February', value: '02' },
  { label: 'March', value: '03' }, { label: 'April', value: '04' },
  { label: 'May', value: '05' }, { label: 'June', value: '06' },
  { label: 'July', value: '07' }, { label: 'August', value: '08' },
  { label: 'September', value: '09' }, { label: 'October', value: '10' },
  { label: 'November', value: '11' }, { label: 'December', value: '12' },
]

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y - 2, y - 1, y, y + 1, y + 2]
})

function isOverdue(t) { if (t.status === 'Complete') return false; return t.due_date && new Date(t.due_date) < new Date() }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function statusClass(s) { return { 'Not Started': 'ns', 'In Progress': 'ip', 'Testing': 'test', 'Awaiting Feedback': 'af', 'Complete': 'done' }[s] || '' }

async function load() {
  loading.value = true
  try {
    const params = {}
    if (filters.staff) params.assigned_to = filters.staff
    if (filters.month) params.month = filters.month
    if (filters.year) params.year = filters.year
    if (filters.progress) params.status = filters.progress
    const r = await axios.get(`${BASE}/tasks/overview`, { params })
    tasks.value = r.data.tasks || []
  } catch { tasks.value = [] }
  finally { loading.value = false }
}

async function loadStaff() {
  try { const r = await axios.get(`${BASE}/staff?per_page=500`); staff.value = r.data.staff?.data || [] } catch { staff.value = [] }
}

onMounted(() => { load(); loadStaff() })
</script>

<style scoped>
.tk-ov-page { font-family: Inter, -apple-system, sans-serif; background: #f8fafc; padding: 24px; }
.tk-ov-header { margin-bottom: 18px; }
.tk-ov-title { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; }
.tk-ov-subtitle { font-size: 12px; color: #94a3b8; margin: 4px 0 0; }
.tk-ov-back { display: inline-flex; align-items: center; gap: 3px; color: #6366f1; font-weight: 600; text-decoration: none; }
.tk-ov-back:hover { text-decoration: underline; }

.tk-ov-filters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.tk-ov-select { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 10px; font-size: 12px; color: #1e293b; background: #fff; cursor: pointer; outline: none; font-family: inherit; }
.tk-ov-select:focus { border-color: #6366f1; }

.tk-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.tk-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.tk-table thead th { background: #f8fafc; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border-bottom: 1.5px solid #e2e8f0; }
.tk-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.tk-row:last-child td { border-bottom: none; }
.tk-row:hover { background: #fafbff; }
.tk-cell-muted { color: #64748b; }
.tk-name-cell { display: flex; flex-direction: column; gap: 1px; }
.tk-name { font-weight: 600; color: #0f172a; font-size: 12.5px; }
.tk-rel { font-size: 10.5px; color: #94a3b8; }
.tk-overdue { color: #dc2626 !important; font-weight: 600; }
.tk-empty-cell { text-align: center; padding: 40px 20px; color: #94a3b8; }
.ta-c { text-align: center; }

.tk-status-badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; white-space: nowrap; }
.tk-status-badge.ns { background: #f8fafc; color: #64748b; }
.tk-status-badge.ip { background: #eff6ff; color: #3b82f6; }
.tk-status-badge.test { background: #fffbeb; color: #d97706; }
.tk-status-badge.af { background: #fff7ed; color: #f97316; }
.tk-status-badge.done { background: #f0fdf4; color: #16a34a; }

.tk-checklist { font-size: 11px; font-weight: 700; font-variant-numeric: tabular-nums; }

@media (max-width: 1024px) { .tk-ov-page { padding: 16px; } .tk-table-wrap { overflow-x: auto; } }
</style>
