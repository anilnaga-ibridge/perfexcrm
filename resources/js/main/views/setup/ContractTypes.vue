<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Contract Types</h2>
      <button class="btn-primary" @click="openNewTypeDrawer">New Contract Type</button>
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
          :dataSource="filteredTypes"
          :columns="typeColumns"
          :pagination="{ pageSize: pageSize, total: filteredTypes.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'name'">
              <div class="type-name-cell">
                <span class="type-name">{{ record.name }}</span>
                <span class="type-count-badge">{{ record.count }}</span>
              </div>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editType(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteType(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Contract Type' : 'New Contract Type'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveType">
        <a-form-item label="* Name" name="name" :rules="[{ required: true, message: 'Name is required' }]">
          <a-input v-model:value="form.name" placeholder="Enter contract type name" />
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Contract Type' : 'Create Contract Type' }}
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
  name: 'ContractTypesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const contractTypes = ref([
      { id: 1, name: 'Adhesion Contracts', count: 0 },
      { id: 2, name: 'Aleatory Contracts', count: 1 },
      { id: 3, name: 'Bilateral and Unilateral Contracts', count: 1 },
      { id: 4, name: 'Contracts under Seal', count: 1 },
      { id: 5, name: 'Executed and Executory Contracts', count: 2 },
      { id: 6, name: 'Express Contracts', count: 1 },
      { id: 7, name: 'Implied Contracts', count: 1 },
      { id: 8, name: 'Unconscionable Contracts', count: 0 },
      { id: 9, name: 'Void and Voidable Contracts', count: 1 }
    ]);

    const form = reactive({
      name: ''
    });

    const typeColumns = [
      { title: 'Name', key: 'name', dataIndex: 'name' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredTypes = computed(() => {
      // Sort alphabetically by Name
      const sorted = [...contractTypes.value].sort((a, b) => a.name.localeCompare(b.name));
      if (!search.value) return sorted;
      return sorted.filter(t => t.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const openNewTypeDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editType = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deleteType = (id) => {
      contractTypes.value = contractTypes.value.filter(t => t.id !== id);
      message.success('Contract Type deleted');
    };

    const saveType = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = contractTypes.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
          }
          message.success('Contract Type updated');
        } else {
          const maxId = contractTypes.value.reduce((max, t) => t.id > max ? t.id : max, 0);
          contractTypes.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            count: 0
          });
          message.success('Contract Type added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving Contract Type');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      contractTypes,
      form,
      typeColumns,
      filteredTypes,
      openNewTypeDrawer,
      editType,
      deleteType,
      saveType,
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
.type-name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}
.type-name {
  font-weight: 600;
  color: #1e293b;
}
.type-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 9999px;
  height: 20px;
  min-width: 20px;
  padding: 0 6px;
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
