<template>
  <div class="generic-settings-container p-6 bg-slate-50 min-h-screen">
    <!-- Header -->
    <div class="settings-header flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="text-indigo-600"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
          {{ moduleName }} Settings
        </h1>
        <p class="text-sm text-slate-500 mt-1">Configure and manage settings for this module.</p>
      </div>
      
      <!-- Actions -->
      <div class="flex items-center gap-3">
        <a-button type="default" @click="confirmReset" :loading="resetLoading" danger>
          Reset to Defaults
        </a-button>
        <a-button type="primary" @click="saveSettings" :loading="saveLoading">
          Save Settings
        </a-button>
      </div>
    </div>

    <!-- Spinner Loading -->
    <div v-if="loading" class="flex justify-center items-center py-20 bg-white border border-slate-150 rounded-xl shadow-sm">
      <a-spin size="large" tip="Loading settings schema..." />
    </div>

    <!-- No Settings Placeholder -->
    <div v-else-if="!schema || !schema.sections || !schema.sections.length" class="bg-white border border-slate-150 rounded-xl p-12 text-center text-slate-400 shadow-sm">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="mx-auto mb-2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <p class="text-sm">No settings schema defined for this module.</p>
    </div>

    <!-- Main Settings Form Layout -->
    <div v-else class="settings-form-wrapper">
      <a-form layout="vertical">
        <!-- Multiple Sections: Tabs Layout -->
        <a-tabs v-if="schema.sections.length > 1" type="card" class="bg-white p-4 border border-slate-150 rounded-xl shadow-sm">
          <a-tab-pane v-for="(section, sIndex) in schema.sections" :key="sIndex">
            <template #tab>
              <span class="flex items-center gap-2">
                <span v-if="section.icon" class="tab-icon" v-html="section.icon"></span>
                <span>{{ section.title }}</span>
              </span>
            </template>
            
            <div class="section-content p-4">
              <div v-if="section.description" class="text-sm text-slate-500 mb-6 bg-slate-50 p-3 rounded-lg border border-slate-100">
                {{ section.description }}
              </div>
              
              <a-row :gutter="16">
                <field-renderer
                  v-for="field in section.fields"
                  :key="field.key"
                  :field="field"
                  :error="fieldErrors[field.key]"
                  v-model:value="formState[field.key]"
                />
              </a-row>
            </div>
          </a-tab-pane>
        </a-tabs>

        <!-- Single Section: Card Layout -->
        <a-card v-else class="border border-slate-150 rounded-xl shadow-sm overflow-hidden">
          <template #title>
            <div class="flex items-center gap-2">
              <span v-if="schema.sections[0].icon" v-html="schema.sections[0].icon"></span>
              <span class="font-bold text-slate-700">{{ schema.sections[0].title }}</span>
            </div>
          </template>
          
          <div v-if="schema.sections[0].description" class="text-sm text-slate-500 mb-6 bg-slate-50 p-3 rounded-lg border border-slate-100">
            {{ schema.sections[0].description }}
          </div>
          
          <a-row :gutter="16">
            <field-renderer
              v-for="field in schema.sections[0].fields"
              :key="field.key"
              :field="field"
              :error="fieldErrors[field.key]"
              v-model:value="formState[field.key]"
            />
          </a-row>
        </a-card>
      </a-form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { message, Modal } from 'ant-design-vue';
import axios from 'axios';
import FieldRenderer from './settings/FieldRenderer.vue';

