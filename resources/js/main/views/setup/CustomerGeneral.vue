<template>
  <div class="settings-card">
    <div class="settings-hint-block">
      Configure general customer settings for registration and login behavior.
    </div>
    <div class="settings-group">
      <h3 class="settings-group-title">General Customer Registration Options</h3>
      <div class="settings-row">
        <div class="settings-label">
          <span>Allow customer registration</span>
          <p class="settings-hint">Allow customers to register their own account from the client portal login area</p>
        </div>
        <a-switch v-model:checked="settings.allow_registration" />
      </div>
      <div class="settings-row">
        <div class="settings-label">
          <span>Require email confirmation on registration</span>
          <p class="settings-hint">Customers must confirm their email before gaining access</p>
        </div>
        <a-switch v-model:checked="settings.email_confirmation" />
      </div>
      <div class="settings-row">
        <div class="settings-label">
          <span>Allow customer to login</span>
        </div>
        <a-switch v-model:checked="settings.allow_login" />
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
  name: 'CustomerGeneralView',
  setup() {
    const settings = reactive({
      allow_registration: true,
      email_confirmation: false,
      allow_login: true,
    });

    const save = () => {
      message.success('Customer settings saved');
    };

    return { settings, save };
  }
});
</script>
