<template>
  <div class="ld-page">
    <!-- HEADER -->
    <div class="ld-header">
      <div>
        <h1 class="ld-title">Leads</h1>
        <p class="ld-subtitle">Track and convert business opportunities</p>
      </div>
      <div class="ld-header-actions">
        <div class="ld-view-toggle">
          <button class="ld-view-btn" :class="{ active: currentView === 'kanban' }" @click="currentView = 'kanban'" title="Kanban">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          </button>
          <button class="ld-view-btn" :class="{ active: currentView === 'table' }" @click="currentView = 'table'" title="Table">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
        <button class="ld-btn-import" @click="showImportModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Import
        </button>
        <button class="ld-btn-primary" @click="openCreateModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Lead
        </button>
      </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="ld-stats-row">
      <div v-for="card in summaryCards" :key="card.label" class="ld-stat-card" :style="{ borderLeftColor: card.color }" @click="filterByStatus(card.statusId)">
        <div class="ld-stat-icon" :style="{ background: card.bg }" v-html="card.icon"></div>
        <div class="ld-stat-info">
          <div class="ld-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="ld-stat-label">{{ card.label }}</div>
          <div v-if="card.pct !== undefined" class="ld-stat-pct">{{ card.pct }}%</div>
        </div>
      </div>
    </div>

    <!-- ====== KANBAN VIEW ====== -->
    <div v-if="currentView === 'kanban'" class="ld-kanban">
      <div class="ld-kanban-filters">
        <div class="ld-search-wrap">
          <svg class="ld-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="searchQuery" placeholder="Search Leads..." class="ld-search-input" @input="onSearch" />
        </div>
        <select v-model="kanbanSort" class="ld-filter-select">
          <option value="date_created">Sort By: Date Created</option>
          <option value="kanban_order">Kanban Order</option>
          <option value="last_contact">Last Contact</option>
        </select>
      </div>

      <div class="ld-kanban-cols">
        <div v-for="col in kanbanColumns" :key="col.status_id" class="ld-kanban-col">
          <div class="ld-kc-hd" :style="{ borderTopColor: col.status_color }">
            <span class="ld-kc-dot" :style="{ background: col.status_color }"></span>
            <span class="ld-kc-title">{{ col.status_name }}</span>
            <span class="ld-kc-count">{{ col.leads.length }}</span>
            <span class="ld-kc-val">${{ getColumnTotal(col.leads) }}</span>
          </div>
          <div class="ld-kc-cards" :class="{ 'ld-drag-over': dragCol === col.status_id }"
            @dragover.prevent="dragCol = col.status_id"
            @dragenter.prevent
            @dragleave="dragCol = null"
            @drop="onDrop(col.status_id)">
            <div v-for="lead in col.leads" :key="lead.id" class="ld-kanban-card"
              draggable="true"
              @dragstart="onDragStart(lead)"
              @dragend="dragCol = null">
              <div class="ld-kcard-hd">
                <span class="ld-kcard-id">#{{ lead.id }}</span>
                <div class="ld-kcard-actions">
                  <button class="ld-kcard-btn" @click.stop="editLead(lead)" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                </div>
              </div>
              <div class="ld-kcard-name" @click="editLead(lead)">{{ lead.name }}</div>
              <div class="ld-kcard-meta">
                <span v-if="lead.source" class="ld-kcard-source">Source: {{ lead.source.name }}</span>
                <span v-if="lead.lead_value > 0" class="ld-kcard-val">${{ lead.lead_value }}</span>
                <span v-else class="ld-kcard-val">Lead Value: --</span>
              </div>
              <div v-if="lead.tags" class="ld-kcard-tags">
                <span v-for="tag in parseTags(lead.tags)" :key="tag" class="ld-tag">{{ tag }}</span>
              </div>
              <div class="ld-kcard-ft">
                <span class="ld-kcard-date">Created: {{ timeAgo(lead.created_at) }}</span>
                <div class="ld-kcard-avatar" :title="lead.assigned?.name || 'Unassigned'">
                  {{ lead.assigned ? lead.assigned.name.charAt(0).toUpperCase() : '?' }}
                </div>
              </div>
              <div class="ld-kcard-move">
                <select :value="lead.status_id" @change="e => moveLead(lead.id, e.target.value)" class="ld-kcard-move-select">
                  <option v-for="s in metadata.statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div v-if="!col.leads.length" class="ld-kanban-empty"
              @dragover.prevent="dragCol = col.status_id"
              @dragenter.prevent
              @dragleave="dragCol = null"
              @drop="onDrop(col.status_id)">Drop here</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ====== TABLE VIEW ====== -->
    <div v-else>
      <div class="ld-filters">
        <div class="ld-filters-left">
          <select class="ld-filter-select" v-model="perPage" @change="fetchLeads">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <select class="ld-filter-select" v-model="filterSource" @change="fetchLeads">
            <option value="">All Sources</option>
            <option v-for="s in metadata.sources" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <select class="ld-filter-select" v-model="filterAssigned" @change="fetchLeads">
            <option value="">All Staff</option>
            <option v-for="s in metadata.staff" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <select class="ld-filter-select" v-model="filterStatus" @change="fetchLeads">
            <option value="">All Statuses</option>
            <option v-for="s in metadata.statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div class="ld-filters-right">
          <div class="ld-search-wrap">
            <svg class="ld-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="searchQuery" placeholder="Search leads..." class="ld-search-input" @input="onSearch" />
          </div>
        </div>
      </div>

      <div class="ld-table-wrap">
        <table class="ld-table">
          <thead>
            <tr>
              <th style="width:44px;">#</th>
              <th>Name</th>
              <th>Company</th>
              <th>Email</th>
              <th>Phone</th>
              <th style="width:80px;">Value</th>
              <th>Tags</th>
              <th>Assigned</th>
              <th style="width:100px;">Status</th>
              <th>Source</th>
              <th style="width:100px;">Last Contact</th>
              <th style="width:100px;">Created</th>
              <th style="width:160px;"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="13" class="ld-empty-cell">
                <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              </td>
            </tr>
            <tr v-for="(lead, idx) in leads" :key="lead.id" class="ld-row">
              <td class="ld-cell-muted">{{ idx + 1 + (page - 1) * (+perPage) }}</td>
              <td>
                <div class="ld-name-cell">
                  <span class="ld-name">{{ lead.name }}</span>
                  <span v-if="lead.title" class="ld-pos">{{ lead.title }}</span>
                </div>
              </td>
              <td class="ld-cell-muted">{{ lead.company || '—' }}</td>
              <td class="ld-cell-muted">{{ lead.email || '—' }}</td>
              <td class="ld-cell-muted">{{ lead.phonenumber || '—' }}</td>
              <td class="ld-cell-muted">${{ lead.lead_value }}</td>
              <td>
                <div class="ld-tags">
                  <span v-if="!lead.tags" class="text-slate-300">—</span>
                  <span v-for="tag in parseTags(lead.tags)" :key="tag" class="ld-tag">{{ tag }}</span>
                </div>
              </td>
              <td class="ld-cell-muted">{{ lead.assigned?.name || '—' }}</td>
              <td>
                <span class="ld-status-badge" :style="{ background: lead.status?.color + '20', color: lead.status?.color, border: '1px solid ' + lead.status?.color + '40' }">
                  {{ lead.status?.name || '—' }}
                </span>
              </td>
              <td class="ld-cell-muted">{{ lead.source?.name || '—' }}</td>
              <td class="ld-cell-muted">{{ lead.last_contacted ? timeAgo(lead.last_contacted) : '—' }}</td>
              <td class="ld-cell-muted">{{ fmtDate(lead.created_at) }}</td>
              <td>
                <div class="ld-row-actions">
                  <button class="ld-act-btn" @click="viewLead(lead)">View</button>
                  <button class="ld-act-btn" @click="editLead(lead)">Edit</button>
                  <button class="ld-act-btn text-rose-600 hover:text-rose-700" @click="deleteLead(lead)">Delete</button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && !leads.length">
              <td colspan="13" class="ld-empty-cell">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                <p class="text-slate-400 text-sm mt-2">No leads found</p>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="ld-pagination" v-if="totalPages > 1">
          <span class="ld-pg-info">Showing {{ leads.length }} of {{ totalPages * (+perPage) }} entries</span>
          <div class="ld-pg-btns">
            <button class="ld-pg-btn" :disabled="page <= 1" @click="page--; fetchLeads()">Previous</button>
            <button class="ld-pg-btn" :disabled="page >= totalPages" @click="page++; fetchLeads()">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="ld-modal-overlay" @click.self="closeModal">
        <div class="ld-modal-box ld-modal-wide">
          <div class="ld-modal-hd">
            <div class="ld-modal-hd-left">
              <div class="ld-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div>
                <div class="ld-modal-title">{{ editingLead ? 'Edit Lead' : 'Add New Lead' }}</div>
                <div class="ld-modal-sub">Fill in the lead details below</div>
              </div>
            </div>
            <button class="ld-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="ld-modal-body">
            <div class="ld-form-grid">
              <!-- Pipeline -->
              <div class="ld-form-group">
                <label>Status <span class="ld-req">*</span></label>
                <select v-model="form.status_id" class="ld-input">
                  <option :value="null">Select Status</option>
                  <option v-for="s in metadata.statuses" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <div class="ld-form-group">
                <label>Source <span class="ld-req">*</span></label>
                <select v-model="form.source_id" class="ld-input">
                  <option :value="null">Select Source</option>
                  <option v-for="s in metadata.sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <div class="ld-form-group">
                <label>Assigned</label>
                <select v-model="form.assigned_id" class="ld-input">
                  <option :value="null">Tre Stamm (default)</option>
                  <option v-for="s in metadata.staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <div class="ld-form-group span-2">
                <label>Tags</label>
                <div class="ld-tag-input-wrap">
                  <div class="ld-tag-list">
                    <span v-for="(tag, i) in form.tagList" :key="i" class="ld-tag-pill">
                      {{ tag }}
                      <button class="ld-tag-pill-del" @click="form.tagList.splice(i, 1); form.tags = form.tagList.join(',')">&times;</button>
                    </span>
                    <input v-model="tagInput" placeholder="Type tag and press Enter" class="ld-tag-field" @keydown.enter.prevent="addTag" @keydown.,.prevent="addTag" />
                  </div>
                </div>
              </div>

              <!-- Contact -->
              <div class="ld-form-group">
                <label>Name <span class="ld-req">*</span></label>
                <input v-model="form.name" placeholder="Full name" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Position</label>
                <input v-model="form.title" placeholder="e.g. Sales Director" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Email Address</label>
                <input v-model="form.email" placeholder="email@example.com" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Website</label>
                <input v-model="form.website" placeholder="example.com" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Phone</label>
                <input v-model="form.phonenumber" placeholder="+1 555-0199" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Lead value ($)</label>
                <input v-model="form.lead_value" type="number" min="0" step="0.01" placeholder="0.00" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Company</label>
                <input v-model="form.company" placeholder="Company name" class="ld-input" />
              </div>

              <!-- Address -->
              <div class="ld-form-group span-2">
                <label>Address</label>
                <input v-model="form.address" placeholder="Street address" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>City</label>
                <input v-model="form.city" placeholder="City" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>State</label>
                <input v-model="form.state" placeholder="State" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Country</label>
                <input v-model="form.country" placeholder="Country" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Zip Code</label>
                <input v-model="form.zip" placeholder="ZIP" class="ld-input" />
              </div>
              <div class="ld-form-group">
                <label>Default Language</label>
                <select v-model="form.default_language" class="ld-input">
                  <option value="">System Default</option>
                  <option value="en">English</option>
                  <option value="es">Spanish</option>
                  <option value="fr">French</option>
                  <option value="de">German</option>
                  <option value="ar">Arabic</option>
                </select>
              </div>

              <!-- Extra options -->
              <div class="ld-form-group span-2">
                <div class="ld-chk-row">
                  <label class="ld-chk-label">
                    <input type="checkbox" v-model="form.is_public" />
                    <span>Public</span>
                  </label>
                  <label class="ld-chk-label">
                    <input type="checkbox" v-model="contactedToday" />
                    <span>Contacted Today</span>
                  </label>
                </div>
              </div>

              <div class="ld-form-group span-2">
                <label>Description</label>
                <textarea v-model="form.description" rows="4" placeholder="Lead description..." class="ld-input ld-textarea"></textarea>
              </div>
            </div>
          </div>

          <div class="ld-modal-ft">
            <button class="ld-btn-secondary" @click="closeModal">Cancel</button>
            <button class="ld-btn-primary" @click="saveLead" :disabled="saving">
              {{ saving ? 'Saving...' : (editingLead ? 'Save Changes' : 'Create Lead') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Import Modal -->
    <Teleport to="body">
      <div v-if="showImportModal" class="ld-modal-overlay" @click.self="showImportModal = false">
        <div class="ld-modal-box">
          <div class="ld-modal-hd">
            <div class="ld-modal-hd-left">
              <div class="ld-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              </div>
              <div>
                <div class="ld-modal-title">Import Leads</div>
                <div class="ld-modal-sub">Upload a CSV file with lead data</div>
              </div>
            </div>
            <button class="ld-modal-close" @click="showImportModal = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="ld-modal-body">
            <div class="ld-import-area" @dragover.prevent @drop.prevent="onImportDrop">
              <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="40" height="40"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <p class="ld-import-text">Drag & drop your CSV file here, or</p>
              <label class="ld-btn-primary" style="cursor:pointer">
                <input type="file" accept=".csv" @change="onImportFile" style="display:none" />
                Browse File
              </label>
              <p class="ld-import-hint" v-if="!importData.length">Expected columns: name, email, phonenumber, company, lead_value, status_id, source_id</p>
              <p class="ld-import-hint" v-else>{{ importData.length }} rows parsed. Click Import to proceed.</p>
            </div>
            <div v-if="importPreview.length" class="ld-import-preview">
              <table>
                <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Value</th></tr></thead>
                <tbody>
                  <tr v-for="(row, i) in importPreview" :key="i">
                    <td>{{ row.name }}</td><td>{{ row.email }}</td><td>{{ row.company }}</td><td>${{ row.lead_value }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="ld-modal-ft">
            <button class="ld-btn-secondary" @click="showImportModal = false">Cancel</button>
            <button class="ld-btn-primary" :disabled="!importData.length || importing" @click="submitImport">
              {{ importing ? 'Importing...' : `Import ${importData.length} Lead(s)` }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../store/authStore'

const authStore = useAuthStore()

const leads = ref([])
const kanbanColumns = ref([])
const loading = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const perPage = ref('25')
const page = ref(1)
const totalPages = ref(1)
const filterSource = ref('')
const filterAssigned = ref('')
const filterStatus = ref('')
const showModal = ref(false)
const editingLead = ref(null)
const currentView = ref('kanban')
const tagInput = ref('')
const contactedToday = ref(false)
const kanbanSort = ref('date_created')
const dragCol = ref(null)
const dragLeadId = ref(null)
const showImportModal = ref(false)
const importData = ref([])
const importing = ref(false)

const metadata = reactive({
  statuses: [], sources: [], staff: [],
})

const form = reactive({
  name: '', title: '', company: '', email: '', website: '', phonenumber: '',
  lead_value: 0, address: '', city: '', state: '', zip: '', country: '',
  status_id: null, source_id: null, assigned_id: null,
  description: '', tags: '', is_public: false, default_language: '', tagList: [],
})

const summaryCards = computed(() => {
  const total = metadata.statuses.reduce((sum, s) => {
    const col = kanbanColumns.value.find(c => c.status_id === s.id)
    return sum + (col ? col.leads.length : 0)
  }, 0)
  const statMap = {}
  kanbanColumns.value.forEach(col => { statMap[col.status_id] = col.leads.length })
  return metadata.statuses.map(s => {
    const val = statMap[s.id] || 0
    return {
      label: s.name, value: val, statusId: s.id,
      color: s.color, bg: s.color + '18',
      pct: s.name === 'Lost Leads' || s.name === 'Lost' ? (total > 0 ? ((val / total) * 100).toFixed(2) : '0.00') : undefined,
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/></svg>',
    }
  })
})

function parseTags(str) {
  if (!str) return []
  try { return JSON.parse(str) } catch { return str.split(',').map(t => t.trim()).filter(Boolean) }
}

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) { form.tagList.push(val); form.tags = form.tagList.join(',') }
  tagInput.value = ''
}

function getColumnTotal(list) {
  return list.reduce((s, l) => s + parseFloat(l.lead_value || 0), 0).toFixed(2)
}

function timeAgo(d) {
  if (!d) return ''
  const diff = Math.floor((Date.now() - new Date(d).getTime()) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return Math.floor(diff / 60) + ' min ago'
  if (diff < 86400) return Math.floor(diff / 3600) + ' hrs ago'
  return Math.floor(diff / 86400) + ' days ago'
}
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }

function filterByStatus(id) {
  if (currentView.value === 'table') { filterStatus.value = id; page.value = 1; fetchLeads() }
}

function sortKanbanLeads(leadsList) {
  const s = kanbanSort.value
  if (s === 'date_created') return [...leadsList].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  if (s === 'last_contact') return [...leadsList].sort((a, b) => new Date(b.last_contacted || 0) - new Date(a.last_contacted || 0))
  return leadsList
}

async function fetchMetadata() {
  try { const r = await axios.get('/lead-metadata'); Object.assign(metadata, r.data) } catch {}
}

async function fetchLeads() {
  loading.value = true
  try {
    if (currentView.value === 'kanban') {
      const params = { kanban: 1 }
      if (searchQuery.value) params.search = searchQuery.value
      const r = await axios.get('/leads', { params })
      kanbanColumns.value = r.data.map(col => ({ ...col, leads: sortKanbanLeads(col.leads) }))
    } else {
      const params = { page: page.value, per_page: perPage.value }
      if (searchQuery.value) params.search = searchQuery.value
      if (filterSource.value) params.source_id = filterSource.value
      if (filterAssigned.value) params.assigned_id = filterAssigned.value
      if (filterStatus.value) params.status_id = filterStatus.value
      const r = await axios.get('/leads', { params })
      leads.value = r.data.data || []
      totalPages.value = Math.ceil((r.data.total || 0) / (+perPage.value))
    }
  } catch {} finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; fetchLeads() }, 350)
}

