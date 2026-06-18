<template>
  <div class="kb-page">
    <div class="page-header">
      <div class="header-left">
        <h1>Knowledge Base</h1>
        <div class="breadcrumb">
          <router-link :to="{ name: 'admin.knowledge-base.groups' }" class="breadcrumb-link">Groups</router-link>
          <span class="breadcrumb-sep">→</span>
          <span class="breadcrumb-current">View Kanban</span>
        </div>
      </div>
      <button class="btn-primary" @click="openCreate">+ New Article</button>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <svg class="stat-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        <div class="stat-value">{{ stats.total || 0 }}</div>
        <div class="stat-label">Total Articles</div>
      </div>
      <div class="stat-card published">
        <svg class="stat-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <div class="stat-value">{{ stats.published || 0 }}</div>
        <div class="stat-label">Published</div>
      </div>
      <div class="stat-card draft">
        <svg class="stat-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        <div class="stat-value">{{ stats.draft || 0 }}</div>
        <div class="stat-label">Drafts</div>
      </div>
      <div class="stat-card views">
        <svg class="stat-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <div class="stat-value">{{ stats.total_views || 0 }}</div>
        <div class="stat-label">Total Views</div>
      </div>
    </div>

    <div class="filters-bar">
      <div class="search-wrap">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="search" placeholder="Search..." class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Status</option>
        <option value="published">Published</option>
        <option value="draft">Draft</option>
      </select>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Article Name</th>
            <th>Group</th>
            <th>Date Published</th>
            <th class="th-actions">Options</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="loading-cell"><div class="loader"></div> Loading...</td>
          </tr>
          <tr v-else-if="!articles.length">
            <td colspan="4" class="empty-cell">No articles found</td>
          </tr>
          <tr v-for="a in articles" :key="a.id" class="data-row">
            <td class="title-cell">{{ a.title }}</td>
            <td>
              <span v-if="a.category" class="group-badge" :style="{ background: a.category.color + '20', color: a.category.color }">{{ a.category.name }}</span>
              <span v-else class="no-group">—</span>
            </td>
            <td class="date-cell">{{ formatDate(a.created_at) }}</td>
            <td>
              <div class="action-links">
                <button class="link-btn" @click="viewArticle(a)">View</button>
                <span class="link-sep">|</span>
                <button class="link-btn" @click="editArticle(a)">Edit</button>
                <span class="link-sep">|</span>
                <button class="link-btn link-danger" @click="deleteArticle(a)">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
        <div class="modal-box">
          <div class="modal-header">
            <h2>{{ editingArticle ? 'Edit Article' : 'Add New Article' }}</h2>
            <button class="close-btn" @click="showModal = false">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-section">
              <div class="field-group">
                <label class="field-label">Subject <span class="req">*</span></label>
                <input v-model="form.title" placeholder="Article subject" class="field-input" />
              </div>
              <div class="field-group">
                <label class="field-label">Group <span class="req">*</span></label>
                <div class="select-wrap">
                  <select v-model="form.category_id" class="field-input field-select">
                    <option :value="null">— Select Group —</option>
                    <option v-for="g in groups" :key="g.id" :value="g.id" :style="{ borderLeft: '3px solid ' + (g.color || '#6b7280') }">{{ g.name }}</option>
                  </select>
                  <svg class="select-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>
            <div class="form-section form-section-toggles">
              <div class="toggle-items">
                <label class="toggle-item">
                  <input type="checkbox" v-model="form.internal" class="toggle-input" />
                  <span class="toggle-track"></span>
                  <span class="toggle-label-text">Internal Article</span>
                </label>
                <label class="toggle-item">
                  <input type="checkbox" v-model="form.disabled" class="toggle-input" />
                  <span class="toggle-track"></span>
                  <span class="toggle-label-text">Disabled</span>
                </label>
              </div>
            </div>
            <div class="form-section">
              <div class="field-group">
                <label class="field-label">Article description</label>
                <textarea v-model="form.content" rows="10" placeholder="Write article content..." class="field-input field-textarea"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" @click="showModal = false">Cancel</button>
            <button class="btn-save" @click="saveArticle">{{ editingArticle ? 'Save Changes' : 'Create Article' }}</button>
          </div>
        </div>
      </div>

      <div v-if="viewingArticle" class="modal-overlay" @click.self="viewingArticle = null">
        <div class="modal-box modal-wide">
          <div class="modal-header">
            <h2>{{ viewingArticle.title }}</h2>
            <button class="close-btn" @click="viewingArticle = null">&times;</button>
          </div>
          <div class="modal-body">
            <div class="view-meta">
              <span v-if="viewingArticle.category" class="group-badge" :style="{ background: viewingArticle.category.color + '20', color: viewingArticle.category.color }">{{ viewingArticle.category.name }}</span>
              <span class="meta-item">{{ viewingArticle.views_count || 0 }} views</span>
              <span class="meta-item">{{ formatDate(viewingArticle.created_at) }}</span>
            </div>
            <div class="view-content">{{ viewingArticle.content }}</div>
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
const articles       = ref([])
const stats          = ref({})
const groups         = ref([])
const loading        = ref(false)
const search         = ref('')
const statusFilter   = ref('')
const showModal      = ref(false)
const editingArticle = ref(null)
const viewingArticle = ref(null)

