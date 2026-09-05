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
      placement="right"
      :width="520"
      :footer-style="{ padding: '16px 24px', background: '#fafafa', borderTop: '1px solid #f1f5f9' }"
      :header-style="{ padding: '20px 24px', background: '#ffffff', borderBottom: '1px solid #f1f5f9' }"
      @close="resetForm"
    >
      <template #title>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center shadow-md shrink-0 theme-primary-grad">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-base font-extrabold text-slate-800 m-0 leading-snug">{{ editingId ? 'Edit Payment Mode' : 'Add New Payment Mode' }}</h3>
            <p class="text-xs text-slate-400 font-medium m-0 mt-0.5 leading-normal">Configure bank account details & display settings</p>
          </div>
        </div>
      </template>

      <form @submit.prevent="saveMode" class="space-y-5 p-1">
        <!-- Payment Mode Name -->
        <div>
          <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
            Payment Mode Name <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.name"
            placeholder="e.g. Bank Transfer, Wire, Credit Card"
            class="w-full h-11 px-4 text-xs font-semibold theme-input-ctrl text-slate-800"
            required
          />
        </div>

        <!-- Bank Accounts / Description Input Box -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
              Bank Accounts / Description
            </label>
            <!-- Quick Preset Helper Chips -->
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all cursor-pointer"
                @click="insertBankTemplate"
                title="Insert standard Bank Account details template"
              >
                + Bank Template
              </button>
              <button
                type="button"
                class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-all cursor-pointer"
                @click="insertWireTemplate"
                title="Insert Wire Transfer details template"
              >
                + Wire Template
              </button>
            </div>
          </div>

          <textarea
            v-model="form.description"
            rows="6"
            placeholder="Enter bank accounts details, IBAN, SWIFT code, routing number or payment instructions to display on invoice PDF..."
            class="w-full p-4 text-xs font-semibold theme-input-ctrl text-slate-800 placeholder-slate-400 resize-y leading-relaxed"
          ></textarea>
          <p class="text-[11px] text-slate-400 font-medium mt-1 m-0">
            This information will be displayed on client invoice documents when selected.
          </p>
        </div>

        <!-- Checkbox Options Group -->
        <div class="p-4 bg-slate-50/70 border border-slate-200/80 rounded-2xl space-y-3">
          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" v-model="form.active" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
            <span class="text-xs font-bold text-slate-700">Active Payment Mode</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" v-model="form.show_on_pdf" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
            <span class="text-xs font-bold text-slate-700">Show Bank Accounts / Description on Invoice PDF</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" v-model="form.selected_by_default" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
            <span class="text-xs font-bold text-slate-700">Selected by Default on Invoice</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" v-model="form.invoices_only" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
            <span class="text-xs font-bold text-slate-700">Invoices Only</span>
          </label>

          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="checkbox" v-model="form.expenses_only" class="w-4 h-4 rounded border-slate-300 cursor-pointer theme-accent-chk" />
            <span class="text-xs font-bold text-slate-700">Expenses Only</span>
          </label>
        </div>
      </form>

      <template #footer>
        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all cursor-pointer border border-slate-200/80"
            @click="openDrawer = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-7 py-2.5 text-xs font-bold text-white rounded-xl cursor-pointer shadow-md transition-all flex items-center gap-2 theme-primary-grad"
            @click="saveMode"
            :disabled="saving"
          >
            <svg v-if="saving" class="animate-spin" fill="none" viewBox="0 0 24 24" width="14" height="14"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editingId ? 'Update Mode' : 'Add Mode') }}
          </button>
        </div>
      </template>
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

    const insertBankTemplate = () => {
      const template = `Bank Name: Chase Bank\nAccount Name: iBridge CRM Ltd\nAccount Number: 1234567890\nRouting Number: 987654321\nBranch: New York Main`;
      form.description = form.description ? form.description + '\n\n' + template : template;
      message.info('Bank template inserted');
    };

    const insertWireTemplate = () => {
      const template = `Beneficiary: iBridge CRM Ltd\nSWIFT/BIC Code: CHASEUS33XXX\nIBAN: US98CHAS12345678901234\nBank Address: 270 Park Ave, New York, NY 10017`;
      form.description = form.description ? form.description + '\n\n' + template : template;
      message.info('Wire transfer template inserted');
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
      resetForm,
      insertBankTemplate,
      insertWireTemplate
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

/* Dynamic Theme Utility Classes */
.theme-primary-btn {
  background: var(--theme-primary, #6366f1) !important;
  color: #ffffff !important;
}
.theme-primary-btn:hover {
  background: var(--theme-primary-hover, #4f46e5) !important;
}
.theme-primary-grad {
  background: linear-gradient(135deg, var(--theme-primary, #6366f1) 0%, var(--theme-primary-hover, #4f46e5) 100%) !important;
  color: #ffffff !important;
}
.theme-input-ctrl {
  background-color: rgba(248, 250, 252, 0.8);
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}
.theme-input-ctrl:focus {
  background-color: #ffffff;
  border-color: var(--theme-primary, #6366f1) !important;
  box-shadow: 0 0 0 4px var(--theme-primary-light, rgba(99, 102, 241, 0.15)) !important;
  outline: none;
}
.theme-accent-chk {
  accent-color: var(--theme-primary, #6366f1) !important;
}
</style>
