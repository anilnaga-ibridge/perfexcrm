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
      <!-- Desktop Table View -->
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
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="action-btn delete-btn" title="Delete" @click="deleteForm(form)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list" v-if="!loading">
        <div 
          v-for="form in forms" 
          :key="'m-er-' + form.id"
          class="mobile-row-card"
          @click="openEdit(form)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="status-badge" :class="form.status">{{ form.status }}</span>
              <span class="font-extrabold text-sm text-sky-600">#{{ form.id }}</span>
            </div>
            <div v-if="form.assigned" class="assignee-cell">
              <img v-if="form.assigned.profile_image" :src="form.assigned.profile_image" class="assignee-avatar" />
              <div v-else class="assignee-avatar assignee-placeholder">{{ form.assigned.name.charAt(0) }}</div>
            </div>
          </div>

          <div class="font-bold text-sm text-slate-800 pt-1">
            {{ form.email || '—' }}
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
            <div class="tags-wrap col-span-2" v-if="form.tags">
              <span v-for="tag in parseTags(form.tags)" :key="tag" class="tag-pill">{{ tag }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-slate-500">
              <span class="text-slate-400">👤 Assigned:</span>
              <span class="font-semibold text-slate-700 truncate">{{ form.assigned?.name || '—' }}</span>
            </div>
            <div class="flex items-center justify-end gap-1.5 text-slate-500">
              <span class="text-slate-400">📅</span>
              <span>{{ formatDate(form.created_at) }}</span>
            </div>
          </div>
        </div>

        <div v-if="!forms.length" class="text-center p-6 text-slate-400 text-xs font-semibold">
          No forms found
        </div>
      </div>

      <div class="pagination" v-if="totalPages > 1">
        <button :disabled="page <= 1" @click="page--; load()">‹ Prev</button>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="page++; load()">Next ›</button>
      </div>
    </div>

    <!-- Create/Edit Drawer -->
    <a-drawer
      v-model:open="showModal"
      placement="right"
      :width="640"
      :footer-style="{ padding: '16px 24px', background: '#fafafa', borderTop: '1px solid #f1f5f9' }"
      :header-style="{ padding: '20px 24px', background: '#ffffff', borderBottom: '1px solid #f1f5f9' }"
      @close="showModal = false"
    >
      <template #title>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center shadow-md shrink-0 theme-primary-grad">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </div>
          <div class="min-w-0">
            <h3 class="text-base font-extrabold text-slate-800 m-0 leading-snug whitespace-nowrap">{{ editing ? 'Edit Form' : 'New Form' }}</h3>
            <p class="text-xs text-slate-400 font-medium m-0 mt-0.5 whitespace-nowrap">Configure web estimate form parameters & notifications</p>
          </div>
        </div>
      </template>

      <div class="space-y-5">
        <!-- Tab Pills -->
        <div class="p-1 bg-slate-100 rounded-2xl border border-slate-200/80 flex items-center gap-1">
          <button
            v-for="t in tabs"
            :key="t.key"
            type="button"
            class="flex-1 py-2 px-3 text-xs font-bold rounded-xl transition-all cursor-pointer text-center"
            :class="activeTab === t.key ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-700'"
            @click="activeTab = t.key"
          >
            {{ t.label }}
          </button>
        </div>

        <div class="p-1">
          <!-- General Tab -->
          <div v-if="activeTab === 'general'" class="space-y-4">
            <div>
              <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
                Form Name <span class="text-rose-500">*</span>
              </label>
              <input v-model="form.name" placeholder="e.g. Website Estimate Request" class="w-full h-11 px-4 text-xs font-semibold theme-input-ctrl" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Use Google Recaptcha</label>
              <div class="flex items-center gap-6 p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                  <input type="radio" v-model="form.recaptcha_enabled" :value="false" class="theme-accent-chk" /> No
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                  <input type="radio" v-model="form.recaptcha_enabled" :value="true" class="theme-accent-chk" /> Yes
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Language <span class="text-rose-500">*</span></label>
                <select v-model="form.language" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl cursor-pointer">
                  <option value="English">English</option>
                  <option value="Spanish">Spanish</option>
                  <option value="French">French</option>
                  <option value="German">German</option>
                  <option value="Italian">Italian</option>
                  <option value="Portuguese">Portuguese</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Status <span class="text-rose-500">*</span></label>
                <select v-model="form.status" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl cursor-pointer">
                  <option value="processing">Processing</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Responsible (Assignee)</label>
                <select v-model="form.assigned_to" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl cursor-pointer">
                  <option :value="null">— Select Staff —</option>
                  <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Email</label>
                <input v-model="form.email" placeholder="contact@example.com" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl" />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Tags</label>
              <div class="p-2.5 border border-slate-200/80 rounded-2xl bg-slate-50/50 flex flex-wrap items-center gap-1.5 focus-within:bg-white theme-input-ctrl transition-all">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="(tag, i) in form.tagList"
                    :key="i"
                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg inline-flex items-center gap-1.5 shadow-2xs theme-tag-chip"
                  >
                    {{ tag }}
                    <button type="button" @click="form.tagList.splice(i, 1); form.tags = form.tagList.join(',')" class="hover:opacity-75 font-extrabold text-xs">&times;</button>
                  </span>
                </div>
                <input
                  v-model="tagInput"
                  placeholder="Type and press Enter..."
                  class="text-xs font-semibold bg-transparent outline-none flex-1 min-w-[140px] text-slate-800 placeholder-slate-400"
                  @keydown.enter.prevent="addTag"
                  @keydown.,.prevent="addTag"
                />
              </div>
            </div>
          </div>

          <!-- Branding Tab -->
          <div v-else-if="activeTab === 'branding'" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Submit Button Text <span class="text-rose-500">*</span></label>
              <input v-model="form.submit_btn_text" placeholder="Submit" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Submit Button Background Color</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.submit_btn_bg_color" class="w-10 h-10 p-1 border border-slate-200 rounded-xl cursor-pointer bg-white shrink-0" />
                <input v-model="form.submit_btn_bg_color" placeholder="#84c529" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Submit Button Text Color</label>
              <div class="flex items-center gap-3">
                <input type="color" v-model="form.submit_btn_text_color" class="w-10 h-10 p-1 border border-slate-200 rounded-xl cursor-pointer bg-white shrink-0" />
                <input v-model="form.submit_btn_text_color" placeholder="#ffffff" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl" />
              </div>
            </div>
          </div>

          <!-- Submission Tab -->
          <div v-else-if="activeTab === 'submission'" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-2">What should happen after a visitor submits this form?</label>
              <div class="space-y-2.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80">
                <label class="flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                  <input type="radio" v-model="form.submission_action" value="message" class="theme-accent-chk" />
                  <span>Display thank you message</span>
                </label>
                <label class="flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                  <input type="radio" v-model="form.submission_action" value="redirect" class="theme-accent-chk" />
                  <span>Redirect to another website</span>
                </label>
              </div>
            </div>
            <div v-if="form.submission_action === 'message'">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Message to show after submission <span class="text-rose-500">*</span></label>
              <textarea v-model="form.submission_message" placeholder="Thank you for your submission..." class="w-full p-4 text-xs font-semibold theme-input-ctrl text-slate-800 placeholder-slate-400 resize-y leading-relaxed" rows="4"></textarea>
            </div>
            <div v-if="form.submission_action === 'redirect'">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Redirect URL <span class="text-rose-500">*</span></label>
              <input v-model="form.submission_redirect_url" placeholder="https://example.com/thank-you" class="w-full h-10 px-3.5 text-xs font-semibold theme-input-ctrl" />
            </div>
          </div>

          <!-- Notifications Tab -->
          <div v-else-if="activeTab === 'notifications'" class="space-y-4">
            <label class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 cursor-pointer text-xs font-bold text-slate-700 select-none">
              <input type="checkbox" v-model="form.notify_enabled" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
              <span>Notify when estimate request submitted in this form</span>
            </label>

            <div v-if="form.notify_enabled" class="space-y-4 pt-2">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Notification Target</label>
                <div class="space-y-2.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80">
                  <label class="flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="radio" v-model="form.notify_type" value="specific" class="theme-accent-chk" /> Specific Staff Members
                  </label>
                  <label class="flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="radio" v-model="form.notify_type" value="responsible" class="theme-accent-chk" /> Responsible person
                  </label>
                </div>
              </div>

              <div v-if="form.notify_type === 'specific'">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Staff Members to Notify</label>
                <div class="max-h-44 overflow-y-auto p-3 border border-slate-200/80 rounded-2xl bg-slate-50/50 space-y-1">
                  <label v-for="s in staffList" :key="s.id" class="flex items-center justify-between p-2 rounded-xl hover:bg-white transition-all cursor-pointer">
                    <span class="text-xs font-semibold text-slate-700">{{ s.name }}</span>
                    <input type="checkbox" :value="s.id" v-model="form.notifyStaffIds" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all cursor-pointer border border-slate-200/80"
            @click="showModal = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-7 py-2.5 text-xs font-bold text-white rounded-xl cursor-pointer shadow-md transition-all flex items-center gap-2 theme-primary-grad"
            @click="saveForm"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ editing ? 'Update Form' : 'Create Form' }}
          </button>
        </div>
      </template>
    </a-drawer>
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

