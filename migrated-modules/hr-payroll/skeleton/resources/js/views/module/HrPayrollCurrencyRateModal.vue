<template>
  <div class="module-page">
    <h1>hr_payroll::currency_rate_modal()</h1>
    <p class="migration-badge">🚧 Manual Migration Required</p>

    <div class="migration-context">
      <table>
        <tr><td>Original controller</td><td><code>hr_payroll.php</code> extends <code>AdminController</code></td></tr>
        <tr><td>Original method</td><td><code>currency_rate_modal()</code></td></tr>
        <tr><td>Original view</td><td><code>views/currency_rate_modal.php</code></td></tr>
        <tr><td>Status</td><td><strong>Pending</strong></td></tr>
      </table>
    </div>

    <a-spin :spinning="loading">
      <!-- TODO(Migration): Replace with migrated content -->
      <a-empty description="Not yet migrated" />
    </a-spin>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: 'HrPayrollCurrencyRateModal',
  setup() {
    const loading = ref(false);

    // TODO(Migration): Fetch data from migrated API endpoint
    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/module/currency_rate_modal');
        // Handle response
      } catch (err) {
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    return {
      loading,
      fetchData,
    };
  },
};
</script>

<style scoped>
.module-page { padding: 24px; }
.migration-badge {
  display: inline-block;
  background: #fef3cd;
  color: #856404;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 16px;
}
.migration-context {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 24px;
  font-size: 14px;
}
.migration-context table { width: 100%; }
.migration-context td { padding: 4px 8px; }
.migration-context td:first-child {
  color: #64748b;
  width: 160px;
}
</style>