<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Ticket Statuses</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> New Ticket Status
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search statuses..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredStatuses"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredStatuses.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'id'">
            <span class="id-cell">{{ record.id }}</span>
          </template>
          <template v-if="column.key === 'name'">
            <div class="status-name-cell">
              <span class="status-dot" :style="{ background: record.color }"></span>
              <span class="status-label">{{ record.name }}</span>
              <span class="status-count">Total {{ record.count }}</span>
            </div>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editStatus(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteStatus(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Ticket Status' : 'New Ticket Status'"
      placement="right"
      :width="440"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveStatus">
        <a-form-item label="* Ticket Status Name" name="name" :rules="[{ required: true, message: 'Status name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter status name" />
        </a-form-item>

        <a-form-item label="Pick Color">
          <div class="color-picker-row">
            <div
              v-for="c in colorOptions"
              :key="c"
              :class="['color-swatch', { 'color-swatch--active': form.color === c }]"
              :style="{ background: c }"
              @click="form.color = c"
            ></div>
          </div>
          <div class="color-input-row">
            <input type="color" v-model="form.color" class="color-input" />
            <a-input v-model:value="form.color" placeholder="#3b82f6" style="width: 120px" size="small" />
          </div>
        </a-form-item>

        <a-form-item label="Status Order">
          <a-input-number v-model:value="form.sort_order" :min="0" style="width: 100%" placeholder="0" />
          <span class="field-hint">Lower numbers appear first in the list</span>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Status' : 'Add Status' }}
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
  name: 'TicketStatusesView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const statuses = ref([
      { id: 1, name: 'Open',        color: '#3b82f6', count: 2, sort_order: 1 },
      { id: 2, name: 'In Progress', color: '#eab308', count: 1, sort_order: 2 },
      { id: 3, name: 'Answered',    color: '#10b981', count: 2, sort_order: 3 },
      { id: 4, name: 'On Hold',     color: '#64748b', count: 1, sort_order: 4 },
      { id: 5, name: 'Closed',      color: '#1e293b', count: 1, sort_order: 5 },
    ]);

    const form = reactive({ name: '', color: '#3b82f6', sort_order: 0 });

    const colorOptions = [
      '#3b82f6', '#10b981', '#eab308', '#f97316', '#ef4444',
      '#ec4899', '#8b5cf6', '#6366f1', '#06b6d4', '#64748b', '#1e293b',
    ];

    const columns = [
      { title: 'ID',                    key: 'id',     dataIndex: 'id',     width: 70 },
      { title: 'Ticket Status Name',    key: 'name',   dataIndex: 'name' },
      { title: 'Options',               key: 'options', width: 150 },
    ];

    const filteredStatuses = computed(() => {
      if (!search.value) return statuses.value;
      return statuses.value.filter(s => s.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editStatus = (record) => {
      editingId.value = record.id;
      Object.assign(form, { name: record.name, color: record.color, sort_order: record.sort_order });
      openDrawer.value = true;
    };

    const deleteStatus = (id) => {
      statuses.value = statuses.value.filter(s => s.id !== id);
      message.success('Status deleted');
    };

    const saveStatus = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const s = statuses.value.find(x => x.id === editingId.value);
        if (s) Object.assign(s, { name: form.name.trim(), color: form.color, sort_order: form.sort_order });
        message.success('Status updated');
      } else {
        statuses.value.push({ id: Date.now(), name: form.name.trim(), color: form.color, count: 0, sort_order: form.sort_order });
        message.success('Status added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      Object.assign(form, { name: '', color: '#3b82f6', sort_order: 0 });
    };

    return {
      search, openDrawer, saving, editingId,
      statuses, form, colorOptions, columns, filteredStatuses,
      editStatus, deleteStatus, saveStatus, resetForm,
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
.status-name-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.status-label { font-weight: 600; color: #1e293b; }
.status-count { font-size: 12px; color: #94a3b8; }
.color-picker-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.color-swatch {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  border: 3px solid transparent;
  transition: all 0.15s;
}
.color-swatch:hover { transform: scale(1.1); }
.color-swatch--active { border-color: #1e293b; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #6366f1; }
.color-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.color-input {
  width: 40px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  padding: 0;
}
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
