<template>
  <div class="tk-page">
    <div class="tk-header">
      <div>
        <h1 class="tk-title">Tasks</h1>
        <p class="tk-subtitle">
          <router-link :to="{ name: 'admin.tasks.overview' }" class="tk-overview-link">
            Tasks Overview
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="9 18 15 12 9 6"/></svg>
          </router-link>
        </p>
      </div>
      <div class="tk-header-actions">
        <div class="tk-view-toggle">
          <button class="tk-view-btn" :class="{ active: currentView === 'kanban' }" @click="currentView='kanban'" title="Kanban">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          </button>
          <button class="tk-view-btn" :class="{ active: currentView === 'table' }" @click="currentView='table'" title="Table">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
        <button class="tk-btn-primary" @click="openCreate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Task
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="tk-stats-row">
      <div v-for="card in summaryCards" :key="card.label" class="tk-stat-card" :style="{ borderLeftColor: card.color }" @click="filterByStatus(card.statusValue)">
        <div class="tk-stat-icon" :style="{ background: card.bg }" v-html="card.icon"></div>
        <div class="tk-stat-info">
          <div class="tk-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="tk-stat-label">{{ card.label }}</div>
          <div v-if="card.myTasks !== undefined" class="tk-stat-mine">My Tasks: {{ card.myTasks }}</div>
        </div>
      </div>
    </div>

    <div v-if="currentView === 'kanban'" class="tk-kanban">
      <div v-for="col in kanbanColumns" :key="col.status" class="tk-kanban-col">
        <div class="tk-kanban-hd" :style="{ borderTopColor: col.color }">
          <span class="tk-kanban-icon" v-html="col.icon"></span>
          <span class="tk-kanban-title">{{ col.title }}</span>
          <span class="tk-kanban-count">{{ tasksByStatus(col.status).length }}</span>
        </div>
        <div class="tk-kanban-cards" :class="{ 'tk-drag-over': dragCol === col.status }"
          @dragover.prevent="dragCol = col.status"
          @dragenter.prevent
          @dragleave="dragCol = null"
          @drop="onDrop(col.status)">
          <div v-for="t in tasksByStatus(col.status)" :key="t.id" class="tk-kanban-card"
            draggable="true"
            @dragstart="onDragStart(t)"
            @dragend="dragCol = null">
            <div class="tk-kc-hd">
              <span class="tk-pri" :class="priClass(t.priority)">{{ t.priority }}</span>
              <button class="tk-kc-menu" @click="openEdit(t)">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
              </button>
            </div>
            <div class="tk-kc-title" @click="openEdit(t)">{{ t.name }}</div>
            <div v-if="t.tags" class="tk-kc-tags">
              <span v-for="tag in parseTags(t.tags)" :key="tag" class="tk-kc-tag">{{ tag }}</span>
            </div>
            <div class="tk-kc-ft">
              <span class="tk-kc-date" :class="isOverdue(t) ? 'tk-overdue' : ''">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ fmtDate(t.due_date) }}
              </span>
              <div class="tk-kc-avatar" :style="{ background: t.assignee ? '#6366f1' : '#e2e8f0' }" :title="t.assignee?.name || 'Unassigned'">
                {{ t.assignee ? t.assignee.name.charAt(0).toUpperCase() : '?' }}
              </div>
            </div>
          </div>
          <div v-if="!tasksByStatus(col.status).length" class="tk-kanban-empty"
            @dragover.prevent="dragCol = col.status"
            @dragenter.prevent
            @dragleave="dragCol = null"
            @drop="onDrop(col.status)">Drop here</div>
        </div>
        <button class="tk-kanban-add" @click="openCreateForStatus(col.status)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add
        </button>
      </div>
    </div>

    <!-- ====== TABLE VIEW ====== -->
    <div v-else>
      <div class="tk-filters">
        <div class="tk-filters-left">
          <select class="tk-filter-select" v-model="perPage" @change="loadTasks">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <select class="tk-filter-select" v-model="filters.priority" @change="loadTasks">
            <option value="">All Priority</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Urgent">Urgent</option>
          </select>
          <select class="tk-filter-select" v-model="filters.assigned_to" @change="loadTasks">
            <option value="">All Staff</option>
            <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="tk-filters-right">
          <div class="tk-search-wrap">
            <svg class="tk-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="filters.search" placeholder="Search tasks..." class="tk-search-input" @input="onSearch" />
          </div>
        </div>
      </div>
      <div class="tk-table-wrap">
        <table class="tk-table">
          <thead>
            <tr>
              <th style="width:44px;">#</th>
              <th>Name</th>
              <th style="width:110px;">Status</th>
              <th style="width:100px;">Start Date</th>
              <th style="width:100px;">Due Date</th>
              <th>Assigned to</th>
              <th>Tags</th>
              <th style="width:80px;">Priority</th>
              <th style="width:120px;"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="9" class="tk-empty-cell">
                <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              </td>
            </tr>
            <tr v-for="(t, idx) in tasks" :key="t.id" class="tk-row">
              <td class="tk-cell-muted">{{ idx + 1 + (page - 1) * (+perPage) }}</td>
              <td>
                <div class="tk-name-cell">
                  <span class="tk-name">{{ t.name }}</span>
                  <span v-if="t.description" class="tk-desc">{{ truncate(t.description, 55) }}</span>
                </div>
              </td>
              <td><span class="tk-status-badge" :class="statusClass(t.status)">{{ t.status }}</span></td>
              <td class="tk-cell-muted">{{ fmtDate(t.start_date) }}</td>
              <td :class="isOverdue(t) ? 'tk-overdue' : 'tk-cell-muted'">{{ fmtDate(t.due_date) }}</td>
              <td class="tk-cell-muted">
                <div class="tk-assignee-cell">
                  <div class="tk-avatar-sm" :style="{ background: t.assignee ? '#6366f1' : '#e2e8f0' }">
                    {{ t.assignee ? t.assignee.name.charAt(0).toUpperCase() : '?' }}
                  </div>
                  <span>{{ t.assignee?.name || '—' }}</span>
                </div>
              </td>
              <td>
                <div class="tk-tags">
                  <span v-if="!t.tags" class="text-slate-300">—</span>
                  <span v-for="tag in parseTags(t.tags)" :key="tag" class="tk-tag">{{ tag }}</span>
                </div>
              </td>
              <td><span class="tk-pri" :class="priClass(t.priority)">{{ t.priority }}</span></td>
              <td>
                <div class="tk-row-actions">
                  <button class="tk-act-btn" @click="toggleTimer(t)" :title="t.timerRunning ? 'Stop Timer' : 'Start Timer'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><polyline v-if="!t.timerRunning" points="10 8 16 12 10 16 10 8"/><rect v-else x="9" y="9" width="6" height="6"/></svg>
                    {{ t.timerRunning ? 'Stop' : 'Start' }}
                  </button>
                  <button class="tk-act-btn-icon" @click="openEdit(t)" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button class="tk-act-btn-icon hover:text-rose-600" @click="deleteTask(t.id)" title="Delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && !tasks.length">
              <td colspan="9" class="tk-empty-cell">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                <p class="text-slate-400 text-sm mt-2">No tasks found</p>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="tk-pagination" v-if="totalPages > 1">
          <span class="tk-pg-info">Showing {{ tasks.length }} of {{ totalPages * (+perPage) }} entries</span>
          <div class="tk-pg-btns">
            <button class="tk-pg-btn" :disabled="page <= 1" @click="page--; loadTasks()">Previous</button>
            <button class="tk-pg-btn" :disabled="page >= totalPages" @click="page++; loadTasks()">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="tk-modal-overlay" @click.self="closeModal">
        <div class="tk-modal-box">
          <div class="tk-modal-hd">
            <div class="tk-modal-hd-left">
              <div class="tk-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              </div>
              <div>
                <h3 class="tk-modal-title">{{ editingId ? 'Edit Task' : 'Add New Task' }}</h3>
                <p class="tk-modal-subtitle">Fill in the task details below</p>
              </div>
            </div>
            <button class="tk-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="tk-modal-body">
            <div class="tk-form-grid">
              <div class="tk-fg-checkboxes">
                <label class="tk-cb-label">
                  <input type="checkbox" v-model="form.is_public" />
                  <span>Public</span>
                </label>
                <label class="tk-cb-label">
                  <input type="checkbox" v-model="form.billable" />
                  <span>Billable</span>
                </label>
                <label class="tk-cb-label tk-cb-file">
                  <span>Attach Files</span>
                  <input type="file" multiple class="tk-file-input" @change="onFileChange" />
                </label>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Subject <span class="text-red-500">*</span></label>
                <input v-model="form.name" placeholder="Enter task subject" class="tk-fg-input" />
              </div>
              <div class="tk-fg-row">
                <label class="tk-fg-label">Hourly Rate</label>
                <input v-model="form.hourly_rate" type="number" min="0" step="0.01" placeholder="0" class="tk-fg-input" />
              </div>
              <div class="tk-fg-row">
                <label class="tk-fg-label">Start Date <span class="text-red-500">*</span></label>
                <input v-model="form.start_date" type="date" class="tk-fg-input" />
              </div>
              <div class="tk-fg-row">
                <label class="tk-fg-label">Due Date</label>
                <input v-model="form.due_date" type="date" class="tk-fg-input" />
              </div>
              <div class="tk-fg-row">
                <label class="tk-fg-label">Priority</label>
                <select v-model="form.priority" class="tk-fg-input">
                  <option value="Low">Low</option>
                  <option value="Medium">Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>
              <div class="tk-fg-row">
                <label class="tk-fg-label">Repeat every</label>
                <select v-model="form.repeat_every" class="tk-fg-input">
                  <option value="">No repeat</option>
                  <option value="1 week">1 Week</option>
                  <option value="2 weeks">2 Weeks</option>
                  <option value="1 month">1 Month</option>
                  <option value="3 months">3 Months</option>
                </select>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Related To</label>
                <div class="tk-rel-grid">
                  <select v-model="form.related_to_type" class="tk-fg-input">
                    <option value="">Select type...</option>
                    <option value="Customer">Customer</option>
                    <option value="Project">Project</option>
                    <option value="Invoice">Invoice</option>
                    <option value="Lead">Lead</option>
                  </select>
                  <input v-model="form.related_to_id" type="number" placeholder="ID..." class="tk-fg-input" />
                </div>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Assignees</label>
                <div class="tk-multi-grid">
                  <label v-for="u in staff" :key="u.id" class="tk-multi-cb">
                    <input type="checkbox" :value="u.id" v-model="form.assignees" />
                    <span>{{ u.name }}</span>
                  </label>
                  <div v-if="!staff.length" class="text-slate-400 text-xs">No staff available</div>
                </div>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Followers</label>
                <div class="tk-multi-grid">
                  <label v-for="u in staff" :key="u.id" class="tk-multi-cb">
                    <input type="checkbox" :value="u.id" v-model="form.followers" />
                    <span>{{ u.name }}</span>
                  </label>
                  <div v-if="!staff.length" class="text-slate-400 text-xs">No staff available</div>
                </div>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Tags</label>
                <div class="tk-tag-wrap">
                  <div class="tk-tag-chips">
                    <span v-for="(tag, i) in form.tagList" :key="i" class="tk-tag-chip">{{ tag }}<button @click="removeTag(i)" class="tk-tag-x">&times;</button></span>
                  </div>
                  <input v-model="tagInput" placeholder="Type and press Enter" class="tk-tag-field" @keydown.enter.prevent="addTag" @keydown.,.prevent="addTag" />
                </div>
              </div>
              <div class="tk-fg-row span-2">
                <label class="tk-fg-label">Task Description</label>
                <textarea v-model="form.description" rows="4" placeholder="Add description..." class="tk-fg-input tk-fg-textarea"></textarea>
              </div>
            </div>
          </div>
          <div class="tk-modal-ft">
            <button class="tk-btn-cancel" @click="closeModal">Cancel</button>
            <button class="tk-btn-save" @click="saveTask" :disabled="saving">
              <svg v-if="saving" class="animate-spin" fill="none" viewBox="0 0 24 24" width="14" height="14"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ saving ? 'Saving...' : (editingId ? 'Save Changes' : 'Create Task') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'

