<template>
  <div class="estimates-page space-y-5">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Estimates</h1>
        <p class="page-subtitle">Manage and track sent business estimates</p>
      </div>
      <div class="flex items-center gap-2">

        <button
          class="icon-btn"
          :class="{ 'icon-btn--active': activeEstimate }"
          @click="toggleTableWidth"
          title="Toggle detail panel"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
            <rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/>
          </svg>
        </button>

        <button
          class="icon-btn"
          :class="{ 'icon-btn--active': viewType === 'pipeline' }"
          @click="toggleViewType"
          :title="viewType === 'list' ? 'Pipeline view' : 'List view'"
        >
          <svg v-if="viewType === 'list'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
            <rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="10" width="7" height="11" rx="1"/>
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
            <line x1="3" y1="6" x2="3.01" y2="6" stroke-linecap="round" stroke-width="3"/>
            <line x1="3" y1="12" x2="3.01" y2="12" stroke-linecap="round" stroke-width="3"/>
            <line x1="3" y1="18" x2="3.01" y2="18" stroke-linecap="round" stroke-width="3"/>
          </svg>
        </button>

        <button
          class="icon-btn"
          :disabled="selectedIds.length === 0 && viewType === 'list'"
          @click="bulkPdfExport"
          title="Bulk PDF export"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/>
            <polyline points="9 15 12 18 15 15"/>
          </svg>
        </button>

        <button v-if="canCreate" class="btn-primary" @click="goToCreatePage">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Estimate
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards" v-if="viewType === 'list'">
      <div class="summary-card" v-for="s in summaryCards" :key="s.label">
        <div class="summary-label">{{ s.label }}</div>
        <div class="summary-value" :class="s.cls">{{ s.value }}</div>
      </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div v-if="selectedIds.length > 0 && viewType === 'list'" class="bulk-bar">
      <div class="bulk-bar-info">
        <span class="bulk-bar-label">Bulk Actions:</span>
        <span class="bulk-bar-count">{{ selectedIds.length }} selected</span>
      </div>
      <div class="bulk-bar-actions">
        <button class="btn-primary-sm" @click="bulkPdfExport">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5l5 5 5-5m-5 5V3"/></svg>
          Bulk PDF Export
        </button>
        <button class="btn-ghost-sm" @click="selectedIds = []">Deselect All</button>
      </div>
    </div>

    <!-- Layout: Split View -->
    <div :class="['layout-grid', { 'split-active': activeEstimate && viewType === 'list' }]">

      <!-- Left: Table / Pipeline -->
      <div class="left-pane">

        <!-- Pipeline View -->
        <div v-if="viewType === 'pipeline'" class="pipeline-scroll">
          <div
            v-for="col in pipelineColumns"
            :key="col.status"
            class="pipeline-col"
          >
            <div class="pipeline-col-header">
              <div class="pipeline-col-title">
                <span class="pipeline-dot" :class="col.dotClass"></span>
                <span class="pipeline-status-label">{{ col.status }}</span>
              </div>
              <div class="pipeline-col-stats">
                <span>{{ col.rows.length }}</span>
                <span class="pipeline-col-divider">|</span>
                <span class="pipeline-col-total">{{ fmtCur(col.total) }}</span>
              </div>
            </div>

            <div class="pipeline-cards">
              <div
                v-for="est in col.rows"
                :key="est.id"
                :class="['pipeline-card', { 'pipeline-card--active': activeEstimate && activeEstimate.id === est.id }]"
                @click="selectEstimate(est)"
              >
                <div class="pipeline-card-head">
                  <span class="pipeline-card-number">{{ est.number }}</span>
                  <span class="pipeline-card-amount">{{ fmtCur(est.amount) }}</span>
                </div>
                <div class="pipeline-card-client">{{ est.client }}</div>
                <div class="pipeline-card-date">{{ est.date }}</div>

                <div class="pipeline-card-divider"></div>

                <div class="pipeline-card-expiry flex justify-between">
                  <span>Expiry:</span>
                  <span class="font-semibold text-slate-700">{{ est.expiry }}</span>
                </div>

                <div class="pipeline-card-footer" @click.stop>
                  <span class="pipeline-card-move-label">Move:</span>
                  <select class="pipeline-status-select" :value="est.status" @change="e => moveEstimateStatus(est.id, e.target.value)">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                    <option value="expired">Expired</option>
                  </select>
                </div>
              </div>

              <div v-if="col.rows.length === 0" class="pipeline-empty">
                No estimates in this stage
              </div>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-else class="table-card">
          <div class="table-toolbar">
            <div class="toolbar-left">
              <select class="select-sm" v-model="perPage">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
              </select>
              <button class="btn-ghost-sm" @click="bulkPdfExport">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5l5 5 5-5m-5 5V3"/></svg>
                Export PDF
              </button>
            </div>
            <div class="toolbar-right">
              <select class="select-sm" v-model="statusFilter">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="accepted">Accepted</option>
                <option value="declined">Declined</option>
                <option value="expired">Expired</option>
              </select>
              <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="input-sm search-input" v-model="search" placeholder="Search estimates..." />
              </div>
            </div>
          </div>

          <!-- Desktop Table View -->
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 44px; min-width: 44px;" class="col-checkbox"><input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="cursor-pointer" /></th>
                <th style="width: 10%;">#</th>
                <th style="width: 26%;">Client</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;" class="text-right">Amount</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 10%;">Expiry</th>
                <th style="width: 4%;"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in filteredRows"
                :key="row.id"
                :class="['table-row', { 'table-row--selected': selectedIds.includes(row.id), 'table-row--active': activeEstimate && activeEstimate.id === row.id }]"
              >
                <td class="col-checkbox chk-td"><input type="checkbox" :value="row.id" v-model="selectedIds" class="cursor-pointer" /></td>
                <td><a class="estimate-link" @click="selectEstimate(row)">{{ row.number }}</a></td>
                <td class="cell-truncate" :title="row.client"><span class="client-link block truncate" @click="selectEstimate(row)">{{ row.client }}</span></td>
                <td><span class="badge" :class="statusClass(row.status)">{{ row.status }}</span></td>
                <td class="text-right amount-cell">{{ fmtCur(row.amount) }}</td>
                <td class="date-cell">{{ row.date }}</td>
                <td class="date-cell">{{ row.expiry }}</td>
                <td class="actions-td">
                  <button class="action-dots" title="Edit" @click="editEstimate(row)">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredRows.length === 0">
                <td colspan="8" class="empty-cell">No estimates found</td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile Responsive Card View -->
          <div class="mobile-cards-list">
            <div 
              v-for="row in filteredRows" 
              :key="'m-est-' + row.id"
              class="mobile-row-card"
              @click="selectEstimate(row)"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <input type="checkbox" :value="row.id" v-model="selectedIds" @click.stop />
                  <span class="font-extrabold text-sm text-indigo-600">{{ row.number }}</span>
                </div>
                <span class="badge" :class="statusClass(row.status)">{{ row.status }}</span>
              </div>

              <div class="font-bold text-sm text-slate-800 line-clamp-2">
                👤 {{ row.client }}
              </div>

              <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                <div class="flex items-center gap-1.5 text-slate-500">
                  <span class="text-slate-400">📅</span>
                  <span>{{ row.date }}</span>
                </div>
                <div class="flex items-center justify-end gap-1.5 font-extrabold text-slate-900 text-sm">
                  <span class="text-slate-400 font-normal">💰</span>
                  <span>{{ fmtCur(row.amount) }}</span>
                </div>
                <div class="flex items-center justify-start gap-1.5 text-slate-400">
                  <span>Exp: {{ row.expiry }}</span>
                </div>
              </div>
            </div>

            <div v-if="filteredRows.length === 0" class="text-center p-6 text-slate-400 text-xs font-semibold">
              No estimates found
            </div>
          </div>

          <div class="table-footer">{{ filteredRows.length }} of {{ rows.length }} estimates</div>
        </div>
      </div>

      <!-- Right: Detail Panel -->
      <div v-if="activeEstimate" class="right-pane">
        <div class="detail-panel">
          <div class="detail-head">
            <div>
              <div class="detail-number">{{ activeEstimate.number }}</div>
              <h2 class="detail-client">{{ activeEstimate.client }}</h2>
            </div>
            <button class="detail-close" @click="activeEstimate = null">&times;</button>
          </div>

          <div class="detail-body">
            <div class="detail-grid">
              <div class="detail-field">
                <span class="detail-label">Client</span>
                <span class="detail-value">{{ activeEstimate.client }}</span>
              </div>
              <div class="detail-field">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value detail-value--amount">{{ fmtCur(activeEstimate.amount) }}</span>
              </div>
              <div class="detail-field">
                <span class="detail-label">Date Created</span>
                <span class="detail-value">{{ activeEstimate.date }}</span>
              </div>
              <div class="detail-field">
                <span class="detail-label">Expiry Date</span>
                <span class="detail-value detail-value--expiry">{{ activeEstimate.expiry }}</span>
              </div>
              <div class="detail-field">
                <span class="detail-label">Status</span>
                <span class="badge" :class="statusClass(activeEstimate.status)">{{ activeEstimate.status }}</span>
              </div>
            </div>

            <div class="detail-summary-box">
              <div class="detail-summary-title">Estimate Summary</div>
              <p class="detail-summary-text">This business estimate outlines the project description, deliverables, and total pricing options for {{ activeEstimate.client }}.</p>
            </div>

            <div class="detail-actions">
              <button class="btn-action btn-action--primary" @click="downloadSinglePdf(activeEstimate)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5l5 5 5-5m-5 5V3"/></svg>
                PDF Preview
              </button>
              <button v-if="canEdit" class="btn-action btn-action--secondary" @click="editEstimate(activeEstimate)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Estimate
              </button>
              <button v-if="canDelete" class="btn-action btn-action--danger" @click="deleteSingleEstimate(activeEstimate.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useEstimatesStore } from '../store/estimatesStore';