export default {
  name: 'GenericModuleSettings',
  components: {
    FieldRenderer,
  },
  setup() {
    const route = useRoute();
    const slug = computed(() => route.params.slug);
    
    const loading = ref(true);
    const saveLoading = ref(false);
    const resetLoading = ref(false);
    
    const schema = ref(null);
    const formState = reactive({});
    const fieldErrors = reactive({});
    
    const moduleName = computed(() => {
      if (!slug.value) return 'Module';
      return slug.value
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    });

    const loadSettings = async () => {
      if (!slug.value) return;
      loading.value = true;
      try {
        const response = await axios.get(`/modules/${slug.value}/settings`);
        if (response.data && response.data.success) {
          schema.value = response.data.data.schema;
          const values = response.data.data.values || {};
          
          // Reset states
          Object.keys(formState).forEach(key => delete formState[key]);
          Object.keys(fieldErrors).forEach(key => delete fieldErrors[key]);
          
          // Populate formState
          Object.keys(values).forEach(key => {
            formState[key] = values[key];
          });
        }
      } catch (err) {
        console.error('Failed to load module settings:', err);
        message.error(err.response?.data?.message || 'Failed to load settings schema.');
      } finally {
        loading.value = false;
      }
    };

    const validateFields = () => {
      // Clear previous errors
      Object.keys(fieldErrors).forEach(key => delete fieldErrors[key]);
      let isValid = true;

      if (!schema.value || !schema.value.sections) return true;

      // Scan all fields for validation constraints
      schema.value.sections.forEach(section => {
        section.fields.forEach(field => {
          const key = field.key;
          const val = formState[key];
          const rules = field.validation || {};

          // Required Check
          if (rules.required && (val === undefined || val === null || val === '')) {
            fieldErrors[key] = `${field.label} is required.`;
            isValid = false;
            return;
          }

          if (val === undefined || val === null || val === '') return;

          // Type specific checks
          if (field.type === 'number') {
            const num = Number(val);
            if (isNaN(num)) {
              fieldErrors[key] = `${field.label} must be a valid number.`;
              isValid = false;
            } else {
              if (rules.min !== undefined && num < rules.min) {
                fieldErrors[key] = `${field.label} must be at least ${rules.min}.`;
                isValid = false;
              }
              if (rules.max !== undefined && num > rules.max) {
                fieldErrors[key] = `${field.label} must be at most ${rules.max}.`;
                isValid = false;
              }
            }
          }

          if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(val)) {
              fieldErrors[key] = `${field.label} must be a valid email address.`;
              isValid = false;
            }
          }

          if (field.type === 'url') {
            try {
              new URL(val);
            } catch (_) {
              fieldErrors[key] = `${field.label} must be a valid URL.`;
              isValid = false;
            }
          }

          if (['text', 'textarea', 'password'].includes(field.type)) {
            const strVal = String(val);
            if (rules.min_length !== undefined && strVal.length < rules.min_length) {
              fieldErrors[key] = `${field.label} must be at least ${rules.min_length} characters.`;
              isValid = false;
            }
            if (rules.max_length !== undefined && strVal.length > rules.max_length) {
              fieldErrors[key] = `${field.label} must be at most ${rules.max_length} characters.`;
              isValid = false;
            }
          }
        });
      });

      return isValid;
    };

    const saveSettings = async () => {
      if (!validateFields()) {
        message.error('Please correct the validation errors before saving.');
        return;
      }
      
      saveLoading.value = true;
      try {
        const response = await axios.put(`/modules/${slug.value}/settings`, formState);
        if (response.data && response.data.success) {
          message.success('Settings saved successfully.');
          
          // Re-populate with values returned from server
          const values = response.data.data || {};
          Object.keys(values).forEach(key => {
            formState[key] = values[key];
          });
        }
      } catch (err) {
        console.error('Failed to save settings:', err);
        if (err.response?.status === 422) {
          message.error(err.response.data.message || 'Validation failed on server.');
          // If server returns field specific error keys, map them
          if (err.response.data.errors) {
            Object.assign(fieldErrors, err.response.data.errors);
          }
        } else {
          message.error(err.response?.data?.message || 'Failed to save settings.');
        }
      } finally {
        saveLoading.value = false;
      }
    };

    const confirmReset = () => {
      Modal.confirm({
        title: 'Are you sure you want to reset settings?',
        content: 'This will purge all custom settings for this module and revert them back to their default values.',
        okText: 'Reset',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: resetToDefaults,
      });
    };

    const resetToDefaults = async () => {
      resetLoading.value = true;
      try {
        const response = await axios.post(`/modules/${slug.value}/settings/reset`);
        if (response.data && response.data.success) {
          message.success('Settings reset to defaults.');
          
          // Sync with reset values returned from server
          const values = response.data.data.values || {};
          
          Object.keys(formState).forEach(key => delete formState[key]);
          Object.keys(fieldErrors).forEach(key => delete fieldErrors[key]);
          
          Object.keys(values).forEach(key => {
            formState[key] = values[key];
          });
        }
      } catch (err) {
        console.error('Failed to reset settings:', err);
        message.error(err.response?.data?.message || 'Failed to reset settings.');
      } finally {
        resetLoading.value = false;
      }
    };

    watch(slug, loadSettings);
    onMounted(loadSettings);

    return {
      slug,
      loading,
      saveLoading,
      resetLoading,
      schema,
      formState,
      fieldErrors,
      moduleName,
      saveSettings,
      confirmReset,
    };
  },
};
</script>

<style scoped>
.generic-settings-container {
  max-width: 1200px;
  margin: 0 auto;
}
:deep(.ant-tabs-card > .ant-tabs-nav .ant-tabs-tab-active) {
  background: #fff;
  border-color: #e2e8f0;
  border-bottom-color: #fff;
}
.tab-icon :deep(svg) {
  width: 16px;
  height: 16px;
}
</style>
