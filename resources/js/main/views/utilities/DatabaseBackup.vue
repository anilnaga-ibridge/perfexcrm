<template>
  <div class="db-backup-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Database Backup</h1>
        <p class="text-xs text-slate-500 mt-0.5">Create and manage database backups</p>
      </div>
    </div>

    <div class="notice-card">
      <div class="notice-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
      </div>
      <span class="notice-text">
        Due to the limited execution time and memory available to PHP, backing up very large databases may not be possible. If your database is very large you might need to backup directly from your SQL server via the command line, or have your server admin do it for you if you do not have root privileges.
      </span>
    </div>

    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <span class="text-sm text-slate-500">{{ backups.length }} backup(s)</span>
        </div>
        <div class="toolbar-right">
          <a-switch
            :checked="autoBackup"
            @change="handleAutoBackupToggle"
            :loading="autoLoading"
            size="small"
          />
          <span class="text-xs text-slate-500 mr-2">{{ autoBackup ? 'Auto Backup On' : 'Auto Backup Off' }}</span>
          <a-button type="primary" size="small" @click="createBackup" :loading="creating">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="inline mr-1">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Create Database Backup
          </a-button>
        </div>
      </div>

      <a-table
        :dataSource="backups"
        :columns="columns"
        :loading="loading"
        row-key="id"
        size="small"
        :scroll="{ x: 600 }"
        :pagination="false"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'filename'">
            <span class="backup-filename">{{ record.filename }}</span>
          </template>

          <template v-if="column.key === 'backup_size'">
            <span class="backup-size">{{ record.backup_size || '—' }}</span>
          </template>

          <template v-if="column.key === 'created_at'">
            <span>{{ formatDateTime(record.created_at) }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="action-btns">
              <a-button size="small" type="link" @click="downloadBackup(record.id)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="inline">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
              </a-button>
              <a-popconfirm title="Delete this backup?" @confirm="deleteBackup(record.id)">
                <a-button size="small" type="link" danger>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="inline">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                  </svg>
                </a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <p>No backups yet. Click "Create Database Backup" to get started.</p>
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
  name: 'DatabaseBackupPage',
  setup() {
    const loading = ref(false);
    const creating = ref(false);
    const autoLoading = ref(false);
    const backups = ref([]);
    const autoBackup = ref(false);

    const columns = [
      { title: 'Backup', key: 'filename' },
      { title: 'Backup size', key: 'backup_size', width: 120 },
      { title: 'Date', key: 'created_at', width: 180 },
      { title: 'Options', key: 'actions', width: 100 },
    ];

    const loadBackups = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/database-backups');
        backups.value = res.data.backups || [];
        autoBackup.value = res.data.auto_backup || false;
      } catch (e) {
        message.error('Failed to load backups');
      } finally {
        loading.value = false;
      }
    };

    const createBackup = async () => {
      creating.value = true;
      try {
        const res = await axios.post('/api/database-backups');
        message.success(res.data.message || 'Backup created');
        await loadBackups();
      } catch (e) {
        const errMsg = e.response?.data?.message || 'Failed to create backup';
        message.error(errMsg);
      } finally {
        creating.value = false;
      }
    };

    const downloadBackup = async (id) => {
      try {
        const res = await axios.get(`/api/database-backups/${id}/download`, {
          responseType: 'blob',
        });
        const contentDisposition = res.headers['content-disposition'];
        let filename = 'backup.sql';
        if (contentDisposition) {
          const match = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
          if (match && match[1]) {
            filename = match[1].replace(/['"]/g, '');
          }
        }
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
      } catch (e) {
        message.error('Failed to download backup');
      }
    };

    const deleteBackup = async (id) => {
      try {
        await axios.delete(`/api/database-backups/${id}`);
        message.success('Backup deleted');
        await loadBackups();
      } catch {
        message.error('Failed to delete backup');
      }
    };

    const handleAutoBackupToggle = async (checked) => {
      autoLoading.value = true;
      try {
        const res = await axios.post('/api/database-backups/toggle-auto', { enabled: checked });
        autoBackup.value = res.data.auto_backup;
        message.success(res.data.message);
      } catch {
        message.error('Failed to toggle auto backup');
      } finally {
        autoLoading.value = false;
      }
    };

    const formatDateTime = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      });
    };

    onMounted(() => {
      loadBackups();
    });

    return {
      loading, creating, autoLoading, backups, autoBackup, columns,
      loadBackups, createBackup, downloadBackup, deleteBackup,
      handleAutoBackupToggle, formatDateTime,
    };
  }
});
</script>

<style scoped>
.db-backup-page {
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

.notice-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #fefce8;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 18px;
}
.notice-icon {
  flex-shrink: 0;
  color: #ca8a04;
  margin-top: 1px;
}
.notice-text {
  font-size: 12.5px;
  color: #713f12;
  line-height: 1.5;
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

.backup-filename {
  font-size: 12.5px;
  color: #1e293b;
  font-weight: 500;
  font-family: monospace;
}
.backup-size {
  font-size: 12px;
  color: #64748b;
}

.action-btns {
  display: flex;
  gap: 4px;
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
