<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Payment Modes</h2>
      <button class="btn-primary" @click="openNewPaymentModeDrawer">Add New Payment Mode</button>
    </div>

    <!-- MAIN CARD / DATA TABLE -->
    <div class="settings-card">
      <div class="settings-hint-block">
        Note: Payment modes listed below are offline modes. Payment gateways can be configured in Setup -> Settings -> Payment Gateways
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
          :dataSource="filteredModes"
          :columns="modeColumns"
          :pagination="{ pageSize: pageSize, total: filteredModes.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="mode-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <span class="mode-name">{{ record.name }}</span>
            </template>
            <template v-if="column.key === 'description'">
              <span class="text-muted">{{ record.description || '' }}</span>
            </template>
            <template v-if="column.key === 'active'">
              <a-tag :color="record.active ? 'success' : 'default'">
                {{ record.active ? 'Active' : 'Inactive' }}
              </a-tag>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editMode(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteMode(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Payment Mode' : 'Add New Payment Mode'"
      placement="right"
      :width="460"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveMode">
        <a-form-item label="* Payment Mode Name" name="name" :rules="[{ required: true, message: 'Payment mode name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter payment mode name" />
        </a-form-item>

        <a-form-item label="Bank Accounts / Description" name="description">
          <a-textarea v-model:value="form.description" :rows="4" placeholder="Enter bank accounts details or description" />
        </a-form-item>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
          <a-form-item name="active" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.active">
              Active
            </a-checkbox>
          </a-form-item>

          <a-form-item name="show_on_pdf" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.show_on_pdf">
              Show Bank Accounts / Description on Invoice PDF
            </a-checkbox>
          </a-form-item>

          <a-form-item name="selected_by_default" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.selected_by_default">
              Selected by default on invoice
            </a-checkbox>
          </a-form-item>

          <a-form-item name="invoices_only" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.invoices_only">
              Invoices Only
            </a-checkbox>
          </a-form-item>

          <a-form-item name="expenses_only" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.expenses_only">
              Expenses Only
            </a-checkbox>
          </a-form-item>
        </div>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Mode' : 'Add Mode' }}
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
  name: 'PaymentModesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const paymentModes = ref([
      { id: 1, name: 'Bank', description: '', active: true, show_on_pdf: true, selected_by_default: false, invoices_only: false, expenses_only: false }
    ]);

    const form = reactive({
      name: '',
      description: '',
      active: true,
      show_on_pdf: false,
      selected_by_default: false,
      invoices_only: false,
      expenses_only: false
    });

    const modeColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Payment Mode Name', dataIndex: 'name', key: 'name' },
      { title: 'Bank Accounts / Description', dataIndex: 'description', key: 'description' },
      { title: 'Active', key: 'active', width: 120 },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredModes = computed(() => {
      const sorted = [...paymentModes.value].sort((a, b) => b.id - a.id);
      if (!search.value) return sorted;
      return sorted.filter(pm => 
        pm.name.toLowerCase().includes(search.value.toLowerCase()) ||
        pm.description.toLowerCase().includes(search.value.toLowerCase())
      );
    });

    const openNewPaymentModeDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editMode = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.description = record.description || '';
      form.active = record.active !== undefined ? record.active : true;
      form.show_on_pdf = record.show_on_pdf || false;
      form.selected_by_default = record.selected_by_default || false;
      form.invoices_only = record.invoices_only || false;
      form.expenses_only = record.expenses_only || false;
      openDrawer.value = true;
    };

    const deleteMode = (id) => {
      paymentModes.value = paymentModes.value.filter(pm => pm.id !== id);
      message.success('Payment Mode deleted');
    };

    const saveMode = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = paymentModes.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.description = form.description.trim();
            item.active = form.active;
            item.show_on_pdf = form.show_on_pdf;
            item.selected_by_default = form.selected_by_default;
            item.invoices_only = form.invoices_only;
            item.expenses_only = form.expenses_only;
          }
          message.success('Payment Mode updated');
        } else {
          const maxId = paymentModes.value.reduce((max, pm) => pm.id > max ? pm.id : max, 0);
          paymentModes.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            description: form.description.trim(),
            active: form.active,
            show_on_pdf: form.show_on_pdf,
            selected_by_default: form.selected_by_default,
            invoices_only: form.invoices_only,
            expenses_only: form.expenses_only
          });
          message.success('Payment Mode added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving Payment Mode');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      Object.assign(form, {
        name: '',
        description: '',
        active: true,
        show_on_pdf: false,
        selected_by_default: false,
        invoices_only: false,
        expenses_only: false
      });
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      paymentModes,
      form,
      modeColumns,
      filteredModes,
      openNewPaymentModeDrawer,
      editMode,
      deleteMode,
      saveMode,
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
.settings-hint-block {
  font-size: 13.5px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 20px;
  color: #475569;
}
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.mode-id {
  color: #64748b;
  font-family: monospace;
}
.mode-name {
  font-weight: 600;
  color: #1e293b;
}
.text-muted {
  color: #64748b;
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
