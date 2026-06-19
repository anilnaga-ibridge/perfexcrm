<template>
  <div class="ct-page">
    <div class="ct-header">
      <div>
        <h1 class="ct-title">Contracts</h1>
        <p class="ct-subtitle">Manage client contracts and agreements</p>
      </div>
      <div class="ct-header-actions">
        <button class="ct-btn-ghost" @click="exportPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Bulk PDF
        </button>
        <button class="ct-btn-primary" @click="openCreate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Contract
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="ct-stats-row">
      <div v-for="card in summaryCards" :key="card.label" class="ct-stat-card" :style="{ borderLeftColor: card.color }">
        <div class="ct-stat-icon" :style="{ background: card.bg }" v-html="card.icon"></div>
        <div class="ct-stat-info">
          <div class="ct-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="ct-stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Charts Row (ApexCharts) -->
    <div class="ct-charts-row">
      <div class="ct-chart-card">
        <div class="ct-chart-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
          Contracts by Type
        </div>
        <VueApexCharts type="bar" height="280" :options="contractsBarOptions" :series="contractsBarSeries"></VueApexCharts>
      </div>
      <div class="ct-chart-card">
        <div class="ct-chart-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
          Contracts Value by Type (USD)
        </div>
        <VueApexCharts type="area" height="280" :options="contractsValueOptions" :series="contractsValueSeries"></VueApexCharts>
      </div>
    </div>

    <!-- Filters -->
    <div class="ct-filters">
      <div class="ct-filters-left">
        <select class="ct-filter-select" v-model="perPage" @change="load">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
      <div class="ct-filters-right">
        <select class="ct-filter-select" v-model="statusFilter" @change="load">
          <option value="">All Statuses</option>
          <option value="Active">Active</option>
          <option value="In Progress">In Progress</option>
          <option value="Expired">Expired</option>
          <option value="Finished">Finished</option>
          <option value="Not Started">Not Started</option>
        </select>
        <div class="ct-search-wrap">
          <svg class="ct-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" placeholder="Search contracts..." class="ct-search-input" @input="onSearch" />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="ct-table-wrap">
      <table class="ct-table">
        <thead>
          <tr>
            <th style="width:40px"><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th>Subject</th>
            <th>Customer</th>
            <th>Contract Type</th>
            <th style="width:100px;text-align:right;">Value</th>
            <th style="width:105px;">Start Date</th>
            <th style="width:105px;">End Date</th>
            <th style="width:80px;">Signed</th>
            <th style="width:90px;">Status</th>
            <th style="width:70px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="ct-empty-cell">
              <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </td>
          </tr>
          <tr v-for="con in contracts" :key="con.id" class="ct-row">
            <td><input type="checkbox" v-model="selected" :value="con.id" /></td>
            <td>
              <div class="ct-subject-row">
                <span class="ct-subject">{{ con.subject }}</span>
                <span v-if="con.description" class="ct-desc">{{ truncate(con.description, 55) }}</span>
              </div>
            </td>
            <td><span class="ct-customer">{{ con.client?.company || '—' }}</span></td>
            <td><span class="ct-type-tag">{{ contractTypeName(con.contract_type_id) }}</span></td>
            <td class="ct-amount-cell">${{ fmt(con.value) }}</td>
            <td class="ct-date-cell">{{ fmtDate(con.start_date) }}</td>
            <td>
              <span :class="isExpired(con.end_date) ? 'ct-expired' : 'ct-date-cell'">
                {{ fmtDate(con.end_date) || '—' }}
              </span>
            </td>
            <td>
              <span class="ct-signed-badge" :class="con.signed ? 'yes' : 'no'">
                {{ con.signed ? 'Signed' : 'No' }}
              </span>
            </td>
            <td><span class="ct-status-badge" :class="statusClass(con.status)">{{ con.status }}</span></td>
            <td>
              <div class="ct-action-group">
                <button @click="editContract(con)" class="ct-action-btn" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                </button>
                <button @click="deleteContract(con)" class="ct-action-btn hover:text-rose-600" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !contracts.length">
            <td colspan="10" class="ct-empty-cell">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              <p class="text-slate-400 text-sm mt-2">No contracts found</p>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="ct-pagination" v-if="totalPages > 1">
        <span class="ct-pg-info">Page {{ page }} of {{ totalPages }}</span>
        <div class="ct-pg-btns">
          <button class="ct-pg-btn" :disabled="page <= 1" @click="page--; load()">Previous</button>
          <button class="ct-pg-btn" :disabled="page >= totalPages" @click="page++; load()">Next</button>
        </div>
      </div>
    </div>

    <!-- Contracts Insights Section -->
    <div class="ct-insights-section">
      <div class="ct-insights-header">
        <h3 class="ct-insights-title">Contracts Insights</h3>
      </div>
      <div class="ct-insights-grid">
        <div class="ct-insight-card">
          <h4 class="ct-insight-label">Status Distribution</h4>
          <VueApexCharts type="donut" height="260" :options="statusDonutOptions" :series="statusDonutSeries"></VueApexCharts>
        </div>
        <div class="ct-insight-card">
          <h4 class="ct-insight-label">Monthly Value Trend</h4>
          <VueApexCharts type="bar" height="260" :options="monthlyTrendOptions" :series="monthlyTrendSeries"></VueApexCharts>
        </div>
        <div class="ct-insight-card">
          <h4 class="ct-insight-label">Expiry Timeline</h4>
          <VueApexCharts type="bar" height="260" :options="expiryTimelineOptions" :series="expiryTimelineSeries"></VueApexCharts>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="ct-modal-overlay" @click.self="closeModal">
        <div class="ct-modal-box">
          <div class="ct-modal-hd">
            <div class="ct-modal-hd-left">
              <div class="ct-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
              <div>
                <h3 class="ct-modal-title">{{ editing ? 'Edit Contract' : 'Contract Information' }}</h3>
                <p class="ct-modal-subtitle">{{ editing ? 'Update contract details' : 'Enter the details for the new contract' }}</p>
              </div>
            </div>
            <button class="ct-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="ct-modal-body">
            <div class="ct-form-grid">
              <div class="ct-form-checkboxes">
                <label class="ct-check-label">
                  <input type="checkbox" v-model="form.trash" />
                  <span>🗑 Trash</span>
                </label>
                <label class="ct-check-label">
                  <input type="checkbox" v-model="form.hide_from_customer" />
                  <span>👁 Hide from customer</span>
                </label>
              </div>
              <div class="ct-fg-row span-2">
                <label class="ct-fg-label">Customer <span class="text-red-500">*</span></label>
                <select v-model="form.client_id" class="ct-fg-input">
                  <option value="">Select customer...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>
              <div class="ct-fg-row span-2">
                <label class="ct-fg-label">Subject <span class="text-red-500">*</span></label>
                <input v-model="form.subject" placeholder="e.g. Website Maintenance SLA" class="ct-fg-input" />
              </div>
              <div class="ct-fg-row">
                <label class="ct-fg-label">Contract Value ($)</label>
                <input v-model="form.value" type="number" min="0" step="0.01" placeholder="0.00" class="ct-fg-input" />
              </div>
              <div class="ct-fg-row">
                <label class="ct-fg-label">Contract type</label>
                <select v-model="form.contract_type_id" class="ct-fg-input">
                  <option value="">Select type...</option>
                  <option v-for="t in contractTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </div>
              <div class="ct-fg-row">
                <label class="ct-fg-label">Start Date <span class="text-red-500">*</span></label>
                <input v-model="form.start_date" type="date" class="ct-fg-input" />
              </div>
              <div class="ct-fg-row">
                <label class="ct-fg-label">End Date</label>
                <input v-model="form.end_date" type="date" class="ct-fg-input" />
              </div>
              <div class="ct-fg-row span-2">
                <label class="ct-fg-label">Description</label>
                <textarea v-model="form.description" rows="4" placeholder="Contract terms, scope of work, additional notes..." class="ct-fg-input ct-fg-textarea"></textarea>
              </div>
            </div>
          </div>
          <div class="ct-modal-ft">
            <button class="ct-btn-cancel" @click="closeModal">Cancel</button>
            <button class="ct-btn-save" @click="save" :disabled="saving">
              <svg v-if="saving" class="animate-spin" fill="none" viewBox="0 0 24 24" width="14" height="14"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Create Contract') }}
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
import VueApexCharts from 'vue3-apexcharts'

