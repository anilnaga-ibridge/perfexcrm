<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">
        {{ viewState === 'integration' ? 'Email Lead Integration' : 'Spam Filters' }}
      </h2>
      <div class="toolbar-actions">
        <button v-if="viewState === 'integration'" class="btn-secondary" @click="viewState = 'spam'">
          Spam Filter
        </button>
        <template v-else>
          <button class="btn-secondary" @click="viewState = 'integration'" style="margin-right: 8px;">
            Back to Email Integration
          </button>
          <button class="btn-primary" @click="openNewSpamDrawer">
            New Spam Filter
          </button>
        </template>
      </div>
    </div>

    <!-- VIEW 1: EMAIL LEAD INTEGRATION FORM -->
    <div v-if="viewState === 'integration'" class="settings-card">
      <div class="settings-hint-block">
        <a href="#" @click.prevent class="link-blue">Click here to read more about leads email integration</a>
      </div>

      <a-form layout="vertical" :model="integrationForm" @finish="saveIntegration">
        <div class="form-row" style="margin-bottom: 15px;">
          <a-form-item label="Active" name="active">
            <a-switch v-model:checked="integrationForm.active" />
          </a-form-item>
        </div>

        <div class="settings-form-grid">
          <a-form-item label="* IMAP Server" name="imap_host" :rules="[{ required: true, message: 'IMAP server required' }]">
            <a-input v-model:value="integrationForm.imap_host" placeholder="imap.domain.com" />
          </a-form-item>

          <a-form-item label="* Email address (Login)" name="imap_email" :rules="[{ required: true, message: 'Email required' }]">
            <a-input v-model:value="integrationForm.imap_email" placeholder="leads@domain.com" />
          </a-form-item>

          <a-form-item label="* Password" name="imap_password" :rules="[{ required: true, message: 'Password required' }]">
            <a-input-password v-model:value="integrationForm.imap_password" />
          </a-form-item>

          <a-form-item label="Encryption" name="encryption">
            <a-select v-model:value="integrationForm.encryption">
              <a-select-option value="tls">TLS</a-select-option>
              <a-select-option value="ssl">SSL</a-select-option>
              <a-select-option value="none">No Encryption</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="* Folder" name="folder" :rules="[{ required: true, message: 'Folder required' }]">
            <div style="display: flex; gap: 8px;">
              <a-input v-model:value="integrationForm.folder" placeholder="INBOX" style="flex: 1;" />
              <a-button type="default" @click="retrieveFolders">Retrieve Folders</a-button>
            </div>
          </a-form-item>

          <a-form-item label="* Check Every (minutes)" name="check_interval" :rules="[{ required: true, message: 'Interval required' }]">
            <a-input-number v-model:value="integrationForm.check_interval" :min="1" style="width: 100%;" />
          </a-form-item>

          <a-form-item label="* Default Status" name="default_status" :rules="[{ required: true, message: 'Default status required' }]">
            <a-select v-model:value="integrationForm.default_status" style="width: 100%;">
              <a-select-option v-for="s in leadStatuses" :key="s" :value="s">{{ s }}</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="* Default Source" name="default_source" :rules="[{ required: true, message: 'Default source required' }]">
            <a-select v-model:value="integrationForm.default_source" style="width: 100%;">
              <a-select-option v-for="src in leadSources" :key="src" :value="src">{{ src }}</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="* Responsible for new lead" name="responsible" :rules="[{ required: true, message: 'Responsible staff required' }]">
            <a-select v-model:value="integrationForm.responsible" style="width: 100%;">
              <a-select-option v-for="staff in staffMembers" :key="staff" :value="staff">{{ staff }}</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <!-- Checkbox settings -->
        <div class="checkbox-settings-group">
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.only_check_unread">Only check non opened emails</a-checkbox>
          </div>
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.create_task_if_customer">Create task if email sender is already customer and assign to responsible staff member.</a-checkbox>
          </div>
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.delete_after_import">Delete mail after import?</a-checkbox>
          </div>
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.auto_mark_public">Auto mark as public</a-checkbox>
          </div>
        </div>

        <!-- Notification settings -->
        <div class="notification-settings-group">
          <h3 class="group-subtitle">Notification settings</h3>
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.notify_on_import">Notify when lead imported</a-checkbox>
          </div>
          <div class="settings-row">
            <a-checkbox v-model:checked="integrationForm.notify_multiple_emails">Notify if lead send email multiple times</a-checkbox>
          </div>
          
          <a-form-item label="Staff members to notify" style="margin-top: 15px;">
            <a-select v-model:value="integrationForm.notified_staff" mode="multiple" placeholder="Specific Staff Members Responsible person" style="width: 100%;">
              <a-select-option v-for="staff in staffMembers" :key="staff" :value="staff">{{ staff }}</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div class="settings-actions">
          <a-button type="primary" html-type="submit">Save Integration</a-button>
        </div>
      </a-form>
    </div>

    <!-- VIEW 2: SPAM FILTERS LIST -->
    <div v-else class="settings-card">
      <!-- Tabs for Blocked Types -->
      <a-tabs v-model:activeKey="activeTab" class="spam-tabs">
        <a-tab-pane key="sender" tab="Blocked Senders" />
        <a-tab-pane key="subject" tab="Blocked Subjects" />
        <a-tab-pane key="phrase" tab="Blocked Phrases" />
      </a-tabs>

      <div class="data-table-wrap">
        <div class="data-table-controls">
          <div class="page-size-select">
            <a-select v-model:value="pageSize" size="small" style="width: 70px">
              <a-select-option :value="10">10</a-select-option>
              <a-select-option :value="25">25</a-select-option>
              <a-select-option :value="50">50</a-select-option>
            </a-select>
          </div>
          <a-input-search
            v-model:value="search"
            placeholder="Search..."
            style="width: 280px"
            size="small"
          />
        </div>

        <a-table
          :dataSource="filteredSpam"
          :columns="spamColumns"
          :pagination="{ pageSize: pageSize, total: filteredSpam.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'value'">
              <span class="spam-content">{{ record.value }}</span>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editSpam(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteSpam(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>

      <!-- Add/Edit Spam Drawer -->
      <a-drawer
        v-model:open="openSpamDrawer"
        :title="editingSpamId ? 'Edit Spam Filter' : 'Add Spam Filter'"
        placement="right"
        :width="400"
        @close="resetSpamForm"
      >
        <a-form layout="vertical" :model="spamForm" @finish="saveSpam">
          <a-form-item label="* Type" name="type" :rules="[{ required: true, message: 'Type required' }]">
            <a-select v-model:value="spamForm.type">
              <a-select-option value="sender">Sender</a-select-option>
              <a-select-option value="subject">Subject</a-select-option>
              <a-select-option value="phrase">Phrase</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="* Content" name="value" :rules="[{ required: true, message: 'Content required' }]">
            <a-textarea v-model:value="spamForm.value" placeholder="Enter block value (e.g. spam@domain.com or phrase)" :rows="4" />
          </a-form-item>
          <div class="drawer-footer">
            <a-button @click="openSpamDrawer = false">Cancel</a-button>
            <a-button type="primary" html-type="submit">
              {{ editingSpamId ? 'Update Filter' : 'Add Filter' }}
            </a-button>
          </div>
        </a-form>
      </a-drawer>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'EmailIntegrationView',
  setup() {
    const viewState = ref('integration'); // 'integration' or 'spam'
    const activeTab = ref('sender');      // 'sender', 'subject', 'phrase'
    const search = ref('');
    const pageSize = ref(25);
    const openSpamDrawer = ref(false);
    const editingSpamId = ref(null);

    const leadStatuses = ref(['New', 'Contacted', 'Working', 'Qualified', 'Proposal Sent', 'Customer', 'Lost']);
    const leadSources = ref(['Google Search', 'Facebook Ads', 'Cold Call', 'Referral']);
    const staffMembers = ref(['Armando Turcotte', 'Cleta Stehr', 'System Admin']);

    const integrationForm = reactive({
      active: true,
      imap_host: 'imap.mailtrap.io',
      imap_email: 'leads-inbox@perfexcrm.com',
      imap_password: '•••••••••••••',
      encryption: 'ssl',
      folder: 'INBOX',
      check_interval: 10,
      default_status: 'New',
      default_source: 'Google Search',
      responsible: 'Armando Turcotte',
      only_check_unread: true,
      create_task_if_customer: false,
      delete_after_import: false,
      auto_mark_public: true,
      notify_on_import: true,
      notify_multiple_emails: false,
      notified_staff: ['Armando Turcotte']
    });

    const spamFilters = ref([
      // Demo spam items
      { id: 1, type: 'sender', value: 'spammer@domain.com' },
      { id: 2, type: 'subject', value: 'Viagra offers' },
      { id: 3, type: 'phrase', value: 'Work from home earn millions' }
    ]);

    const spamForm = reactive({
      type: 'sender',
      value: ''
    });

    const spamColumns = [
      { title: 'Content', dataIndex: 'value', key: 'value' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredSpam = computed(() => {
      // Filter by type matching current tab
      const typeFiltered = spamFilters.value.filter(s => s.type === activeTab.value);
      if (!search.value) return typeFiltered;
      return typeFiltered.filter(s => s.value.toLowerCase().includes(search.value.toLowerCase()));
    });

    const retrieveFolders = () => {
      message.loading({ content: 'Retrieving folders from server...', key: 'ret' });
      setTimeout(() => {
        message.success({ content: 'Folders retrieved: INBOX, Junk, Sent', key: 'ret', duration: 2 });
      }, 1000);
    };

    const saveIntegration = () => {
      message.success('Email lead integration settings saved successfully');
    };

    const openNewSpamDrawer = () => {
      resetSpamForm();
      spamForm.type = activeTab.value;
      openSpamDrawer.value = true;
    };

    const editSpam = (record) => {
      editingSpamId.value = record.id;
      spamForm.type = record.type;
      spamForm.value = record.value;
      openSpamDrawer.value = true;
    };

    const deleteSpam = (id) => {
      spamFilters.value = spamFilters.value.filter(s => s.id !== id);
      message.success('Spam filter removed');
    };

    const saveSpam = () => {
      if (!spamForm.value.trim()) return;
      if (editingSpamId.value) {
        const item = spamFilters.value.find(x => x.id === editingSpamId.value);
        if (item) {
          item.type = spamForm.type;
          item.value = spamForm.value.trim();
        }
        message.success('Spam filter updated');
      } else {
        spamFilters.value.push({
          id: Date.now(),
          type: spamForm.type,
          value: spamForm.value.trim()
        });
        message.success('Spam filter added');
      }
      openSpamDrawer.value = false;
      resetSpamForm();
    };

    const resetSpamForm = () => {
      editingSpamId.value = null;
      spamForm.type = 'sender';
      spamForm.value = '';
    };

    return {
      viewState,
      activeTab,
      search,
      pageSize,
      openSpamDrawer,
      editingSpamId,
      leadStatuses,
      leadSources,
      staffMembers,
      integrationForm,
      spamForm,
      spamColumns,
      filteredSpam,
      retrieveFolders,
      saveIntegration,
      openNewSpamDrawer,
      editSpam,
      deleteSpam,
      saveSpam,
      resetSpamForm
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
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
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
.btn-secondary {
  display: inline-flex;
  align-items: center;
  padding: 10px 20px;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.12s;
  white-space: nowrap;
}
.btn-secondary:hover {
  background: #e2e8f0;
  color: #1e293b;
}
.settings-card {
  background: #fff;
}
.settings-hint-block {
  font-size: 13.5px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 20px;
}
.link-blue {
  color: #4f46e5;
  font-weight: 600;
  text-decoration: none;
}
.link-blue:hover {
  text-decoration: underline;
}
.settings-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 20px;
}
.checkbox-settings-group,
.notification-settings-group {
  margin-top: 20px;
  padding: 15px 0;
  border-top: 1px solid #f1f5f9;
}
.group-subtitle {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 12px;
}
.settings-row {
  display: flex;
  align-items: center;
  padding: 6px 0;
}
.settings-actions {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
}
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.spam-tabs {
  margin-bottom: 16px;
}
.spam-content {
  font-weight: 600;
  color: #1e293b;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
