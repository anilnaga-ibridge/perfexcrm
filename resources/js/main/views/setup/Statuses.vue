<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Lead Statuses</h2>
      <button class="btn-primary" @click="openNewStatusDrawer">New Lead Status</button>
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
        :dataSource="filteredStatuses"
        :columns="leadStatusColumns"
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
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="status-dot" :style="{ background: record.color }"></span>
                <span class="status-name" :style="{ color: record.color }">{{ record.name }}</span>
              </div>
              <div class="status-leads-count">
                Total Leads: {{ record.totalLeads }}
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

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Lead Status' : 'New Lead Status'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveStatus">
        <a-form-item label="* Status Name" name="name" :rules="[{ required: true, message: 'Status name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter status name" />
        </a-form-item>
        <a-form-item label="Color" name="color">
          <a-input v-model:value="form.color" placeholder="#6366f1" />
        </a-form-item>
        <a-form-item label="Order" name="order">
          <a-input-number v-model:value="form.order" :min="1" style="width: 100%" />
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
  name: 'StatusesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const leadStatuses = ref([
      { id: 3, name: 'Contacted',     color: '#0ea5e9', totalLeads: 9, order: 3 },
      { id: 1, name: 'Customer',      color: '#22c55e', totalLeads: 8, order: 1 },
      { id: 2, name: 'New',           color: '#6366f1', totalLeads: 10, order: 2 },
      { id: 6, name: 'Proposal Sent', color: '#8b5cf6', totalLeads: 8, order: 6 },
      { id: 4, name: 'Qualified',     color: '#10b981', totalLeads: 8, order: 4 },
      { id: 5, name: 'Working',       color: '#f59e0b', totalLeads: 7, order: 5 }
    ]);

    const form = reactive({
      name: '',
      color: '#6366f1',
      order: 1
    });

    const leadStatusColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Status Name', dataIndex: 'name', key: 'name' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredStatuses = computed(() => {
      // Sort leadStatuses alphabetically by name
      const sorted = [...leadStatuses.value].sort((a, b) => a.name.localeCompare(b.name));
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
      form.color = record.color;
      form.order = record.order || 1;
      openDrawer.value = true;
    };

    const deleteStatus = (id) => {
      leadStatuses.value = leadStatuses.value.filter(s => s.id !== id);
      message.success('Status deleted');
    };

    const saveStatus = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const item = leadStatuses.value.find(x => x.id === editingId.value);
        if (item) {
          item.name = form.name.trim();
          item.color = form.color.trim();
          item.order = form.order;
        }
        message.success('Status updated');
      } else {
        // Find maximum ID to increment
        const maxId = leadStatuses.value.reduce((max, s) => s.id > max ? s.id : max, 0);
        leadStatuses.value.push({
          id: maxId + 1,
          name: form.name.trim(),
          color: form.color.trim() || '#6366f1',
          totalLeads: 0,
          order: form.order
        });
        message.success('Status added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.color = '#6366f1';
      form.order = 1;
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      leadStatuses,
      form,
      leadStatusColumns,
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
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.status-name {
  font-weight: 600;
}
.status-leads-count {
  font-size: 11.5px;
  color: #64748b;
  margin-top: 2px;
  padding-left: 18px;
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
