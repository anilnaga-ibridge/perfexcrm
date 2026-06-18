<template>
  <div class="maillists-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mail Lists</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage email lists for survey distribution</p>
      </div>
      <div class="header-actions">
        <a-button @click="$router.push('/admin/utilities/surveys')">Back to Surveys</a-button>
        <a-button type="primary" @click="openCreateDrawer">New Mail List</a-button>
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
        :dataSource="filteredLists"
        :columns="columns"
        :loading="loading"
        :pagination="{ pageSize: 10, showSizeChanger: true }"
        rowKey="id"
        size="middle"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'creator'">
            {{ record.creator_name || record.creator || '—' }}
          </template>
          <template v-else-if="column.key === 'options'">
            <a-button type="link" size="small" @click="openEditDrawer(record)">Edit</a-button>
            <a-popconfirm title="Delete this mail list?" @confirm="deleteList(record.id)">
              <a-button type="link" size="small" danger>Delete</a-button>
            </a-popconfirm>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <p>No mail lists found</p>
          </div>
        </template>
      </a-table>
    </div>

    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Mail List' : 'Add New Mail List'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="form" @finish="saveList">
        <a-form-item label="Mail List Name" name="name" :rules="[{ required: true, message: 'Required' }]">
          <a-input v-model:value="form.name" placeholder="Mail list name" />
        </a-form-item>

        <a-form-item label="Custom Fields">
          <div v-for="(field, idx) in form.custom_fields" :key="idx" class="custom-field-row">
            <a-input v-model:value="field.name" placeholder="Field name" style="flex:1" />
            <a-button type="text" danger @click="removeField(idx)" size="small">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a-button>
          </div>
          <a-button type="dashed" block @click="addField" style="margin-top:8px">+ Add custom field</a-button>
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
  name: 'MailListsPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const lists = ref([]);
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);
    const search = ref('');

    const columns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 60 },
      { title: 'List Name', dataIndex: 'name', key: 'name' },
      { title: 'Date Created', dataIndex: 'date_created', key: 'date_created', width: 140 },
      { title: 'Creator', key: 'creator', width: 140 },
      { title: 'Options', key: 'options', width: 140 },
    ];

    const form = reactive({
      name: '',
      custom_fields: [],
    });

    const filteredLists = computed(() => {
      if (!search.value) return lists.value;
      const q = search.value.toLowerCase();
      return lists.value.filter(l => (l.name || '').toLowerCase().includes(q));
    });

    const loadLists = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/mail-lists', { params: { per_page: 200 } });
        lists.value = res.data.lists?.data || [];
      } catch {
        message.error('Failed to load mail lists');
      } finally {
        loading.value = false;
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      form.name = '';
      form.custom_fields = [];
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      form.name = record.name || '';
      form.custom_fields = record.custom_fields || [];
      showDrawer.value = true;
    };

    const addField = () => {
      form.custom_fields.push({ name: '' });
    };

    const removeField = (idx) => {
      form.custom_fields.splice(idx, 1);
    };

    const saveList = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/mail-lists/${editingId.value}`, form);
          message.success('Mail list updated');
        } else {
          await axios.post('/api/mail-lists', form);
          message.success('Mail list created');
        }
        showDrawer.value = false;
        loadLists();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) message.error(Object.values(errors).flat().join(', '));
        else message.error('Failed to save mail list');
      } finally {
        saving.value = false;
      }
    };

    const deleteList = async (id) => {
      try {
        await axios.delete(`/api/mail-lists/${id}`);
        message.success('Mail list deleted');
        loadLists();
      } catch {
        message.error('Failed to delete mail list');
      }
    };

    onMounted(() => loadLists());

    return {
      loading, saving, lists, showDrawer, isEdit, form, search, columns,
      filteredLists, openCreateDrawer, openEditDrawer, addField, removeField,
      saveList, deleteList,
    };
  }
});
</script>

<style scoped>
.maillists-page { font-family: 'Inter', -apple-system, sans-serif; }
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
.custom-field-row {
  display: flex; gap: 8px; align-items: center; margin-bottom: 6px;
}
.empty-state { text-align: center; padding: 40px; color: #94a3b8; }
:deep(.ant-table-cell) { font-size: 13px; }
</style>