function onDragStart(lead) { dragLeadId.value = lead.id }
function onDrop(newStatusId) {
  const id = dragLeadId.value
  const lead = leads.value.find(l => l.id === id)
  if (id && lead && lead.status_id !== newStatusId) {
    axios.put(`/leads/${id}/status`, { status_id: newStatusId }).then(() => fetchLeads()).catch(() => {})
  }
  dragLeadId.value = null; dragCol.value = null
}

async function moveLead(id, statusId) {
  try { await axios.put(`/leads/${id}/status`, { status_id: statusId }); fetchLeads() } catch {}
}

function openCreateModal() {
  editingLead.value = null
  Object.assign(form, {
    name: '', title: '', company: '', email: '', website: '', phonenumber: '',
    lead_value: 0, address: '', city: '', state: '', zip: '', country: '',
    status_id: metadata.statuses[0]?.id || null,
    source_id: metadata.sources[0]?.id || null,
    assigned_id: authStore.user?.id || null,
    description: '', tags: '', is_public: false, default_language: '', tagList: [],
  })
  contactedToday.value = false
  showModal.value = true
}

function editLead(lead) {
  editingLead.value = lead
  Object.assign(form, {
    name: lead.name, title: lead.title || '', company: lead.company || '',
    email: lead.email || '', website: lead.website || '', phonenumber: lead.phonenumber || '',
    lead_value: parseFloat(lead.lead_value || 0), address: lead.address || '',
    city: lead.city || '', state: lead.state || '', zip: lead.zip || '', country: lead.country || '',
    status_id: lead.status_id, source_id: lead.source_id,
    assigned_id: lead.assigned_id || null,
    description: lead.description || '', tags: lead.tags || '', is_public: false, default_language: '',
    tagList: parseTags(lead.tags),
  })
  contactedToday.value = false
  showModal.value = true
}