const BASE = '/api'
const contracts = ref([])
const stats     = ref({})
const clients   = ref([])
const contractTypes = ref([])
const loading   = ref(false)
const saving    = ref(false)
const search    = ref('')
const statusFilter = ref('')
const perPage   = ref('25')
const page      = ref(1)
const totalPages = ref(1)
const selectAll = ref(false)
const selected  = ref([])
const showModal = ref(false)
const editing   = ref(null)

const form = reactive({
  subject: '', client_id: '', contract_type_id: '', value: '',
  start_date: '', end_date: '', status: 'Not Started',
  description: '', signed: false, trash: false, hide_from_customer: false,
})

const CHART_COLORS = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899']

const summaryCards = computed(() => [
  { label: 'Contracts', value: stats.value.total || 0, color: '#1e293b', bg: '#f1f5f9',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>' },
  { label: 'Active', value: stats.value.active || 0, color: '#16a34a', bg: '#f0fdf4',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>' },
  { label: 'Expired', value: stats.value.expired || 0, color: '#dc2626', bg: '#fef2f2',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>' },
  { label: 'About to Expire', value: stats.value.about_to_expire || 0, color: '#f59e0b', bg: '#fffbeb',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' },
  { label: 'Recently Added', value: stats.value.recently_added || 0, color: '#6366f1', bg: '#eef2ff',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { label: 'Trash', value: stats.value.trash || 0, color: '#64748b', bg: '#f8fafc',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' },
])

const contractsByType = computed(() => {
  const groups = {}
  contracts.value.forEach(c => {
    const name = contractTypeName(c.contract_type_id)
    if (!groups[name]) groups[name] = 0
    groups[name]++
  })
  const entries = Object.entries(groups).map(([name, count]) => ({ name, count }))
  const max = Math.max(...entries.map(e => e.count), 1)
  return entries.map((e, i) => ({ ...e, pct: (e.count / max) * 100, color: CHART_COLORS[i % CHART_COLORS.length] }))
})

const contractsValueByType = computed(() => {
  const groups = {}
  contracts.value.forEach(c => {
    const name = contractTypeName(c.contract_type_id)
    if (!groups[name]) groups[name] = 0
    groups[name] += Number(c.value || 0)
  })
  const entries = Object.entries(groups).map(([name, value]) => ({ name, value }))
  const max = Math.max(...entries.map(e => e.value), 1)
  return entries.map((e, i) => ({ ...e, pct: (e.value / max) * 100, color: CHART_COLORS[(i + 3) % CHART_COLORS.length] }))
})

const statusDistribution = computed(() => {
  const counts = { Active: 0, 'In Progress': 0, Expired: 0, Finished: 0, 'Not Started': 0 }
  contracts.value.forEach(c => { if (counts[c.status] !== undefined) counts[c.status]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ status: s, count: v }))
})

const STATUS_COLORS = { Active: '#16a34a', 'In Progress': '#3b82f6', Expired: '#dc2626', Finished: '#16a34a', 'Not Started': '#94a3b8' }
const STATUS_LABELS  = { Active: 'Active', 'In Progress': 'In Progress', Expired: 'Expired', Finished: 'Finished', 'Not Started': 'Not Started' }

const contractsBarOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: contractsByType.value.map(c => c.name), labels: { style: { fontSize: '12px', fontWeight: 600 } } },
  yaxis: { labels: { style: { fontSize: '12px' } } },
  colors: ['#6366f1'],
  plotOptions: { bar: { horizontal: true, columnWidth: '55%', borderRadius: 4 } },
  dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700, colors: ['#1e293b'] } },
  grid: { borderColor: '#f1f5f9' },
}))
const contractsBarSeries = computed(() => [{ name: 'Contracts', data: contractsByType.value.map(c => c.count) }])