/* Dynamic Theme Utility Classes */
.theme-primary-btn {
  background: var(--theme-primary, #6366f1) !important;
  color: #ffffff !important;
}
.theme-primary-btn:hover {
  background: var(--theme-primary-hover, #4f46e5) !important;
}
.theme-primary-grad {
  background: linear-gradient(135deg, var(--theme-primary, #6366f1) 0%, var(--theme-primary-hover, #4f46e5) 100%) !important;
  color: #ffffff !important;
}
.theme-input-ctrl {
  background-color: rgba(248, 250, 252, 0.8);
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}
.theme-input-ctrl:focus {
  background-color: #ffffff;
  border-color: var(--theme-primary, #6366f1) !important;
  box-shadow: 0 0 0 4px var(--theme-primary-light, rgba(99, 102, 241, 0.15)) !important;
  outline: none;
}
.theme-accent-chk {
  accent-color: var(--theme-primary, #6366f1) !important;
}
.theme-tag-chip {
  background: var(--theme-primary-light, rgba(99, 102, 241, 0.12)) !important;
  color: var(--theme-primary, #6366f1) !important;
  border: 1px solid var(--theme-primary-light, rgba(99, 102, 241, 0.25)) !important;
}
/* Mobile Cards List Hidden by Default on Desktop */
.mobile-cards-list {
  display: none;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
  }
  .stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .filters-bar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 12px !important;
  }
  .search-wrap, .search-input, .filter-select {
    width: 100% !important;
  }
  .data-table {
    display: none !important;
  }
  .mobile-cards-list {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
  }
}

@media (max-width: 560px) {
  .stats-grid {
    grid-template-columns: 1fr !important;
  }
  .form-row { flex-direction: column; }
}
</style>
