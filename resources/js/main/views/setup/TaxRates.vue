<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Tax Rates</h2>
      <button class="btn-primary" @click="openNewTaxDrawer">New Tax</button>
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
          :dataSource="filteredTaxRates"
          :columns="taxColumns"
          :pagination="{ pageSize: pageSize, total: filteredTaxRates.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="tax-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <span class="tax-name">{{ record.name }}</span>
            </template>
            <template v-if="column.key === 'rate'">
              <span>{{ record.rate.toFixed(2) }}</span>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editTax(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteTax(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Tax' : 'Add New Tax'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveTax">
        <a-form-item label="* Tax Name" name="name" :rules="[{ required: true, message: 'Tax name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter tax name" />
        </a-form-item>
        <a-form-item label="Tax Rate (percent)" name="rate" :rules="[{ required: true, message: 'Tax rate required' }]">
          <a-input-number v-model:value="form.rate" :min="0" :max="100" :precision="2" style="width: 100%" placeholder="e.g. 5.00" />
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Tax' : 'Add Tax' }}
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
  name: 'TaxRatesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const taxRates = ref([
      { id: 3, name: 'TAX3', rate: 5.00 },
      { id: 2, name: 'TAX2', rate: 10.00 },
      { id: 1, name: 'TAX1', rate: 18.00 }
    ]);

    const form = reactive({
      name: '',
      rate: null
    });

    const taxColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Tax Name', dataIndex: 'name', key: 'name' },
      { title: 'Rate (percent)', dataIndex: 'rate', key: 'rate', width: 180 },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredTaxRates = computed(() => {
      // Sort descending by ID
      const sorted = [...taxRates.value].sort((a, b) => b.id - a.id);
      if (!search.value) return sorted;
      return sorted.filter(t => 
        t.name.toLowerCase().includes(search.value.toLowerCase()) ||
        t.rate.toString().includes(search.value)
      );
    });

    const openNewTaxDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editTax = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.rate = record.rate;
      openDrawer.value = true;
    };

    const deleteTax = (id) => {
      taxRates.value = taxRates.value.filter(t => t.id !== id);
      message.success('Tax rate deleted');
    };

    const saveTax = () => {
      if (!form.name.trim() || form.rate === null) return;
      saving.value = true;
      
      try {
        if (editingId.value) {
          const item = taxRates.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.rate = parseFloat(form.rate);
          }
          message.success('Tax rate updated');
        } else {
          const maxId = taxRates.value.reduce((max, t) => t.id > max ? t.id : max, 0);
          taxRates.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            rate: parseFloat(form.rate)
          });
          message.success('Tax rate added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('An error occurred while saving');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.rate = null;
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      taxRates,
      form,
      taxColumns,
      filteredTaxRates,
      openNewTaxDrawer,
      editTax,
      deleteTax,
      saveTax,
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
.tax-id {
  color: #64748b;
  font-family: monospace;
}
.tax-name {
  font-weight: 600;
  color: #1e293b;
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
