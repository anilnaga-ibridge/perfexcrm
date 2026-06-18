<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Currencies</h2>
      <button class="btn-primary" @click="openNewCurrencyDrawer">New Currency</button>
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
          :dataSource="filteredCurrencies"
          :columns="currencyColumns"
          :pagination="{ pageSize: pageSize, total: filteredCurrencies.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'name'">
              <div class="currency-name-cell">
                <span class="currency-code">{{ record.code }}</span>
                <span v-if="record.is_base" class="base-currency-label">Base Currency</span>
              </div>
            </template>
            <template v-if="column.key === 'symbol'">
              <span class="currency-symbol">{{ record.symbol }}</span>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editCurrency(record)">Edit</a-button>
                <a-button size="small" type="link" danger :disabled="record.is_base" @click="deleteCurrency(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Currency' : 'Add New Currency'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveCurrency">
        <div class="settings-hint-block warn-hint">
          Make sure to enter valid currency ISO code.
        </div>

        <a-form-item label="* Currency Code" name="code" extra="ISO Code" :rules="[{ required: true, message: 'Currency code required' }]">
          <a-input v-model:value="form.code" placeholder="e.g. USD" />
        </a-form-item>

        <a-form-item label="* Symbol" name="symbol" :rules="[{ required: true, message: 'Symbol required' }]">
          <a-input v-model:value="form.symbol" placeholder="e.g. $" />
        </a-form-item>

        <a-form-item label="* Decimal Separator" name="decimal_sep" :rules="[{ required: true, message: 'Decimal separator required' }]">
          <a-input v-model:value="form.decimal_sep" placeholder="." />
        </a-form-item>

        <a-form-item label="* Thousand Separator" name="thousand_sep" :rules="[{ required: true, message: 'Thousand separator required' }]">
          <a-input v-model:value="form.thousand_sep" placeholder="," />
        </a-form-item>

        <a-form-item label="* Currency Placement" name="placement">
          <a-radio-group v-model:value="form.placement" class="currency-placement-group">
            <a-radio value="before">Before Amount</a-radio>
            <a-radio value="after" style="margin-left: 16px;">After Amount</a-radio>
          </a-radio-group>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Currency' : 'Add Currency' }}
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
  name: 'CurrenciesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const currencies = ref([
      { id: 1, code: 'EUR', symbol: '€', name: 'Euro', is_base: false, decimal_sep: '.', thousand_sep: ',', placement: 'before' },
      { id: 2, code: 'USD', symbol: '$', name: 'US Dollar', is_base: true, decimal_sep: '.', thousand_sep: ',', placement: 'before' }
    ]);

    const form = reactive({
      code: '',
      symbol: '',
      decimal_sep: '.',
      thousand_sep: ',',
      placement: 'before'
    });

    const currencyColumns = [
      { title: 'Name', key: 'name', dataIndex: 'code' },
      { title: 'Symbol', key: 'symbol', dataIndex: 'symbol', width: 150 },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredCurrencies = computed(() => {
      if (!search.value) return currencies.value;
      return currencies.value.filter(c => 
        c.code.toLowerCase().includes(search.value.toLowerCase()) ||
        c.symbol.toLowerCase().includes(search.value.toLowerCase())
      );
    });

    const openNewCurrencyDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editCurrency = (record) => {
      editingId.value = record.id;
      form.code = record.code;
      form.symbol = record.symbol;
      form.decimal_sep = record.decimal_sep || '.';
      form.thousand_sep = record.thousand_sep || ',';
      form.placement = record.placement || 'before';
      openDrawer.value = true;
    };

    const deleteCurrency = (id) => {
      currencies.value = currencies.value.filter(c => c.id !== id);
      message.success('Currency deleted');
    };

    const saveCurrency = () => {
      if (!form.code.trim() || !form.symbol.trim() || !form.decimal_sep.trim() || !form.thousand_sep.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = currencies.value.find(x => x.id === editingId.value);
          if (item) {
            item.code = form.code.trim().toUpperCase();
            item.symbol = form.symbol.trim();
            item.decimal_sep = form.decimal_sep.trim();
            item.thousand_sep = form.thousand_sep.trim();
            item.placement = form.placement;
          }
          message.success('Currency updated');
        } else {
          currencies.value.push({
            id: Date.now(),
            code: form.code.trim().toUpperCase(),
            symbol: form.symbol.trim(),
            decimal_sep: form.decimal_sep.trim(),
            thousand_sep: form.thousand_sep.trim(),
            placement: form.placement,
            is_base: false
          });
          message.success('Currency added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving currency');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      Object.assign(form, {
        code: '',
        symbol: '',
        decimal_sep: '.',
        thousand_sep: ',',
        placement: 'before'
      });
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      currencies,
      form,
      currencyColumns,
      filteredCurrencies,
      openNewCurrencyDrawer,
      editCurrency,
      deleteCurrency,
      saveCurrency,
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
.currency-name-cell {
  display: flex;
  flex-direction: column;
}
.currency-code {
  font-weight: 600;
  color: #1e293b;
}
.base-currency-label {
  font-size: 11px;
  color: #10b981;
  font-weight: 500;
  margin-top: 2px;
}
.currency-symbol {
  font-weight: 500;
  color: #475569;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.settings-hint-block {
  font-size: 13.5px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 20px;
}
.settings-hint-block.warn-hint {
  background: #fef3c7;
  border-color: #fde68a;
  color: #d97706;
  font-weight: 500;
}
.currency-placement-group {
  display: flex;
  align-items: center;
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
