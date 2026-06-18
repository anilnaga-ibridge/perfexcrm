<template>
  <div class="tpl-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Ticket Pipe Log</h1>
        <p class="text-xs text-slate-500 mt-0.5">Log of incoming emails processed via the ticket pipe</p>
      </div>
    </div>

    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadLogs">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
          <a-range-picker
            v-model:value="dateRange"
            size="small"
            style="width:220px"
            @change="onDateChange"
          />
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search..."
            size="small"
            style="width:240px"
            @search="loadLogs"
          />
        </div>
        <div class="toolbar-right">
          <a-popconfirm title="Clear all ticket pipe logs? This cannot be undone." @confirm="clearLogs">
            <a-button size="small" danger>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="inline mr-1"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
              Clear Log
            </a-button>
          </a-popconfirm>
        </div>
      </div>

      <a-table
        :dataSource="logs"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 900 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'from_name'">
            <span class="tpl-from">{{ record.from_name || '—' }}</span>
          </template>

          <template v-if="column.key === 'created_at'">
            <span>{{ formatDateTime(record.created_at) }}</span>
          </template>

          <template v-if="column.key === 'to'">
            <span class="tpl-to">{{ record.to || '—' }}</span>
          </template>

          <template v-if="column.key === 'from_email'">
            <span class="tpl-email">{{ record.from_email || '—' }}</span>
          </template>

          <template v-if="column.key === 'subject'">
            <span class="tpl-subject">{{ record.subject || '—' }}</span>
          </template>

          <template v-if="column.key === 'message'">
            <span class="tpl-message">{{ truncateMessage(record.message) }}</span>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="statusColor(record.status)">{{ record.status }}</a-tag>
          </template>

          <template v-if="column.key === 'actions'">
            <a-popconfirm title="Delete this entry?" @confirm="deleteLog(record.id)">
              <a-button size="small" type="link" danger>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="inline">
                  <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
              </a-button>
            </a-popconfirm>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
            </svg>
            <p>No entries found</p>
          </div>
        </template>
      </a-table>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'TicketPipeLogPage',
  setup() {
    const loading = ref(false);
    const logs = ref([]);

    const filters = reactive({ search: '', perPage: 25, from_date: null, to_date: null });
    const dateRange = ref([]);
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} entries` });

    const columns = [
      { title: 'From Name',  key: 'from_name',   width: 140 },
      { title: 'Date',       key: 'created_at',   width: 170 },
      { title: 'To',         key: 'to',           width: 180 },
      { title: 'From Email', key: 'from_email',   width: 200 },
      { title: 'Subject',    key: 'subject' },
      { title: 'Message',    key: 'message',      width: 200 },
      { title: 'Status',     key: 'status',       width: 100 },
      { title: 'Actions',    key: 'actions',      width: 60 },
    ];

    const loadLogs = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/ticket-pipe-logs', {
          params: {
            search: filters.search,
            per_page: filters.perPage,
            page: pagination.current,
            from_date: filters.from_date,
            to_date: filters.to_date,
          }
        });
        logs.value = res.data.logs?.data || [];
        pagination.total = res.data.logs?.total || 0;
      } catch (e) {
        message.error('Failed to load ticket pipe logs');
      } finally {
        loading.value = false;
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadLogs();
    };

    const onDateChange = (dates) => {
      if (dates && dates.length === 2) {
        filters.from_date = dates[0].format('YYYY-MM-DD');
        filters.to_date = dates[1].format('YYYY-MM-DD');
      } else {
        filters.from_date = null;
        filters.to_date = null;
      }
      pagination.current = 1;
      loadLogs();
    };

    const clearLogs = async () => {
      try {
        await axios.delete('/api/ticket-pipe-logs/clear');
        message.success('Ticket pipe log cleared');
        loadLogs();
      } catch {
        message.error('Failed to clear ticket pipe log');
      }
    };

    const deleteLog = async (id) => {
      try {
        await axios.delete(`/api/ticket-pipe-logs/${id}`);
        message.success('Entry deleted');
        loadLogs();
      } catch {
        message.error('Failed to delete entry');
      }
    };

    const formatDateTime = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      });
    };

    const truncateMessage = (msg) => {
      if (!msg) return '—';
      if (msg.length <= 60) return msg;
      return msg.substring(0, 58) + '...';
    };

    const statusColor = (status) => {
      const map = {
        Imported: 'green',
        Failed: 'red',
        Pending: 'orange',
        Duplicate: 'purple',
      };
      return map[status] || 'default';
    };

    onMounted(() => {
      loadLogs();
    });

    return {
      loading, logs, filters, dateRange, pagination, columns,
      loadLogs, handleTableChange, onDateChange, clearLogs, deleteLog,
      formatDateTime, truncateMessage, statusColor,
    };
  }
});
</script>

<style scoped>
.tpl-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.page-header {
  margin-bottom: 18px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 10px;
}
.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tpl-from, .tpl-to, .tpl-email, .tpl-subject {
  font-size: 12.5px;
  color: #1e293b;
}
.tpl-from {
  font-weight: 600;
}
.tpl-email {
  color: #475569;
}
.tpl-message {
  font-size: 11.5px;
  color: #64748b;
  font-family: monospace;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}
</style>
