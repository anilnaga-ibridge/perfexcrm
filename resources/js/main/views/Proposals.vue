<template>
  <div class="module-page space-y-6">
    <!-- Action Bar -->
    <div class="page-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title text-xl font-bold text-slate-800">Proposals</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage and track sent business proposals</p>
      </div>
      <div class="flex items-center gap-2">
        <!-- Toggle Table Width -->
        <button 
          class="icon-btn" 
          :class="{ 'icon-btn--active': activeProposal }" 
          @click="toggleTableWidth" 
          title="Toggle table"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <line x1="9" y1="3" x2="9" y2="21"/>
          </svg>
        </button>

        <!-- Switch Pipeline / List -->
        <button 
          class="icon-btn" 
          :class="{ 'icon-btn--active': viewType === 'pipeline' }"
          @click="toggleViewType" 
          :title="viewType === 'list' ? 'Switch to pipeline' : 'Switch to list'"
        >
          <!-- Pipeline Icon (Kanban columns) -->
          <svg v-if="viewType === 'list'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <rect x="3" y="3" width="7" height="9" rx="1" />
            <rect x="14" y="3" width="7" height="5" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="10" width="7" height="11" rx="1" />
          </svg>
          <!-- List Icon (Table lines) -->
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <line x1="8" y1="6" x2="21" y2="6" />
            <line x1="8" y1="12" x2="21" y2="12" />
            <line x1="8" y1="18" x2="21" y2="18" />
            <line x1="3" y1="6" x2="3.01" y2="6" stroke-linecap="round" stroke-width="3" />
            <line x1="3" y1="12" x2="3.01" y2="12" stroke-linecap="round" stroke-width="3" />
            <line x1="3" y1="18" x2="3.01" y2="18" stroke-linecap="round" stroke-width="3" />
          </svg>
        </button>

        <!-- Bulk PDF Export -->
        <button 
          class="icon-btn" 
          :disabled="selectedIds.length === 0 && viewType === 'list'"
          @click="bulkPdfExport" 
          :title="selectedIds.length === 0 && viewType === 'list' ? 'Bulk pdf export (select items below first)' : 'Bulk pdf export'"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="12" y1="18" x2="12" y2="12" />
            <polyline points="9 15 12 18 15 15" />
          </svg>
        </button>

        <!-- New Proposal Button -->
        <button class="btn-primary" @click="goToCreatePage">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="mr-1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Proposal
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

    <!-- Bulk Actions Alert -->
    <div v-if="selectedIds.length > 0 && viewType === 'list'" class="bulk-actions-bar flex items-center justify-between bg-slate-50 border border-indigo-100 rounded-lg p-3 text-xs">
      <div class="flex items-center space-x-2">
        <span class="font-semibold text-slate-700">Bulk Actions:</span>
        <span class="text-slate-500">Selected <strong>{{ selectedIds.length }}</strong> proposals</span>
      </div>
      <div class="flex items-center space-x-2">
        <button class="btn-primary-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded px-3 py-1 font-semibold" @click="bulkPdfExport">
          Bulk PDF Export
        </button>
        <button class="btn-outline-sm border rounded px-3 py-1 text-slate-600 hover:bg-slate-100" @click="selectedIds = []">
          Deselect All
        </button>
      </div>
    </div>

    <!-- Layout Wrapper: Split View support -->
    <div :class="['layout-grid', { 'split-active': activeProposal && viewType === 'list' }]">
      
      <!-- Left Area: List Table or Pipeline Grid -->
      <div class="left-pane flex-1">
        
        <!-- Pipeline Board View -->
        <div v-if="viewType === 'pipeline'" class="pipeline-container flex space-x-4 overflow-x-auto pb-4 scrollbar">
          <div 
            v-for="col in pipelineColumns" 
            :key="col.status"
            class="pipeline-col bg-[#eef2f6] p-3 rounded-lg w-72 shrink-0 border border-slate-200/60 flex flex-col max-h-[calc(100vh-220px)]"
          >
            <!-- Column Header -->
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-300/60">
              <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="col.dotClass"></span>
                <span class="font-bold text-slate-800 text-xs tracking-tight capitalize">{{ col.status }}</span>
              </div>
              <div class="flex items-center space-x-2 text-[10px] text-slate-400 font-semibold">
                <span>{{ col.rows.length }}</span>
                <span>|</span>
                <span class="text-slate-600 font-bold">{{ fmtCur(col.total) }}</span>
              </div>
            </div>

            <!-- Cards Container -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-1 scrollbar">
              <div 
                v-for="prop in col.rows" 
                :key="prop.id"
                :class="['pipeline-card bg-white p-4 rounded-md border border-slate-200 shadow-sm hover:shadow-md transition-all relative group cursor-pointer', { 'card-active': activeProposal && activeProposal.id === prop.id }]"
                @click="selectProposal(prop)"
              >
                <div>
                  <div class="flex justify-between items-start">
                    <span class="font-bold text-slate-800 text-xs hover:text-indigo-600">
                      {{ prop.number }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-700">{{ fmtCur(prop.amount) }}</span>
                  </div>
                  <h4 class="text-xs font-semibold text-slate-600 mt-1 truncate">{{ prop.subject }}</h4>
                  <p class="text-[10px] text-slate-400 font-semibold mt-0.5">To: {{ prop.to }}</p>
                </div>

                <div class="text-[10px] space-y-1 text-slate-500 border-t border-slate-100 pt-2 mt-2">
                  <div class="flex justify-between">
                    <span>Open Till:</span>
                    <span class="font-semibold text-slate-600">{{ prop.open_till }}</span>
                  </div>
                </div>

                <!-- Status Select -->
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100" @click.stop>
                  <span class="text-[9px] font-bold text-slate-400 uppercase">Move:</span>
                  <select 
                    class="select-xs text-[10px] border border-slate-200 rounded px-1"
                    :value="prop.status"
                    @change="e => moveProposalStatus(prop.id, e.target.value)"
                  >
                    <option value="draft">Draft</option>
                    <option value="open">Open</option>
                    <option value="sent">Sent</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                  </select>
                </div>
              </div>

              <div v-if="col.rows.length === 0" class="text-center py-8 text-slate-400 text-[10px] border border-dashed border-slate-300 rounded-md bg-white/50">
                No proposals in this stage
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
              <button class="btn-outline" @click="bulkPdfExport">Export PDF</button>
            </div>
            <div class="toolbar-right">
              <select class="select-sm" v-model="statusFilter">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="open">Open</option>
                <option value="accepted">Accepted</option>
                <option value="declined">Declined</option>
              </select>
              <input class="input-sm" v-model="search" placeholder="Search..." />
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th class="w-8"><input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" /></th>
                <th>#</th>
                <th>Subject</th>
                <th>To</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Open Till</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="row in filteredRows" 
                :key="row.id" 
                :class="[{ 'row-selected': selectedIds.includes(row.id) }, { 'row-active': activeProposal && activeProposal.id === row.id }]"
              >
                <td><input type="checkbox" :value="row.id" v-model="selectedIds" /></td>
                <td><a class="link-blue" @click="selectProposal(row)">{{ row.number }}</a></td>
                <td><strong class="cursor-pointer hover:text-indigo-600" @click="selectProposal(row)">{{ row.subject }}</strong></td>
                <td>{{ row.to }}</td>
                <td><span class="badge" :class="statusClass(row.status)">{{ row.status }}</span></td>
                <td class="text-right font-semibold">{{ fmtCur(row.amount) }}</td>
                <td>{{ row.date }}</td>
                <td>{{ row.open_till }}</td>
                <td>
                  <div class="row-actions">
                    <button class="dot-btn" title="Edit" @click="editProposal(row)">⋮</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredRows.length === 0">
                <td colspan="9" class="empty-cell">No proposals found</td>
              </tr>
            </tbody>
          </table>
          <div class="table-footer">Showing {{ filteredRows.length }} of {{ rows.length }} proposals</div>
        </div>
      </div>

      <!-- Right Area: Split View Proposal Details -->
      <div v-if="activeProposal" class="right-pane w-full lg:w-[480px] shrink-0">
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm space-y-4 relative">
          <!-- Detail Head -->
          <div class="flex justify-between items-start pb-3 border-b">
            <div>
              <span class="text-xs font-bold text-slate-400">{{ activeProposal.number }}</span>
              <h2 class="text-sm font-bold text-slate-800 mt-0.5">{{ activeProposal.subject }}</h2>
            </div>
            <button class="text-slate-400 hover:text-slate-600 text-lg font-bold" @click="activeProposal = null">×</button>
          </div>

          <!-- Detail Body -->
          <div class="space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-y-2">
              <span class="text-slate-400 font-semibold">Recipient:</span>
              <span class="text-slate-800 font-bold">{{ activeProposal.to }}</span>
              
              <span class="text-slate-400 font-semibold">Total Amount:</span>
              <span class="text-slate-800 font-bold text-sm text-indigo-600">{{ fmtCur(activeProposal.amount) }}</span>
              
              <span class="text-slate-400 font-semibold">Date Created:</span>
              <span class="text-slate-600">{{ activeProposal.date }}</span>
              
              <span class="text-slate-400 font-semibold">Open Till:</span>
              <span class="text-slate-600 font-bold text-rose-500">{{ activeProposal.open_till }}</span>
              
              <span class="text-slate-400 font-semibold">Status:</span>
              <span><span class="badge" :class="statusClass(activeProposal.status)">{{ activeProposal.status }}</span></span>
            </div>

            <!-- Items list in split view if present -->
            <div v-if="activeProposal.items && activeProposal.items.length" class="space-y-2 border-t pt-3 mt-2">
              <span class="font-bold text-slate-700 block uppercase tracking-wider text-[10px]">Proposal Items</span>
              <table class="w-full text-[11px] text-slate-600">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-1">Item</th>
                    <th class="text-center py-1">Qty</th>
                    <th class="text-right py-1">Rate</th>
                    <th class="text-right py-1">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(it, i) in activeProposal.items" :key="i" class="border-b border-slate-100">
                    <td class="py-1"><strong>{{ it.name }}</strong></td>
                    <td class="text-center py-1">{{ it.qty }}</td>
                    <td class="text-right py-1">{{ fmtCur(it.rate) }}</td>
                    <td class="text-right py-1 font-semibold">{{ fmtCur(it.amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Fake Content Details -->
            <div class="p-3 bg-slate-50 border border-slate-100 rounded text-slate-600 space-y-2">
              <span class="font-bold text-slate-700 block uppercase tracking-wider text-[10px]">Proposal Summary</span>
              <p class="leading-relaxed">This business proposal offers comprehensive services to support requirements, including analysis, timeline milestones, and delivery specifications for {{ activeProposal.to }}.</p>
            </div>

            <!-- Actions inside details panel -->
            <div class="flex flex-wrap gap-2 pt-3 border-t">
              <button class="btn-primary-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] px-2.5 py-1.5 font-bold flex items-center" @click="downloadSinglePdf(activeProposal)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11" class="mr-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4m4-5l5 5 5-5m-5 5V3"/></svg>
                PDF Preview
              </button>
              <button class="btn-outline-sm border rounded text-[11px] px-2.5 py-1.5 text-slate-600 hover:bg-slate-100 font-bold" @click="editProposal(activeProposal)">
                Edit Proposal
              </button>
              <button class="btn-outline-sm border rounded text-[11px] px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 font-bold border-rose-100" @click="deleteSingleProposal(activeProposal.id)">
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
import { useProposalsStore } from '../store/proposalsStore';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ProposalsPage',
  setup() {
    const router = useRouter();
    const proposalsStore = useProposalsStore();

    const viewType = ref('list');
    const perPage = ref('25'); 
    const search = ref(''); 
    const statusFilter = ref('');
    const activeProposal = ref(null);
    const selectedIds = ref([]);

    // Reactive computation bound to the Pinia Proposals store
    const rows = computed(() => proposalsStore.proposals);

    // Computed columns for pipeline Kanban grouped by status
    const pipelineColumns = computed(() => {
      const statuses = ['draft', 'open', 'sent', 'accepted', 'declined'];
      const dotClasses = {
        draft: 'bg-slate-400',
        open: 'bg-blue-500',
        sent: 'bg-yellow-500',
        accepted: 'bg-green-500',
        declined: 'bg-red-500'
      };

      return statuses.map(status => {
        const matchingRows = rows.value.filter(r => r.status === status && (
          !search.value || 
          r.to.toLowerCase().includes(search.value.toLowerCase()) || 
          r.number.toLowerCase().includes(search.value.toLowerCase()) || 
          r.subject.toLowerCase().includes(search.value.toLowerCase())
        ));
        const total = matchingRows.reduce((sum, curr) => sum + Number(curr.amount || 0), 0);
        return {
          status,
          rows: matchingRows,
          total,
          dotClass: dotClasses[status] || 'bg-slate-400'
        };
      });
    });

    const summaryCards = computed(() => {
      const total = rows.value.length;
      const by = (s) => rows.value.filter(r => r.status === s).length;
      return [
        { label: 'Total', value: total, cls: '' },
        { label: 'Draft', value: by('draft'), cls: '' },
        { label: 'Open', value: by('open'), cls: 'text-info' },
        { label: 'Sent', value: by('sent'), cls: 'text-warning' },
        { label: 'Accepted', value: by('accepted'), cls: 'text-success' },
        { label: 'Declined', value: by('declined'), cls: 'text-danger' }
      ];
    });

    const filteredRows = computed(() => rows.value.filter(r => {
      if (statusFilter.value && r.status !== statusFilter.value) return false;
      if (search.value) {
        const query = search.value.toLowerCase();
        return r.to.toLowerCase().includes(query) || r.number.toLowerCase().includes(query) || r.subject.toLowerCase().includes(query);
      }
      return true;
    }));

    // Checkbox selections helpers
    const isAllSelected = computed(() => {
      return filteredRows.value.length > 0 && selectedIds.value.length === filteredRows.value.length;
    });

    const toggleSelectAll = (e) => {
      if (e.target.checked) {
        selectedIds.value = filteredRows.value.map(r => r.id);
      } else {
        selectedIds.value = [];
      }
    };

    const statusClass = (s) => ({
      draft: 'badge-default', open: 'badge-blue', sent: 'badge-yellow',
      accepted: 'badge-green', declined: 'badge-red'
    }[s] || 'badge-default');

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    const toggleViewType = () => {
      viewType.value = viewType.value === 'list' ? 'pipeline' : 'list';
      selectedIds.value = [];
    };

    const toggleTableWidth = () => {
      if (activeProposal.value) {
        activeProposal.value = null;
      } else if (rows.value.length > 0) {
        activeProposal.value = rows.value[0];
      }
    };

    const selectProposal = (prop) => {
      activeProposal.value = prop;
    };

    const moveProposalStatus = (proposalId, newStatus) => {
      proposalsStore.updateProposal(proposalId, { status: newStatus });
      if (activeProposal.value && activeProposal.value.id === proposalId) {
        activeProposal.value.status = newStatus;
      }
      message.success(`Status updated.`);
    };

    const goToCreatePage = () => {
      router.push('/admin/proposals/proposal');
    };

    const editProposal = (row) => {
      router.push(`/admin/proposals/proposal/${row.id}`);
    };

    const deleteSingleProposal = (id) => {
      proposalsStore.deleteProposal(id);
      if (activeProposal.value && activeProposal.value.id === id) {
        activeProposal.value = null;
      }
      message.success('Proposal deleted successfully.');
    };

    const bulkPdfExport = () => {
      const count = selectedIds.value.length || filteredRows.value.length;
      message.loading('Generating bulk PDF exports...', 1.5).then(() => {
        message.success(`Successfully exported ${count} proposals to PDF bundle!`);
        selectedIds.value = [];
      });
    };

    const downloadSinglePdf = (proposal) => {
      message.loading(`Generating PDF for ${proposal.number}...`, 1).then(() => {
        message.success(`Successfully downloaded PDF for ${proposal.number}!`);
      });
    };

    return {
      viewType,
      perPage,
      search,
      statusFilter,
      rows,
      summaryCards,
      filteredRows,
      statusClass,
      fmtCur,
      pipelineColumns,
      selectedIds,
      isAllSelected,
      toggleSelectAll,
      toggleViewType,
      toggleTableWidth,
      selectProposal,
      activeProposal,
      moveProposalStatus,
      goToCreatePage,
      editProposal,
      deleteSingleProposal,
      bulkPdfExport,
      downloadSinglePdf,
    };
  }
});
</script>

<style scoped>
@import '@/main/module-shared.css';

/* Split Layout Grid styling */
.layout-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
  align-items: stretch;
  transition: all 0.2s ease;
}

@media (min-width: 1024px) {
  .layout-grid.split-active {
    flex-direction: row;
  }
  .layout-grid.split-active .left-pane {
    max-width: 50%;
  }
  .layout-grid.split-active .right-pane {
    width: 50%;
  }
}

.pipeline-container {
  display: flex;
  overflow-x: auto;
  gap: 16px;
  padding-bottom: 8px;
}

.pipeline-col {
  height: calc(100vh - 220px);
}

.pipeline-card {
  border-left: 3.5px solid #94a3b8;
  transition: all 0.15s ease;
}
.pipeline-card.card-active {
  border-color: #0d6efd !important;
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12) !important;
  transform: translateY(-2px);
}
.pipeline-card:hover {
  transform: translateY(-1.5px);
}

.pipeline-card.group:hover {
  border-left-width: 5px;
}

/* Row states in table */
.row-selected {
  background-color: #f8fafc;
}
.row-active td {
  background-color: #eff6ff !important;
  border-left: 3px solid #0d6efd;
}

.card-active {
  background-color: #f8fafc;
}

/* Column indicators */
.bg-slate-400 { background-color: #94a3b8; }
.bg-blue-500  { background-color: #3b82f6; }
.bg-yellow-500{ background-color: #eab308; }
.bg-green-500 { background-color: #22c55e; }
.bg-red-500   { background-color: #ef4444; }

.scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 5px;
}
.scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 6px;
}
.scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Icon Buttons style */
.icon-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  cursor: pointer;
  transition: all 0.12s;
}
.icon-btn:hover {
  background: #f8fafc;
  color: #1e293b;
  border-color: #cbd5e1;
}
.icon-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.icon-btn--active {
  background: #eff6ff;
  color: #0d6efd;
  border-color: #bfdbfe;
}
</style>
