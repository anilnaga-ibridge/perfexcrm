<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Ticket Priorities</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> Add Priority
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search priorities..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredPriorities"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredPriorities.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'id'">
            <span class="id-cell">{{ record.id }}</span>
          </template>
          <template v-if="column.key === 'name'">
            <span class="priority-name">{{ record.name }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editPriority(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deletePriority(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Priority' : 'Add Priority'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="savePriority">
        <a-form-item label="* Priority Name" name="name" :rules="[{ required: true, message: 'Priority name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter priority name" />
        </a-form-item>
        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Priority' : 'Add Priority' }}
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
  name: 'TicketPriorityView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const priorities = ref([
      { id: 1, name: 'Low' },
      { id: 2, name: 'Medium' },
      { id: 3, name: 'High' },
    ]);

    const form = reactive({ name: '' });

    const columns = [
      { title: 'ID',                    key: 'id',     dataIndex: 'id',     width: 70 },
      { title: 'Ticket Priority Name',  key: 'name',   dataIndex: 'name' },
      { title: 'Options',               key: 'options', width: 150 },
    ];

    const filteredPriorities = computed(() => {
      if (!search.value) return priorities.value;
      return priorities.value.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editPriority = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deletePriority = (id) => {
      priorities.value = priorities.value.filter(p => p.id !== id);
      message.success('Priority deleted');
    };

    const savePriority = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const p = priorities.value.find(x => x.id === editingId.value);
        if (p) p.name = form.name.trim();
        message.success('Priority updated');
      } else {
        priorities.value.push({ id: Date.now(), name: form.name.trim() });
        message.success('Priority added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
    };

    return {
      search, openDrawer, saving, editingId,
      priorities, form, columns, filteredPriorities,
      editPriority, deletePriority, savePriority, resetForm,
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
.id-cell { color: #94a3b8; font-size: 13px; }
.priority-name { font-weight: 600; color: #1e293b; }
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