function viewLead(lead) { editLead(lead) }

async function saveLead() {
  if (!form.name || !form.status_id || !form.source_id) {
    return alert('Name, Status, and Source are required')
  }
  saving.value = true
  const payload = { ...form, tagList: undefined }
  if (contactedToday.value) payload.last_contacted = new Date().toISOString()
  try {
    if (editingLead.value) { await axios.put(`/leads/${editingLead.value.id}`, payload) }
    else { await axios.post('/leads', payload) }
    closeModal(); fetchLeads()
  } catch (e) { alert(e.response?.data?.message || 'Failed to save lead') }
  finally { saving.value = false }
}

async function deleteLead(lead) {
  if (!confirm(`Delete lead "${lead.name}"?`)) return
  try { await axios.delete(`/leads/${lead.id}`); fetchLeads() }
  catch { alert('Failed to delete lead') }
}

function closeModal() { showModal.value = false; editingLead.value = null }

// Import
function onImportFile(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => parseCSV(reader.result)
  reader.readAsText(file)
}
function onImportDrop(e) { parseCSV(e.dataTransfer.files[0]) }

function parseCSV(text) {
  const lines = text.split('\n').filter(l => l.trim())
  if (!lines.length) return
  const headers = lines[0].split(',').map(h => h.trim().toLowerCase().replace(/[^a-z_]/g, ''))
  const rows = lines.slice(1).map(line => {
    const vals = line.split(',').map(v => v.trim().replace(/^"|"$/g, ''))
    const row = {}
    headers.forEach((h, i) => { row[h] = vals[i] || '' })
    return row
  }).filter(r => r.name)
  importData.value = rows
  importPreview.value = rows.slice(0, 5)
}

