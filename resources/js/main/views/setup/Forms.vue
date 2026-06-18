<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Estimate Request Forms</h2>
      <button class="btn-primary" @click="openNewFormDrawer">New Form</button>
    </div>

    <!-- MAIN CARD / DATA TABLE -->
    <div class="settings-card">
      <div class="settings-hint-block">
        <a href="#" @click.prevent class="link-blue">Click here to read more about web to lead forms</a>
      </div>

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
          :dataSource="filteredForms"
          :columns="formColumns"
          :pagination="{ pageSize: pageSize, total: filteredForms.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="form-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <div class="form-name-cell">
                <span class="form-name">{{ record.name }}</span>
                <div class="row-actions-inline">
                  <a href="#" @click.prevent="editForm(record)" class="action-link">Edit</a> |
                  <a href="#" @click.prevent="deleteForm(record.id)" class="action-link text-danger">Delete</a>
                </div>
              </div>
            </template>
            <template v-if="column.key === 'totalSubmissions'">
              <span>{{ record.totalSubmissions }}</span>
            </template>
            <template v-if="column.key === 'created_at'">
              <span class="text-muted">{{ record.created_at }}</span>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      title="Create form first to be able to use the form builder."
      placement="right"
      :width="560"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveForm">
        <a-tabs v-model:activeKey="activeTab" class="drawer-tabs">
          <!-- GENERAL TAB -->
          <a-tab-pane key="general" tab="General">
            <a-form-item label="* Form Name" name="name" :rules="[{ required: true, message: 'Form name required' }]">
              <a-input v-model:value="form.name" placeholder="Enter form name" />
            </a-form-item>

            <a-form-item label="Use Google Recaptcha" name="recaptcha">
              <a-radio-group v-model:value="form.recaptcha">
                <a-radio-button value="no">No</a-radio-button>
                <a-radio-button value="yes">Yes</a-radio-button>
              </a-radio-group>
            </a-form-item>

            <a-form-item label="* Language" name="language" :rules="[{ required: true, message: 'Language required' }]">
              <a-select v-model:value="form.language">
                <a-select-option value="English">English</a-select-option>
                <a-select-option value="Spanish">Spanish</a-select-option>
                <a-select-option value="French">French</a-select-option>
                <a-select-option value="German">German</a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="* Status" name="status" :rules="[{ required: true, message: 'Status required' }]">
              <a-select v-model:value="form.status">
                <a-select-option v-for="s in formStatuses" :key="s" :value="s">{{ s }}</a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="Responsible (Assignee)" name="responsible">
              <a-select v-model:value="form.responsible" placeholder="Select staff member">
                <a-select-option v-for="staff in staffMembers" :key="staff" :value="staff">{{ staff }}</a-select-option>
              </a-select>
            </a-form-item>
          </a-tab-pane>

          <!-- BRANDING TAB -->
          <a-tab-pane key="branding" tab="Branding">
            <a-form-item label="Submit Button Text">
              <a-input v-model:value="form.submit_btn_text" placeholder="Submit" />
            </a-form-item>
            <a-form-item label="Submit Button Background Color">
              <a-input v-model:value="form.submit_btn_bg" placeholder="#4f46e5" />
            </a-form-item>
            <a-form-item label="Submit Button Text Color">
              <a-input v-model:value="form.submit_btn_color" placeholder="#ffffff" />
            </a-form-item>
          </a-tab-pane>

          <!-- SUBMISSION TAB -->
          <a-tab-pane key="submission" tab="Submission">
            <a-form-item label="What should happen after a visitor submits this form?" name="submission_action">
              <a-radio-group v-model:value="form.submission_action" style="display: flex; flex-direction: column; gap: 8px;">
                <a-radio value="message">Display thank you message</a-radio>
                <a-radio value="redirect">Redirect to another website</a-radio>
              </a-radio-group>
            </a-form-item>

            <a-form-item
              v-if="form.submission_action === 'message'"
              label="* Message to show after form is succcesfully submitted"
              name="success_message"
              :rules="[{ required: true, message: 'Message is required' }]"
            >
              <a-textarea v-model:value="form.success_message" :rows="4" placeholder="Enter thank you message" />
            </a-form-item>

            <a-form-item
              v-else-if="form.submission_action === 'redirect'"
              label="* Redirect URL"
              name="redirect_url"
              :rules="[{ required: true, message: 'Redirect URL is required' }]"
            >
              <a-input v-model:value="form.redirect_url" placeholder="https://example.com" />
            </a-form-item>
          </a-tab-pane>

          <!-- NOTIFICATIONS TAB -->
          <a-tab-pane key="notifications" tab="Notifications">
            <div style="font-weight: 600; font-size: 14px; margin-bottom: 16px; color: #1e293b;">
              Notification settings
            </div>

            <a-form-item name="notify_on_import" style="margin-bottom: 16px;">
              <a-checkbox v-model:checked="form.notify_on_import">
                Notify when request imported
              </a-checkbox>
            </a-form-item>

            <div v-if="form.notify_on_import" style="padding-left: 24px; display: flex; flex-direction: column; gap: 16px;">
              <a-form-item name="notify_type" style="margin-bottom: 8px;">
                <a-radio-group v-model:value="form.notify_type" style="display: flex; flex-direction: column; gap: 8px;">
                  <a-radio value="specific">Specific Staff Members</a-radio>
                  <a-radio value="roles">Staff members with roles</a-radio>
                  <a-radio value="responsible">Responsible person</a-radio>
                </a-radio-group>
              </a-form-item>

              <div v-if="form.notify_type === 'specific'" style="margin-top: 8px;">
                <div style="font-weight: 500; font-size: 13px; margin-bottom: 8px; color: #475569;">
                  Staff Members to Notify
                </div>
                <div class="staff-checkbox-grid">
                  <a-checkbox-group v-model:value="form.notified_staff" style="width: 100%;">
                    <a-row :gutter="[16, 8]">
                      <a-col v-for="staff in staffMembers" :key="staff" :span="12">
                        <a-checkbox :value="staff">{{ staff }}</a-checkbox>
                      </a-col>
                    </a-row>
                  </a-checkbox-group>
                </div>
              </div>
            </div>
          </a-tab-pane>
        </a-tabs>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit">
            {{ editingId ? 'Update Form' : 'Create Form' }}
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
  name: 'FormsView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const activeTab = ref('general');
    const editingId = ref(null);

    const formStatuses = ref(['Processing', 'Pending', 'Sent', 'Completed', 'Declined']);
    const staffMembers = ref([
      'Verner Wintheiser',
      'Rosario Hoeger',
      'Nicklaus Rogahn',
      'Monroe Sanford',
      'Joel Wisoky',
      'Ed Koepp',
      'Dudley Daugherty',
      'Cristian Wuckert',
      'Clemens Volkman',
      'Chaim Stark'
    ]);

    const formsList = ref([]);

    const form = reactive({
      name: '',
      recaptcha: 'no',
      language: 'English',
      status: 'Processing',
      responsible: undefined,
      submit_btn_text: 'Submit',
      submit_btn_bg: '#4f46e5',
      submit_btn_color: '#ffffff',
      submission_action: 'message',
      success_message: 'Form submitted successfully!',
      redirect_url: '',
      notify_on_import: false,
      notify_type: 'specific',
      notified_staff: []
    });

    const formColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Form Name', dataIndex: 'name', key: 'name' },
      { title: 'Total Submissions', dataIndex: 'totalSubmissions', key: 'totalSubmissions', width: 180 },
      { title: 'Created', dataIndex: 'created_at', key: 'created_at', width: 180 }
    ];

    const filteredForms = computed(() => {
      if (!search.value) return formsList.value;
      return formsList.value.filter(f => f.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const openNewFormDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editForm = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.recaptcha = record.recaptcha;
      form.language = record.language;
      form.status = record.status;
      form.responsible = record.responsible;
      form.submit_btn_text = record.submit_btn_text || 'Submit';
      form.submit_btn_bg = record.submit_btn_bg || '#4f46e5';
      form.submit_btn_color = record.submit_btn_color || '#ffffff';
      form.submission_action = record.submission_action || 'message';
      form.success_message = record.success_message || '';
      form.redirect_url = record.redirect_url || '';
      form.notify_on_import = record.notify_on_import || false;
      form.notify_type = record.notify_type || 'specific';
      form.notified_staff = record.notified_staff || [];
      openDrawer.value = true;
    };

    const deleteForm = (id) => {
      formsList.value = formsList.value.filter(f => f.id !== id);
      message.success('Form deleted successfully');
    };

    const saveForm = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const item = formsList.value.find(x => x.id === editingId.value);
        if (item) {
          item.name = form.name.trim();
          item.recaptcha = form.recaptcha;
          item.language = form.language;
          item.status = form.status;
          item.responsible = form.responsible;
          item.submit_btn_text = form.submit_btn_text;
          item.submit_btn_bg = form.submit_btn_bg;
          item.submit_btn_color = form.submit_btn_color;
          item.submission_action = form.submission_action;
          item.success_message = form.success_message;
          item.redirect_url = form.redirect_url;
          item.notify_on_import = form.notify_on_import;
          item.notify_type = form.notify_type;
          item.notified_staff = [...form.notified_staff];
        }
        message.success('Form details updated');
      } else {
        formsList.value.push({
          id: Date.now(),
          name: form.name.trim(),
          totalSubmissions: 0,
          created_at: 'Just now',
          recaptcha: form.recaptcha,
          language: form.language,
          status: form.status,
          responsible: form.responsible,
          submit_btn_text: form.submit_btn_text,
          submit_btn_bg: form.submit_btn_bg,
          submit_btn_color: form.submit_btn_color,
          submission_action: form.submission_action,
          success_message: form.success_message,
          redirect_url: form.redirect_url,
          notify_on_import: form.notify_on_import,
          notify_type: form.notify_type,
          notified_staff: [...form.notified_staff]
        });
        message.success('Form created successfully');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      activeTab.value = 'general';
      Object.assign(form, {
        name: '',
        recaptcha: 'no',
        language: 'English',
        status: 'Processing',
        responsible: undefined,
        submit_btn_text: 'Submit',
        submit_btn_bg: '#4f46e5',
        submit_btn_color: '#ffffff',
        submission_action: 'message',
        success_message: 'Form submitted successfully!',
        redirect_url: '',
        notify_on_import: false,
        notify_type: 'specific',
        notified_staff: []
      });
    };

    return {
      search,
      pageSize,
      openDrawer,
      activeTab,
      editingId,
      formStatuses,
      staffMembers,
      formsList,
      form,
      formColumns,
      filteredForms,
      openNewFormDrawer,
      editForm,
      deleteForm,
      saveForm,
      resetForm
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
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.form-id {
  color: #64748b;
  font-family: monospace;
}
.form-name-cell {
  display: flex;
  flex-direction: column;
}
.form-name {
  font-weight: 600;
  color: #1e293b;
}
.row-actions-inline {
  font-size: 12px;
  margin-top: 4px;
  display: flex;
  gap: 6px;
  color: #cbd5e1;
}
.action-link {
  color: #4f46e5;
  text-decoration: none;
  font-weight: 500;
}
.action-link:hover {
  text-decoration: underline;
}
.action-link.text-danger {
  color: #ef4444;
}
.text-muted {
  color: #64748b;
}
.drawer-tabs {
  margin-bottom: 16px;
}
.staff-checkbox-grid {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 16px;
  max-height: 200px;
  overflow-y: auto;
  margin-top: 8px;
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
