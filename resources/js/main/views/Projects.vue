<template>
  <div class="pj-page">
    <div class="pj-header">
      <div>
        <h1 class="pj-title">Projects</h1>
        <p class="pj-subtitle">Manage your active and archived projects</p>
      </div>
      <div class="pj-header-actions">
        <button class="pj-btn-ghost" @click="exportPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Bulk PDF
        </button>
        <button class="pj-btn-primary" @click="openCreate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Project
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="pj-stats-row">
      <div v-for="card in summaryCards" :key="card.label"
           class="pj-stat-card" :style="{ borderLeftColor: card.color }"
           @click="filterByStatus(card.filter)">
        <div class="pj-stat-icon" :style="{ background: card.bg }" v-html="card.icon"></div>
        <div class="pj-stat-info">
          <div class="pj-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="pj-stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Filters + View Toggle -->
    <div class="pj-filters">
      <div class="pj-filters-left">
        <select class="pj-filter-select" v-model="perPage" @change="load">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <div class="pj-status-pills">
          <button v-for="s in statusFilters" :key="s.value"
                  class="pj-pill" :class="{ active: statusFilter === s.value }"
                  @click="filterByStatus(s.value)">
            {{ s.label }}
          </button>
        </div>
      </div>
      <div class="pj-filters-right">
        <div class="pj-search-wrap">
          <svg class="pj-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" placeholder="Search projects..." class="pj-search-input" @input="onSearch" />
        </div>
        <div class="pj-view-toggle">
          <button class="pj-view-btn" :class="{ active: view === 'table' }" @click="view = 'table'" title="Table View">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          </button>
          <button class="pj-view-btn" :class="{ active: view === 'kanban' }" @click="view = 'kanban'" title="Kanban View">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
          </button>
          <button class="pj-view-btn" :class="{ active: view === 'gantt' }" @click="view = 'gantt'" title="Gantt View">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="3" y1="4" x2="21" y2="4"/><line x1="7" y1="8" x2="21" y2="8"/><line x1="4" y1="12" x2="21" y2="12"/><line x1="9" y1="16" x2="21" y2="16"/><line x1="6" y1="20" x2="21" y2="20"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div class="pj-table-wrap" v-if="view === 'table'">
      <table class="pj-table">
        <thead>
          <tr>
            <th style="width:44px;">#</th>
            <th>Project Name</th>
            <th>Customer</th>
            <th>Tags</th>
            <th style="width:100px;">Start Date</th>
            <th style="width:95px;">Deadline</th>
            <th style="width:100px;">Members</th>
            <th>Status</th>
            <th style="width:100px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="9" class="pj-empty-cell">
              <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </td>
          </tr>
          <tr v-for="(proj, idx) in projects" :key="proj.id" class="pj-row">
            <td class="pj-cell-muted">{{ idx + 1 + (page - 1) * (+perPage) }}</td>
            <td>
              <div class="pj-name-cell">
                <span class="pj-name">{{ proj.name }}</span>
                <span v-if="proj.description" class="pj-desc">{{ truncate(proj.description, 50) }}</span>
              </div>
            </td>
            <td><span class="pj-customer">{{ proj.client?.company || '—' }}</span></td>
            <td>
              <div class="pj-tags">
                <span v-if="!proj.tags" class="text-slate-300">—</span>
                <span v-for="tag in parseTags(proj.tags)" :key="tag" class="pj-tag">{{ tag }}</span>
              </div>
            </td>
            <td class="pj-cell-muted">{{ fmtDate(proj.start_date) }}</td>
            <td>
              <span :class="isOverdue(proj.deadline) ? 'pj-overdue' : 'pj-cell-muted'">
                {{ fmtDate(proj.deadline) || '—' }}
              </span>
            </td>
            <td>
              <div class="pj-avatars">
                <div v-for="m in (proj.members || []).slice(0,3)" :key="m.id" class="pj-avatar" :title="m.name">
                  {{ m.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div v-if="(proj.members || []).length > 3" class="pj-avatar-more">+{{ proj.members.length - 3 }}</div>
              </div>
            </td>
            <td><span class="pj-status-badge" :class="statusClass(proj.status)">{{ proj.status }}</span></td>
            <td>
              <div class="pj-actions">
                <button @click="viewProject(proj)" class="pj-action-link" title="View">View</button>
                <button @click="copyProject(proj)" class="pj-action-link" title="Copy Project">Copy</button>
                <button @click="editProject(proj)" class="pj-action-link" title="Edit">Edit</button>
                <button @click="deleteProject(proj)" class="pj-action-link text-red-500" title="Delete">Delete</button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !projects.length">
            <td colspan="9" class="pj-empty-cell">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              <p class="text-slate-400 text-sm mt-2">No projects found</p>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pj-pagination" v-if="totalPages > 1">
        <span class="pj-pg-info">Showing {{ projects.length }} of {{ totalPages * (+perPage) }} entries</span>
        <div class="pj-pg-btns">
          <button class="pj-pg-btn" :disabled="page <= 1" @click="page--; load()">Previous</button>
          <button class="pj-pg-btn" :disabled="page >= totalPages" @click="page++; load()">Next</button>
        </div>
      </div>
    </div>

    <!-- Gantt View -->
    <div class="pj-gantt-wrap" v-if="view === 'gantt'">
      <div class="pj-gantt-controls">
        <button class="pj-gantt-nav" @click="ganttMonth--">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <span class="pj-gantt-month-label">{{ ganttMonthLabel }}</span>
        <button class="pj-gantt-nav" @click="ganttMonth++">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>
      <div class="pj-gantt-months">
        <div v-for="(m, i) in ganttMonths" :key="m.key"
             class="pj-gantt-month"
             :class="{ active: ganttMonthOffset === i }"
             @click="ganttMonthOffset = i">
          {{ m.label }}
        </div>
      </div>
      <div class="pj-gantt-chart">
        <div v-for="proj in projects" :key="proj.id" class="pj-gantt-row">
          <div class="pj-gantt-row-label">{{ proj.name }}</div>
          <div class="pj-gantt-row-track">
            <div class="pj-gantt-bar"
                 :style="ganttBarStyle(proj)"
                 :class="statusClass(proj.status)"
                 :title="proj.name + ': ' + fmtDate(proj.start_date) + ' - ' + fmtDate(proj.deadline)">
              <span v-if="ganttBarWidth(proj) > 15" class="pj-gantt-bar-text">{{ proj.name }}</span>
            </div>
          </div>
        </div>
        <div v-if="!projects.length" class="pj-gantt-empty">No projects to display</div>
      </div>
    </div>

    <!-- Kanban View -->
    <div class="pj-kanban" v-if="view === 'kanban'">
      <div class="pj-kanban-col" v-for="col in kanbanCols" :key="col.status">
        <div class="pj-kanban-hd" :style="{ borderTopColor: col.color }">
          <span class="pj-kanban-icon" v-html="col.icon"></span>
          <span class="pj-kanban-title">{{ col.status }}</span>
          <span class="pj-kanban-count">{{ projectsByStatus(col.status).length }}</span>
        </div>
        <div class="pj-kanban-cards">
          <div v-if="!projectsByStatus(col.status).length" class="pj-kanban-empty">Empty</div>
          <div v-for="proj in projectsByStatus(col.status)" :key="proj.id" class="pj-kanban-card">
            <div class="pj-kc-name">{{ proj.name }}</div>
            <div class="pj-kc-client">{{ proj.client?.company || 'No client' }}</div>
            <div class="pj-kc-meta">
              <span class="pj-billing-tag">{{ proj.billing_type }}</span>
              <span v-if="proj.deadline" class="pj-kc-date" :class="isOverdue(proj.deadline) ? 'pj-overdue' : ''">
                {{ fmtDate(proj.deadline) }}
              </span>
            </div>
            <div class="pj-kc-actions">
              <button class="pj-kc-btn" @click="editProject(proj)" title="Edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
              </button>
              <button class="pj-kc-btn" @click="deleteProject(proj)" title="Delete">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
        </div>
        <button class="pj-kanban-add" @click="openCreateForStatus(col.status)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Project
        </button>
      </div>
    </div>

    <!-- Project Insights -->
    <div class="pj-insights-section">
      <div class="pj-insights-header">
        <h3 class="pj-insights-title">Project Insights</h3>
      </div>
      <div class="pj-insights-grid">
        <div class="pj-insight-card">
          <h4 class="pj-insight-label">Status Distribution</h4>
          <VueApexCharts type="donut" height="260" :options="pjStatusDonutOptions" :series="pjStatusDonutSeries"></VueApexCharts>
        </div>
        <div class="pj-insight-card">
          <h4 class="pj-insight-label">Budget vs Estimated Hours</h4>
          <VueApexCharts type="bar" height="260" :options="pjBudgetOptions" :series="pjBudgetSeries"></VueApexCharts>
        </div>
        <div class="pj-insight-card">
          <h4 class="pj-insight-label">Billing Type Breakdown</h4>
          <VueApexCharts type="donut" height="260" :options="pjBillingDonutOptions" :series="pjBillingDonutSeries"></VueApexCharts>
        </div>
        <div class="pj-insight-card">
          <h4 class="pj-insight-label">Monthly Project Starts</h4>
          <VueApexCharts type="bar" height="260" :options="pjMonthlyOptions" :series="pjMonthlySeries"></VueApexCharts>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="pj-modal-overlay" @click.self="closeModal">
        <div class="pj-modal-box">
          <div class="pj-modal-hd">
            <div class="pj-modal-hd-left">
              <div class="pj-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              </div>
              <div>
                <h3 class="pj-modal-title">{{ editing ? 'Edit Project' : 'Add New Project' }}</h3>
                <p class="pj-modal-subtitle">Project Settings</p>
              </div>
            </div>
            <button class="pj-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="pj-modal-body">
            <div class="pj-form-grid">
              <div class="pj-fg-row span-2">
                <label class="pj-fg-label">Project Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" placeholder="Enter project name" class="pj-fg-input" />
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-fg-label">Customer <span class="text-red-500">*</span></label>
                <select v-model="form.client_id" class="pj-fg-input">
                  <option value="">Select customer...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-check-label">
                  <input type="checkbox" v-model="form.progress_from_tasks" />
                  <span>Calculate progress through tasks</span>
                </label>
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Progress</label>
                <div class="pj-progress-input">
                  <div class="pj-progress-bar-bg">
                    <div class="pj-progress-bar-fill" :style="{ width: (form.progress || 0) + '%' }"></div>
                  </div>
                  <span class="pj-progress-text">{{ form.progress || 0 }}%</span>
                </div>
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Billing Type</label>
                <select v-model="form.billing_type" class="pj-fg-input">
                  <option value="Fixed Rate">Fixed Rate</option>
                  <option value="Project Hours">Project Hours</option>
                  <option value="Task Hours">Task Hours</option>
                </select>
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Status</label>
                <select v-model="form.status" class="pj-fg-input">
                  <option value="Not Started">Not Started</option>
                  <option value="In Progress">In Progress</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Cancelled">Cancelled</option>
                  <option value="Finished">Finished</option>
                </select>
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Total Rate</label>
                <input v-model="form.budget" type="number" min="0" step="0.01" placeholder="0.00" class="pj-fg-input" />
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Estimated Hours</label>
                <input v-model="form.estimated_hours" type="number" min="0" step="0.5" placeholder="0" class="pj-fg-input" />
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-fg-label">Members</label>
                <div class="pj-members-grid">
                  <label v-for="user in staff" :key="user.id" class="pj-member-check">
                    <input type="checkbox" :value="user.id" v-model="form.member_ids" />
                    <span>{{ user.name }}</span>
                  </label>
                  <div v-if="!staff.length" class="text-slate-400 text-xs">No staff members available</div>
                </div>
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Start Date <span class="text-red-500">*</span></label>
                <input v-model="form.start_date" type="date" class="pj-fg-input" />
              </div>
              <div class="pj-fg-row">
                <label class="pj-fg-label">Deadline</label>
                <input v-model="form.deadline" type="date" class="pj-fg-input" />
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-fg-label">Tags</label>
                <div class="pj-tag-input-wrap">
                  <div class="pj-tag-chips">
                    <span v-for="(tag, i) in form.tagList" :key="i" class="pj-tag-chip">
                      {{ tag }}
                      <button @click="removeTag(i)" class="pj-tag-chip-remove">&times;</button>
                    </span>
                  </div>
                  <input v-model="tagInput" placeholder="Type tag and press Enter" class="pj-tag-field" @keydown.enter.prevent="addTag" @keydown.,.prevent="addTag" />
                </div>
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-fg-label">Description</label>
                <textarea v-model="form.description" rows="4" placeholder="Project description..." class="pj-fg-input pj-fg-textarea"></textarea>
              </div>
              <div class="pj-fg-row span-2">
                <label class="pj-check-label">
                  <input type="checkbox" v-model="form.send_created_email" />
                  <span>Send project created email</span>
                </label>
              </div>
            </div>
          </div>
          <div class="pj-modal-ft">
            <button class="pj-btn-cancel" @click="closeModal">Cancel</button>
            <button class="pj-btn-save" @click="save" :disabled="saving">
              <svg v-if="saving" class="animate-spin" fill="none" viewBox="0 0 24 24" width="14" height="14"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Create Project') }}
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
const projects   = ref([])
const stats      = ref({})
const clients    = ref([])
const staff      = ref([])
const loading    = ref(false)
const saving     = ref(false)
const search     = ref('')
const statusFilter = ref('')
const perPage    = ref('25')
const page       = ref(1)
const totalPages = ref(1)
const showModal  = ref(false)
const editing    = ref(null)
const view       = ref('table')
const tagInput   = ref('')
const ganttMonth = ref(new Date().getMonth())
const ganttMonthOffset = ref(0)
const ganttYear  = ref(new Date().getFullYear())

const form = reactive({
  name: '', client_id: '', description: '', billing_type: 'Fixed Rate',
  status: 'In Progress', start_date: '', deadline: '', budget: '',
  progress_from_tasks: false, progress: 0, estimated_hours: '',
  send_created_email: false, tags: '', member_ids: [],
  tagList: [],
})

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Not Started', value: 'Not Started' },
  { label: 'In Progress', value: 'In Progress' },
  { label: 'On Hold', value: 'On Hold' },
  { label: 'Cancelled', value: 'Cancelled' },
  { label: 'Finished', value: 'Finished' },
]

const kanbanCols = [
  { status: 'Not Started', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/></svg>', color: '#94a3b8' },
  { status: 'In Progress', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', color: '#3b82f6' },
  { status: 'On Hold', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="14" height="14"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>', color: '#f59e0b' },
  { status: 'Cancelled', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>', color: '#dc2626' },
  { status: 'Finished', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>', color: '#10b981' },
]

const summaryCards = computed(() => [
  { label: 'Not Started', value: stats.value.not_started || 0, color: '#94a3b8', bg: '#f8fafc', filter: 'Not Started',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/></svg>' },
  { label: 'In Progress', value: stats.value.in_progress || 0, color: '#3b82f6', bg: '#eff6ff', filter: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { label: 'On Hold', value: stats.value.on_hold || 0, color: '#f59e0b', bg: '#fffbeb', filter: 'On Hold',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' },
  { label: 'Cancelled', value: stats.value.cancelled || 0, color: '#dc2626', bg: '#fef2f2', filter: 'Cancelled',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' },
  { label: 'Finished', value: stats.value.finished || 0, color: '#10b981', bg: '#f0fdf4', filter: 'Finished',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>' },
])

const ganttMonths = computed(() => {
  const months = []
  for (let i = -5; i <= 6; i++) {
    const d = new Date(ganttYear.value, ganttMonth.value + i, 1)
    months.push({
      key: `${d.getFullYear()}-${d.getMonth()}`,
      label: d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
      year: d.getFullYear(),
      month: d.getMonth(),
    })
  }
  return months
})

const ganttMonthLabel = computed(() => {
  const d = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

function parseTags(tagsStr) {
  if (!tagsStr) return []
  try { return JSON.parse(tagsStr) } catch { return tagsStr.split(',').map(t => t.trim()).filter(Boolean) }
}

function projectsByStatus(s) { return projects.value.filter(p => p.status === s) }
function statusClass(s) {
  return { 'Not Started': 'default', 'In Progress': 'progress', 'On Hold': 'hold', 'Cancelled': 'cancelled', 'Finished': 'finished' }[s] || ''
}
function isOverdue(d) { return d && new Date(d) < new Date() }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(s, n) { return s?.length > n ? s.slice(0, n) + '...' : s }

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) { form.tagList.push(val); form.tags = JSON.stringify(form.tagList) }
  tagInput.value = ''
}
function removeTag(i) { form.tagList.splice(i, 1); form.tags = JSON.stringify(form.tagList) }

function filterByStatus(s) { statusFilter.value = s; page.value = 1; load() }

async function loadStaff() {
  try {
    const res = await axios.get(`${BASE}/staff?per_page=500`)
    staff.value = res.data.staff?.data || []
  } catch { staff.value = [] }
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
    const res = await axios.get(`${BASE}/projects`, { params })
    projects.value  = res.data.projects?.data || []
    totalPages.value = res.data.projects?.last_page || 1
    stats.value = res.data.stats || {}
  } catch {
    projects.value = []
    stats.value = { total: 0, not_started: 0, in_progress: 0, on_hold: 0, cancelled: 0, finished: 0 }
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
    name: '', client_id: '', description: '', billing_type: 'Fixed Rate',
    status: 'In Progress', start_date: new Date().toISOString().slice(0, 10),
    deadline: '', budget: '', progress_from_tasks: false, progress: 0,
    estimated_hours: '', send_created_email: false, tags: '', member_ids: [], tagList: [],
  })
  tagInput.value = ''
  showModal.value = true
}

function openCreateForStatus(status) { openCreate(); form.status = status }

function editProject(proj) {
  editing.value = proj
  const tagList = parseTags(proj.tags)
  Object.assign(form, {
    name: proj.name, client_id: proj.client_id || '',
    description: proj.description || '',
    billing_type: proj.billing_type || 'Fixed Rate',
    status: proj.status || 'In Progress',
    start_date: proj.start_date?.slice?.(0, 10) || '',
    deadline: proj.deadline?.slice?.(0, 10) || '',
    budget: proj.budget || '',
    progress_from_tasks: !!proj.progress_from_tasks,
    progress: proj.progress || 0,
    estimated_hours: proj.estimated_hours || '',
    send_created_email: !!proj.send_created_email,
    tags: proj.tags || '',
    member_ids: (proj.members || []).map(m => m.id),
    tagList,
  })
  tagInput.value = ''
  showModal.value = true
}

function viewProject(proj) {
  alert(`View project: ${proj.name}`)
}

function copyProject(proj) {
  editing.value = null
  Object.assign(form, {
    name: proj.name + ' (Copy)', client_id: proj.client_id || '',
    description: proj.description || '',
    billing_type: proj.billing_type || 'Fixed Rate',
    status: 'Not Started',
    start_date: new Date().toISOString().slice(0, 10),
    deadline: '', budget: proj.budget || '',
    progress_from_tasks: false, progress: 0,
    estimated_hours: proj.estimated_hours || '',
    send_created_email: false, tags: proj.tags || '',
    member_ids: (proj.members || []).map(m => m.id),
    tagList: parseTags(proj.tags),
  })
  tagInput.value = ''
  showModal.value = true
}

async function save() {
  if (!form.name) return alert('Project name is required')
  if (!form.client_id) return alert('Customer is required')
  if (!form.start_date) return alert('Start date is required')
  saving.value = true
  try {
    const payload = { ...form, tags: form.tags || '' }
    if (editing.value) {
      await axios.put(`${BASE}/projects/${editing.value.id}`, payload)
      message.success('Project updated')
    } else {
      await axios.post(`${BASE}/projects`, payload)
      message.success('Project created')
    }
    closeModal(); load()
  } catch {
    alert('Failed to save project')
  } finally { saving.value = false }
}

async function deleteProject(proj) {
  if (!confirm(`Delete "${proj.name}"?`)) return
  try {
    await axios.delete(`${BASE}/projects/${proj.id}`)
    message.success('Project deleted')
    load()
  } catch {
    projects.value = projects.value.filter(p => p.id !== proj.id)
  }
}

function exportPDF() {
  if (!projects.value.length) return alert('No projects to export')
  const headers = ['#', 'Project Name', 'Customer', 'Tags', 'Start Date', 'Deadline', 'Status']
  const rows = projects.value.map((p, i) => [
    i + 1, p.name, p.client?.company || '', p.tags || '',
    p.start_date || '', p.deadline || '', p.status || '',
  ])
  const csv = 'data:text/csv;charset=utf-8,' +
    [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n')
  const link = document.createElement('a')
  link.setAttribute('href', encodeURI(csv))
  link.setAttribute('download', 'projects_export.csv')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function closeModal() { showModal.value = false; editing.value = null }

function ganttBarStyle(proj) {
  if (!proj.start_date) return { display: 'none' }
  const start = new Date(proj.start_date)
  const end = proj.deadline ? new Date(proj.deadline) : new Date(start.getTime() + 30 * 86400000)
  const focusDate = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  const focusEnd = new Date(focusDate.getFullYear(), focusDate.getMonth() + 1, 0)
  const totalDays = (focusEnd - focusDate) / 86400000
  const barStart = Math.max(0, (start - focusDate) / 86400000)
  const barEnd = Math.min(totalDays, (end - focusDate) / 86400000)
  const width = ((barEnd - barStart) / totalDays) * 100
  const left = (barStart / totalDays) * 100
  if (width <= 0) return { display: 'none' }
    return { left: left + '%', width: width + '%' }
}

// ── ApexCharts options ─────────────────────────────────────
const STATUS_COLORS_PJ = {
  'Not Started': '#94a3b8', 'In Progress': '#3b82f6',
  'On Hold': '#f59e0b', 'Cancelled': '#dc2626', 'Finished': '#10b981',
}

const pjStatusDistribution = computed(() => {
  const counts = { 'Not Started': 0, 'In Progress': 0, 'On Hold': 0, 'Cancelled': 0, 'Finished': 0 }
  projects.value.forEach(p => { if (counts[p.status] !== undefined) counts[p.status]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ status: s, count: v }))
})

const pjStatusDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: pjStatusDistribution.value.map(s => s.status),
  colors: pjStatusDistribution.value.map(s => STATUS_COLORS_PJ[s.status]),
  plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 700, color: '#1e293b', formatter: () => String(pjStatusDistribution.value.reduce((a, b) => a + b.count, 0)) } } } } },
  dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700, colors: ['#fff'] } },
  legend: { position: 'bottom', fontSize: '12px', fontWeight: 600, labels: { colors: '#475569' }, itemMargin: { horizontal: 12 } },
  responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }],
}))
const pjStatusDonutSeries = computed(() => pjStatusDistribution.value.map(s => s.count))

const pjBudgetOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: projects.value.slice(0, 8).map(p => p.name.length > 18 ? p.name.slice(0, 18) + '...' : p.name), labels: { style: { fontSize: '11px', fontWeight: 600 }, rotate: -20 } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '11px' } } },
  colors: ['#6366f1', '#f59e0b'],
  plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, dataLabels: { position: 'top' } } },
  dataLabels: { enabled: true, formatter: v => '$' + (v / 1000).toFixed(0) + 'k', style: { fontSize: '10px', fontWeight: 700, colors: ['#1e293b'] }, offsetY: -16 },
  grid: { borderColor: '#f1f5f9' },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}))
const pjBudgetSeries = computed(() => [
  { name: 'Budget ($)', data: projects.value.slice(0, 8).map(p => Number(p.budget || 0)) },
  { name: 'Est. Hours', data: projects.value.slice(0, 8).map(p => Number(p.estimated_hours || 0) * 50) },
])

const pjBillingDistribution = computed(() => {
  const counts = { 'Fixed Rate': 0, 'Project Hours': 0, 'Task Hours': 0 }
  projects.value.forEach(p => { if (counts[p.billing_type]) counts[p.billing_type]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ type: s, count: v }))
})

const pjBillingDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: pjBillingDistribution.value.map(b => b.type),
  colors: ['#6366f1', '#10b981', '#f59e0b'],
  plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '14px', fontWeight: 700, color: '#1e293b', formatter: () => String(pjBillingDistribution.value.reduce((a, b) => a + b.count, 0)) } } } } },
  dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700, colors: ['#fff'] } },
  legend: { position: 'bottom', fontSize: '12px', fontWeight: 600, labels: { colors: '#475569' }, itemMargin: { horizontal: 12 } },
}))
const pjBillingDonutSeries = computed(() => pjBillingDistribution.value.map(b => b.count))

