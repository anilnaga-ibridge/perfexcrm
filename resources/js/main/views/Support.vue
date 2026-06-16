<template>
  <div class="module-page">
    <div class="page-header">
      <h1 class="page-title">Support Tickets</h1>
      <button class="btn-primary" @click="showCreate = true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Open Ticket
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card" v-for="s in summaryCards" :key="s.label">
        <div class="summary-label">{{ s.label }}</div>
        <div class="summary-value" :class="s.cls">{{ s.value }}</div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <select class="select-sm" v-model="perPage">
            <option value="10">10</option><option value="25">25</option><option value="50">50</option>
          </select>
          <button class="btn-outline">Export</button>
        </div>
        <div class="toolbar-right">
          <select class="select-sm" v-model="statusFilter">
            <option value="">All Statuses</option>
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Answered">Answered</option>
            <option value="Closed">Closed</option>
          </select>
          <input class="input-sm" v-model="search" placeholder="Search subject/client/dept..." />
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th><input type="checkbox" /></th>
            <th>#</th>
            <th>Subject</th>
            <th>Client</th>
            <th>Department</th>
            <th>Priority</th>
            <th>Last Reply</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in filteredRows" :key="row.id">
            <td><input type="checkbox" /></td>
            <td><span class="ticket-tag font-semibold">#{{ row.number }}</span></td>
            <td>
              <a class="link-blue font-semibold">{{ row.subject }}</a>
              <div style="font-size:11px;color:#64748b;margin-top:2px">{{ row.message_excerpt }}</div>
            </td>
            <td>{{ row.client }}</td>
            <td>{{ row.department }}</td>
            <td><span class="priority-lbl" :class="priorityClass(row.priority)">{{ row.priority }}</span></td>
            <td>{{ row.last_reply }}</td>
            <td><span class="badge" :class="statusClass(row.status)">{{ row.status }}</span></td>
            <td>
              <div class="row-actions">
                <button class="dot-btn" title="Edit">⋮</button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredRows.length === 0">
            <td colspan="9" class="empty-cell">No tickets found</td>
          </tr>
        </tbody>
      </table>
      <div class="table-footer">Showing {{ filteredRows.length }} of {{ rows.length }} tickets</div>
    </div>

    <!-- Create Drawer -->
    <div v-if="showCreate" class="drawer-overlay" @click.self="showCreate=false">
      <div class="drawer">
        <div class="drawer-head">
          <span class="drawer-title">Open Support Ticket</span>
          <button class="drawer-close" @click="showCreate=false">×</button>
        </div>
        <div class="drawer-body">
          <label>Subject</label>
          <input class="form-ctrl" type="text" v-model="form.subject" placeholder="Ticket Subject / Title" />

          <label>Client</label>
          <select class="form-ctrl" v-model="form.client">
            <option v-for="c in clients" :key="c">{{ c }}</option>
          </select>

          <label>Department</label>
          <select class="form-ctrl" v-model="form.department">
            <option v-for="d in departments" :key="d">{{ d }}</option>
          </select>

          <label>Priority</label>
          <select class="form-ctrl" v-model="form.priority">
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
          </select>

          <label>Ticket Status</label>
          <select class="form-ctrl" v-model="form.status">
            <option value="Open">Open</option>
            <option value="In Progress">In Progress</option>
            <option value="Answered">Answered</option>
          </select>

          <label>Ticket Message</label>
          <textarea class="form-ctrl" rows="5" v-model="form.message" placeholder="Describe the issue in detail..."></textarea>
        </div>
        <div class="drawer-foot">
          <button class="btn-ghost" @click="showCreate=false">Cancel</button>
          <button class="btn-primary" @click="addRow">Open Ticket</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
