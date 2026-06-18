<template>
  <div class="er-page">
    <div class="page-header">
      <div class="header-left">
        <h1>Estimate Request Forms</h1>
        <span class="subtitle">Manage estimate request form definitions</span>
      </div>
      <button class="btn-primary" @click="openCreate">+ New Form</button>
    </div>

    <div class="stats-grid">
      <div class="stat-card" :class="{active: !statusFilter}" @click="statusFilter=''; load()">
        <div class="stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-value">{{ stats.total || 0 }}</div>
        <div class="stat-label">Total Forms</div>
      </div>
      <div class="stat-card processing" :class="{active: statusFilter==='processing'}" @click="statusFilter='processing'; load()">
        <div class="stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-value">{{ stats.processing || 0 }}</div>
        <div class="stat-label">Processing</div>
      </div>
      <div class="stat-card active" :class="{active: statusFilter==='active'}" @click="statusFilter='active'; load()">
        <div class="stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="stat-value">{{ stats.active || 0 }}</div>
        <div class="stat-label">Active</div>
      </div>
      <div class="stat-card inactive" :class="{active: statusFilter==='inactive'}" @click="statusFilter='inactive'; load()">
        <div class="stat-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <div class="stat-value">{{ stats.inactive || 0 }}</div>
        <div class="stat-label">Inactive</div>
      </div>
    </div>

    <div class="filters-bar">
      <div class="search-wrap">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" placeholder="Search by name or email..." class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Status</option>
        <option value="processing">Processing</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Email</th>
            <th>Tags</th>
            <th>Assigned</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="loading-cell"><div class="loader"></div> Loading...</td>
          </tr>
          <tr v-else-if="!forms.length">
            <td colspan="7" class="empty-cell">
              <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>No forms found</p>
                <button class="btn-empty" @click="openCreate">Create your first form</button>
              </div>
            </td>
          </tr>
          <tr v-for="form in forms" :key="form.id" class="data-row">
            <td class="id-cell">#{{ form.id }}</td>
            <td class="email-cell">{{ form.email || '—' }}</td>
            <td>
              <div class="tags-wrap">
                <span v-for="tag in parseTags(form.tags)" :key="tag" class="tag-pill">{{ tag }}</span>
                <span v-if="!form.tags" class="no-tags">—</span>
              </div>
            </td>
            <td>
              <div v-if="form.assigned" class="assignee-cell">
                <img v-if="form.assigned.profile_image" :src="form.assigned.profile_image" class="assignee-avatar" />
                <div v-else class="assignee-avatar assignee-placeholder">{{ form.assigned.name.charAt(0) }}</div>
                <span>{{ form.assigned.name }}</span>
              </div>
              <span v-else class="no-tags">—</span>
            </td>
            <td><span class="status-badge" :class="form.status">{{ form.status }}</span></td>
            <td class="date-cell">{{ formatDate(form.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" title="Edit" @click="openEdit(form)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="action-btn delete-btn" title="Delete" @click="deleteForm(form)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
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

    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal-box form-modal">
          <div class="modal-header">
            <h2>{{ editing ? 'Edit Form' : 'New Form' }}</h2>
            <button class="close-btn" @click="showModal = false">&times;</button>
          </div>

          <div class="form-tabs">
            <button v-for="t in tabs" :key="t.key" class="form-tab" :class="{active: activeTab === t.key}" @click="activeTab = t.key">{{ t.label }}</button>
          </div>

          <div class="modal-body">
            <div v-if="activeTab === 'general'" class="tab-content">
              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Form Name <span class="req">*</span></label>
                  <input v-model="form.name" placeholder="e.g. Website Estimate Request" class="er-input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Use Google Recaptcha</label>
                  <div class="radio-group">
                    <label class="radio-label"><input type="radio" v-model="form.recaptcha_enabled" :value="false" /> No</label>
                    <label class="radio-label"><input type="radio" v-model="form.recaptcha_enabled" :value="true" /> Yes</label>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Language <span class="req">*</span></label>
                  <select v-model="form.language" class="er-input">
                    <option value="English">English</option>
                    <option value="Spanish">Spanish</option>
                    <option value="French">French</option>
                    <option value="German">German</option>
                    <option value="Italian">Italian</option>
                    <option value="Portuguese">Portuguese</option>
                  </select>
                </div>
                <div class="form-group flex-1">
                  <label>Status <span class="req">*</span></label>
                  <select v-model="form.status" class="er-input">
                    <option value="processing">Processing</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Responsible (Assignee)</label>
                  <select v-model="form.assigned_to" class="er-input">
                    <option :value="null">— Select Staff —</option>
                    <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                </div>
                <div class="form-group flex-1">
                  <label>Email</label>
                  <input v-model="form.email" placeholder="contact@example.com" class="er-input" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Tags</label>
                  <div class="tag-input-wrap">
                    <div class="tag-list">
                      <span v-for="(tag, i) in form.tagList" :key="i" class="tag-pill">
                        {{ tag }}
                        <button class="tag-pill-del" @click="form.tagList.splice(i, 1); form.tags = form.tagList.join(',')">&times;</button>
                      </span>
                      <input v-model="tagInput" placeholder="Type and press Enter" class="tag-field" @keydown.enter.prevent="addTag" @keydown.,.prevent="addTag" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="activeTab === 'branding'" class="tab-content">
              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Submit button text <span class="req">*</span></label>
                  <input v-model="form.submit_btn_text" placeholder="Submit" class="er-input" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Submit button background color</label>
                  <div class="color-input-row">
                    <input type="color" v-model="form.submit_btn_bg_color" class="color-picker" />
                    <input v-model="form.submit_btn_bg_color" placeholder="#84c529" class="er-input color-text-input" />
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group flex-1">
                  <label>Submit button background text</label>
                  <div class="color-input-row">
                    <input type="color" v-model="form.submit_btn_text_color" class="color-picker" />
                    <input v-model="form.submit_btn_text_color" placeholder="#ffffff" class="er-input color-text-input" />
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="activeTab === 'submission'" class="tab-content">
              <div class="form-row">
                <div class="form-group flex-1">
                  <label>What should happen after a visitor submits this form?</label>
                  <div class="radio-group vertical">
                    <label class="radio-label"><input type="radio" v-model="form.submission_action" value="message" /> Display thank you message</label>
                    <label class="radio-label"><input type="radio" v-model="form.submission_action" value="redirect" /> Redirect to another website</label>
                  </div>
                </div>
              </div>
              <div v-if="form.submission_action === 'message'" class="form-row">
                <div class="form-group flex-1">
                  <label>Message to show after form is successfully submitted <span class="req">*</span></label>
                  <textarea v-model="form.submission_message" placeholder="Thank you for your submission. We will get back to you shortly." class="er-input er-textarea" rows="4"></textarea>
                </div>
              </div>
              <div v-if="form.submission_action === 'redirect'" class="form-row">
                <div class="form-group flex-1">
                  <label>Redirect URL <span class="req">*</span></label>
                  <input v-model="form.submission_redirect_url" placeholder="https://example.com/thank-you" class="er-input" />
                </div>
              </div>
            </div>

            <div v-else-if="activeTab === 'notifications'" class="tab-content">
              <div class="form-row">
                <div class="form-group flex-1">
                  <label class="toggle-row">
                    <input type="checkbox" v-model="form.notify_enabled" class="toggle-checkbox" />
                    <span class="toggle-switch"></span>
                    Notify when estimate request submitted in this form
                  </label>
                </div>
              </div>

              <div v-if="form.notify_enabled">
                <div class="form-row">
                  <div class="form-group flex-1">
                    <label>Staff Members with roles</label>
                    <div class="radio-group vertical">
                      <label class="radio-label"><input type="radio" v-model="form.notify_type" value="specific" /> Specific Staff Members</label>
                      <label class="radio-label"><input type="radio" v-model="form.notify_type" value="responsible" /> Responsible person</label>
                    </div>
                  </div>
                </div>

                <div v-if="form.notify_type === 'specific'" class="form-row">
                  <div class="form-group flex-1">
                    <label>Staff Members to Notify</label>
                    <div class="staff-checkbox-grid">
                      <label v-for="s in staffList" :key="s.id" class="staff-checkbox-item">
                        <input type="checkbox" :value="s.id" v-model="form.notifyStaffIds" />
                        <span>{{ s.name }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn-cancel" @click="showModal = false">Cancel</button>
            <button class="btn-save" @click="saveForm">{{ editing ? 'Update Form' : 'Create Form' }}</button>
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
const forms       = ref([])
const stats       = ref({})
const staffList   = ref([])
const loading     = ref(false)
const search      = ref('')
const statusFilter = ref('')
const page        = ref(1)
const totalPages  = ref(1)
const showModal   = ref(false)
const editing     = ref(false)
const activeTab   = ref('general')
const tagInput    = ref('')

const tabs = [
  { key: 'general', label: 'General' },
  { key: 'branding', label: 'Branding' },
  { key: 'submission', label: 'Submission' },
  { key: 'notifications', label: 'Notifications' },
]

const defaultForm = () => ({
  name: '',
  email: '',
  tags: '',
  tagList: [],
  assigned_to: null,
  status: 'processing',
  language: 'English',
  recaptcha_enabled: false,
  submit_btn_text: 'Submit',
  submit_btn_bg_color: '#84c529',
  submit_btn_text_color: '#ffffff',
  submission_action: 'message',
  submission_message: '',
  submission_redirect_url: '',
  notify_enabled: false,
  notify_type: 'specific',
  notifyStaffIds: [],
})

const form = reactive(defaultForm())

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) {
    form.tagList.push(val)
    form.tags = form.tagList.join(',')
  }
  tagInput.value = ''
}

function parseTags(tags) {
  if (!tags) return []
  return String(tags).split(',').map(t => t.trim()).filter(Boolean)
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 15, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/estimate-request-forms`, { params })
    forms.value     = res.data.forms?.data || []
    totalPages.value = res.data.forms?.last_page || 1
    stats.value      = res.data.stats || {}
    staffList.value  = res.data.staff || []
  } catch {
    forms.value = [
      { id: 1, name: 'Website Estimate', email: 'contact@website.com', tags: 'web,design', assigned: { id: 1, name: 'Tre Stamm', profile_image: null }, status: 'processing', language: 'English', recaptcha_enabled: true, submit_btn_text: 'Submit', submit_btn_bg_color: '#84c529', submit_btn_text_color: '#ffffff', submission_action: 'message', submission_message: 'Thanks!', notify_enabled: false, created_at: new Date().toISOString() },
      { id: 2, name: 'Consulting Quote', email: 'hello@consulting.io', tags: 'consulting', assigned: { id: 2, name: 'Sarah Lane', profile_image: null }, status: 'active', language: 'English', recaptcha_enabled: false, submit_btn_text: 'Send Request', submit_btn_bg_color: '#0b6eff', submit_btn_text_color: '#ffffff', submission_action: 'redirect', submission_redirect_url: 'https://example.com/thanks', notify_enabled: true, notify_type: 'responsible', created_at: new Date(Date.now() - 86400000).toISOString() },
      { id: 3, name: 'SEO Audit Form', email: 'info@seo.co', tags: 'seo,marketing', assigned: null, status: 'processing', language: 'Spanish', recaptcha_enabled: true, submit_btn_text: 'Submit', submit_btn_bg_color: '#84c529', submit_btn_text_color: '#ffffff', submission_action: 'message', submission_message: 'We will review your request.', notify_enabled: true, notify_type: 'specific', notify_staff_ids: '1,2', created_at: new Date(Date.now() - 172800000).toISOString() },
    ]
    stats.value = { total: 3, processing: 2, active: 1, inactive: 0 }
    staffList.value = [
      { id: 1, name: 'Tre Stamm', profile_image: null },
      { id: 2, name: 'Sarah Lane', profile_image: null },
    ]
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  Object.assign(form, defaultForm())
  editing.value = false
  activeTab.value = 'general'
  showModal.value = true
}

function openEdit(f) {
  const notifyIds = f.notify_staff_ids ? String(f.notify_staff_ids).split(',').map(Number).filter(Boolean) : []
  Object.assign(form, {
    name: f.name || '',
    email: f.email || '',
    tags: f.tags || '',
    tagList: parseTags(f.tags),
    assigned_to: f.assigned?.id || null,
    status: f.status || 'processing',
    language: f.language || 'English',
    recaptcha_enabled: f.recaptcha_enabled || false,
    submit_btn_text: f.submit_btn_text || 'Submit',
    submit_btn_bg_color: f.submit_btn_bg_color || '#84c529',
    submit_btn_text_color: f.submit_btn_text_color || '#ffffff',
    submission_action: f.submission_action || 'message',
    submission_message: f.submission_message || '',
    submission_redirect_url: f.submission_redirect_url || '',
    notify_enabled: f.notify_enabled || false,
    notify_type: f.notify_type || 'specific',
    notifyStaffIds: notifyIds,
  })
  editing.value = f
  activeTab.value = 'general'
  showModal.value = true
}

async function saveForm() {
  if (!form.name) return alert('Form Name is required')
  const payload = {
    name: form.name,
    email: form.email || null,
    tags: form.tags || null,
    assigned_to: form.assigned_to || null,
    status: form.status,
    language: form.language,
    recaptcha_enabled: form.recaptcha_enabled,
    submit_btn_text: form.submit_btn_text || 'Submit',
    submit_btn_bg_color: form.submit_btn_bg_color || '#84c529',
    submit_btn_text_color: form.submit_btn_text_color || '#ffffff',
    submission_action: form.submission_action || 'message',
    submission_message: form.submission_message || null,
    submission_redirect_url: form.submission_redirect_url || null,
    notify_enabled: form.notify_enabled || false,
    notify_type: form.notify_type || 'specific',
    notify_staff_ids: form.notifyStaffIds.length ? form.notifyStaffIds.join(',') : null,
  }
  try {
    if (editing.value) {
      await axios.put(`${BASE}/estimate-request-forms/${editing.value.id}`, payload)
    } else {
      await axios.post(`${BASE}/estimate-request-forms`, payload)
    }
    showModal.value = false
    load()
  } catch (e) {
    const msg = e.response?.data?.message || 'Failed to save form'
    alert(msg)
  }
}

async function deleteForm(f) {
  if (!confirm(`Delete form "${f.name}"?`)) return
  try {
    await axios.delete(`${BASE}/estimate-request-forms/${f.id}`)
    load()
  } catch {
    alert('Failed to delete')
  }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(load)
</script>

<style scoped>
.er-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-left h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; margin-top: 2px; }
.btn-primary { display: flex; align-items: center; gap: 6px; padding: 10px 20px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,154,255,.35); }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; cursor: pointer; transition: all .2s; }
.stat-card:hover { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.stat-card.active { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.stat-card .stat-icon { color: #94a3b8; margin-bottom: 8px; }
.stat-card.processing .stat-value { color: #d97706; }
.stat-card.active .stat-value { color: #10b981; }
.stat-card.inactive .stat-value { color: #94a3b8; }
.stat-value { font-size: 28px; font-weight: 800; color: #1e293b; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 2px; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; }
.search-wrap { position: relative; flex: 1; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; }
.search-input { width: 100%; padding: 9px 12px 9px 36px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; outline: none; background: #fff; box-sizing: border-box; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; cursor: pointer; outline: none; background: #fff; }

.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 11px 16px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.id-cell { font-weight: 700; color: #1e9aff; }
.email-cell { color: #1e293b; }
.date-cell { color: #64748b; font-size: 12px; }
.loading-cell, .empty-cell { text-align: center; padding: 48px 20px; color: #94a3b8; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.btn-empty { padding: 8px 18px; background: #1e9aff; color: #fff; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin .7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

.tags-wrap { display: flex; gap: 4px; flex-wrap: wrap; }
.tag-pill { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; background: #eef2ff; color: #4f46e5; border-radius: 20px; font-size: 11px; font-weight: 600; }
.no-tags { color: #94a3b8; }

.assignee-cell { display: flex; align-items: center; gap: 6px; }
.assignee-avatar { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; }
.assignee-placeholder { background: #1e9aff; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }

.status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-badge.processing { background: #fffbeb; color: #d97706; }
.status-badge.active { background: #f0fdf4; color: #16a34a; }
.status-badge.inactive { background: #f1f5f9; color: #64748b; }

.action-buttons { display: flex; gap: 4px; }
.action-btn { background: none; border: 1px solid transparent; cursor: pointer; padding: 5px 7px; border-radius: 6px; color: #64748b; transition: all .15s; }
.action-btn:hover { background: #f1f5f9; color: #1e293b; border-color: #e2e8f0; }
.action-btn.delete-btn:hover { color: #dc2626; background: #fef2f2; border-color: #fecaca; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: .4; cursor: not-allowed; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 680px; box-shadow: 0 20px 60px rgba(0,0,0,.25); max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; flex-shrink: 0; }
.modal-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #94a3b8; padding: 0; line-height: 1; }

.form-tabs { display: flex; gap: 0; border-bottom: 1.5px solid #e2e8f0; padding: 0 24px; flex-shrink: 0; }
.form-tab { padding: 12px 16px; background: none; border: none; font-size: 13px; font-weight: 500; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1.5px; transition: all .15s; }
.form-tab:hover { color: #1e293b; }
.form-tab.active { color: #1e9aff; border-bottom-color: #1e9aff; }

.modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }
.tab-content { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: flex; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group label { font-size: 12px; font-weight: 600; color: #374151; }
.req { color: #dc2626; }
.er-input { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; background: #fff; }
.er-input:focus { border-color: #1e9aff; }
select.er-input { cursor: pointer; }
.flex-1 { flex: 1; }

.radio-group { display: flex; gap: 20px; padding: 6px 0; }
.radio-group.vertical { flex-direction: column; gap: 8px; }
.radio-label { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; cursor: pointer; }
.radio-label input { accent-color: #1e9aff; }

.color-input-row { display: flex; gap: 8px; align-items: center; }
.color-picker { width: 40px; height: 38px; padding: 2px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: none; }
.color-text-input { flex: 1; }

.er-textarea { resize: vertical; min-height: 80px; font-family: inherit; }

.toggle-row { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: #334155; }
.toggle-checkbox { display: none; }
.toggle-switch { position: relative; width: 36px; height: 20px; background: #e2e8f0; border-radius: 10px; transition: background .2s; flex-shrink: 0; }
.toggle-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: transform .2s; }
.toggle-checkbox:checked + .toggle-switch { background: #1e9aff; }
.toggle-checkbox:checked + .toggle-switch::after { transform: translateX(16px); }

.staff-checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 6px; padding: 8px 0; }
.staff-checkbox-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; cursor: pointer; padding: 4px 8px; border-radius: 6px; }
.staff-checkbox-item:hover { background: #f8fafc; }
.staff-checkbox-item input { accent-color: #1e9aff; }

.tag-input-wrap { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 5px 8px; min-height: 38px; }
.tag-list { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }
.tag-field { border: none; outline: none; font-size: 13px; padding: 4px 2px; min-width: 120px; flex: 1; }
.tag-pill-del { background: none; border: none; cursor: pointer; color: #4f46e5; font-size: 14px; padding: 0 0 0 2px; line-height: 1; }

.placeholder-tab { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; gap: 12px; color: #94a3b8; font-size: 14px; }

.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; flex-shrink: 0; }
.btn-cancel { padding: 9px 18px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; color: #475569; }
.btn-save { padding: 9px 22px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-save:hover { box-shadow: 0 4px 14px rgba(30,154,255,.35); }
</style>
