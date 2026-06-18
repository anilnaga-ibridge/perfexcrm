<template>
  <div class="bulk-pdf-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Bulk PDF Export</h1>
        <p class="text-xs text-slate-500 mt-0.5">Generate and export multiple documents as PDF files</p>
      </div>
    </div>

    <div class="export-card">
      <div class="form-row">
        <label class="form-label">Select Type</label>
        <a-select v-model:value="form.type" style="width:100%" placeholder="Choose document type">
          <a-select-option value="invoices">Invoices</a-select-option>
          <a-select-option value="estimates">Estimates</a-select-option>
          <a-select-option value="credit_notes">Credit Notes</a-select-option>
          <a-select-option value="payments">Payments</a-select-option>
        </a-select>
      </div>

      <div class="form-row">
        <label class="form-label">From Date</label>
        <a-date-picker v-model:value="form.from_date" style="width:100%" value-format="YYYY-MM-DD" />
      </div>

      <div class="form-row">
        <label class="form-label">To Date</label>
        <a-date-picker v-model:value="form.to_date" style="width:100%" value-format="YYYY-MM-DD" />
      </div>

      <div class="form-row">
        <label class="checkbox-label">
          <input type="checkbox" v-model="form.include_tag" />
          <span class="checkbox-text">Include Tag</span>
        </label>
      </div>

      <div class="form-actions">
        <a-button type="primary" size="large" @click="handleExport" :loading="exporting">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="inline mr-1"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Generate PDF Export
        </a-button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive, ref } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'BulkPdfExport',
  setup() {
    const exporting = ref(false);

    const form = reactive({
      type: undefined,
      from_date: null,
      to_date: null,
      include_tag: false,
    });

    const handleExport = async () => {
      if (!form.type) {
        message.warning('Please select a document type');
        return;
      }
      if (!form.from_date || !form.to_date) {
        message.warning('Please select both From and To dates');
        return;
      }

      exporting.value = true;
      try {
        const res = await axios.post('/api/utilities/bulk-pdf-export', {
          type: form.type,
          from_date: form.from_date,
          to_date: form.to_date,
          include_tag: form.include_tag,
        }, { responseType: 'blob' });

        const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `bulk-export-${form.type}-${form.from_date}-${form.to_date}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        message.success('PDF export generated successfully');
      } catch (e) {
        message.error('Failed to generate PDF export');
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
.bulk-pdf-page {
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

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.checkbox-text {
  font-size: 13px;
  color: #334155;
  font-weight: 500;
}

.form-actions {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid #f1f5f9;
}
</style>
