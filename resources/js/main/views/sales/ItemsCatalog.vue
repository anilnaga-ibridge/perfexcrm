<template>
  <div class="items-catalog-page">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Items Catalog</h1>
      <button class="btn-primary" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Predefined Item
      </button>
    </div>

    <!-- Filters & Table Card -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadItems">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
          <button class="btn-outline" @click="exportCSV">Export CSV</button>
        </div>
        <div class="toolbar-right">
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search item name, description..."
            size="small"
            style="width:260px"
            @search="loadItems"
          />
        </div>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="items"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 800 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <a class="item-name" @click="openEditDrawer(record)">{{ record.name }}</a>
          </template>

          <template v-if="column.key === 'description'">
            <span class="item-desc">{{ record.description || '—' }}</span>
          </template>

          <template v-if="column.key === 'rate'">
            <span class="amount">{{ formatCurrency(record.rate) }}</span>
          </template>

          <template v-if="column.key === 'tax_rate'">
            <a-tag color="blue" v-if="record.tax_rate">{{ record.tax_rate }}%</a-tag>
            <span v-else>—</span>
          </template>

          <template v-if="column.key === 'unit'">
            <span>{{ record.unit || '—' }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-dropdown :trigger="['click']">
                <button class="action-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="openEditDrawer(record)">Edit Item</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteItem(record.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
              <line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            <p>No catalog items found</p>
          </div>
        </template>
      </a-table>
    </div>

    <!-- Record/Edit Predefined Item Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Predefined Item' : 'New Predefined Item'"
      placement="right"
      :width="400"
    >
      <a-form layout="vertical" :model="itemForm" @finish="saveItem">
        <a-form-item label="Item Name" name="name" :rules="[{required:true,message:'Required'}]">
          <a-input v-model:value="itemForm.name" placeholder="e.g. Consulting Fee" />
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea v-model:value="itemForm.description" :rows="3" placeholder="Long description..." />
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Rate ($)" name="rate" :rules="[{required:true,message:'Required'}]">
            <a-input-number v-model:value="itemForm.rate" :min="0" style="width:100%" placeholder="0.00" />
          </a-form-item>

          <a-form-item label="Tax Rate (%)" name="tax_rate">
            <a-select v-model:value="itemForm.tax_rate" style="width:100%" placeholder="Select Tax Rate">
              <a-select-option :value="0">0% (No Tax)</a-select-option>
              <a-select-option :value="5">5%</a-select-option>
              <a-select-option :value="10">10%</a-select-option>
              <a-select-option :value="12">12%</a-select-option>
              <a-select-option :value="18">18%</a-select-option>
              <a-select-option :value="20">20%</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <a-form-item label="Unit" name="unit">
          <a-input v-model:value="itemForm.unit" placeholder="e.g. qty, hr, box" />
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
import { defineComponent, ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ItemsCatalogPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const items = ref([]);
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);

    const filters = reactive({ search: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} items` });

    const itemForm = reactive({
      name: '',
      description: '',
      rate: null,
      tax_rate: 0,
      unit: '',
    });

    const columns = [
      { title: 'Item Name',   key: 'name',        width: 200 },
      { title: 'Description', key: 'description' },
      { title: 'Rate',        key: 'rate',        width: 120, align: 'right' },
      { title: 'Tax Rate',    key: 'tax_rate',    width: 120 },
      { title: 'Unit',        key: 'unit',        width: 100 },
      { title: '',            key: 'actions',     width: 60, align: 'center' },
    ];

    const loadItems = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/predefined-items', {
          params: {
            search: filters.search,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        items.value = res.data.items?.data || [];
        pagination.total = res.data.items?.total || 0;
      } catch (e) {
        message.error('Failed to load catalog items');
      } finally {
        loading.value = false;
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(itemForm, {
        name: '',
        description: '',
        rate: null,
        tax_rate: 0,
        unit: 'qty',
      });
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(itemForm, {
        name: record.name,
        description: record.description || '',
        rate: record.rate,
        tax_rate: record.tax_rate || 0,
        unit: record.unit || '',
      });
      showDrawer.value = true;
    };

    const saveItem = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/predefined-items/${editingId.value}`, itemForm);
          message.success('Catalog item updated successfully');
        } else {
          await axios.post('/api/predefined-items', itemForm);
          message.success('Catalog item created successfully');
        }
        showDrawer.value = false;
        loadItems();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save catalog item');
        }
      } finally {
        saving.value = false;
      }
    };

    const deleteItem = async (id) => {
      try {
        await axios.delete(`/api/predefined-items/${id}`);
        message.success('Catalog item deleted');
        loadItems();
      } catch {
        message.error('Failed to delete item');
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadItems();
    };

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const exportCSV = () => {
      if (!items.value.length) return message.warning('No items to export');
      const headers = ['Item Name', 'Description', 'Rate', 'Tax Rate', 'Unit'];
      const rows = items.value.map(i => [
        i.name || '',
        i.description || '',
        i.rate || 0,
        i.tax_rate || 0,
        i.unit || '',
      ]);
      const csvContent = "data:text/csv;charset=utf-8," 
        + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "items_catalog_export.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    onMounted(() => {
      loadItems();
    });

    return {
      loading, saving, items, filters, pagination, columns,
      itemForm, showDrawer, isEdit,
      openCreateDrawer, openEditDrawer, saveItem, deleteItem, handleTableChange,
      formatCurrency, exportCSV
    };
  }
});
</script>

<style scoped>
.items-catalog-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-primary:hover { background: #0b5ed7; }
.btn-primary svg { width: 14px; height: 14px; }

/* Table */
.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 10px;
}
.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-outline {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 4px 12px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-outline:hover { background: #f8fafc; }

.item-name {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
}
.item-desc {
  color: #64748b;
  font-size: 12px;
}
.amount {
  font-weight: 600;
  color: #1e293b;
}
.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 4px;
}
.action-btn:hover { background: #f1f5f9; color: #475569; }
.row-actions { display: flex; justify-content: center; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
</style>