import { useAuthStore } from '../store/authStore';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'EstimatesPage',
  setup() {
    const router = useRouter();
    const estimatesStore = useEstimatesStore();
    const authStore = useAuthStore();

    const canCreate = computed(() => authStore.hasPermission('Estimates', 'create'));
    const canEdit   = computed(() => authStore.hasPermission('Estimates', 'edit'));
    const canDelete = computed(() => authStore.hasPermission('Estimates', 'delete'));

    const perPage = ref('25');
    const search = ref('');
    const statusFilter = ref('');
    const activeEstimate = ref(null);
    const selectedIds = ref([]);
    const viewType = ref('list');

    const rows = computed(() => estimatesStore.estimates);

    const summaryCards = computed(() => {
      const total = rows.value.length;
      const by = (s) => rows.value.filter(r => r.status === s).length;
      return [
        { label: 'Total',    value: total,       cls: '' },
        { label: 'Draft',    value: by('draft'),  cls: '' },
        { label: 'Sent',     value: by('sent'),   cls: 'text-info' },
        { label: 'Accepted', value: by('accepted'), cls: 'text-success' },
        { label: 'Declined', value: by('declined'), cls: 'text-danger' },
        { label: 'Expired',  value: by('expired'), cls: 'text-muted' }
      ];
    });

    const filteredRows = computed(() => rows.value.filter(r => {
      if (statusFilter.value && r.status !== statusFilter.value) return false;
      if (search.value) {
        const query = search.value.toLowerCase();
        return r.client.toLowerCase().includes(query) || r.number.toLowerCase().includes(query);
      }
      return true;
    }));

    const pipelineColumns = computed(() => {
      const statuses = ['draft', 'sent', 'accepted', 'declined', 'expired'];
      const dotClasses = {
        draft: 'pipeline-dot--draft',
        sent: 'pipeline-dot--sent',
        accepted: 'pipeline-dot--accepted',
        declined: 'pipeline-dot--declined',
        expired: 'pipeline-dot--expired'
      };
      return statuses.map(status => {
        const matchingRows = rows.value.filter(r => r.status === status && (
          !search.value || 
          r.client.toLowerCase().includes(search.value.toLowerCase()) || 
          r.number.toLowerCase().includes(search.value.toLowerCase())
        ));
        const total = matchingRows.reduce((sum, curr) => sum + Number(curr.amount || 0), 0);
        return { status, rows: matchingRows, total, dotClass: dotClasses[status] || 'pipeline-dot--draft' };
      });
    });

    const isAllSelected = computed(() => {
      return filteredRows.value.length > 0 && selectedIds.value.length === filteredRows.value.length;
    });

    const toggleSelectAll = (e) => {
      if (e.target.checked) selectedIds.value = filteredRows.value.map(r => r.id);
      else selectedIds.value = [];
    };

    const statusClass = (s) => ({
      draft: 'badge-default', sent: 'badge-blue', accepted: 'badge-green',
      declined: 'badge-red', expired: 'badge-gray'
    }[s] || 'badge-default');

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    const toggleViewType = () => {
      viewType.value = viewType.value === 'list' ? 'pipeline' : 'list';
      selectedIds.value = [];
    };

    const toggleTableWidth = () => {
      if (activeEstimate.value) activeEstimate.value = null;
      else if (rows.value.length > 0) activeEstimate.value = rows.value[0];
    };

    const selectEstimate = (est) => { activeEstimate.value = est; };

    const moveEstimateStatus = (estimateId, newStatus) => {
      estimatesStore.updateEstimate(estimateId, { status: newStatus });
      if (activeEstimate.value && activeEstimate.value.id === estimateId) {
        activeEstimate.value.status = newStatus;
      }
      message.success('Status updated.');
    };

    const goToCreatePage = () => router.push('/admin/estimates/estimate');
    const editEstimate = (est) => router.push(`/admin/estimates/estimate/${est.id}`);

    const bulkPdfExport = () => {
      const count = selectedIds.value.length || filteredRows.value.length;
      message.loading('Generating bulk PDF exports...', 1.5).then(() => {
        message.success(`Exported ${count} estimates!`);
        selectedIds.value = [];
      });
    };

    const downloadSinglePdf = (estimate) => {
      message.loading(`Generating PDF...`, 1).then(() => {
        message.success(`PDF downloaded for ${estimate.number}!`);
      });
    };

    const deleteSingleEstimate = (id) => {
      estimatesStore.deleteEstimate(id);
      if (activeEstimate.value && activeEstimate.value.id === id) activeEstimate.value = null;
      message.success('Estimate deleted.');
    };

    return {
      perPage, search, statusFilter, rows, summaryCards, filteredRows,
      statusClass, fmtCur, editEstimate, bulkPdfExport, downloadSinglePdf,
      deleteSingleEstimate, viewType, activeEstimate, selectedIds,
      pipelineColumns, isAllSelected, toggleSelectAll, toggleViewType,
      toggleTableWidth, selectEstimate, moveEstimateStatus, goToCreatePage
    };
  }
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.estimates-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

