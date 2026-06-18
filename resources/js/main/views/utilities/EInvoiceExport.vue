<template>
  <div class="einvoice-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">e-Invoice Export</h1>
        <p class="text-xs text-slate-500 mt-0.5">Export electronic invoices in standard format</p>
      </div>
    </div>

    <div class="export-card">
      <div class="form-row">
        <label class="form-label">Export Type</label>
        <a-select v-model:value="form.export_type" style="width:100%" placeholder="Select export type">
          <a-select-option value="xml">XML (Standard)</a-select-option>
          <a-select-option value="json">JSON</a-select-option>
          <a-select-option value="csv">CSV</a-select-option>
        </a-select>
      </div>

      <div class="form-row">
        <label class="form-label">Period</label>
        <a-select v-model:value="form.period" style="width:100%" placeholder="Select period">
          <a-select-option value="all_time">All Time</a-select-option>
          <a-select-option value="this_month">This Month</a-select-option>
          <a-select-option value="last_month">Last Month</a-select-option>
          <a-select-option value="this_quarter">This Quarter</a-select-option>
          <a-select-option value="last_quarter">Last Quarter</a-select-option>
          <a-select-option value="this_year">This Year</a-select-option>
          <a-select-option value="last_year">Last Year</a-select-option>
          <a-select-option value="custom">Custom Range</a-select-option>
        </a-select>
      </div>

      <div v-if="form.period === 'custom'" class="custom-range">
        <div class="form-row half">
          <label class="form-label">From Date</label>
          <a-date-picker v-model:value="form.from_date" style="width:100%" value-format="YYYY-MM-DD" />
        </div>
        <div class="form-row half">
          <label class="form-label">To Date</label>
          <a-date-picker v-model:value="form.to_date" style="width:100%" value-format="YYYY-MM-DD" />
        </div>
      </div>

      <div class="form-actions">
        <a-button type="primary" size="large" @click="handleExport" :loading="exporting">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="inline mr-1"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export
        </a-button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref, watch } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'EInvoiceExport',
  setup() {
    const exporting = ref(false);

    const form = reactive({
      export_type: undefined,
      period: undefined,
      from_date: null,
      to_date: null,
    });

    watch(() => form.period, (val) => {
      if (val !== 'custom') {
        form.from_date = null;
        form.to_date = null;
      }
    });

    const handleExport = async () => {
      if (!form.export_type) {
        message.warning('Please select an export type');
        return;
      }
      if (!form.period) {
        message.warning('Please select a period');
        return;
      }
      if (form.period === 'custom' && (!form.from_date || !form.to_date)) {
        message.warning('Please select both From and To dates');
        return;
      }

      exporting.value = true;
      try {
        const ext = form.export_type === 'xml' ? 'xml' : form.export_type;
        const res = await axios.post('/api/utilities/e-invoice-export', form, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `e-invoice-export.${ext}`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        message.success('Export generated successfully');
      } catch (e) {
        message.error('Failed to generate export');
        console.error(e);
      } finally {
        exporting.value = false;
      }
    };

    return { form, exporting, handleExport };
  }
});
</script>

<style scoped>
.einvoice-page {
  font-family: 'Inter', -apple-system, sans-serif;
}
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.export-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 24px;
  max-width: 480px;
}

.form-row {
  margin-bottom: 18px;
}
.form-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
  text-transform: uppercase;
}

.custom-range {
  display: flex;
  gap: 12px;
}
.custom-range .half {
  flex: 1;
}

.form-actions {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid #f1f5f9;
}
</style>
