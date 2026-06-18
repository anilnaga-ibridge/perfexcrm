<template>
  <div class="activity-log-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Activity Log</h1>
        <p class="text-xs text-slate-500 mt-0.5">Audit log of system activities, actions, and administrative operations</p>
      </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadLogs">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
        </div>
        <div class="toolbar-right">
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search activity description or user..."
            size="small"
            style="width:300px"
            @search="loadLogs"
          />
          <a-popconfirm title="Clear all activity logs? This cannot be undone." @confirm="clearLogs">
            <a-button size="small" danger>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="inline mr-1"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
              Clear Log
            </a-button>
          </a-popconfirm>
        </div>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="logs"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 800 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'description'">
            <span class="log-desc">{{ record.description }}</span>
          </template>

          <template v-if="column.key === 'user_name'">
            <span class="log-user">{{ record.user_name || 'System' }}</span>
          </template>

          <template v-if="column.key === 'ip_address'">
            <span class="log-ip">{{ record.ip_address || '—' }}</span>
          </template>

          <template v-if="column.key === 'user_agent'">
            <span class="log-ua" :title="record.user_agent">{{ truncateAgent(record.user_agent) }}</span>
          </template>

          <template v-if="column.key === 'created_at'">
            <span>{{ formatDateTime(record.created_at) }}</span>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="9" x2="15" y2="9"/>
              <line x1="9" y1="13" x2="15" y2="13"/>
              <line x1="9" y1="17" x2="13" y2="17"/>
            </svg>
            <p>No activity logs found</p>
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
  name: 'ActivityLogPage',
  setup() {
    const loading = ref(false);
    const logs = ref([]);

    const filters = reactive({ search: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} logs` });

    const columns = [
      { title: 'Date / Time',    key: 'created_at',   width: 170 },
      { title: 'Description',    key: 'description' },
      { title: 'User / Staff',   key: 'user_name',    width: 140 },
      { title: 'IP Address',     key: 'ip_address',   width: 130 },
      { title: 'User Agent',     key: 'user_agent',   width: 200 },
    ];

    const loadLogs = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/activity-logs', {
          params: {
            search: filters.search,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        logs.value = res.data.logs?.data || [];
        pagination.total = res.data.logs?.total || 0;
      } catch (e) {
        message.error('Failed to load activity logs');
      } finally {
        loading.value = false;
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadLogs();
    };

    const formatDateTime = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    };

    const truncateAgent = (ua) => {
      if (!ua) return '—';
      if (ua.length <= 32) return ua;
      return ua.substring(0, 30) + '...';
    };

    const clearLogs = async () => {
      try {
        await axios.delete('/api/activity-logs');
        message.success('Activity log cleared');
        loadLogs();
      } catch {
        message.error('Failed to clear activity log');
      }
    };

    onMounted(() => {
      loadLogs();
    });

    return {
      loading, logs, filters, pagination, columns,
      loadLogs, handleTableChange, formatDateTime, truncateAgent, clearLogs
    };
  }
});
</script>

<style scoped>
.activity-log-page {
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

/* Table */
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

.log-desc {
  font-size: 12.5px;
  color: #1e293b;
  font-weight: 500;
}
.log-user {
  font-weight: 600;
  color: #475569;
}
.log-ip {
  font-family: monospace;
  color: #64748b;
  font-size: 11.5px;
}
.log-ua {
  color: #94a3b8;
  font-size: 11px;
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
