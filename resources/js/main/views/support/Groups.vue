<template>
  <div class="gr-page">
    <div class="page-header">
      <div class="header-left">
        <h1>Knowledge Base Groups</h1>
        <div class="breadcrumb">
          <router-link :to="{ name: 'admin.knowledge-base' }" class="breadcrumb-link">Articles</router-link>
          <span class="breadcrumb-sep">→</span>
          <span class="breadcrumb-current">Groups</span>
        </div>
      </div>
      <button class="btn-primary" @click="openCreate">+ New Group</button>
    </div>

    <div class="filters-bar">
      <div class="search-wrap">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" placeholder="Search..." class="search-input" @input="onSearch" />
      </div>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Active</th>
            <th class="th-actions">Options</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="3" class="loading-cell"><div class="loader"></div> Loading...</td>
          </tr>
          <tr v-else-if="!categories.length">
            <td colspan="3" class="empty-cell">No groups found</td>
          </tr>
          <tr v-for="g in categories" :key="g.id" class="data-row">
            <td class="name-cell">
              <span class="color-dot" :style="{ background: g.color || '#6b7280' }"></span>
              {{ g.name }}
            </td>
            <td>
              <span v-if="!g.disabled" class="active-badge active-yes">Yes</span>
              <span v-else class="active-badge active-no">No</span>
            </td>
            <td>
              <div class="action-links">
                <button class="link-btn" @click="editCategory(g)">Edit</button>
                <span class="link-sep">|</span>
                <button class="link-btn link-danger" @click="deleteCategory(g)">Delete</button>
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
        <div class="modal-box">
          <div class="modal-header">
            <h2>{{ editing ? 'Edit Group' : 'New Group' }}</h2>
            <button class="close-btn" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-section">
              <div class="field-group">
                <label class="field-label">Group Name <span class="req">*</span></label>
                <input v-model="form.name" placeholder="Group name" class="field-input" />
              </div>
              <div class="field-group">
                <label class="field-label">Color</label>
                <div class="color-pick-row">
                  <input type="color" v-model="form.color" class="color-pick" />
                  <div class="color-preview" :style="{ background: form.color }"></div>
                  <input v-model="form.color" placeholder="#6b7280" class="field-input color-field" />
                </div>
              </div>
              <div class="field-group">
                <label class="field-label">Short description</label>
                <input v-model="form.description" placeholder="Brief description of this group" class="field-input" />
              </div>
              <div class="field-row">
                <div class="field-group field-group-sm">
                  <label class="field-label">Order</label>
                  <input v-model.number="form.order_num" type="number" min="0" class="field-input order-field" />
                </div>
              </div>
              <div class="field-group">
                <label class="toggle-item">
                  <input type="checkbox" v-model="form.disabled" class="toggle-input" />
                  <span class="toggle-track"></span>
                  <span class="toggle-label-text">Disabled</span>
                </label>
                <p class="toggle-note">Note: All articles in this group will be hidden if disabled is checked</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="showModal = false">Cancel</button>
            <button class="btn-save" @click="saveCategory">{{ editing ? 'Update Group' : 'Create Group' }}</button>
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
const categories  = ref([])
const loading     = ref(false)
const search      = ref('')
const page        = ref(1)
const totalPages  = ref(1)
const showModal   = ref(false)
const editing     = ref(null)

const defaultForm = () => ({
  name: '',
  color: '#6b7280',
  description: '',
  order_num: 6,
  disabled: false,
})