const BASE = '/api'
const tasks = ref([])
const stats = ref({})
const staff = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const perPage = ref('25')
const page = ref(1)
const totalPages = ref(1)
const showModal = ref(false)
const editingId = ref(null)
const currentView = ref('kanban')
const tagInput = ref('')
const attachedFiles = ref([])
const dragCol = ref(null)
const dragTaskId = ref(null)

const filters = reactive({ search: '', priority: '', assigned_to: '' })

const form = reactive({
  name: '', description: '', priority: 'Medium', status: 'Not Started',
  start_date: '', due_date: '', assigned_to: null,
  related_to_type: '', related_to_id: '',
  billable: false, is_public: false, hourly_rate: 0,
  repeat_every: '', tags: '', assignees: [], followers: [], tagList: [],
})

const kanbanColumns = [
  { title: 'Not Started', status: 'Not Started', color: '#94a3b8', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/></svg>' },
  { title: 'In Progress', status: 'In Progress', color: '#3b82f6', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { title: 'Testing', status: 'Testing', color: '#f59e0b', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="14" height="14"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5"/></svg>' },
  { title: 'Awaiting Feedback', status: 'Awaiting Feedback', color: '#f97316', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" width="14" height="14"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>' },
  { title: 'Complete', status: 'Complete', color: '#22c55e', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>' },
]

const summaryCards = computed(() => [
  { label: 'Not Started', value: stats.value.not_started || 0, myTasks: stats.value.my_not_started || 0, color: '#94a3b8', bg: '#f8fafc', statusValue: 'Not Started',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/></svg>' },
  { label: 'In Progress', value: stats.value.in_progress || 0, myTasks: stats.value.my_in_progress || 0, color: '#3b82f6', bg: '#eff6ff', statusValue: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { label: 'Testing', value: stats.value.testing || 0, myTasks: stats.value.my_testing || 0, color: '#f59e0b', bg: '#fffbeb', statusValue: 'Testing',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5"/></svg>' },
  { label: 'Awaiting Feedback', value: stats.value.awaiting_feedback || 0, myTasks: stats.value.my_awaiting_feedback || 0, color: '#f97316', bg: '#fff7ed', statusValue: 'Awaiting Feedback',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>' },
  { label: 'Complete', value: stats.value.complete || 0, myTasks: stats.value.my_complete || 0, color: '#22c55e', bg: '#f0fdf4', statusValue: 'Complete',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>' },
])

function parseTags(str) {
  if (!str) return []
  try { return JSON.parse(str) } catch { return str.split(',').map(t => t.trim()).filter(Boolean) }
}

function tasksByStatus(status) { return tasks.value.filter(t => t.status === status) }
function statusClass(s) { return { 'Not Started': 'ns', 'In Progress': 'ip', 'Testing': 'test', 'Awaiting Feedback': 'af', 'Complete': 'done' }[s] || '' }
function priClass(p) { return { Low: 'low', Medium: 'med', High: 'high', Urgent: 'urg' }[p] || 'med' }
function isOverdue(t) { if (t.status === 'Complete') return false; return t.due_date && new Date(t.due_date) < new Date() }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(s, n) { return s?.length > n ? s.slice(0, n) + '...' : s }

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) { form.tagList.push(val); form.tags = JSON.stringify(form.tagList) }
  tagInput.value = ''
}
function removeTag(i) { form.tagList.splice(i, 1); form.tags = JSON.stringify(form.tagList) }
function onFileChange(e) { attachedFiles.value = Array.from(e.target.files || []) }

function filterByStatus(s) { filters.status = s; if (currentView.value !== 'kanban') loadTasks() }

function onDragStart(task) { dragTaskId.value = task.id }
function onDrop(newStatus) {
  const id = dragTaskId.value
  const task = tasks.value.find(t => t.id === id)
  if (id && task && task.status !== newStatus) { updateStatus(task, newStatus) }
  dragTaskId.value = null; dragCol.value = null
}

async function loadStaff() {
  try { const r = await axios.get(`${BASE}/staff?per_page=500`); staff.value = r.data.staff?.data || [] } catch { staff.value = [] }
}

async function loadTasks() {
  loading.value = true
  const all = currentView.value === 'kanban'
  try {
    const params = { search: filters.search, priority: filters.priority, assigned_to: filters.assigned_to, status: filters.status, all, per_page: perPage.value, page: page.value }
    const r = await axios.get(`${BASE}/tasks`, { params })
    if (all) { tasks.value = r.data.tasks || [] } else { tasks.value = r.data.tasks?.data || []; totalPages.value = r.data.tasks?.last_page || 1 }
    stats.value = r.data.stats || {}
  } catch { tasks.value = []; stats.value = {} }
  finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; loadTasks() }, 350)
}

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    name: '', description: '', priority: 'Medium', status: 'Not Started',
    start_date: new Date().toISOString().slice(0, 10), due_date: '',
    assigned_to: null, related_to_type: '', related_to_id: '',
    billable: false, is_public: false, hourly_rate: 0,
    repeat_every: '', tags: '', assignees: [], followers: [], tagList: [],
  })
  tagInput.value = ''; attachedFiles.value = []
  loadStaff()
  showModal.value = true
}

function openCreateForStatus(status) { openCreate(); form.status = status }

function openEdit(task) {
  editingId.value = task.id
  const tagList = parseTags(task.tags)
  Object.assign(form, {
    name: task.name, description: task.description || '',
    priority: task.priority || 'Medium', status: task.status || 'Not Started',
    start_date: task.start_date?.slice?.(0, 10) || '',
    due_date: task.due_date?.slice?.(0, 10) || '',
    assigned_to: task.assigned_to || null,
    related_to_type: task.related_to_type || '',
    related_to_id: task.related_to_id || '',
    billable: !!task.billable, is_public: !!task.is_public,
    hourly_rate: task.hourly_rate || 0,
    repeat_every: task.repeat_every || '', tags: task.tags || '',
    assignees: task.assignees || [], followers: task.followers || [],
    tagList,
  })
  tagInput.value = ''; attachedFiles.value = []
  loadStaff()
  showModal.value = true
}

async function saveTask() {
  if (!form.name) return alert('Subject is required')
  if (!form.start_date) return alert('Start date is required')
  saving.value = true
  try {
    const payload = { ...form, tags: form.tags || '' }
    if (editingId.value) {
      await axios.put(`${BASE}/tasks/${editingId.value}`, payload)
      message.success('Task updated')
    } else {
      await axios.post(`${BASE}/tasks`, payload)
      message.success('Task created')
    }
    closeModal(); loadTasks()
  } catch (e) {
    const errs = e.response?.data?.errors
    alert(errs ? Object.values(errs).flat().join(', ') : 'Failed to save task')
  } finally { saving.value = false }
}

async function updateStatus(task, newStatus) {
  try { await axios.put(`${BASE}/tasks/${task.id}`, { status: newStatus }); message.success('Status updated'); loadTasks() } catch { message.error('Failed') }
}

async function deleteTask(id) {
  if (!confirm('Delete this task?')) return
  try { await axios.delete(`${BASE}/tasks/${id}`); message.success('Task deleted'); loadTasks() } catch { message.error('Failed') }
}

function toggleTimer(task) {
  task.timerRunning = !task.timerRunning
  message.success(task.timerRunning ? 'Timer started' : 'Timer stopped')
}

function closeModal() { showModal.value = false; editingId.value = null }

watch(currentView, () => { if (currentView.value === 'kanban') filters.status = ''; loadTasks() })

onMounted(() => { loadTasks(); loadStaff() })
</script>

<style scoped>
.tk-page { font-family: Inter, -apple-system, sans-serif; background: #f8fafc; padding: 24px; }

/* Header */
.tk-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
.tk-title { font-size: 20px; font-weight: 700; color: #0f172a; margin: 0; }
.tk-subtitle { font-size: 12px; color: #94a3b8; margin: 2px 0 0; }
.tk-overview-link { color: #6366f1; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; }
.tk-overview-link:hover { text-decoration: underline; }
.tk-header-actions { display: flex; gap: 10px; align-items: center; }

.tk-btn-primary {
  display: inline-flex; align-items: center; gap: 6px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; border: none; border-radius: 8px; padding: 9px 16px;
  font-size: 12.5px; font-weight: 600; cursor: pointer;
  transition: all .2s; box-shadow: 0 4px 12px rgba(99,102,241,.25);
  font-family: inherit;
}
.tk-btn-primary:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.35); }

.tk-view-toggle { display: flex; gap: 2px; background: #f1f5f9; padding: 3px; border-radius: 8px; }
.tk-view-btn { background: transparent; border: none; border-radius: 6px; width: 30px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; }
.tk-view-btn:hover { color: #64748b; }
.tk-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* Summary Cards */
.tk-stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 18px; }
@media (max-width: 900px) { .tk-stats-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .tk-stats-row { grid-template-columns: repeat(2, 1fr); } }
.tk-stat-card { background: #fff; border: 1px solid #f1f5f9; border-left: 3px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.02); }
.tk-stat-card:hover { border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,.04); transform: translateY(-1px); }
.tk-stat-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tk-stat-val { font-size: 16px; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
.tk-stat-label { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
.tk-stat-mine { font-size: 10px; color: #94a3b8; margin-top: 1px; font-weight: 500; }

/* Overview */
.tk-overview-hd { margin-bottom: 14px; }
.tk-back-btn { display: inline-flex; align-items: center; gap: 4px; background: none; border: none; color: #6366f1; font-size: 12.5px; font-weight: 600; cursor: pointer; padding: 4px 0; font-family: inherit; }
.tk-back-btn:hover { text-decoration: underline; }
.tk-ov-filters { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.tk-ov-select { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 10px; font-size: 12px; color: #1e293b; background: #fff; cursor: pointer; outline: none; font-family: inherit; }
.tk-ov-select:focus { border-color: #6366f1; }

/* Table */
.tk-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.tk-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.tk-table thead th { background: #f8fafc; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; border-bottom: 1.5px solid #e2e8f0; }
.tk-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.tk-row:last-child td { border-bottom: none; }
.tk-row:hover { background: #fafbff; }
.tk-cell-muted { color: #64748b; }
.tk-name-cell { display: flex; flex-direction: column; gap: 1px; }
.tk-name { font-weight: 600; color: #0f172a; font-size: 12.5px; }
.tk-desc, .tk-rel { font-size: 10.5px; color: #94a3b8; }
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
.tk-on-time { font-size: 11px; font-weight: 600; padding: 1px 7px; border-radius: 6px; }
.tk-on-time.yes { background: #f0fdf4; color: #16a34a; }
.tk-on-time.no { background: #fef2f2; color: #dc2626; }

.tk-assignee-cell { display: flex; align-items: center; gap: 6px; }
.tk-avatar-sm { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; flex-shrink: 0; }

.tk-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.tk-tag { background: #eef2ff; color: #6366f1; padding: 1px 7px; border-radius: 12px; font-size: 10px; font-weight: 600; }

.tk-pri { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; text-transform: uppercase; white-space: nowrap; }
.tk-pri.low { background: #f1f5f9; color: #64748b; }
.tk-pri.med { background: #eff6ff; color: #3b82f6; }
.tk-pri.high { background: #fff7ed; color: #f97316; }
.tk-pri.urg { background: #fef2f2; color: #dc2626; }

.tk-row-actions { display: flex; gap: 3px; align-items: center; }
.tk-act-btn { display: inline-flex; align-items: center; gap: 3px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 8px; font-size: 10.5px; font-weight: 600; color: #475569; cursor: pointer; font-family: inherit; }
.tk-act-btn:hover { border-color: #6366f1; color: #6366f1; }
.tk-act-btn-icon { background: transparent; border: 1px solid #e2e8f0; border-radius: 6px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; }
.tk-act-btn-icon:hover { background: #f8fafc; border-color: #cbd5e1; color: #6366f1; }
.hover\:text-rose-600:hover { color: #e11d48; }

/* Filters */
.tk-filters { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.tk-filters-left, .tk-filters-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.tk-filter-select { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 10px; font-size: 12px; color: #1e293b; background: #fff; cursor: pointer; outline: none; font-family: inherit; }
.tk-filter-select:focus { border-color: #6366f1; }
.tk-search-wrap { position: relative; }
.tk-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; pointer-events: none; }
.tk-search-input { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 7px 12px 7px 30px; font-size: 12px; color: #1e293b; background: #fff; width: 180px; outline: none; font-family: inherit; }
.tk-search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }

/* Pagination */
.tk-pagination { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-top: 1px solid #f1f5f9; font-size: 11.5px; color: #64748b; }
.tk-pg-info { color: #94a3b8; }
.tk-pg-btns { display: flex; gap: 6px; }
.tk-pg-btn { background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 11px; font-size: 11.5px; color: #475569; cursor: pointer; transition: all .12s; font-family: inherit; }
.tk-pg-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.tk-pg-btn:disabled { opacity: .4; cursor: not-allowed; }

/* Kanban */
.tk-kanban { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
@media (max-width: 1200px) { .tk-kanban { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .tk-kanban { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .tk-kanban { grid-template-columns: 1fr; } }
.tk-kanban-col { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.tk-kanban-hd { display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; border-top: 3px solid transparent; margin-top: -12px; padding-top: 12px; border-radius: 12px 12px 0 0; }
.tk-kanban-title { font-weight: 700; font-size: 11.5px; color: #1e293b; flex: 1; }
.tk-kanban-count { background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; }
.tk-kanban-cards { display: flex; flex-direction: column; gap: 6px; flex: 1; min-height: 60px; }
.tk-kanban-cards.tk-drag-over { background: #eef2ff; border-radius: 8px; outline: 2px dashed #6366f1; outline-offset: -2px; }
.tk-kanban-empty { text-align: center; color: #cbd5e1; font-size: 11px; padding: 16px; min-height: 40px; display: flex; align-items: center; justify-content: center; }

.tk-kanban-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; display: flex; flex-direction: column; gap: 6px; transition: all .2s; cursor: grab; }
.tk-kanban-card:active { cursor: grabbing; }
.tk-kanban-card:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.08); }
.tk-kc-hd { display: flex; justify-content: space-between; align-items: center; }
.tk-kc-menu { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 2px; border-radius: 4px; }
.tk-kc-menu:hover { background: #f1f5f9; }
.tk-kc-title { font-weight: 600; font-size: 12px; color: #0f172a; cursor: pointer; line-height: 1.4; }
.tk-kc-title:hover { color: #6366f1; }
.tk-kc-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.tk-kc-tag { background: #eef2ff; color: #6366f1; padding: 1px 6px; border-radius: 10px; font-size: 9px; font-weight: 600; }
.tk-kc-ft { display: flex; align-items: center; justify-content: space-between; }
.tk-kc-date { font-size: 10px; color: #64748b; display: flex; align-items: center; gap: 3px; }
.tk-kc-avatar { width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: #fff; }
.tk-kc-move { margin-top: 2px; padding-top: 6px; border-top: 1px solid #f1f5f9; }
.tk-kc-move-select { width: 100%; border: none; background: #f8fafc; font-size: 10.5px; font-weight: 600; color: #64748b; cursor: pointer; padding: 3px 6px; border-radius: 4px; outline: none; font-family: inherit; }
.tk-kanban-add { display: flex; align-items: center; justify-content: center; gap: 4px; background: none; border: 1.5px dashed #e2e8f0; border-radius: 8px; padding: 7px; color: #94a3b8; font-size: 11px; font-weight: 600; cursor: pointer; font-family: inherit; }
.tk-kanban-add:hover { border-color: #6366f1; color: #6366f1; }

/* Modal */
.tk-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.tk-modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 660px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
.tk-modal-hd { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.tk-modal-hd-left { display: flex; gap: 12px; align-items: flex-start; }
.tk-modal-icon { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tk-modal-title { font-size: 16px; font-weight: 700; margin: 0; color: #0f172a; }
.tk-modal-subtitle { font-size: 11.5px; color: #94a3b8; margin: 2px 0 0; }
.tk-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.tk-modal-close:hover { background: #f1f5f9; color: #475569; }
.tk-modal-body { padding: 18px 24px; }
.tk-modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }

.tk-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.tk-fg-row { display: flex; flex-direction: column; gap: 4px; }
.tk-fg-row.span-2 { grid-column: span 2; }
.tk-fg-label { font-size: 11.5px; font-weight: 600; color: #334155; }

.tk-fg-checkboxes { grid-column: span 2; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; margin-bottom: 4px; }
.tk-cb-label { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 500; color: #475569; cursor: pointer; }
.tk-cb-label input[type=checkbox] { width: 15px; height: 15px; cursor: pointer; }
.tk-cb-file { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 10px; }
.tk-file-input { font-size: 11px; max-width: 140px; }

.tk-fg-input { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12.5px; outline: none; width: 100%; box-sizing: border-box; font-family: inherit; transition: border-color .12s; }
.tk-fg-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.tk-fg-textarea { resize: vertical; min-height: 80px; }

.tk-rel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.tk-multi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 4px; max-height: 180px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px; padding: 8px 10px; }
.tk-multi-cb { display: flex; align-items: center; gap: 5px; font-size: 11.5px; color: #475569; cursor: pointer; }
.tk-multi-cb input { width: 14px; height: 14px; cursor: pointer; }

.tk-tag-wrap { display: flex; flex-direction: column; gap: 4px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; min-height: 36px; }
.tk-tag-wrap:focus-within { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.08); }
.tk-tag-chips { display: flex; gap: 4px; flex-wrap: wrap; }
.tk-tag-chip { display: inline-flex; align-items: center; gap: 3px; background: #eef2ff; color: #6366f1; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.tk-tag-x { background: none; border: none; color: #6366f1; font-size: 14px; cursor: pointer; padding: 0; line-height: 1; opacity: .6; }
.tk-tag-x:hover { opacity: 1; }
.tk-tag-field { border: none; outline: none; font-size: 12px; font-family: inherit; flex: 1; min-width: 120px; padding: 2px 0; }

.tk-btn-cancel { padding: 8px 18px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; color: #64748b; font-size: 12.5px; font-weight: 600; cursor: pointer; font-family: inherit; }
.tk-btn-cancel:hover { border-color: #cbd5e1; color: #334155; }
.tk-btn-save { display: inline-flex; align-items: center; gap: 5px; padding: 8px 20px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: all .2s; box-shadow: 0 4px 12px rgba(99,102,241,.3); font-family: inherit; }
.tk-btn-save:hover { background: linear-gradient(135deg, #4f46e5, #7c3aed); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.4); }
.tk-btn-save:disabled { opacity: .6; cursor: not-allowed; transform: none; }

@media (max-width: 1024px) { .tk-page { padding: 16px; } .tk-table-wrap { overflow-x: auto; } }
@media (max-width: 640px) { .tk-filters { flex-direction: column; align-items: stretch; } .tk-filters-right { flex-direction: column; } .tk-search-input { width: 100%; } .tk-form-grid { grid-template-columns: 1fr; } .tk-fg-row.span-2, .tk-fg-checkboxes { grid-column: span 1; } }
</style>