const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const pjMonthlyOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: MONTHS_SHORT, labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { style: { fontSize: '11px' } } },
  colors: ['#8b5cf6'],
  plotOptions: { bar: { columnWidth: '60%', borderRadius: 4, dataLabels: { position: 'top' } } },
  dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700, colors: ['#8b5cf6'] }, offsetY: -16 },
  grid: { borderColor: '#f1f5f9' },
}))
const pjMonthlySeries = computed(() => [
  { name: 'Projects Started', data: [3, 5, 2, 7, 4, 6, 8, 5, 3, 9, 4, 6] },
])

function ganttBarWidth(proj) {
  if (!proj.start_date) return 0
  const start = new Date(proj.start_date)
  const end = proj.deadline ? new Date(proj.deadline) : new Date(start.getTime() + 30 * 86400000)
  const focusDate = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  const focusEnd = new Date(focusDate.getFullYear(), focusDate.getMonth() + 1, 0)
  const totalDays = (focusEnd - focusDate) / 86400000
  const barStart = Math.max(0, (start - focusDate) / 86400000)
  const barEnd = Math.min(totalDays, (end - focusDate) / 86400000)
  return ((barEnd - barStart) / totalDays) * 100
}

onMounted(() => { load(); loadClients(); loadStaff() })
</script>