const form = reactive(defaultForm())

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: 25, search: search.value }
    const res = await axios.get(`${BASE}/kb-categories`, { params })
    categories.value = res.data.categories?.data || []
    totalPages.value = res.data.categories?.last_page || 1
  } catch {
    categories.value = [
      { id: 1, name: 'Sales', color: '#10b981', description: 'Sales-related articles', order_num: 1, disabled: false, articles_count: 6 },
      { id: 2, name: 'Info', color: '#3b82f6', description: 'General information', order_num: 2, disabled: false, articles_count: 2 },
      { id: 3, name: 'Support', color: '#8b5cf6', description: 'Technical support', order_num: 3, disabled: false, articles_count: 0 },
      { id: 4, name: 'Development', color: '#f59e0b', description: 'Developer guides', order_num: 4, disabled: false, articles_count: 0 },
      { id: 5, name: 'Security', color: '#ef4444', description: 'Security docs', order_num: 5, disabled: true, articles_count: 0 },
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
  editing.value = null
  showModal.value = true
}

function editCategory(g) {
  editing.value = g
  Object.assign(form, {
    name: g.name || '',
    color: g.color || '#6b7280',
    description: g.description || '',
    order_num: g.order_num ?? 6,
    disabled: g.disabled || false,
  })
  showModal.value = true
}

async function saveCategory() {
  if (!form.name) return alert('Group name is required')
  const payload = {
    name: form.name,
    color: form.color || '#6b7280',
    description: form.description || null,
    order_num: form.order_num ?? 6,
    disabled: form.disabled || false,
  }
  try {
    if (editing.value) {
      await axios.put(`${BASE}/kb-categories/${editing.value.id}`, payload)
    } else {
      await axios.post(`${BASE}/kb-categories`, payload)
    }
    showModal.value = false
    load()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to save group')
  }
}

async function deleteCategory(g) {
  if (!confirm(`Delete group "${g.name}"?`)) return
  try {
    await axios.delete(`${BASE}/kb-categories/${g.id}`)
    load()
  } catch { alert('Failed to delete') }
}

onMounted(load)
</script>

<style scoped>
.gr-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-left h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 13px; }
.breadcrumb-link { color: #1e9aff; text-decoration: none; font-weight: 500; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { color: #94a3b8; }
.breadcrumb-current { color: #64748b; }
.btn-primary { padding: 10px 20px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,154,255,.35); }

.filters-bar { display: flex; gap: 12px; margin-bottom: 16px; align-items: center; }
.search-wrap { position: relative; flex: 1; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; }
.search-input { width: 100%; padding: 9px 12px 9px 36px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; outline: none; background: #fff; box-sizing: border-box; }
.search-input:focus { border-color: #1e9aff; }

.table-container { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { padding: 11px 16px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1.5px solid #e2e8f0; }
.th-actions { text-align: right; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.name-cell { display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; }
.color-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.loading-cell, .empty-cell { text-align: center; padding: 48px 20px; color: #94a3b8; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin .7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

.active-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.active-yes { background: #f0fdf4; color: #16a34a; }
.active-no { background: #fef2f2; color: #dc2626; }

.action-links { display: flex; justify-content: flex-end; gap: 6px; align-items: center; }
.link-btn { background: none; border: none; cursor: pointer; font-size: 12px; color: #1e9aff; padding: 0; }
.link-btn:hover { text-decoration: underline; }
.link-danger { color: #dc2626; }
.link-sep { color: #e2e8f0; font-size: 12px; }

.pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding: 14px; font-size: 13px; color: #64748b; }
.pagination button { padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:hover:not(:disabled) { border-color: #1e9aff; color: #1e9aff; }
.pagination button:disabled { opacity: .4; cursor: not-allowed; }

/* ── Modal ───────────────────────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 60px rgba(0,0,0,.3); max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 22px 28px 18px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
.modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; color: #0f172a; }
.close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #94a3b8; padding: 4px 8px; border-radius: 8px; line-height: 1; transition: all .15s; }
.close-btn:hover { background: #f1f5f9; color: #475569; }
.modal-body { padding: 24px 28px; overflow-y: auto; flex: 1; }

.form-section { display: flex; flex-direction: column; gap: 18px; }
.field-group { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 13px; font-weight: 600; color: #0f172a; letter-spacing: .01em; }
.req { color: #ef4444; margin-left: 1px; }
.field-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; background: #fff; box-sizing: border-box; transition: border-color .15s; color: #0f172a; }
.field-input:focus { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.field-input::placeholder { color: #94a3b8; }
.color-pick-row { display: flex; gap: 10px; align-items: center; }
.color-pick { width: 44px; height: 40px; padding: 2px; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; background: none; }
.color-preview { width: 28px; height: 28px; border-radius: 8px; border: 1.5px solid #e2e8f0; flex-shrink: 0; }
.color-field { flex: 1; }
.field-row { display: flex; }
.field-group-sm { max-width: 110px; }
.order-field { width: 100%; text-align: center; }

.toggle-item { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.toggle-input { display: none; }
.toggle-track { position: relative; width: 36px; height: 20px; background: #e2e8f0; border-radius: 10px; transition: background .2s; flex-shrink: 0; }
.toggle-track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
.toggle-input:checked + .toggle-track { background: linear-gradient(135deg,#1e9aff,#0b6eff); }
.toggle-input:checked + .toggle-track::after { transform: translateX(16px); }
.toggle-label-text { font-size: 13px; font-weight: 500; color: #334155; }
.toggle-note { font-size: 12px; color: #94a3b8; margin: 8px 0 0; line-height: 1.5; }

.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 28px 22px; border-top: 1px solid #f1f5f9; flex-shrink: 0; }
.btn-cancel { padding: 10px 20px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; color: #475569; transition: all .15s; }
.btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-save { padding: 10px 24px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,154,255,.35); }
</style>
