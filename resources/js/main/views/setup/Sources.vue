<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Lead Sources</h2>
      <button class="btn-primary" @click="openNewSourceDrawer">New Source</button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredSources"
        :columns="leadSourceColumns"
        :pagination="{ pageSize: 10, total: filteredSources.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'id'">
            <span class="source-id">{{ record.id }}</span>
          </template>
          <template v-if="column.key === 'name'">
            <div class="source-name-cell">
              <span class="source-name">{{ record.name }}</span>
              <div class="source-leads-count">
                Total Leads: {{ record.totalLeads }}
              </div>
            </div>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editSource(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteSource(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Source' : 'New Source'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveSource">
        <a-form-item label="* Source Name" name="name" :rules="[{ required: true, message: 'Source name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter source name" />
        </a-form-item>
        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Source' : 'Add Source' }}
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
  name: 'SourcesView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const leadSources = ref([
      { id: 2, name: 'Facebook', totalLeads: 25 },
      { id: 1, name: 'Google', totalLeads: 25 }
    ]);

    const form = reactive({ name: '' });

    const leadSourceColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Source Name', dataIndex: 'name', key: 'name' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredSources = computed(() => {
      // Sort leadSources by ID in descending order
      const sorted = [...leadSources.value].sort((a, b) => b.id - a.id);
      if (!search.value) return sorted;
      return sorted.filter(s => s.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const openNewSourceDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editSource = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deleteSource = (id) => {
      leadSources.value = leadSources.value.filter(s => s.id !== id);
      message.success('Source deleted');
    };

    const saveSource = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const item = leadSources.value.find(x => x.id === editingId.value);
        if (item) item.name = form.name.trim();
        message.success('Source updated');
      } else {
        // Find maximum ID to increment
        const maxId = leadSources.value.reduce((max, s) => s.id > max ? s.id : max, 0);
        leadSources.value.push({
          id: maxId + 1,
          name: form.name.trim(),
          totalLeads: 0
        });
        message.success('Source added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
    };

    return {
      search,
      openDrawer,
      saving,
      editingId,
      leadSources,
      form,
      leadSourceColumns,
      filteredSources,
      openNewSourceDrawer,
      editSource,
      deleteSource,
      saveSource,
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
.data-table-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}
.source-id {
  color: #64748b;
  font-family: monospace;
}
.source-name-cell {
  display: flex;
  flex-direction: column;
}
.source-name {
  font-weight: 600;
  color: #1e293b;
}
.source-leads-count {
  font-size: 11.5px;
  color: #64748b;
  margin-top: 2px;
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