const contractsValueOptions = computed(() => ({
  chart: { type: 'area', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: contractsValueByType.value.map(c => c.name), labels: { style: { fontSize: '12px', fontWeight: 600 } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '12px' } } },
  colors: ['#10b981'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
  stroke: { curve: 'smooth', width: 2 },
  markers: { size: 4, colors: ['#fff'], strokeColors: '#10b981', strokeWidth: 2 },
  dataLabels: { enabled: true, formatter: v => '$' + v.toLocaleString(), style: { fontSize: '11px', fontWeight: 700, colors: ['#10b981'] } },
  grid: { borderColor: '#f1f5f9' },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}))
const contractsValueSeries = computed(() => [{ name: 'Value (USD)', data: contractsValueByType.value.map(c => c.value) }])

const statusDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: statusDistribution.value.map(s => s.status),
  colors: statusDistribution.value.map(s => STATUS_COLORS[s.status]),
  plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 700, color: '#1e293b', formatter: () => String(statusDistribution.value.reduce((a, b) => a + b.count, 0)) } } } } },
  dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700, colors: ['#fff'] } },
  legend: { position: 'bottom', fontSize: '12px', fontWeight: 600, labels: { colors: '#475569' }, itemMargin: { horizontal: 12 } },
  responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }],
}))
const statusDonutSeries = computed(() => statusDistribution.value.map(s => s.count))

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const monthlyTrendOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: MONTHS, labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '11px' } } },
  colors: ['#8b5cf6'],
  plotOptions: { bar: { columnWidth: '60%', borderRadius: 4, dataLabels: { position: 'top' } } },
  dataLabels: { enabled: true, formatter: v => '$' + (v / 1000).toFixed(1) + 'k', style: { fontSize: '10px', fontWeight: 700, colors: ['#8b5cf6'] }, offsetY: -18 },
  grid: { borderColor: '#f1f5f9' },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}))
