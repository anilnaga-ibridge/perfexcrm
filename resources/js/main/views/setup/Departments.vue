<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Departments</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> New Department
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search departments..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredDepts"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredDepts.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <span class="dept-name">{{ record.name }}</span>
          </template>
          <template v-if="column.key === 'email'">
            <span>{{ record.email || '—' }}</span>
          </template>
          <template v-if="column.key === 'google_calendar'">
            <span>{{ record.google_calendar_id || '—' }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editDept(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteDept(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- New/Edit Department Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Department' : 'New Department'"
      placement="right"
      :width="520"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveDept">
        <a-form-item label="* Department Name" name="name" :rules="[{ required: true, message: 'Department name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter department name" />
        </a-form-item>

        <a-form-item label="Hide from client?">
          <a-switch v-model:checked="form.hide_from_client" />
          <span class="field-hint">If enabled, this department won't be visible to clients</span>
        </a-form-item>

        <a-form-item label="Department Email">
          <a-input v-model:value="form.email" placeholder="e.g. support@yourcompany.com" />
        </a-form-item>

        <a-divider orientation="left">Email to ticket configuration</a-divider>

        <a-form-item label="IMAP Username">
          <a-input v-model:value="form.imap_username" placeholder="IMAP username" />
        </a-form-item>

        <a-form-item label="IMAP Host">
          <a-input v-model:value="form.imap_host" placeholder="e.g. imap.gmail.com" />
        </a-form-item>

        <a-form-item label="Password">
          <a-input-password v-model:value="form.imap_password" placeholder="IMAP password" />
        </a-form-item>

        <a-form-item label="Encryption">
          <a-radio-group v-model:value="form.imap_encryption">
            <a-radio value="tls">TLS</a-radio>
            <a-radio value="ssl">SSL</a-radio>
            <a-radio value="">No Encryption</a-radio>
          </a-radio-group>
        </a-form-item>

        <a-form-item label="Folder">
          <a-input v-model:value="form.imap_folder" placeholder="INBOX" />
          <span class="field-hint">Retrieve Folders</span>
        </a-form-item>

        <a-form-item label="Delete mail after import?">
          <a-switch v-model:checked="form.delete_after_import" />
        </a-form-item>

        <a-form-item>
          <a-button @click="testImap" :loading="testingImap" style="margin-right: 8px">
            Test IMAP Connection
          </a-button>
          <a-tag v-if="imapStatus === 'success'" color="success">Connected</a-tag>
          <a-tag v-else-if="imapStatus === 'error'" color="error">Failed</a-tag>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Department' : 'Add Department' }}
          </a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'DepartmentsView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);
    const testingImap = ref(false);
    const imapStatus = ref('');

    const departments = ref([
      { id: 3, name: 'Abuse', email: 'abuse@perfexcrm.com', google_calendar_id: '', hide_from_client: false, imap_username: '', imap_host: '', imap_password: '', imap_encryption: 'tls', imap_folder: 'INBOX', delete_after_import: false },
      { id: 1, name: 'Marketing', email: 'marketing@perfexcrm.com', google_calendar_id: '', hide_from_client: false, imap_username: '', imap_host: '', imap_password: '', imap_encryption: 'tls', imap_folder: 'INBOX', delete_after_import: false },
      { id: 2, name: 'Sales', email: 'sales@perfexcrm.com', google_calendar_id: '', hide_from_client: false, imap_username: '', imap_host: '', imap_password: '', imap_encryption: 'tls', imap_folder: 'INBOX', delete_after_import: false },
    ]);

    const form = reactive({
      name: '',
      email: '',
      hide_from_client: false,
      imap_username: '',
      imap_host: '',
      imap_password: '',
      imap_encryption: 'tls',
      imap_folder: 'INBOX',
      delete_after_import: false,
    });

    const columns = [
      { title: 'ID',                 key: 'id',              dataIndex: 'id',              width: 60 },
      { title: 'Name',               key: 'name',            dataIndex: 'name' },
      { title: 'Department Email',   key: 'email',           dataIndex: 'email' },
      { title: 'Google Calendar ID', key: 'google_calendar', dataIndex: 'google_calendar_id' },
      { title: 'Options',            key: 'options',         width: 150 },
    ];

    const filteredDepts = computed(() => {
      if (!search.value) return departments.value;
      return departments.value.filter(d => d.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editDept = (record) => {
      editingId.value = record.id;
      Object.assign(form, {
        name: record.name,
        email: record.email || '',
        hide_from_client: record.hide_from_client || false,
        imap_username: record.imap_username || '',
        imap_host: record.imap_host || '',
        imap_password: record.imap_password || '',
        imap_encryption: record.imap_encryption || 'tls',
        imap_folder: record.imap_folder || 'INBOX',
        delete_after_import: record.delete_after_import || false,
      });
      imapStatus.value = '';
      openDrawer.value = true;
    };

    const deleteDept = (id) => {
      departments.value = departments.value.filter(d => d.id !== id);
      message.success('Department deleted');
    };

    const saveDept = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const d = departments.value.find(x => x.id === editingId.value);
        if (d) Object.assign(d, { ...form });
        message.success('Department updated');
      } else {
        departments.value.push({ id: Date.now(), ...form });
        message.success('Department added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const testImap = async () => {
      testingImap.value = true;
      imapStatus.value = '';
      try {
        await new Promise(resolve => setTimeout(resolve, 1500));
        imapStatus.value = 'success';
        message.success('IMAP connection successful');
      } catch (e) {
        imapStatus.value = 'error';
        message.error('IMAP connection failed');
      } finally {
        testingImap.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      imapStatus.value = '';
      Object.assign(form, {
        name: '', email: '', hide_from_client: false,
        imap_username: '', imap_host: '', imap_password: '',
        imap_encryption: 'tls', imap_folder: 'INBOX', delete_after_import: false,
      });
    };

    return {
      search, openDrawer, saving, editingId, testingImap, imapStatus,
      departments, form, columns, filteredDepts,
      editDept, deleteDept, saveDept, testImap, resetForm,
    };
  }
});
</script>

<style scoped>
.section-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-toolbar .section-title { margin: 0; }
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}
.btn-primary span { font-size: 16px; font-weight: 700; line-height: 1; }
.data-table-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}
.row-actions { display: flex; gap: 4px; }
.dept-name { font-weight: 600; color: #1e293b; }
.field-hint { font-size: 12px; color: #94a3b8; margin-left: 8px; }
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
