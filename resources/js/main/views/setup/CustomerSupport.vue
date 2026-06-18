<template>
  <div class="settings-card">
    <div class="settings-hint-block">
      Configure support settings related to customers.
    </div>
    <div class="settings-group">
      <h3 class="settings-group-title">Customer Support Settings</h3>
      <div class="settings-row">
        <div class="settings-label">
          <span>Allow non-customers to submit tickets</span>
          <p class="settings-hint">Allow unregistered users to create support tickets</p>
        </div>
        <a-switch v-model:checked="settings.allow_non_customer_tickets" />
      </div>
      <div class="settings-row">
        <div class="settings-label">
          <span>Auto-close inactive tickets after (days)</span>
          <p class="settings-hint">Automatically close tickets with no activity</p>
        </div>
        <a-input-number v-model:value="settings.auto_close_days" :min="1" :max="90" style="width: 100px" />
      </div>
      <div class="settings-row">
        <div class="settings-label">
          <span>Default ticket priority</span>
        </div>
        <a-select v-model:value="settings.default_priority" style="width: 150px">
          <a-select-option value="low">Low</a-select-option>
          <a-select-option value="medium">Medium</a-select-option>
          <a-select-option value="high">High</a-select-option>
        </a-select>
      </div>
    </div>
    <div class="settings-actions">
      <a-button type="primary" @click="save">Save Settings</a-button>
    </div>
  </div>
</template>

<script>
import { defineComponent, reactive } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'CustomerSupportView',
  setup() {
    const settings = reactive({
      allow_non_customer_tickets: true,
      auto_close_days: 7,
      default_priority: 'medium',
    });

    const save = () => {
      message.success('Customer support settings saved');
    };

    return { settings, save };
  }
});
</script>