/* ── Header ── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  line-height: 1.3;
}
.page-subtitle {
  font-size: 12.5px;
  color: #94a3b8;
  margin: 1px 0 0;
}

/* ── Icon Buttons ── */
.icon-btn {
  background: #fff;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}
.icon-btn:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
}
.icon-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.icon-btn--active {
  background: #1e293b;
  color: #fff;
  border-color: #1e293b;
}
.icon-btn--active:hover {
  background: #0f172a;
  border-color: #0f172a;
  color: #fff;
}

/* ── Bulk Actions Bar ── */
.bulk-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 16px;
}
.bulk-bar-info {
  display: flex;
  align-items: center;
  gap: 8px;
}
.bulk-bar-label {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
}
.bulk-bar-count {
  font-size: 12px;
  color: #64748b;
}
.bulk-bar-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-primary-sm {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.btn-primary-sm:hover {
  background: #0f172a;
}
.btn-ghost-sm {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.btn-ghost-sm:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* ── Layout Grid ── */
.layout-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  align-items: stretch;
  transition: all 0.2s ease;
}
.left-pane {
  width: 100%;
  max-width: 100%;
  flex: 1 1 100%;
  min-width: 0;
}
@media (min-width: 1024px) {
  .layout-grid.split-active {
    flex-direction: row;
  }
  .layout-grid.split-active .left-pane { width: 55%; max-width: 55%; flex: 0 0 55%; }
  .layout-grid.split-active .right-pane { width: 45%; max-width: 45%; flex: 0 0 45%; }
}

/* ── Pipeline ── */
.pipeline-scroll {
  display: flex;
  gap: 14px;
  overflow-x: auto;
  padding-bottom: 8px;
}
.pipeline-scroll::-webkit-scrollbar { height: 5px; }
.pipeline-scroll::-webkit-scrollbar-track { background: transparent; }
.pipeline-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }

