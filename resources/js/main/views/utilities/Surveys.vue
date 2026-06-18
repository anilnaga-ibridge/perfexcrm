<template>
  <div class="surveys-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Surveys</h1>
        <p class="text-xs text-slate-500 mt-0.5">Create and manage surveys</p>
      </div>
      <div class="header-actions">
        <a-button @click="$router.push('/admin/utilities/mail-lists')">Mail List</a-button>
        <a-button type="primary" @click="openCreateDrawer">Add New Survey</a-button>
      </div>
    </div>

    <div class="table-card">
      <div class="table-toolbar">
        <a-input v-model:value="search" placeholder="Search..." style="width:260px" allow-clear>
          <template #prefix>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </template>
        </a-input>
      </div>

      <a-table
        :dataSource="filteredSurveys"
        :columns="columns"
        :loading="loading"
        :pagination="{ pageSize: 10, showSizeChanger: true }"
        rowKey="id"
        size="middle"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'total_questions'">
            {{ record.total_questions || 0 }}
          </template>
          <template v-else-if="column.key === 'total_participants'">
            {{ record.total_participants || 0 }}
          </template>
          <template v-else-if="column.key === 'active'">
            <a-switch :checked="record.active === 1 || record.active === true" disabled size="small" />
          </template>
          <template v-else-if="column.key === 'actions'">
            <a-button type="link" size="small" @click="viewSurvey(record)">View Survey</a-button>
            <a-button type="link" size="small" @click="openEditDrawer(record)">Edit</a-button>
            <a-popconfirm title="Delete this survey?" @confirm="deleteSurvey(record.id)">
              <a-button type="link" size="small" danger>Delete</a-button>
            </a-popconfirm>
          </template>
        </template>
      </a-table>
    </div>

    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Survey' : 'Add New Survey'"
      placement="right"
      :width="500"
    >
      <a-form layout="vertical" :model="form" @finish="saveSurvey">
        <a-form-item label="Survey subject" name="subject" :rules="[{ required: true, message: 'Required' }]">
          <a-input v-model:value="form.subject" placeholder="Survey subject" />
        </a-form-item>

        <a-form-item label="Survey short description (View Description)">
          <a-textarea v-model:value="form.view_description" :rows="2" placeholder="Short description shown to participants" />
        </a-form-item>

        <a-form-item label="Survey description (Email Description)">
          <a-textarea v-model:value="form.email_description" :rows="3" placeholder="Description included in invitation email" />
        </a-form-item>

        <a-form-item>
          <a-checkbox v-model:checked="form.include_link">Include survey link in description: {survey_link}</a-checkbox>
        </a-form-item>

        <a-form-item label="From (displayed in email)">
          <a-input v-model:value="form.from_email" placeholder="e.g. noreply@example.com" />
        </a-form-item>

        <a-form-item label="Survey redirect URL">
          <a-input v-model:value="form.redirect_url" placeholder="https://..." />
        </a-form-item>

        <a-form-item>
          <a-checkbox v-model:checked="form.disabled">Disabled</a-checkbox>
        </a-form-item>

        <a-form-item>
          <a-checkbox v-model:checked="form.logged_in_only">Only for logged in participants (staff, customers)</a-checkbox>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Save</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SurveysPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const surveys = ref([]);
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);
    const search = ref('');

    const columns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 60 },
      { title: 'Name', dataIndex: 'subject', key: 'subject' },
      { title: 'Total Questions', key: 'total_questions', width: 130 },
      { title: 'Total Participants', key: 'total_participants', width: 150 },
      { title: 'Date Created', dataIndex: 'date_created', key: 'date_created', width: 140 },
      { title: 'Active', key: 'active', width: 80 },
      { title: 'Actions', key: 'actions', width: 240 },
    ];

    const form = reactive({
      subject: '',
      view_description: '',
      email_description: '',
      include_link: true,
      from_email: '',
      redirect_url: '',
      disabled: false,
      logged_in_only: false,
    });

    const filteredSurveys = computed(() => {
      if (!search.value) return surveys.value;
      const q = search.value.toLowerCase();
      return surveys.value.filter(s => (s.subject || '').toLowerCase().includes(q));
    });

    const loadSurveys = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/surveys', { params: { per_page: 200 } });
        surveys.value = res.data.surveys?.data || [];
      } catch {
        message.error('Failed to load surveys');
      } finally {
        loading.value = false;
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(form, {
        subject: '',
        view_description: '',
        email_description: '',
        include_link: true,
        from_email: '',
        redirect_url: '',
        disabled: false,
        logged_in_only: false,
      });
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(form, {
        subject: record.subject || '',
        view_description: record.view_description || '',
        email_description: record.email_description || '',
        include_link: record.include_link !== undefined ? record.include_link : true,
        from_email: record.from_email || '',
        redirect_url: record.redirect_url || '',
        disabled: record.disabled === 1 || record.disabled === true,
        logged_in_only: record.logged_in_only === 1 || record.logged_in_only === true,
      });
      showDrawer.value = true;
    };

    const viewSurvey = (record) => {
      message.info('View survey: ' + record.subject);
    };

    const saveSurvey = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/surveys/${editingId.value}`, form);
          message.success('Survey updated');
        } else {
          await axios.post('/api/surveys', form);
          message.success('Survey created');
        }
        showDrawer.value = false;
        loadSurveys();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) message.error(Object.values(errors).flat().join(', '));
        else message.error('Failed to save survey');
      } finally {
        saving.value = false;
      }
    };

    const deleteSurvey = async (id) => {
      try {
        await axios.delete(`/api/surveys/${id}`);
        message.success('Survey deleted');
        loadSurveys();
      } catch {
        message.error('Failed to delete survey');
      }
    };

    onMounted(() => loadSurveys());

    return {
      loading, saving, surveys, showDrawer, isEdit, form, search, columns,
      filteredSurveys, openCreateDrawer, openEditDrawer, viewSurvey, saveSurvey, deleteSurvey,
    };
  }
});
</script>

<style scoped>
.surveys-page { font-family: 'Inter', -apple-system, sans-serif; }
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.page-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; }
.header-actions { display: flex; gap: 8px; }
.table-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;
}
.table-toolbar { margin-bottom: 16px; }
.drawer-footer {
  display: flex; justify-content: flex-end; gap: 8px;
  margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;
}
:deep(.ant-table-cell) { font-size: 13px; }
</style>