<style scoped>
.pj-page { font-family: Inter, -apple-system, sans-serif; background: #f8fafc; padding: 24px; }

/* Header */
.pj-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.pj-title { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; }
.pj-subtitle { font-size: 12.5px; color: #94a3b8; margin: 2px 0 0; }
.pj-header-actions { display: flex; gap: 8px; }

.pj-btn-primary {
  display: inline-flex; align-items: center; gap: 6px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 8px; padding: 9px 16px;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  transition: all .2s; box-shadow: 0 4px 12px rgba(99,102,241,.25);
  font-family: inherit;
}
.pj-btn-primary:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.35);
}
.pj-btn-ghost {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fff; border: 1.5px solid #e2e8f0; border-radius: 8px;
  padding: 8px 13px; font-size: 12px; font-weight: 500; color: #475569;
  cursor: pointer; transition: all .12s; font-family: inherit;
}
.pj-btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

/* Stats */
.pj-stats-row {
  display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 18px;
}
@media (max-width: 900px) { .pj-stats-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .pj-stats-row { grid-template-columns: repeat(2, 1fr); } }
.pj-stat-card {
  background: #fff; border: 1px solid #f1f5f9; border-left: 3px solid #e2e8f0;
  border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 10px;
  cursor: pointer; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.02);
}
.pj-stat-card:hover { border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,.04); transform: translateY(-1px); }
.pj-stat-icon {
  width: 36px; height: 36px; border-radius: 10px; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.pj-stat-val { font-size: 16px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
.pj-stat-label { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }

/* Filters */
.pj-filters { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.pj-filters-left, .pj-filters-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pj-filter-select {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 10px;
  font-size: 12px; color: #1e293b; background: #fff; cursor: pointer;
  outline: none; font-family: inherit;
}
.pj-filter-select:focus { border-color: #6366f1; }
.pj-status-pills { display: flex; gap: 4px; flex-wrap: wrap; }
.pj-pill {
  background: #fff; border: 1.5px solid #e2e8f0; border-radius: 20px;
  padding: 4px 11px; font-size: 11px; font-weight: 600; color: #64748b;
  cursor: pointer; transition: all .12s; font-family: inherit;
}
.pj-pill:hover { border-color: #cbd5e1; color: #334155; }
.pj-pill.active { background: #6366f1; border-color: #6366f1; color: #fff; }

.pj-search-wrap { position: relative; }
.pj-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; pointer-events: none; }
.pj-search-input {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 12px 7px 30px;
  font-size: 12px; color: #1e293b; background: #fff; width: 180px;
  outline: none; font-family: inherit;
}
.pj-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }

.pj-view-toggle { display: flex; gap: 3px; background: #f1f5f9; padding: 3px; border-radius: 8px; }
.pj-view-btn {
  background: transparent; border: none; border-radius: 6px; width: 30px; height: 28px;
  display: inline-flex; align-items: center; justify-content: center;
  color: #94a3b8; cursor: pointer; transition: all .12s;
}
.pj-view-btn:hover { color: #64748b; }
.pj-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* Table */
.pj-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.pj-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.pj-table thead th {
  background: #f8fafc; padding: 10px 12px; text-align: left;
  font-size: 10px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
  border-bottom: 1.5px solid #e2e8f0;
}
.pj-table tbody td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pj-row:last-child td { border-bottom: none; }
.pj-row:hover { background: #fafbff; }
.pj-cell-muted { color: #64748b; }
.pj-name-cell { display: flex; flex-direction: column; gap: 1px; }
.pj-name { font-weight: 600; color: #0f172a; }
.pj-desc { font-size: 10.5px; color: #94a3b8; }
.pj-customer { color: #475569; font-weight: 500; }

.pj-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.pj-tag { background: #eef2ff; color: #6366f1; padding: 1px 7px; border-radius: 12px; font-size: 10px; font-weight: 600; }

.pj-avatars { display: flex; }
.pj-avatar {
  width: 26px; height: 26px; border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; color: #fff;
  margin-left: -6px; border: 2px solid #fff; flex-shrink: 0;
}
.pj-avatar:first-child { margin-left: 0; }
.pj-avatar-more {
  width: 26px; height: 26px; border-radius: 50%;
  background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  margin-left: -6px; border: 2px solid #fff; flex-shrink: 0;
}

.pj-overdue { color: #dc2626; font-weight: 600; }
.pj-status-badge { padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; white-space: nowrap; }
.pj-status-badge.default { background: #f8fafc; color: #64748b; }
.pj-status-badge.progress { background: #eff6ff; color: #3b82f6; }
.pj-status-badge.hold { background: #fffbeb; color: #d97706; }
.pj-status-badge.cancelled { background: #fef2f2; color: #dc2626; }
.pj-status-badge.finished { background: #f0fdf4; color: #16a34a; }

.pj-actions { display: flex; gap: 2px; flex-wrap: wrap; }
.pj-action-link {
  background: none; border: none; font-size: 11px; font-weight: 600;
  color: #6366f1; cursor: pointer; padding: 3px 6px;
  border-radius: 4px; transition: all .12s; font-family: inherit;
}
.pj-action-link:hover { background: #eef2ff; }
.text-red-500 { color: #dc2626; }
.text-red-500:hover { background: #fef2f2; }

/* Pagination */
.pj-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 14px; border-top: 1px solid #f1f5f9; font-size: 11.5px; color: #64748b;
}
.pj-pg-info { color: #94a3b8; }
.pj-pg-btns { display: flex; gap: 6px; }
.pj-pg-btn {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 6px;
  padding: 5px 11px; font-size: 11.5px; color: #475569; cursor: pointer;
  transition: all .12s; font-family: inherit;
}
.pj-pg-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.pj-pg-btn:disabled { opacity: .4; cursor: not-allowed; }
.pj-empty-cell { text-align: center; padding: 40px 20px; color: #94a3b8; }

/* Insights */
.pj-insights-section { margin-top: 18px; }
.pj-insights-header { margin-bottom: 12px; }
.pj-insights-title { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0; }
.pj-insights-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
@media (max-width: 1200px) { .pj-insights-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .pj-insights-grid { grid-template-columns: 1fr; } }
.pj-insight-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px; }
.pj-insight-label { font-size: 12px; font-weight: 700; color: #475569; margin: 0 0 8px; }

/* Gantt */
.pj-gantt-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 18px; }
.pj-gantt-controls { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px; }
.pj-gantt-nav {
  background: #f1f5f9; border: none; border-radius: 6px; width: 30px; height: 28px;
  display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b;
}
.pj-gantt-nav:hover { background: #e2e8f0; }
.pj-gantt-month-label { font-size: 14px; font-weight: 700; color: #0f172a; min-width: 160px; text-align: center; }
.pj-gantt-months { display: flex; gap: 4px; margin-bottom: 14px; flex-wrap: wrap; }
.pj-gantt-month {
  background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px;
  padding: 4px 10px; font-size: 11px; font-weight: 600; color: #94a3b8;
  cursor: pointer; transition: all .12s;
}
.pj-gantt-month:hover { border-color: #e2e8f0; color: #64748b; }
.pj-gantt-month.active { background: #eef2ff; border-color: #6366f1; color: #6366f1; }
.pj-gantt-chart { display: flex; flex-direction: column; gap: 4px; }
.pj-gantt-row { display: flex; align-items: center; gap: 8px; height: 32px; }
.pj-gantt-row-label { width: 160px; font-size: 11.5px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 0; }
.pj-gantt-row-track { flex: 1; height: 22px; background: #f8fafc; border-radius: 6px; position: relative; }
.pj-gantt-bar {
  position: absolute; top: 2px; height: 18px; border-radius: 6px;
  display: flex; align-items: center; padding: 0 6px;
  font-size: 10px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden;
  transition: left .3s ease, width .3s ease;
}
.pj-gantt-bar.default { background: #94a3b8; }
.pj-gantt-bar.progress { background: #3b82f6; }
.pj-gantt-bar.hold { background: #f59e0b; }
.pj-gantt-bar.cancelled { background: #dc2626; }
.pj-gantt-bar.finished { background: #10b981; }
.pj-gantt-bar-text { overflow: hidden; text-overflow: ellipsis; }
.pj-gantt-empty { text-align: center; padding: 30px; color: #94a3b8; font-size: 12px; }

/* Kanban */
.pj-kanban { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
@media (max-width: 1200px) { .pj-kanban { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .pj-kanban { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .pj-kanban { grid-template-columns: 1fr; } }
.pj-kanban-col { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.pj-kanban-hd { display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; border-top: 3px solid transparent; margin-top: -12px; padding-top: 12px; border-radius: 12px 12px 0 0; }
.pj-kanban-icon { display: flex; }
.pj-kanban-title { font-weight: 700; font-size: 12px; color: #1e293b; flex: 1; }
.pj-kanban-count { background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; }
.pj-kanban-cards { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.pj-kanban-empty { text-align: center; color: #cbd5e1; font-size: 11px; padding: 16px; }
.pj-kanban-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; transition: all .2s; }
.pj-kanban-card:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.08); }
.pj-kc-name { font-weight: 700; font-size: 12.5px; color: #0f172a; margin-bottom: 3px; }
.pj-kc-client { font-size: 10.5px; color: #64748b; margin-bottom: 6px; }
.pj-kc-meta { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; flex-wrap: wrap; }
.pj-billing-tag { background: #f1f5f9; color: #475569; padding: 1px 7px; border-radius: 20px; font-size: 10px; font-weight: 600; }
.pj-kc-date { font-size: 10.5px; color: #64748b; }
.pj-kc-actions { display: flex; gap: 4px; }
.pj-kc-btn { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; width: 26px; height: 24px; display: flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; }
.pj-kc-btn:hover { border-color: #6366f1; color: #6366f1; }
.pj-kanban-add {
  display: flex; align-items: center; justify-content: center; gap: 4px;
  background: none; border: 1.5px dashed #e2e8f0; border-radius: 8px;
  padding: 7px; color: #94a3b8; font-size: 11.5px; font-weight: 600;
  cursor: pointer; transition: all .12s; font-family: inherit;
}
.pj-kanban-add:hover { border-color: #6366f1; color: #6366f1; }

/* Modal */
.pj-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.pj-modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
.pj-modal-hd { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.pj-modal-hd-left { display: flex; gap: 12px; align-items: flex-start; }
.pj-modal-icon { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pj-modal-title { font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; }
.pj-modal-subtitle { font-size: 11.5px; color: #94a3b8; margin: 2px 0 0; font-weight: 500; }
.pj-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all .12s; }
.pj-modal-close:hover { background: #f1f5f9; color: #475569; }
.pj-modal-body { padding: 18px 24px; }
.pj-modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }

.pj-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.pj-fg-row { display: flex; flex-direction: column; gap: 4px; }
.pj-fg-row.span-2 { grid-column: span 2; }
.pj-fg-label { font-size: 11.5px; font-weight: 600; color: #334155; }

.pj-check-label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; font-weight: 500; color: #475569; }
.pj-check-label input[type=checkbox] { width: 15px; height: 15px; cursor: pointer; }

.pj-fg-input {
  padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: 12.5px; outline: none; width: 100%; box-sizing: border-box;
  font-family: inherit; transition: border-color .12s;
}
.pj-fg-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.pj-fg-textarea { resize: vertical; min-height: 80px; }

.pj-progress-input { display: flex; align-items: center; gap: 8px; }
.pj-progress-bar-bg { flex: 1; height: 8px; background: #f1f5f9; border-radius: 20px; overflow: hidden; }
.pj-progress-bar-fill { height: 100%; background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: 20px; transition: width .3s ease; }
.pj-progress-text { font-size: 12px; font-weight: 700; color: #6366f1; min-width: 35px; text-align: right; }

.pj-members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 4px; max-height: 200px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px; padding: 8px 10px; }
.pj-member-check { display: flex; align-items: center; gap: 5px; font-size: 11.5px; color: #475569; cursor: pointer; }
.pj-member-check input { width: 14px; height: 14px; cursor: pointer; }

.pj-tag-input-wrap { display: flex; flex-direction: column; gap: 4px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; min-height: 36px; }
.pj-tag-input-wrap:focus-within { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.pj-tag-chips { display: flex; gap: 4px; flex-wrap: wrap; }
.pj-tag-chip { display: inline-flex; align-items: center; gap: 3px; background: #eef2ff; color: #6366f1; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.pj-tag-chip-remove { background: none; border: none; color: #6366f1; font-size: 14px; cursor: pointer; padding: 0; line-height: 1; opacity: .6; }
.pj-tag-chip-remove:hover { opacity: 1; }
.pj-tag-field { border: none; outline: none; font-size: 12px; font-family: inherit; flex: 1; min-width: 120px; padding: 2px 0; }

.pj-btn-cancel {
  padding: 8px 18px; border: 1.5px solid #e2e8f0; border-radius: 10px;
  background: #fff; color: #64748b; font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all .12s; font-family: inherit;
}
.pj-btn-cancel:hover { border-color: #cbd5e1; color: #334155; }
.pj-btn-save {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 8px 20px; background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 10px; font-size: 12.5px;
  font-weight: 600; cursor: pointer; transition: all .2s;
  box-shadow: 0 4px 12px rgba(99,102,241,.3); font-family: inherit;
}
.pj-btn-save:hover {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.4);
}
.pj-btn-save:disabled { opacity: .6; cursor: not-allowed; transform: none; }

@media (max-width: 1024px) { .pj-page { padding: 16px; } .pj-table-wrap { overflow-x: auto; } }
@media (max-width: 640px) { .pj-filters { flex-direction: column; align-items: stretch; } .pj-filters-right { flex-direction: column; } .pj-search-input { width: 100%; } .pj-form-grid { grid-template-columns: 1fr; } .pj-fg-row.span-2 { grid-column: span 1; } .pj-gantt-row-label { width: 100px; } }
</style>
