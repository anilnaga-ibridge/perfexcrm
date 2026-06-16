<template>
  <div class="announcements-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Announcements</h1>
        <p class="text-xs text-slate-500 mt-0.5">Post and broadcast announcements to staff members and clients</p>
      </div>
      <button class="btn-primary" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14" class="inline mr-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Announcement
      </button>
    </div>

    <!-- Filters & Table Card -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadAnnouncements">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
        </div>
        <div class="toolbar-right">
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search announcements..."
            size="small"
            style="width:260px"
            @search="loadAnnouncements"
          />
        </div>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="announcements"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 800 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'subject'">
            <a class="subj-link" @click="openEditDrawer(record)">{{ record.subject }}</a>
          </template>

          <template v-if="column.key === 'message'">
            <span class="truncate-msg">{{ record.message }}</span>
          </template>

          <template v-if="column.key === 'show_to_staff'">
            <a-tag :color="record.show_to_staff ? 'blue' : 'gray'">
              {{ record.show_to_staff ? 'Yes' : 'No' }}
            </a-tag>
          </template>

          <template v-if="column.key === 'show_to_clients'">
            <a-tag :color="record.show_to_clients ? 'purple' : 'gray'">
              {{ record.show_to_clients ? 'Yes' : 'No' }}
            </a-tag>
          </template>

          <template v-if="column.key === 'created_at'">
            <span>{{ formatDate(record.created_at) }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-dropdown :trigger="['click']">
                <button class="action-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="openEditDrawer(record)">Edit</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteAnnouncement(record.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <p>No announcements posted yet</p>
          </div>
        </template>
      </a-table>
    </div>

    <!-- Announcement Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Announcement' : 'Post Announcement'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="annForm" @finish="saveAnnouncement">
        <a-form-item label="Subject" name="subject" :rules="[{required:true,message:'Required'}]">
          <a-input v-model:value="annForm.subject" placeholder="e.g. Scheduled System Maintenance" />
        </a-form-item>

        <a-form-item label="Message" name="message" :rules="[{required:true,message:'Required'}]">
          <a-textarea v-model:value="annForm.message" :rows="8" placeholder="Announcement details..." />
        </a-form-item>

        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px">
          <label class="checkbox-lbl">
            <input type="checkbox" v-model="annForm.show_to_staff" />
            Show to Staff
          </label>
          <label class="checkbox-lbl">
            <input type="checkbox" v-model="annForm.show_to_clients" />
            Show to Clients
          </label>
        </div>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Save</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'AnnouncementsPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const announcements = ref([]);
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);

    const filters = reactive({ search: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} announcements` });

    const annForm = reactive({
      subject: '',
      message: '',
      show_to_staff: true,
      show_to_clients: false,
    });

    const columns = [
      { title: 'Subject',         key: 'subject',       width: 200 },
      { title: 'Message',         key: 'message' },
      { title: 'Show to Staff',   key: 'show_to_staff',  width: 120 },
      { title: 'Show to Clients', key: 'show_to_clients',width: 130 },
      { title: 'Posted Date',     key: 'created_at',     width: 140 },
      { title: '',                key: 'actions',        width: 60, align: 'center' },
    ];

    const loadAnnouncements = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/announcements', {
          params: {
            search: filters.search,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        announcements.value = res.data.announcements?.data || [];
        pagination.total = res.data.announcements?.total || 0;
      } catch (e) {
        message.error('Failed to load announcements');
      } finally {
        loading.value = false;
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(annForm, {
        subject: '',
        message: '',
        show_to_staff: true,
        show_to_clients: false,
      });
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(annForm, {
        subject: record.subject,
        message: record.message,
        show_to_staff: record.show_to_staff,
        show_to_clients: record.show_to_clients,
      });
      showDrawer.value = true;
    };

    const saveAnnouncement = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/announcements/${editingId.value}`, annForm);
          message.success('Announcement updated');
        } else {
          await axios.post('/api/announcements', annForm);
          message.success('Announcement posted');
        }
        showDrawer.value = false;
        loadAnnouncements();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to post announcement');
        }
      } finally {
        saving.value = false;
      }
    };

    const deleteAnnouncement = async (id) => {
      try {
        await axios.delete(`/api/announcements/${id}`);
        message.success('Announcement deleted');
        loadAnnouncements();
      } catch {
        message.error('Failed to delete announcement');
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadAnnouncements();
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(() => {
      loadAnnouncements();
    });

    return {
      loading, saving, announcements, filters, pagination, columns,
      annForm, showDrawer, isEdit,
      openCreateDrawer, openEditDrawer, saveAnnouncement, deleteAnnouncement, handleTableChange,
      formatDate
    };
  }
});
</script>

<style scoped>
.announcements-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
}
.btn-primary:hover { background: #0b5ed7; }

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

.subj-link {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
}
.truncate-msg {
  color: #475569;
  font-size: 12px;
  display: inline-block;
  max-width: 380px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 4px;
}
.action-btn:hover { background: #f1f5f9; color: #475569; }
.row-actions { display: flex; justify-content: center; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.checkbox-lbl {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #334155;
  cursor: pointer;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
</style>