const form = reactive({ title: '', category_id: null, internal: false, disabled: false, content: '' })

async function load() {
  loading.value = true
  try {
    const params = { search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/kb-articles`, { params })
    articles.value = res.data.articles?.data || []
    stats.value    = res.data.stats || {}
    groups.value   = res.data.categories || []
  } catch {
    articles.value = [
      { id: 1, title: 'He unfolded the paper.', content: 'He unfolded the paper and studied it for a moment.', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 25, created_at: new Date().toISOString() },
      { id: 2, title: 'Alice, every now and then, and.', content: 'Alice, every now and then, and found in it a very good height indeed.', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 18, created_at: new Date().toISOString() },
      { id: 3, title: 'For instance, if you could keep.', content: 'For instance, if you could keep it in the lock.', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 14, created_at: new Date().toISOString() },
      { id: 4, title: 'Alice was not easy to know.', content: 'Alice was not easy to know when the Rabbit came near her.', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 22, created_at: new Date().toISOString() },
      { id: 5, title: 'There ought to eat her.', content: 'There ought to eat her up in a very fine day!', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 9, created_at: new Date().toISOString() },
      { id: 6, title: 'I\'ll get into her.', content: 'I\'ll get into her eyes, and felt quite strange at first.', category: { id: 1, name: 'Sales', color: '#10b981' }, status: 'published', views_count: 32, created_at: new Date().toISOString() },
      { id: 7, title: 'Alice felt a violent shake at the stick.', content: 'Alice felt a violent shake at the stick.', category: { id: 2, name: 'Info', color: '#3b82f6' }, status: 'published', views_count: 45, created_at: new Date().toISOString() },
      { id: 8, title: 'Dodo in an encouraging opening for a.', content: 'Dodo in an encouraging opening for a conversation.', category: { id: 2, name: 'Info', color: '#3b82f6' }, status: 'published', views_count: 17, created_at: new Date().toISOString() },
    ]
    stats.value = { total: 8, published: 8, draft: 0, total_views: 182 }
    groups.value = [
      { id: 1, name: 'Sales', color: '#10b981' },
      { id: 2, name: 'Info', color: '#3b82f6' },
    ]
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(), 350)
}

function openCreate() {
  editingArticle.value = null
  Object.assign(form, { title: '', category_id: null, internal: false, disabled: false, content: '' })
  showModal.value = true
}

function editArticle(a) {
  editingArticle.value = a
  Object.assign(form, {
    title: a.title || '',
    category_id: a.category_id || a.category?.id || null,
    internal: a.internal || false,
    disabled: a.disabled || false,
    content: a.content || '',
  })
  showModal.value = true
}

function viewArticle(a) { viewingArticle.value = a }

async function saveArticle() {
  if (!form.title) return alert('Subject is required')
  const payload = {
    title: form.title,
    category_id: form.category_id || null,
    internal: form.internal || false,
    disabled: form.disabled || false,
    content: form.content || '',
    status: 'published',
  }
  try {
    if (editingArticle.value) {
      await axios.put(`${BASE}/kb-articles/${editingArticle.value.id}`, payload)
    } else {
      await axios.post(`${BASE}/kb-articles`, payload)
    }
    showModal.value = false
    load()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to save article')
  }
}

async function deleteArticle(a) {
  if (!confirm(`Delete "${a.title}"?`)) return
  try {
    await axios.delete(`${BASE}/kb-articles/${a.id}`)
    load()
  } catch { alert('Failed to delete') }
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('en-GB', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(',', '')
}

onMounted(load)
</script>

<style scoped>
.kb-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-left h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 13px; }
.breadcrumb-link { color: #1e9aff; text-decoration: none; font-weight: 500; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { color: #94a3b8; }
.breadcrumb-current { color: #64748b; }
.btn-primary { padding: 10px 20px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,154,255,.35); }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; }
.stat-svg { color: #94a3b8; margin-bottom: 8px; }
.stat-card.published .stat-value { color: #10b981; }
.stat-card.draft .stat-value { color: #f59e0b; }
.stat-card.views .stat-value { color: #6366f1; }
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
.th-actions { text-align: right; }
.data-row:hover { background: #f8fafc; }
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.title-cell { font-weight: 600; color: #1e293b; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.date-cell { color: #64748b; font-size: 12px; }
.loading-cell, .empty-cell { text-align: center; padding: 48px 20px; color: #94a3b8; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin .7s linear infinite; display: inline-block; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

.group-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.no-group { color: #94a3b8; }

.action-links { display: flex; justify-content: flex-end; gap: 6px; align-items: center; }
.link-btn { background: none; border: none; cursor: pointer; font-size: 12px; color: #1e9aff; padding: 0; }
.link-btn:hover { text-decoration: underline; }
.link-danger { color: #dc2626; }
.link-sep { color: #e2e8f0; font-size: 12px; }

/* ── Modal ───────────────────────────────────────────── */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(2px); }
.modal-box { background: #fff; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 25px 60px rgba(0,0,0,.3); max-height: 90vh; display: flex; flex-direction: column; }
.modal-wide { max-width: 760px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 22px 28px 18px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
.modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; color: #0f172a; }
.close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #94a3b8; padding: 4px 8px; border-radius: 8px; line-height: 1; transition: all .15s; }
.close-btn:hover { background: #f1f5f9; color: #475569; }
.modal-body { padding: 24px 28px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 20px; }
.form-section { display: flex; flex-direction: column; gap: 16px; }
.form-section-toggles { background: #f8fafc; border-radius: 10px; padding: 16px 18px; }

.field-group { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 13px; font-weight: 600; color: #0f172a; letter-spacing: .01em; }
.req { color: #ef4444; margin-left: 1px; }
.field-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; outline: none; background: #fff; box-sizing: border-box; transition: border-color .15s; color: #0f172a; }
.field-input:focus { border-color: #1e9aff; box-shadow: 0 0 0 3px rgba(30,154,255,.12); }
.field-input::placeholder { color: #94a3b8; }
.field-select { appearance: none; cursor: pointer; padding-right: 36px; }
.select-wrap { position: relative; }
.select-chevron { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; }
.field-textarea { resize: vertical; min-height: 160px; font-family: inherit; line-height: 1.6; }

.toggle-items { display: flex; gap: 28px; align-items: center; }
.toggle-item { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.toggle-input { display: none; }
.toggle-track { position: relative; width: 36px; height: 20px; background: #e2e8f0; border-radius: 10px; transition: background .2s; flex-shrink: 0; }
.toggle-track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
.toggle-input:checked + .toggle-track { background: linear-gradient(135deg,#1e9aff,#0b6eff); }
.toggle-input:checked + .toggle-track::after { transform: translateX(16px); }
.toggle-label-text { font-size: 13px; font-weight: 500; color: #334155; }

.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 18px 28px 22px; border-top: 1px solid #f1f5f9; flex-shrink: 0; }
.btn-cancel { padding: 10px 20px; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; color: #475569; transition: all .15s; }
.btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-save { padding: 10px 24px; background: linear-gradient(135deg,#1e9aff,#0b6eff); color: #fff; border: none; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,154,255,.35); }

.view-meta { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
.meta-item { font-size: 12px; color: #64748b; }
.view-content { font-size: 14px; color: #334155; line-height: 1.8; white-space: pre-wrap; background: #f8fafc; padding: 16px; border-radius: 8px; max-height: 400px; overflow-y: auto; }
</style>