const monthlyTrendSeries = computed(() => [
  { name: 'Contract Value', data: [45000, 52000, 38000, 61000, 48000, 72000, 55000, 68000, 59000, 81000, 74000, 92000] },
])

const expiryTimelineOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: ['This Week', 'Next Week', 'In 2 Weeks', 'In 3 Weeks', 'Next Month', 'Beyond'], labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { style: { fontSize: '11px' } } },
  colors: ['#f59e0b'],
  plotOptions: { bar: { horizontal: true, columnWidth: '55%', borderRadius: 4 } },
  dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700, colors: ['#1e293b'] } },
  grid: { borderColor: '#f1f5f9' },
}))
const expiryTimelineSeries = computed(() => [
  { name: 'Expiring Contracts', data: [3, 5, 2, 4, 8, 12] },
])

function contractTypeName(id) {
  if (!id) return 'General'
  const t = contractTypes.value.find(ct => ct.id == id)
  return t ? t.name : `Type #${id}`
}

async function loadContractTypes() {
  try {
    const res = await axios.get(`${BASE}/contract-types`)
    contractTypes.value = res.data || []
  } catch { contractTypes.value = [] }
}

async function loadClients() {
  try {
    const res = await axios.get(`${BASE}/clients?per_page=1000`)
    clients.value = res.data.clients?.data || []
  } catch { clients.value = [] }
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/contracts`, { params })
    contracts.value = res.data.contracts?.data || []
    totalPages.value = res.data.contracts?.last_page || 1
    stats.value = res.data.stats || {}
  } catch {
    contracts.value = []
    stats.value = { total: 0, active: 0, expired: 0, about_to_expire: 0, recently_added: 0, trash: 0, total_value: 0 }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  editing.value = null
  Object.assign(form, {
    subject: '', client_id: '', contract_type_id: '', value: '',
    start_date: new Date().toISOString().slice(0, 10), end_date: '',
    status: 'Not Started', description: '', signed: false, trash: false, hide_from_customer: false,
  })
  showModal.value = true
}

function editContract(con) {
  editing.value = con
  Object.assign(form, {
    subject: con.subject,
    client_id: con.client_id || '',
    contract_type_id: String(con.contract_type_id || ''),
    value: con.value || '',
    start_date: con.start_date?.slice?.(0, 10) || '',
    end_date: con.end_date?.slice?.(0, 10) || '',
    status: con.status || 'Not Started',
    description: con.description || '',
    signed: !!con.signed,
    trash: !!con.trash,
    hide_from_customer: !!con.hide_from_customer,
  })
  showModal.value = true
}

async function save() {
  if (!form.subject) return alert('Subject is required')
  if (!form.client_id) return alert('Customer is required')
  if (!form.start_date) return alert('Start date is required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/contracts/${editing.value.id}`, form)
      message.success('Contract updated')
    } else {
      await axios.post(`${BASE}/contracts`, form)
      message.success('Contract created')
    }
    closeModal(); load()
  } catch {
    alert('Failed to save contract')
  } finally { saving.value = false }
}

async function deleteContract(con) {
  if (!confirm(`Delete contract "${con.subject}"?`)) return
  try {
    await axios.delete(`${BASE}/contracts/${con.id}`)
    message.success('Contract deleted')
    load()
  } catch {
    contracts.value = contracts.value.filter(c => c.id !== con.id)
  }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select contracts to export')
  alert(`Exporting ${selected.value.length} contract(s) as PDF...`)
}