const importPreview = ref([])

async function submitImport() {
  if (!importData.value.length) return
  importing.value = true
  try {
    await axios.post('/leads/import', { leads: importData.value })
    showImportModal.value = false; importData.value = []
    fetchLeads()
  } catch { alert('Import failed') }
  finally { importing.value = false }
}

onMounted(async () => { await fetchMetadata(); await fetchLeads() })
watch(currentView, () => fetchLeads())
</script>

<style scoped>
.ld-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }

/* HEADER */
.ld-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.ld-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px; }
.ld-subtitle { font-size: 12.5px; color: #64748b; margin: 2px 0 0; }
.ld-header-actions { display: flex; align-items: center; gap: 8px; }
.ld-view-toggle { display: flex; background: #f1f5f9; border-radius: 8px; padding: 3px; gap: 2px; }
.ld-view-btn { background: none; border: none; padding: 6px 8px; border-radius: 6px; cursor: pointer; color: #94a3b8; transition: all .15s; display: flex; align-items: center; }
.ld-view-btn:hover { color: #475569; }
.ld-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* BUTTONS */
.ld-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity .15s; white-space: nowrap; }
.ld-btn-primary:hover { opacity: .9; }
.ld-btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.ld-btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }
.ld-btn-secondary:hover { background: #f8fafc; }
.ld-btn-import { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 12.5px; font-weight: 500; cursor: pointer; transition: all .15s; }
.ld-btn-import:hover { border-color: #6366f1; color: #6366f1; background: #f8fafc; }

/* STATS */
.ld-stats-row { display: grid; grid-template-columns: repeat(auto-fill,minmax(150px,1fr)); gap: 10px; margin-bottom: 20px; }
.ld-stat-card { background: #fff; border: 1px solid #f1f5f9; border-left: 3px solid; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all .2s; }
.ld-stat-card:hover { border-color: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.ld-stat-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff; }
.ld-stat-info { flex: 1; min-width: 0; }
.ld-stat-val { font-size: 20px; font-weight: 800; line-height: 1.1; }
.ld-stat-label { font-size: 10px; color: #64748b; font-weight: 500; margin-top: 1px; }
.ld-stat-pct { font-size: 10px; color: #ef4444; font-weight: 600; }

/* KANBAN */
.ld-kanban-filters { display: flex; gap: 10px; margin-bottom: 14px; align-items: center; }
.ld-search-wrap { position: relative; flex: 1; min-width: 200px; }
.ld-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; }
.ld-search-input { width: 100%; padding: 8px 12px 8px 32px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.ld-search-input:focus { border-color: #6366f1; }
.ld-filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12px; cursor: pointer; background: #fff; outline: none; }
.ld-kanban-cols { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; }
.ld-kanban-col { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px; min-width: 260px; max-width: 280px; flex: 1; }
.ld-kc-hd { display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; border-top: 3px solid transparent; margin-top: -12px; padding-top: 12px; border-radius: 12px 12px 0 0; }
.ld-kc-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.ld-kc-title { font-weight: 700; font-size: 11px; color: #1e293b; flex: 1; text-transform: uppercase; letter-spacing: .3px; }
.ld-kc-count { background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; }
.ld-kc-val { font-size: 10px; font-weight: 700; color: #10b981; }
.ld-kc-cards { display: flex; flex-direction: column; gap: 6px; flex: 1; min-height: 60px; }
.ld-kc-cards.ld-drag-over { background: #eef2ff; border-radius: 8px; outline: 2px dashed #6366f1; outline-offset: -2px; }
.ld-kanban-empty { text-align: center; color: #cbd5e1; font-size: 11px; padding: 16px; min-height: 40px; display: flex; align-items: center; justify-content: center; cursor: default; }

.ld-kanban-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; display: flex; flex-direction: column; gap: 5px; transition: all .2s; cursor: grab; }
.ld-kanban-card:active { cursor: grabbing; }
.ld-kanban-card:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.08); }
.ld-kcard-hd { display: flex; justify-content: space-between; align-items: center; }
.ld-kcard-id { font-size: 10px; font-weight: 600; color: #94a3b8; }
.ld-kcard-actions { display: flex; gap: 2px; }
.ld-kcard-btn { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 2px; border-radius: 4px; display: flex; }
.ld-kcard-btn:hover { background: #f1f5f9; color: #475569; }
.ld-kcard-name { font-weight: 600; font-size: 12px; color: #0f172a; cursor: pointer; line-height: 1.4; }
.ld-kcard-name:hover { color: #6366f1; }
.ld-kcard-meta { display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #64748b; }
.ld-kcard-source { color: #64748b; }
.ld-kcard-val { font-weight: 600; }
.ld-kcard-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.ld-tag { background: #f1f5f9; color: #475569; font-size: 9px; padding: 1px 5px; border-radius: 3px; font-weight: 500; }
.ld-kcard-ft { display: flex; justify-content: space-between; align-items: center; margin-top: 2px; }
.ld-kcard-date { font-size: 9px; color: #94a3b8; }
.ld-kcard-avatar { width: 20px; height: 20px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: #64748b; }
.ld-kcard-move { margin-top: 2px; }
.ld-kcard-move-select { width: 100%; padding: 3px 6px; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 10px; background: #f8fafc; outline: none; cursor: pointer; }

/* FILTERS */
.ld-filters { display: flex; gap: 10px; margin-bottom: 14px; align-items: center; flex-wrap: wrap; }
.ld-filters-left { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.ld-filters-right { margin-left: auto; }

/* TABLE */
.ld-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.ld-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.ld-table th { padding: 10px 10px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 11px; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; }
.ld-row { transition: background .15s; }
.ld-row:hover { background: #f8fafc; }
.ld-table td { padding: 10px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ld-cell-muted { color: #64748b; }
.ld-name-cell { display: flex; flex-direction: column; }
.ld-name { font-weight: 600; color: #0f172a; }
.ld-pos { font-size: 11px; color: #94a3b8; }
.ld-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.ld-status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }
.ld-empty-cell { text-align: center; padding: 40px; color: #94a3b8; }
.ld-row-actions { display: flex; gap: 6px; }
.ld-act-btn { background: none; border: none; cursor: pointer; font-size: 12px; color: #6366f1; padding: 3px 6px; border-radius: 4px; }
.ld-act-btn:hover { background: #eef2ff; }
.text-rose-600 { color: #e11d48; }
.hover\:text-rose-700:hover { color: #be123c; }

/* PAGINATION */
.ld-pagination { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; font-size: 12px; color: #64748b; }
.ld-pg-info { color: #94a3b8; }
.ld-pg-btns { display: flex; gap: 6px; }
.ld-pg-btn { padding: 5px 14px; border: 1.5px solid #e2e8f0; border-radius: 6px; background: #fff; cursor: pointer; font-size: 12px; color: #475569; transition: all .15s; }
.ld-pg-btn:hover:not(:disabled) { border-color: #6366f1; color: #6366f1; }
.ld-pg-btn:disabled { opacity: .4; cursor: not-allowed; }

/* MODAL */
.ld-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; overflow-y: auto; }
.ld-modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.25); margin: auto; }
.ld-modal-wide { max-width: 720px; }
.ld-modal-hd { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px 14px; }
.ld-modal-hd-left { display: flex; gap: 12px; align-items: flex-start; }
.ld-modal-icon { width: 38px; height: 38px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6366f1; flex-shrink: 0; }
.ld-modal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
.ld-modal-sub { font-size: 12px; color: #64748b; margin-top: 1px; }
.ld-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; }
.ld-modal-close:hover { background: #f1f5f9; color: #475569; }
.ld-modal-body { padding: 0 24px 16px; }
.ld-modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px 20px; border-top: 1.5px solid #f1f5f9; }

/* FORM */
.ld-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ld-form-group { display: flex; flex-direction: column; gap: 4px; }
.ld-form-group.span-2 { grid-column: span 2; }
.ld-form-group label { font-size: 12px; font-weight: 600; color: #374151; }
.ld-req { color: #ef4444; }
.ld-input { width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; transition: border-color .15s; }
.ld-input:focus { border-color: #6366f1; }
.ld-textarea { resize: vertical; min-height: 80px; font-family: inherit; line-height: 1.5; }

/* TAG INPUT */
.ld-tag-input-wrap { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 6px 8px; display: flex; flex-wrap: wrap; gap: 4px; min-height: 36px; }
.ld-tag-list { display: flex; flex-wrap: wrap; gap: 4px; width: 100%; }
.ld-tag-pill { background: #eef2ff; color: #6366f1; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px; }
.ld-tag-pill-del { background: none; border: none; cursor: pointer; color: #6366f1; font-size: 13px; line-height: 1; padding: 0; }
.ld-tag-field { border: none; outline: none; font-size: 12.5px; flex: 1; min-width: 80px; padding: 2px 0; }

/* CHECKBOX ROW */
.ld-chk-row { display: flex; gap: 24px; }
.ld-chk-label { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; cursor: pointer; }
.ld-chk-label input { width: 16px; height: 16px; accent-color: #6366f1; }

/* IMPORT */
.ld-import-area { border: 2px dashed #d1d5db; border-radius: 12px; padding: 36px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; background: #f8fafc; }
.ld-import-text { font-size: 13px; color: #64748b; margin: 0; }
.ld-import-hint { font-size: 11px; color: #94a3b8; margin: 0; }
.ld-import-preview { margin-top: 14px; max-height: 200px; overflow-y: auto; }
.ld-import-preview table { width: 100%; font-size: 12px; border-collapse: collapse; }
.ld-import-preview th { text-align: left; padding: 6px 8px; background: #f1f5f9; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
.ld-import-preview td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; color: #334155; }

/* UTILITY */
.text-slate-300 { color: #cbd5e1; }
.text-sm { font-size: 12px; }
.mt-2 { margin-top: 6px; }
.animate-spin { animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.opacity-25 { opacity: .25; }
.opacity-75 { opacity: .75; }

@media (max-width: 1200px) { .ld-stats-row { grid-template-columns: repeat(4,1fr); } }
@media (max-width: 768px) { .ld-stats-row { grid-template-columns: repeat(2,1fr); } }
</style>
