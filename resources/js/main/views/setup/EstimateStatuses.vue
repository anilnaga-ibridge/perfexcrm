<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Estimate Request Statuses</h2>
      <button class="btn-primary" @click="openNewStatusDrawer">New Status</button>
    </div>

    <!-- MAIN CARD / DATA TABLE -->
    <div class="settings-card">
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
          :dataSource="filteredStatuses"
          :columns="statusColumns"
          :pagination="{ pageSize: pageSize, total: filteredStatuses.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="status-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <div class="status-name-cell">
                <div class="status-name-row">
                  <span class="status-dot" :style="{ background: record.color }"></span>
                  <span class="status-name">{{ record.name }}</span>
                </div>
                <div class="status-requests-count">
                  Total Request: {{ record.requests_count }}
                </div>
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
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Status' : 'New Status'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveStatus">
        <a-form-item label="* Status Name" name="name" :rules="[{ required: true, message: 'Status name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter status name" />
        </a-form-item>

        <a-form-item label="Color" name="color">
          <div style="display: flex; gap: 8px; align-items: center;">
            <a-input v-model:value="form.color" placeholder="#6366f1" style="flex: 1" />
            <input type="color" v-model="form.color" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 0; width: 32px; height: 32px; cursor: pointer;" />
          </div>
        </a-form-item>

        <a-form-item label="Status Order" name="order">
          <a-input-number v-model:value="form.order" :min="1" style="width: 100%" placeholder="4" />
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
  name: 'EstimateStatusesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const estimateStatuses = ref([
      { id: 1, name: 'Cancelled', color: '#94a3b8', requests_count: 0, order: 1 },
      { id: 3, name: 'Completed', color: '#10b981', requests_count: 0, order: 3 },
      { id: 2, name: 'Processing', color: '#6366f1', requests_count: 0, order: 2 }
    ]);

    const form = reactive({
      name: '',
      color: '#6366f1',
      order: 4
    });

    const statusColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Status Name', key: 'name' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredStatuses = computed(() => {
      // Sort alphabetically by Name
      const sorted = [...estimateStatuses.value].sort((a, b) => a.name.localeCompare(b.name));
      if (!search.value) return sorted;
      return sorted.filter(s => s.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const openNewStatusDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editStatus = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.color = record.color || '#6366f1';
      form.order = record.order !== undefined ? record.order : 4;
      openDrawer.value = true;
    };

    const deleteStatus = (id) => {
      estimateStatuses.value = estimateStatuses.value.filter(s => s.id !== id);
      message.success('Status deleted');
    };

    const saveStatus = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = estimateStatuses.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.color = form.color;
            item.order = form.order;
          }
          message.success('Status updated');
        } else {
          const maxId = estimateStatuses.value.reduce((max, s) => s.id > max ? s.id : max, 0);
          estimateStatuses.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            color: form.color,
            order: form.order,
            requests_count: 0
          });
          message.success('Status added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving status');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.color = '#6366f1';
      form.order = 4;
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      estimateStatuses,
      form,
      statusColumns,
      filteredStatuses,
      openNewStatusDrawer,
      editStatus,
      deleteStatus,
      saveStatus,
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
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.status-id {
  color: #64748b;
  font-family: monospace;
}
.status-name-cell {
  display: flex;
  flex-direction: column;
}
.status-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.status-name {
  font-weight: 600;
  color: #1e293b;
}
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}
.status-requests-count {
  font-size: 11.5px;
  color: #64748b;
  margin-top: 2px;
  margin-left: 16px;
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
