<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Services</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> New Service
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search services..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredServices"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredServices.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'id'">
            <span class="id-cell">{{ record.id }}</span>
          </template>
          <template v-if="column.key === 'name'">
            <span class="service-name">{{ record.name }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editService(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteService(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Service' : 'New Service'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveService">
        <a-form-item label="* Service Name" name="name" :rules="[{ required: true, message: 'Service name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter service name" />
        </a-form-item>
        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Service' : 'Add Service' }}
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
  name: 'ServicesView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const services = ref([]);

    const form = reactive({ name: '' });

    const columns = [
      { title: 'ID',            key: 'id',     dataIndex: 'id',     width: 70 },
      { title: 'Service Name',  key: 'name',   dataIndex: 'name' },
      { title: 'Options',       key: 'options', width: 150 },
    ];

    const filteredServices = computed(() => {
      if (!search.value) return services.value;
      return services.value.filter(s => s.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editService = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deleteService = (id) => {
      services.value = services.value.filter(s => s.id !== id);
      message.success('Service deleted');
    };

    const saveService = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const s = services.value.find(x => x.id === editingId.value);
        if (s) s.name = form.name.trim();
        message.success('Service updated');
      } else {
        services.value.push({ id: Date.now(), name: form.name.trim() });
        message.success('Service added');
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
      services, form, columns, filteredServices,
      editService, deleteService, saveService, resetForm,
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
.service-name { font-weight: 600; color: #1e293b; }
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
