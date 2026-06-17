<template>
  <div class="tickets-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Support Tickets</h1>
        <span class="subtitle">Manage customer support requests</span>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="openCreateModal">
          <span class="icon">＋</span> New Ticket
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card" :class="{ active: activeFilter === 'all' }" @click="filterBy('all')">
        <div class="stat-value">{{ stats.total || 0 }}</div>
        <div class="stat-label">Total</div>
      </div>
      <div class="stat-card open" :class="{ active: activeFilter === 'Open' }" @click="filterBy('Open')">
        <div class="stat-value">{{ stats.open || 0 }}</div>
        <div class="stat-label">Open</div>
      </div>
      <div class="stat-card in-progress" :class="{ active: activeFilter === 'In Progress' }" @click="filterBy('In Progress')">
        <div class="stat-value">{{ stats.in_progress || 0 }}</div>
        <div class="stat-label">In Progress</div>
      </div>
      <div class="stat-card answered" :class="{ active: activeFilter === 'Answered' }" @click="filterBy('Answered')">
        <div class="stat-value">{{ stats.answered || 0 }}</div>
        <div class="stat-label">Answered</div>
      </div>
      <div class="stat-card on-hold" :class="{ active: activeFilter === 'On Hold' }" @click="filterBy('On Hold')">
        <div class="stat-value">{{ stats.on_hold || 0 }}</div>
        <div class="stat-label">On Hold</div>
      </div>
      <div class="stat-card closed" :class="{ active: activeFilter === 'Closed' }" @click="filterBy('Closed')">
        <div class="stat-value">{{ stats.closed || 0 }}</div>
        <div class="stat-label">Closed</div>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search tickets..." class="search-input" @input="onSearch" />
      </div>
      <select v-model="priorityFilter" class="filter-select" @change="load">
        <option value="">All Priorities</option>
        <option value="Urgent">Urgent</option>
        <option value="High">High</option>
        <option value="Medium">Medium</option>
        <option value="Low">Low</option>
      </select>
      <button class="btn-bulk-pdf" title="Export PDF" @click="exportPDF">
        <span>📄</span> Bulk PDF
      </button>
    </div>

    <!-- Tickets Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th>ID</th>
            <th>Subject</th>
            <th>Client</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Assigned</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="9" class="loading-cell">
              <div class="loader"></div> Loading tickets...
            </td>
          </tr>
          <tr v-else-if="!tickets.length">
            <td colspan="9" class="empty-cell">
              <div class="empty-state">
                <span class="empty-icon">🎫</span>
                <p>No tickets found</p>
              </div>
            </td>
          </tr>
          <tr v-for="ticket in tickets" :key="ticket.id" class="data-row" @click="viewTicket(ticket)">
            <td @click.stop><input type="checkbox" v-model="selected" :value="ticket.id" /></td>
            <td class="ticket-id">#{{ ticket.id }}</td>
            <td class="ticket-subject">
              <span class="subject-text">{{ ticket.subject }}</span>
            </td>
            <td>{{ ticket.client?.company || '—' }}</td>
            <td>
              <span class="priority-badge" :class="ticket.priority.toLowerCase()">
                {{ ticket.priority }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="statusClass(ticket.status)">
                {{ ticket.status }}
              </span>
            </td>
            <td>{{ ticket.assignee?.name || '—' }}</td>
            <td>{{ formatDate(ticket.created_at) }}</td>
            <td @click.stop>
              <div class="action-buttons">
                <button class="action-btn view-btn" title="View" @click="viewTicket(ticket)">👁</button>
                <button class="action-btn edit-btn" title="Edit" @click="editTicket(ticket)">✏️</button>
                <button class="action-btn delete-btn" title="Delete" @click="deleteTicket(ticket)">🗑</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

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
        <div class="modal-box modal-wide">
          <div class="modal-header">
            <h2>{{ editingTicket ? 'Edit Ticket' : 'New Support Ticket' }}</h2>
            <button class="close-btn" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <!-- Subject -->
              <div class="form-row span-2">
                <label>Subject *</label>
                <input v-model="form.subject" placeholder="Ticket subject" class="form-input" required />
              </div>

              <!-- Contact Selection -->
              <div class="form-row">
                <label>Contact *</label>
                <select v-model="form.contact_id" class="form-input" required>
                  <option :value="null">Select Contact</option>
                  <option v-for="c in metadata.contacts" :key="c.id" :value="c.id">
                    {{ c.firstname }} {{ c.lastname }}
                  </option>
                </select>
              </div>

              <!-- Customer (Auto-filled) -->
              <div class="form-row">
                <label>Customer (Auto-filled)</label>
                <input :value="selectedClient ? selectedClient.company : ''" class="form-input read-only-input" readonly placeholder="Customer will auto-populate" />
              </div>

              <!-- Contact Name -->
              <div class="form-row">
                <label>Contact Name</label>
                <input :value="selectedContact ? (selectedContact.firstname + ' ' + (selectedContact.lastname || '')) : ''" class="form-input read-only-input" readonly placeholder="Name will auto-populate" />
              </div>

              <!-- Contact Email -->
              <div class="form-row">
                <label>Email Address</label>
                <input :value="selectedContact ? selectedContact.email : ''" class="form-input read-only-input" readonly placeholder="Email will auto-populate" />
              </div>

              <!-- Department -->
              <div class="form-row">
                <label>Department *</label>
                <select v-model="form.department_id" class="form-input" required>
                  <option :value="null">Select Department</option>
                  <option v-for="d in metadata.departments" :key="d.id" :value="d.id">
                    {{ d.name }}
                  </option>
                </select>
              </div>

              <!-- CC -->
              <div class="form-row">
                <label>CC</label>
                <input v-model="form.cc" placeholder="cc@example.com" class="form-input" />
              </div>

              <!-- Assign Ticket -->
              <div class="form-row">
                <label>Assign Ticket (Staff)</label>
                <select v-model="form.assigned_to" class="form-input">
                  <option :value="null">Select Staff</option>
                  <option v-for="s in metadata.staff" :key="s.id" :value="s.id">
                    {{ s.name }}
                  </option>
                </select>
              </div>

              <!-- Priority -->
              <div class="form-row">
                <label>Priority</label>
                <select v-model="form.priority" class="form-input">
                  <option value="Low">Low</option>
                  <option value="Medium">Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>

              <!-- Service -->
              <div class="form-row">
                <label>Service</label>
                <select v-model="form.service_id" class="form-input">
                  <option :value="null">Select Service</option>
                  <option v-for="s in metadata.services" :key="s.id" :value="s.id">
                    {{ s.name }}
                  </option>
                </select>
              </div>

              <!-- Project -->
              <div class="form-row">
                <label>Project</label>
                <select v-model="form.project_id" class="form-input" :disabled="!form.client_id">
                  <option :value="null">Select Project</option>
                  <option v-for="p in filteredProjects" :key="p.id" :value="p.id">
                    {{ p.name }}
                  </option>
                </select>
              </div>

              <!-- Tags -->
              <div class="form-row span-2">
                <label>Tags</label>
                <input v-model="form.tags" placeholder="e.g. support, billing" class="form-input" />
              </div>

              <!-- Message -->
              <div class="form-row span-2">
                <label>Ticket Body *</label>
                <div class="ticket-body-toolbar mb-2">
                  <select class="ticket-body-select" v-model="form.predefined_reply" @change="handlePredefinedReplyChange">
                    <option value="">Insert predefined reply</option>
                    <option v-for="r in predefinedReplies" :key="r.id" :value="r.id">{{ r.title }}</option>
                  </select>
                  <select class="ticket-body-select" v-model="form.kb_link" @change="handleKbLinkChange">
                    <option value="">Insert knowledge base link</option>
                    <option v-for="a in kbArticles" :key="a.id" :value="a.id">{{ a.title }}</option>
                  </select>
                </div>
                <textarea v-model="form.message" rows="5" placeholder="Describe the issue..." class="form-input form-textarea" required></textarea>
              </div>

              <!-- Attachments -->
              <div class="form-row span-2">
                <label>Attachments</label>
                <div class="attachment-upload">
                  <input type="file" @change="handleFileChange" class="file-input-hidden" id="ticket-file" />
                  <label for="ticket-file" class="btn-file-select">Choose File</label>
                  <span class="file-name-display">{{ attachmentFileName || 'No file chosen' }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="saveTicket" :disabled="saving">
              {{ saving ? 'Saving...' : (editingTicket ? 'Save Changes' : 'Create Ticket') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Ticket Modal -->
    <Teleport to="body">
      <div v-if="viewingTicket" class="modal-overlay" @click.self="viewingTicket = null">
        <div class="modal-box modal-wide">
          <div class="modal-header">
            <h2>Ticket #{{ viewingTicket.id }} — {{ viewingTicket.subject }}</h2>
            <button class="close-btn" @click="viewingTicket = null">✕</button>
          </div>
          <div class="modal-body ticket-detail">
            <div class="ticket-meta">
              <span class="priority-badge" :class="viewingTicket.priority?.toLowerCase()">{{ viewingTicket.priority }}</span>
              <span class="status-badge" :class="statusClass(viewingTicket.status)">{{ viewingTicket.status }}</span>
              <span class="meta-item">📅 {{ formatDate(viewingTicket.created_at) }}</span>
              <span class="meta-item">🏢 Client: <strong>{{ viewingTicket.client?.company || 'N/A' }}</strong></span>
            </div>

            <!-- Detail Grid for Support Ticket Info -->
            <div class="ticket-info-grid">
              <div class="info-item">
                <span class="info-label">Contact:</span>
                <span class="info-value">{{ viewingTicket.contact ? (viewingTicket.contact.firstname + ' ' + (viewingTicket.contact.lastname || '')) : '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ viewingTicket.contact?.email || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Department:</span>
                <span class="info-value">{{ viewingTicket.department?.name || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Assigned To:</span>
                <span class="info-value">{{ viewingTicket.assignee?.name || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Service:</span>
                <span class="info-value">{{ getServiceName(viewingTicket.service_id) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Project:</span>
                <span class="info-value">{{ viewingTicket.project?.name || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">CC:</span>
                <span class="info-value">{{ viewingTicket.cc || '—' }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Tags:</span>
                <span class="info-value">{{ viewingTicket.tags || '—' }}</span>
              </div>
            </div>

            <div class="message-bubble admin">
              <div class="bubble-header">Original Message</div>
              <div class="bubble-content">{{ viewingTicket.message }}</div>
            </div>

            <div class="replies-section">
              <h3>Replies ({{ viewingTicket.replies?.length || 0 }})</h3>
              <div v-for="reply in viewingTicket.replies" :key="reply.id"
                   class="message-bubble" :class="reply.is_admin_reply ? 'admin' : 'client'">
                <div class="bubble-header">{{ reply.name || reply.user?.name || 'Support' }} — {{ formatDate(reply.created_at) }}</div>
                <div class="bubble-content">{{ reply.message }}</div>
              </div>

              <div class="reply-form">
                <textarea v-model="replyMessage" rows="3" placeholder="Type your reply..." class="form-input form-textarea"></textarea>
                <button class="btn-primary" @click="sendReply" :disabled="sendingReply">
                  {{ sendingReply ? 'Sending...' : 'Send Reply' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../store/authStore'

const BASE = '/api'
const route = useRoute()
const authStore = useAuthStore()

const tickets     = ref([])
const stats       = ref({})
const loading     = ref(false)
const saving      = ref(false)
const search      = ref('')
const page        = ref(1)
const totalPages  = ref(1)
const activeFilter  = ref('all')
const priorityFilter = ref('')
const selectAll   = ref(false)
const selected    = ref([])
const showModal   = ref(false)
const editingTicket = ref(null)
const viewingTicket = ref(null)
const replyMessage  = ref('')
const sendingReply  = ref(false)

const metadata = ref({
  departments: [],
  staff: [],
  clients: [],
  contacts: [],
  projects: [],
  services: []
})

const selectedContact = ref(null)
const selectedClient = ref(null)
const attachmentFile = ref(null)
const attachmentFileName = ref('')

const form = reactive({
  subject: '',
  contact_id: null,
  client_id: null,
  priority: 'Medium',
  status: 'Open',
  department_id: null,
  message: '',
  assigned_to: null,
  tags: '',
  service_id: null,
  project_id: null,
  cc: '',
  predefined_reply: '',
  kb_link: '',
})

// Watch contact selection to auto-select client and populate details
watch(() => form.contact_id, (newVal) => {
  if (newVal) {
    const contact = metadata.value.contacts.find(c => c.id === newVal)
    if (contact) {
      selectedContact.value = contact
      form.client_id = contact.client_id
      const client = metadata.value.clients.find(c => c.id === contact.client_id)
      selectedClient.value = client
    }
  } else {
    selectedContact.value = null
    selectedClient.value = null
    form.client_id = null
  }
})

const filteredProjects = computed(() => {
  if (!form.client_id) return []
  return metadata.value.projects.filter(p => p.client_id === form.client_id)
})

const kbArticles = ref([])

const predefinedReplies = [
  { id: 'reply1', title: 'Thank you for contacting us...', content: 'Thank you for contacting us. We have received your ticket and our support team is looking into it. We will get back to you as soon as possible.' },
  { id: 'reply2', title: 'We have received your request...', content: 'We have received your request. A support representative will review your ticket shortly. Thank you for your patience.' },
  { id: 'reply3', title: 'SLA / Urgency update', content: 'We are prioritizing your request as per our Service Level Agreement (SLA). Our team is working on a resolution and will provide an update within the next hour.' }
]

async function loadKbArticles() {
  try {
    const res = await axios.get(`${BASE}/kb-articles`, { params: { status: 'published', per_page: 100 } })
    kbArticles.value = res.data.articles?.data || []
  } catch (err) {
    console.error('Failed to load KB articles', err)
  }
}

function handlePredefinedReplyChange(e) {
  const val = e.target.value
  if (!val) return
  const reply = predefinedReplies.find(r => r.id === val)
  if (reply) {
    if (form.message) {
      form.message += "\n" + reply.content
    } else {
      form.message = reply.content
    }
  }
  form.predefined_reply = ''
}

function handleKbLinkChange(e) {
  const articleId = e.target.value
  if (!articleId) return
  const article = kbArticles.value.find(a => a.id == articleId)
  if (article) {
    const path = window.location.origin + window.config.path + '/knowledge-base/article/' + article.id
    const linkText = `\nKnowledge Base Article: ${article.title} - ${path}`
    if (form.message) {
      form.message += linkText
    } else {
      form.message = linkText.trim()
    }
  }
  form.kb_link = ''
}

async function loadMetadata() {
  try {
    const res = await axios.get(`${BASE}/tickets/metadata`)
    metadata.value = res.data
  } catch (err) {
    console.error('Failed to load metadata', err)
  }
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15, search: search.value }
    if (activeFilter.value !== 'all') params.status = activeFilter.value
    if (priorityFilter.value) params.priority = priorityFilter.value

    const res = await axios.get(`${BASE}/tickets`, { params })
    tickets.value   = res.data.tickets?.data || []
    totalPages.value = res.data.tickets?.last_page || 1
    stats.value     = res.data.stats || {}
  } catch {
    // Demo mode - show sample data
    tickets.value = [
      { id: 1, subject: 'Cannot login to account', priority: 'High', status: 'Open', created_at: new Date().toISOString(), client: { company: 'Acme Corp' }, assignee: { name: 'Tom Kunze' } },
      { id: 2, subject: 'Invoice not generating', priority: 'Urgent', status: 'In Progress', created_at: new Date().toISOString(), client: { company: 'Nader Corp' }, assignee: null },
      { id: 3, subject: 'Request for new feature', priority: 'Low', status: 'Answered', created_at: new Date().toISOString(), client: null, assignee: { name: 'Alice' } },
    ]
    stats.value = { total: 3, open: 1, in_progress: 1, answered: 1, on_hold: 0, closed: 0 }
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function handleFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    attachmentFile.value = file
    attachmentFileName.value = file.name
  } else {
    attachmentFile.value = null
    attachmentFileName.value = ''
  }
}

function openCreateModal() {
  editingTicket.value = null
  Object.assign(form, {
    subject: '',
    contact_id: null,
    client_id: null,
    priority: 'Medium',
    status: 'Open',
    department_id: null,
    message: '',
    assigned_to: authStore.user?.id || null,
    tags: '',
    service_id: null,
    project_id: null,
    cc: '',
  })
  selectedContact.value = null
  selectedClient.value = null
  attachmentFile.value = null
  attachmentFileName.value = ''
  showModal.value = true
}

function editTicket(ticket) {
  editingTicket.value = ticket
  Object.assign(form, {
    subject: ticket.subject,
    contact_id: ticket.contact_id || null,
    client_id: ticket.client_id || null,
    priority: ticket.priority,
    status: ticket.status,
    department_id: ticket.department_id || null,
    message: ticket.message,
    assigned_to: ticket.assigned_to || null,
    tags: ticket.tags || '',
    service_id: ticket.service_id || null,
    project_id: ticket.project_id || null,
    cc: ticket.cc || '',
  })
  if (ticket.contact_id) {
    const contact = metadata.value.contacts.find(c => c.id === ticket.contact_id)
    selectedContact.value = contact || null
  } else {
    selectedContact.value = null
  }
  if (ticket.client_id) {
    const client = metadata.value.clients.find(c => c.id === ticket.client_id)
    selectedClient.value = client || null
  } else {
    selectedClient.value = null
  }
  attachmentFile.value = null
  attachmentFileName.value = ''
  showModal.value = true
}

async function viewTicket(ticket) {
  try {
    const res = await axios.get(`${BASE}/tickets/${ticket.id}`)
    viewingTicket.value = res.data
  } catch {
    viewingTicket.value = { ...ticket, replies: [], message: ticket.message || 'No message.' }
  }
}

async function saveTicket() {
  if (!form.subject || !form.message || !form.contact_id || !form.department_id) {
    return alert('Subject, Contact, Department, and Message are required')
  }
  saving.value = true
  try {
    if (editingTicket.value) {
      await axios.put(`${BASE}/tickets/${editingTicket.value.id}`, form)
    } else {
      await axios.post(`${BASE}/tickets`, form)
    }
    closeModal()
    load()
  } catch {
    alert('Failed to save ticket')
  } finally {
    saving.value = false
  }
}

async function deleteTicket(ticket) {
  if (!confirm(`Delete ticket "${ticket.subject}"?`)) return
  try {
    await axios.delete(`${BASE}/tickets/${ticket.id}`)
    load()
  } catch {
    alert('Failed to delete ticket')
  }
}

async function sendReply() {
  if (!replyMessage.value.trim()) return
  sendingReply.value = true
  try {
    const res = await axios.post(`${BASE}/tickets/${viewingTicket.value.id}/reply`, {
      message: replyMessage.value, is_admin_reply: true,
    })
    viewingTicket.value.replies = [...(viewingTicket.value.replies || []), res.data]
    viewingTicket.value.status = 'Answered'
    replyMessage.value = ''
    load()
  } catch {
    alert('Failed to send reply')
  } finally {
    sendingReply.value = false
  }
}

function closeModal() { showModal.value = false; editingTicket.value = null }

function exportPDF() {
  if (!selected.value.length) return alert('Select tickets to export')
  alert(`Exporting ${selected.value.length} ticket(s) as PDF...`)
}

function toggleAll() {
  selected.value = selectAll.value ? tickets.value.map(t => t.id) : []
}

function statusClass(status) {
  const map = { 'Open': 'open', 'In Progress': 'in-progress', 'Answered': 'answered', 'On Hold': 'on-hold', 'Closed': 'closed' }
  return map[status] || ''
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function getServiceName(serviceId) {
  if (!serviceId) return '—'
  const service = metadata.value.services.find(s => s.id === serviceId)
  return service ? service.name : '—'
}

onMounted(async () => {
  await loadMetadata()
  await loadKbArticles()
  await load()

  if (route.query.contact_id || route.query.userid) {
    openCreateModal()
    if (route.query.contact_id) {
      form.contact_id = parseInt(route.query.contact_id)
    }
    if (route.query.userid) {
      form.client_id = parseInt(route.query.userid)
    }
  }
})
</script>

<style scoped>
.tickets-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }

.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }

/* Stats */
.stats-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; cursor: pointer; transition: all 0.2s; text-align: center; }
.stat-card:hover, .stat-card.active { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.stat-card.open .stat-value    { color: #3b82f6; }
.stat-card.in-progress .stat-value { color: #f59e0b; }
.stat-card.answered .stat-value { color: #10b981; }
.stat-card.on-hold .stat-value  { color: #6366f1; }
.stat-card.closed .stat-value   { color: #94a3b8; }
.stat-value { font-size: 26px; font-weight: 800; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 2px; }

/* Filters */
.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; flex-wrap: wrap; }
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; background: #fff; outline: none; }
.btn-bulk-pdf { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-size: 13px; transition: background 0.15s; }
.btn-bulk-pdf:hover { background: #e2e8f0; }

/* Table */
.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 10px 14px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; }
.data-row { transition: background 0.15s; cursor: pointer; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

.ticket-id { color: #64748b; font-weight: 600; font-size: 12px; }
.ticket-subject { font-weight: 500; color: #1e293b; }

.priority-badge, .status-badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.priority-badge.urgent { background: #fef2f2; color: #dc2626; }
.priority-badge.high   { background: #fff7ed; color: #ea580c; }
.priority-badge.medium { background: #fefce8; color: #ca8a04; }
.priority-badge.low    { background: #f0fdf4; color: #16a34a; }
.status-badge.open       { background: #eff6ff; color: #3b82f6; }
.status-badge.in-progress { background: #fffbeb; color: #d97706; }
.status-badge.answered   { background: #f0fdf4; color: #16a34a; }
.status-badge.on-hold    { background: #f5f3ff; color: #7c3aed; }
.status-badge.closed     { background: #f8fafc; color: #94a3b8; }

.loading-cell, .empty-cell { text-align: center; padding: 40px; color: #94a3b8; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 32px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 8px; vertical-align: middle; }
@keyframes spin { to { transform: rotate(360deg); } }

.action-buttons { display: flex; gap: 4px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 4px 6px; border-radius: 6px; transition: background 0.15s; }
.action-btn:hover { background: #f1f5f9; }

/* Pagination */
.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; transition: all 0.15s; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: 0.4; cursor: not-allowed; }

/* Buttons */
.btn-primary { display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #1e9aff, #0d7bd6); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9000; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; overflow-y: auto; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.25); margin-top: auto; margin-bottom: auto; }
.modal-wide { max-width: 760px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; color: #1e293b; }
.close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; line-height: 1; }
.close-btn:hover { color: #475569; }
.modal-body { padding: 20px 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }

.form-row { margin-bottom: 16px; }
.form-row.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-row label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.form-input { width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.form-input:focus { border-color: #1e9aff; }
.form-textarea { resize: vertical; min-height: 100px; font-family: inherit; }

/* Ticket detail */
.ticket-meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
.meta-item { font-size: 12px; color: #64748b; }
.replies-section h3 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 20px 0 12px; }
.message-bubble { border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; }
.message-bubble.admin  { background: #eff6ff; border-left: 3px solid #3b82f6; }
.message-bubble.client { background: #f0fdf4; border-left: 3px solid #10b981; }
.bubble-header { font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; }
.bubble-content { font-size: 13px; color: #334155; line-height: 1.6; }
.reply-form { margin-top: 16px; display: flex; flex-direction: column; gap: 10px; }

@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 640px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .filters-bar { flex-direction: column; } }

/* Form grid and extra styles */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-row.span-2 { grid-column: span 2; }
.read-only-input { background-color: #f8fafc; color: #64748b; cursor: not-allowed; border-color: #e2e8f0; }
.attachment-upload { display: flex; align-items: center; gap: 12px; border: 1.5px dashed #cbd5e1; padding: 12px; border-radius: 8px; background-color: #f8fafc; width: 100%; box-sizing: border-box; }
.file-input-hidden { display: none; }
.btn-file-select { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 6px 12px; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.15s; }
.btn-file-select:hover { background: #f1f5f9; }
.file-name-display { font-size: 13px; color: #64748b; }

/* Ticket Detail grid */
.ticket-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 14px; background: #f8fafc; border-radius: 10px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
.info-item { display: flex; gap: 8px; font-size: 13px; }
.info-label { font-weight: 600; color: #475569; min-width: 100px; }
.info-value { color: #1e293b; }

/* Ticket body toolbar & select styling */
.ticket-body-toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 8px;
}
.ticket-body-select {
  border: 1.5px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 13px;
  color: #334155;
  background-color: #ffffff;
  cursor: pointer;
  outline: none;
  min-width: 200px;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.ticket-body-select:hover {
  border-color: #94a3b8;
  background-color: #f8fafc;
}
.ticket-body-select:focus {
  border-color: #1e9aff;
  box-shadow: 0 0 0 2px rgba(30, 154, 255, 0.1);
}
</style>