.pipeline-col {
  min-width: 270px;
  width: 270px;
  background: #f1f4f9;
  border-radius: 12px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 220px);
  border: 1px solid #e2e8f0;
}
.pipeline-col-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 10px;
  border-bottom: 1.5px solid #e2e8f0;
}
.pipeline-col-title {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pipeline-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.pipeline-dot--draft { background: #94a3b8; }
.pipeline-dot--sent { background: #3b82f6; }
.pipeline-dot--accepted { background: #22c55e; }
.pipeline-dot--declined { background: #ef4444; }
.pipeline-dot--expired { background: #94a3b8; }
.pipeline-status-label {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  text-transform: capitalize;
}
.pipeline-col-stats {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
}
.pipeline-col-divider { color: #cbd5e1; }
.pipeline-col-total { color: #1e293b; font-weight: 700; }

.pipeline-cards {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-right: 2px;
}
.pipeline-cards::-webkit-scrollbar { width: 3px; }
.pipeline-cards::-webkit-scrollbar-track { background: transparent; }
.pipeline-cards::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }

.pipeline-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.15s;
  border-left: 3.5px solid #cbd5e1;
}
.pipeline-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  transform: translateY(-1px);
}
.pipeline-card--active {
  border-left-color: #1e293b !important;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.1);
}
.pipeline-card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.pipeline-card-number {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
}
.pipeline-card-amount {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
}
.pipeline-card-client {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  margin-top: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pipeline-card-date {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 2px;
}
.pipeline-card-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 10px 0;
}
.pipeline-card-expiry {
  font-size: 11px;
  color: #94a3b8;
}
.pipeline-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10px;
  padding-top: 8px;
  border-top: 1px solid #f1f5f9;
}
.pipeline-card-move-label {
  font-size: 9px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.pipeline-status-select {
  font-size: 11px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 3px 6px;
  background: #fff;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
  font-weight: 600;
}
.pipeline-empty {
  text-align: center;
  padding: 24px 12px;
  color: #94a3b8;
  font-size: 11px;
  border: 1.5px dashed #e2e8f0;
  border-radius: 8px;
  background: #fff;
}

/* ── Table ── */
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  gap: 10px;
  flex-wrap: wrap;
}
.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.search-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.search-icon {
  position: absolute;
  left: 9px;
  color: #94a3b8;
  pointer-events: none;
}
.search-input {
  padding-left: 28px !important;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.data-table thead th {
  background: #f8fafc;
  padding: 10px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
  border-bottom: 2px solid #e2e8f0;
}
.chk-th, .chk-td { width: 40px; text-align: center; }
.actions-th { width: 40px; }
.data-table thead th.text-right { text-align: right; }
.data-table tbody td {
  padding: 11px 14px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.amount-cell { font-weight: 700; color: #1e293b; }
.date-cell { color: #64748b; font-size: 12.5px; }
.estimate-link {
  color: #1e293b;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: color 0.1s;
}
.estimate-link:hover { color: #0f172a; text-decoration: underline; }
.client-link {
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  transition: color 0.1s;
}
.client-link:hover { color: #1e293b; }
.actions-td { text-align: center; }
.action-dots {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.12s;
  display: inline-flex;
}
.action-dots:hover {
  background: #f1f5f9;
  color: #475569;
}

.table-row--selected { background: #f8fafc; }
.table-row--active td {
  background: #f1f4f9 !important;
  border-left: 3px solid #1e293b;
}
.table-footer {
  padding: 10px 16px;
  font-size: 12px;
  color: #64748b;
  border-top: 1px solid #f1f5f9;
}

/* ── Detail Panel ── */
.detail-panel {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.detail-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding-bottom: 14px;
  border-bottom: 1px solid #e2e8f0;
}
.detail-number {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.detail-client {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 2px 0 0;
}
.detail-close {
  background: none;
  border: none;
  font-size: 20px;
  color: #94a3b8;
  cursor: pointer;
  padding: 2px 6px;
  border-radius: 6px;
  line-height: 1;
  transition: all 0.12s;
}
.detail-close:hover {
  background: #f1f5f9;
  color: #475569;
}
.detail-body {
  padding-top: 14px;
}
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.detail-field {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.detail-label {
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.detail-value {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}
.detail-value--amount {
  color: #1e293b;
  font-size: 15px;
  font-weight: 700;
}
.detail-value--expiry {
  color: #ef4444;
  font-weight: 700;
}
.detail-summary-box {
  margin-top: 16px;
  padding: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}
.detail-summary-title {
  font-size: 10px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 6px;
}
.detail-summary-text {
  font-size: 12.5px;
  color: #475569;
  line-height: 1.6;
  margin: 0;
}
.detail-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #e2e8f0;
}
.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
  border: none;
}
.btn-action--primary {
  background: #1e293b;
  color: #fff;
}
.btn-action--primary:hover { background: #0f172a; }
.btn-action--secondary {
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
}
.btn-action--secondary:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-action--danger {
  background: #fff;
  color: #ef4444;
  border: 1px solid #fecaca;
}
.btn-action--danger:hover { background: #fef2f2; border-color: #fca5a5; }

/* ── Misc ── */
button { cursor: pointer; }
</style>