function toggleAll() { selected.value = selectAll.value ? contracts.value.map(c => c.id) : [] }
function closeModal() { showModal.value = false; editing.value = null }
function statusClass(s) {
  return { 'Active': 'active', 'In Progress': 'progress', 'Expired': 'expired', 'Finished': 'finished', 'Not Started': 'draft' }[s] || ''
}
function isExpired(d) { return d && new Date(d) < new Date() }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(s, n) { return s?.length > n ? s.slice(0, n) + '...' : s }

onMounted(() => { load(); loadContractTypes(); loadClients() })
</script>

<style scoped>
.ct-page { font-family: Inter, -apple-system, sans-serif; background: #f8fafc; padding: 24px; }

/* Header */
.ct-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.ct-title { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; }
.ct-subtitle { font-size: 12.5px; color: #94a3b8; margin: 2px 0 0; }
.ct-header-actions { display: flex; gap: 8px; }

.ct-btn-primary {
  display: inline-flex; align-items: center; gap: 6px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 8px; padding: 9px 16px;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  transition: all 0.2s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
  font-family: inherit;
}
.ct-btn-primary:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
}

.ct-btn-ghost {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: 8px 13px; font-size: 12px; font-weight: 500; color: #475569;
  cursor: pointer; transition: all 0.12s; font-family: inherit;
}
.ct-btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

/* Stats */
.ct-stats-row {
  display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 18px;
}
@media (max-width: 1000px) { .ct-stats-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .ct-stats-row { grid-template-columns: repeat(2, 1fr); } }
.ct-stat-card {
  background: #fff; border: 1px solid #f1f5f9; border-left: 3px solid #e2e8f0;
  border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 10px;
  transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,.02);
}
.ct-stat-card:hover {
  border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,.04); transform: translateY(-1px);
}
.ct-stat-icon {
  width: 36px; height: 36px; border-radius: 10px; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.ct-stat-val { font-size: 16px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
.ct-stat-label { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }

/* Gantt Charts / ApexCharts */
.ct-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px; }
@media (max-width: 800px) { .ct-charts-row { grid-template-columns: 1fr; } }
.ct-chart-card {
  background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px;
}
.ct-chart-title {
  font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 14px;
  display: flex; align-items: center; gap: 7px;
}
/* Insights Section */
.ct-insights-section { margin-top: 18px; }
.ct-insights-header { margin-bottom: 12px; }
.ct-insights-title { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
.ct-insights-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media (max-width: 1100px) { .ct-insights-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 700px) { .ct-insights-grid { grid-template-columns: 1fr; } }
.ct-insight-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px; }
.ct-insight-label { font-size: 12px; font-weight: 700; color: #475569; margin: 0 0 8px; }

/* Filters */
.ct-filters { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
.ct-filters-left, .ct-filters-right { display: flex; align-items: center; gap: 8px; }
.ct-filter-select {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 10px;
  font-size: 12px; color: #1e293b; background: #fff; cursor: pointer;
  outline: none; font-family: inherit;
}
.ct-filter-select:focus { border-color: #6366f1; }
.ct-search-wrap { position: relative; }
.ct-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; pointer-events: none; }
.ct-search-input {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 12px 7px 30px;
  font-size: 12px; color: #1e293b; background: #fff; width: 200px;
  outline: none; font-family: inherit;
}
.ct-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }

/* Table */
.ct-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.ct-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.ct-table thead th {
  background: #f8fafc; padding: 10px 12px; text-align: left;
  font-size: 10px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
  border-bottom: 1.5px solid #e2e8f0;
}
.ct-table tbody td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ct-row:last-child td { border-bottom: none; }
.ct-row:hover { background: #fafbff; }
.ct-subject-row { display: flex; flex-direction: column; gap: 1px; }
.ct-subject { font-weight: 600; color: #0f172a; }
.ct-desc { font-size: 10.5px; color: #94a3b8; }
.ct-customer { color: #475569; font-weight: 500; }
.ct-type-tag { background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; white-space: nowrap; }
.ct-amount-cell { font-weight: 700; color: #0f172a; text-align: right; font-variant-numeric: tabular-nums; }
.ct-date-cell { color: #64748b; }
.ct-expired { color: #dc2626; font-weight: 600; }
.ct-signed-badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; white-space: nowrap; }
.ct-signed-badge.yes { background: #f0fdf4; color: #16a34a; }
.ct-signed-badge.no { background: #fef2f2; color: #dc2626; }
.ct-status-badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; white-space: nowrap; }
.ct-status-badge.active { background: #f0fdf4; color: #16a34a; }
.ct-status-badge.progress { background: #eff6ff; color: #3b82f6; }
.ct-status-badge.expired { background: #fef2f2; color: #dc2626; }
.ct-status-badge.finished { background: #f0fdf4; color: #16a34a; }
.ct-status-badge.draft { background: #f8fafc; color: #64748b; }

.ct-action-group { display: flex; gap: 3px; }
.ct-action-btn {
  background: transparent; border: 1px solid #e2e8f0; border-radius: 6px;
  width: 28px; height: 28px; display: inline-flex; align-items: center;
  justify-content: center; color: #94a3b8; cursor: pointer; transition: all 0.12s;
}
.ct-action-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #6366f1; }
.hover\:text-rose-600:hover { color: #e11d48; }

/* Pagination */
.ct-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 14px; border-top: 1px solid #f1f5f9; font-size: 11.5px; color: #64748b;
}
.ct-pg-info { color: #94a3b8; }
.ct-pg-btns { display: flex; gap: 6px; }
.ct-pg-btn {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 6px;
  padding: 5px 11px; font-size: 11.5px; color: #475569; cursor: pointer;
  transition: all 0.12s; font-family: inherit;
}
.ct-pg-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.ct-pg-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Empty */
.ct-empty-cell { text-align: center; padding: 40px 20px; color: #94a3b8; }

/* Modal */
.ct-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.ct-modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
.ct-modal-hd {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9;
}
.ct-modal-hd-left { display: flex; gap: 12px; align-items: flex-start; }
.ct-modal-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #6366f1;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ct-modal-title { font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; }
.ct-modal-subtitle { font-size: 12px; color: #94a3b8; margin: 2px 0 0; }
.ct-modal-close {
  background: none; border: none; cursor: pointer; color: #94a3b8;
  width: 32px; height: 32px; border-radius: 8px; display: flex;
  align-items: center; justify-content: center; transition: all 0.12s;
}
.ct-modal-close:hover { background: #f1f5f9; color: #475569; }
.ct-modal-body { padding: 18px 24px; }
.ct-modal-ft {
  display: flex; justify-content: flex-end; gap: 10px;
  padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9;
}

.ct-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ct-form-checkboxes {
  grid-column: span 2; display: flex; gap: 20px; margin-bottom: 4px;
}
.ct-check-label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12.5px; font-weight: 500; color: #475569; }
.ct-check-label input { width: 15px; height: 15px; cursor: pointer; }
.ct-fg-row { display: flex; flex-direction: column; gap: 4px; }
.ct-fg-row.span-2 { grid-column: span 2; }
.ct-fg-label { font-size: 11.5px; font-weight: 600; color: #334155; }
.ct-fg-input {
  padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: 12.5px; outline: none; width: 100%; box-sizing: border-box;
  font-family: inherit; transition: border-color 0.12s;
}
.ct-fg-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.ct-fg-textarea { resize: vertical; min-height: 90px; }

.ct-btn-cancel {
  padding: 8px 18px; border: 1.5px solid #e2e8f0; border-radius: 10px;
  background: #fff; color: #64748b; font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all 0.12s; font-family: inherit;
}
.ct-btn-cancel:hover { border-color: #cbd5e1; color: #334155; }
.ct-btn-save {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 8px 20px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 10px; font-size: 12.5px;
  font-weight: 600; cursor: pointer; transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99,102,241,.3); font-family: inherit;
}
.ct-btn-save:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.4);
}
.ct-btn-save:disabled { opacity: .6; cursor: not-allowed; transform: none; }

@media (max-width: 1024px) { .ct-page { padding: 16px; } .ct-table-wrap { overflow-x: auto; } }
@media (max-width: 640px) { .ct-filters { flex-direction: column; align-items: stretch; } .ct-filters-right { flex-direction: column; } .ct-search-input { width: 100%; } .ct-form-grid { grid-template-columns: 1fr; } .ct-fg-row.span-2, .ct-form-checkboxes { grid-column: span 1; } }
</style>
