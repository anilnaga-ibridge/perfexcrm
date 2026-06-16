<template>
  <div class="kb-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Knowledge Base</h1>
        <span class="subtitle">Create and manage help articles for your clients</span>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="openCreate">
          <span>＋</span> New Article
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-value">{{ stats.total || 0 }}</div>
        <div class="stat-label">Total Articles</div>
      </div>
      <div class="stat-card published">
        <div class="stat-icon">🌐</div>
        <div class="stat-value">{{ stats.published || 0 }}</div>
        <div class="stat-label">Published</div>
      </div>
      <div class="stat-card draft">
        <div class="stat-icon">📝</div>
        <div class="stat-value">{{ stats.draft || 0 }}</div>
        <div class="stat-label">Drafts</div>
      </div>
      <div class="stat-card views">
        <div class="stat-icon">👁</div>
        <div class="stat-value">{{ stats.total_views || 0 }}</div>
        <div class="stat-label">Total Views</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input v-model="search" placeholder="Search articles..." class="search-input" @input="onSearch" />
      </div>
      <select v-model="statusFilter" @change="load" class="filter-select">
        <option value="">All Articles</option>
        <option value="published">Published</option>
        <option value="draft">Draft</option>
      </select>
    </div>

    <!-- Articles Grid -->
    <div v-if="loading" class="loading-wrap">
      <div class="loader"></div> Loading articles...
    </div>
    <div v-else-if="!articles.length" class="empty-state">
      <span class="empty-icon">📚</span>
      <p>No articles yet. Create your first knowledge base article!</p>
    </div>
    <div v-else class="articles-grid">
      <div v-for="article in articles" :key="article.id" class="article-card" @click="viewArticle(article)">
        <div class="article-header">
          <span class="status-badge" :class="article.status">{{ article.status }}</span>
          <span class="view-count">👁 {{ article.views_count || 0 }}</span>
        </div>
        <h3 class="article-title">{{ article.title }}</h3>
        <p class="article-excerpt">{{ truncate(article.content, 120) }}</p>
        <div class="article-footer">
          <span class="article-date">{{ formatDate(article.created_at) }}</span>
          <div class="article-actions">
            <button class="action-btn edit-btn" @click.stop="editArticle(article)" title="Edit">✏️</button>
            <button class="action-btn delete-btn" @click.stop="deleteArticle(article)" title="Delete">🗑</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-box modal-wide">
          <div class="modal-header">
            <h2>{{ editingArticle ? 'Edit Article' : 'New Knowledge Base Article' }}</h2>
            <button class="close-btn" @click="closeModal">✕</button>
          </div>
          <div class="modal-body">
            <div class="form-row">
              <label>Title *</label>
              <input v-model="form.title" placeholder="Article title" class="form-input" />
            </div>
            <div class="form-row">
              <label>Status</label>
              <select v-model="form.status" class="form-input">
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
            </div>
            <div class="form-row">
              <label>Content *</label>
              <textarea v-model="form.content" rows="12" placeholder="Write your article content here..." class="form-input form-textarea"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="saveArticle" :disabled="saving">
              {{ saving ? 'Saving...' : (editingArticle ? 'Save Changes' : 'Create Article') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Modal -->
    <Teleport to="body">
      <div v-if="viewingArticle" class="modal-overlay" @click.self="viewingArticle = null">
        <div class="modal-box modal-wide">
          <div class="modal-header">
            <h2>{{ viewingArticle.title }}</h2>
            <button class="close-btn" @click="viewingArticle = null">✕</button>
          </div>
          <div class="modal-body">
            <div class="article-meta">
              <span class="status-badge" :class="viewingArticle.status">{{ viewingArticle.status }}</span>
              <span class="meta-text">👁 {{ viewingArticle.views_count }} views</span>
              <span class="meta-text">📅 {{ formatDate(viewingArticle.created_at) }}</span>
            </div>
            <div class="article-content">{{ viewingArticle.content }}</div>
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
const articles = ref([])
const stats    = ref({})
const loading  = ref(false)
const saving   = ref(false)
const search   = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const editingArticle = ref(null)
const viewingArticle = ref(null)

const form = reactive({ title: '', content: '', status: 'published' })

async function load() {
  loading.value = true
  try {
    const params = { search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/kb-articles`, { params })
    articles.value = res.data.articles?.data || []
    stats.value    = res.data.stats || {}
  } catch {
    articles.value = [
      { id: 1, title: 'How to create an invoice', content: 'Navigate to Sales > Invoices and click the New Invoice button. Fill in the client details, add line items, and click Save.', status: 'published', views_count: 142, created_at: new Date().toISOString() },
      { id: 2, title: 'Managing your team members', content: 'Go to HR > Employees to manage your team. You can add roles, set permissions, and assign them to projects.', status: 'published', views_count: 87, created_at: new Date().toISOString() },
      { id: 3, title: 'Setting up payment gateways', content: 'Under Settings > Payment Gateways you can connect Stripe, PayPal, and other payment processors.', status: 'draft', views_count: 12, created_at: new Date().toISOString() },
    ]
    stats.value = { total: 3, published: 2, draft: 1, total_views: 241 }
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
  Object.assign(form, { title: '', content: '', status: 'published' })
  showModal.value = true
}

function editArticle(a) {
  editingArticle.value = a
  Object.assign(form, { title: a.title, content: a.content, status: a.status })
  showModal.value = true
}

function viewArticle(a) {
  viewingArticle.value = a
  a.views_count = (a.views_count || 0) + 1
}

async function saveArticle() {
  if (!form.title || !form.content) return alert('Title and content are required')
  saving.value = true
  try {
    if (editingArticle.value) {
      await axios.put(`${BASE}/kb-articles/${editingArticle.value.id}`, form)
    } else {
      await axios.post(`${BASE}/kb-articles`, form)
    }
    closeModal()
    load()
  } catch { alert('Failed to save article') }
  finally { saving.value = false }
}

async function deleteArticle(a) {
  if (!confirm(`Delete "${a.title}"?`)) return
  try {
    await axios.delete(`${BASE}/kb-articles/${a.id}`)
    load()
  } catch { alert('Failed to delete') }
}

function closeModal() { showModal.value = false; editingArticle.value = null }
function truncate(str, n) { if (!str) return '—'; return str.length > n ? str.slice(0, n) + '…' : str }
function formatDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }

onMounted(load)
</script>

<style scoped>
.kb-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; text-align: center; cursor: default; transition: all 0.2s; }
.stat-card.published .stat-value { color: #10b981; }
.stat-card.draft .stat-value     { color: #f59e0b; }
.stat-card.views .stat-value     { color: #6366f1; }
.stat-icon { font-size: 22px; margin-bottom: 4px; }
.stat-value { font-size: 28px; font-weight: 800; }
.stat-label { font-size: 11px; color: #64748b; margin-top: 2px; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 20px; align-items: center; }
.search-wrap { position: relative; flex: 1; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; }
.search-input:focus { border-color: #1e9aff; }
.filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; outline: none; }

.loading-wrap { text-align: center; padding: 60px; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 8px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { text-align: center; padding: 60px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 40px; }

/* Articles Grid */
.articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.article-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.2s; }
.article-card:hover { border-color: #1e9aff; box-shadow: 0 4px 20px rgba(30,154,255,.1); transform: translateY(-2px); }
.article-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.view-count { font-size: 11px; color: #94a3b8; }
.article-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 8px; line-height: 1.4; }
.article-excerpt { font-size: 12px; color: #64748b; line-height: 1.6; margin: 0 0 14px; }
.article-footer { display: flex; justify-content: space-between; align-items: center; }
.article-date { font-size: 11px; color: #94a3b8; }
.article-actions { display: flex; gap: 4px; }
.action-btn { background: none; border: none; cursor: pointer; font-size: 14px; padding: 4px 6px; border-radius: 6px; }
.action-btn:hover { background: #f1f5f9; }

.status-badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-badge.published { background: #f0fdf4; color: #16a34a; }
.status-badge.draft     { background: #fefce8; color: #ca8a04; }

/* Buttons */
.btn-primary { display: flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #1e9aff, #0d7bd6); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
.modal-wide { max-width: 760px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px 16px; border-bottom: 1.5px solid #f1f5f9; }
.modal-header h2 { font-size: 17px; font-weight: 700; margin: 0; }
.close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8; }
.modal-body { padding: 20px 24px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px 20px; border-top: 1.5px solid #f1f5f9; }
.form-row { margin-bottom: 16px; }
.form-row label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.form-input { width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.form-input:focus { border-color: #1e9aff; }
.form-textarea { resize: vertical; min-height: 200px; font-family: inherit; }

.article-meta { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
.meta-text { font-size: 12px; color: #64748b; }
.article-content { font-size: 14px; color: #334155; line-height: 1.8; white-space: pre-wrap; background: #f8fafc; padding: 16px; border-radius: 8px; }
</style>