export default defineComponent({
  name: 'SupportPage',
  setup() {
    const perPage = ref('25'); const search = ref(''); const statusFilter = ref('');
    const showCreate = ref(false);
    const form = ref({ subject: '', client: 'Nader-Abernathy', department: 'Technical Support', priority: 'Medium', status: 'Open', message: '' });

    const clients = ['Nader-Abernathy', 'Mertz-Bergnaum', 'Schroeder and Sons', 'Bayer Group', 'Halvorson LLC', 'Kub Group'];
    const departments = ['Technical Support', 'Sales & Pre-Sales', 'Billing & Finance', 'Account Management'];

    const rows = ref([
      { id: 1, number: 1024, subject: 'Cannot connect to database', message_excerpt: 'Getting access denied errors for user crm_db_user from 10.0.3...', client: 'Nader-Abernathy', department: 'Technical Support', priority: 'High', last_reply: 'Jun 13, 2026 14:22', status: 'In Progress' },
      { id: 2, number: 1023, subject: 'Inquiry regarding e-Invoice v1.0 module', message_excerpt: 'Would like to purchase a licensing subscription pack for multiple clients...', client: 'Mertz-Bergnaum', department: 'Sales & Pre-Sales', priority: 'Medium', last_reply: 'Jun 12, 2026 18:05', status: 'Answered' },
      { id: 3, number: 1022, subject: 'Billing discrepancy - Invoice INV-00018', message_excerpt: 'Double charge on credit card for the maintenance SLA billing cycle...', client: 'Schroeder and Sons', department: 'Billing & Finance', priority: 'High', last_reply: 'Jun 12, 2026 09:12', status: 'Open' },
      { id: 4, number: 1021, subject: 'Requesting custom layout styles help', message_excerpt: 'We need help adding our custom logo font files into the PDF templates...', client: 'Bayer Group', department: 'Technical Support', priority: 'Low', last_reply: 'May 28, 2026 11:45', status: 'Closed' },
      { id: 5, number: 1020, subject: 'Surveys feedback result exports failing', message_excerpt: 'Getting 500 error when clicking CSV exports for goals v2.3...', client: 'Halvorson LLC', department: 'Technical Support', priority: 'Medium', last_reply: 'Jun 13, 2026 10:15', status: 'Open' }
    ]);

    const summaryCards = computed(() => {
      const total = rows.value.length;
      const by = (s) => rows.value.filter(r => r.status === s).length;
      return [
        { label: 'Total Tickets', value: total, cls: '' },
        { label: 'Open', value: by('Open'), cls: 'text-danger' },
        { label: 'In Progress', value: by('In Progress'), cls: 'text-info' },
        { label: 'Answered', value: by('Answered'), cls: 'text-success' },
        { label: 'Closed', value: by('Closed'), cls: 'text-muted' }
      ];
    });

    const filteredRows = computed(() => rows.value.filter(r => {
      if (statusFilter.value && r.status !== statusFilter.value) return false;
      if (search.value) {
        const query = search.value.toLowerCase();
        return r.subject.toLowerCase().includes(query) || r.client.toLowerCase().includes(query) || r.department.toLowerCase().includes(query) || String(r.number).includes(query);
      }
      return true;
    }));

    const statusClass = (s) => ({
      Open: 'badge-red',
      'In Progress': 'badge-blue',
      Answered: 'badge-green',
      Closed: 'badge-gray'
    }[s] || 'badge-default');

    const priorityClass = (p) => ({
      High: 'text-danger font-semibold',
      Medium: 'text-warning font-semibold',
      Low: 'text-muted'
    }[p] || '');

    const addRow = () => {
      const newId = rows.value.length + 1;
      const newNum = 1000 + newId;
      const now = new Date();
      const lastReplyStr = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

      rows.value.unshift({
        id: newId,
        number: newNum,
        subject: form.value.subject || 'Untitled Issue',
        message_excerpt: form.value.message ? form.value.message.substring(0, 75) + '...' : 'No message body provided.',
        client: form.value.client,
        department: form.value.department,
        priority: form.value.priority,
        last_reply: lastReplyStr,
        status: form.value.status
      });
      showCreate.value = false;
      // Reset form
      form.value = { subject: '', client: 'Nader-Abernathy', department: 'Technical Support', priority: 'Medium', status: 'Open', message: '' };
    };

    return { perPage, search, statusFilter, rows, summaryCards, filteredRows, statusClass, priorityClass, showCreate, form, clients, departments, addRow };
  }
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.ticket-tag {
  color: #64748b;
  font-family: monospace;
  font-size: 13px;
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
}

.priority-lbl {
  font-size: 12px;
}
</style>
