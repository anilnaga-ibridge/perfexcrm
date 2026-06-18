<template>
  <div class="add-customer-page space-y-6">
    
    <!-- Title -->
    <div>
      <h1 class="text-xl font-bold text-slate-800 animate-fade-in">Add New Customer</h1>
    </div>

    <!-- Main Card -->
    <div class="customer-card">
      <!-- Tabs Selector -->
      <div class="card-tabs">
        <button 
          type="button"
          :class="['tab-btn', { 'tab-btn--active': activeTab === 'details' }]" 
          @click="activeTab = 'details'"
        >
          Customer Details
        </button>
        <button 
          type="button"
          :class="['tab-btn', { 'tab-btn--active': activeTab === 'billing' }]" 
          @click="activeTab = 'billing'"
        >
          Billing & Shipping
        </button>
      </div>

      <a-form :model="form" layout="vertical" ref="formRef">
        <!-- Card Body -->
        <div class="card-body">
          
          <!-- Tab 1: Customer Details -->
          <div v-show="activeTab === 'details'" class="form-grid-vertical">
            <a-form-item name="company" :rules="[{ required: true, message: 'Please enter company name' }]">
              <template #label><span class="required-asterisk">*</span> Company</template>
              <a-input v-model:value="form.company" />
            </a-form-item>

            <a-form-item label="VAT Number" name="vat">
              <a-input v-model:value="form.vat" />
            </a-form-item>

            <a-form-item label="Phone" name="phonenumber">
              <a-input v-model:value="form.phonenumber" />
            </a-form-item>

            <a-form-item label="Website" name="website">
              <a-input v-model:value="form.website" />
            </a-form-item>

            <a-form-item label="Groups" name="groups">
              <div class="flex gap-2 items-center">
                <a-select 
                  v-model:value="form.groups" 
                  mode="multiple" 
                  placeholder="Nothing selected"
                  style="width: 100%"
                  :options="groupOptions"
                  allow-clear
                />
                <button class="btn-outline add-group-btn" type="button">+</button>
              </div>
            </a-form-item>

            <div class="grid grid-cols-2 gap-4">
              <a-form-item name="currency">
                <template #label>
                  <span class="help-label" title="Customer Currency">
                    <span class="help-icon">?</span> Currency
                  </span>
                </template>
                <a-select v-model:value="form.currency" style="width: 100%">
                  <a-select-option value="System Default">System Default</a-select-option>
                  <a-select-option value="USD">USD</a-select-option>
                  <a-select-option value="EUR">EUR</a-select-option>
                  <a-select-option value="GBP">GBP</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default Language" name="default_language">
                <a-select v-model:value="form.default_language" style="width: 100%">
                  <a-select-option value="system_default">System Default</a-select-option>
                  <a-select-option value="english">English</a-select-option>
                  <a-select-option value="german">German</a-select-option>
                  <a-select-option value="french">French</a-select-option>
                  <a-select-option value="spanish">Spanish</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <a-form-item label="Address" name="address">
              <a-textarea v-model:value="form.address" :rows="4" />
            </a-form-item>

            <a-form-item label="City" name="city">
              <a-input v-model:value="form.city" />
            </a-form-item>

            <a-form-item label="State" name="state">
              <a-input v-model:value="form.state" />
            </a-form-item>

            <a-form-item label="Zip Code" name="zip">
              <a-input v-model:value="form.zip" />
            </a-form-item>

            <a-form-item label="Country" name="country">
              <a-select 
                v-model:value="form.country" 
                show-search 
                placeholder="Nothing selected" 
                style="width: 100%" 
                :options="countryOptions" 
                allow-clear
              />
            </a-form-item>

            <div class="grid grid-cols-2 gap-4">
              <a-form-item label="Latitude (Google Maps)" name="latitude">
                <a-input v-model:value="form.latitude" placeholder="e.g. 40.7128" />
              </a-form-item>
              <a-form-item label="Longitude (Google Maps)" name="longitude">
                <a-input v-model:value="form.longitude" placeholder="e.g. -74.0060" />
              </a-form-item>
            </div>
          </div>

          <!-- Tab 2: Billing & Shipping -->
          <div v-show="activeTab === 'billing'" class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Billing Address Column -->
            <div class="space-y-4">
              <div class="flex justify-between items-center pb-1.5 border-b border-slate-100">
                <h3 class="font-bold text-slate-700 text-sm">Billing Address</h3>
                <a class="link-action text-xs font-semibold cursor-pointer" @click="sameAsCustomerInfo">Same as Customer Info</a>
              </div>

              <a-form-item label="Street" name="billing_street">
                <a-textarea v-model:value="form.billing_street" :rows="4" />
              </a-form-item>

              <a-form-item label="City" name="billing_city">
                <a-input v-model:value="form.billing_city" />
              </a-form-item>

              <a-form-item label="State" name="billing_state">
                <a-input v-model:value="form.billing_state" />
              </a-form-item>

              <a-form-item label="Zip Code" name="billing_zip">
                <a-input v-model:value="form.billing_zip" />
              </a-form-item>

              <a-form-item label="Country" name="billing_country">
                <a-select 
                  v-model:value="form.billing_country" 
                  show-search 
                  placeholder="Nothing selected" 
                  style="width: 100%" 
                  :options="countryOptions" 
                  allow-clear
                />
              </a-form-item>
            </div>

            <!-- Shipping Address Column -->
            <div class="space-y-4">
              <div class="flex justify-between items-center pb-1.5 border-b border-slate-100">
                <h3 class="font-bold text-slate-700 text-sm help-label">
                  <span class="help-icon">?</span> Shipping Address
                </h3>
                <a class="link-action text-xs font-semibold cursor-pointer" @click="copyBillingAddress">Copy Billing Address</a>
              </div>

              <a-form-item label="Street" name="shipping_street">
                <a-textarea v-model:value="form.shipping_street" :rows="4" />
              </a-form-item>

              <a-form-item label="City" name="shipping_city">
                <a-input v-model:value="form.shipping_city" />
              </a-form-item>

              <a-form-item label="State" name="shipping_state">
                <a-input v-model:value="form.shipping_state" />
              </a-form-item>

              <a-form-item label="Zip Code" name="shipping_zip">
                <a-input v-model:value="form.shipping_zip" />
              </a-form-item>

              <a-form-item label="Country" name="shipping_country">
                <a-select 
                  v-model:value="form.shipping_country" 
                  show-search 
                  placeholder="Nothing selected" 
                  style="width: 100%" 
                  :options="countryOptions" 
                  allow-clear
                />
              </a-form-item>
            </div>

          </div>

        </div>

        <!-- Card Footer -->
        <div class="card-footer">
          <button type="button" class="btn-ghost" @click="saveAndCreateContact">Save and create contact</button>
          <button type="button" class="btn-save" @click="saveCustomer">Save</button>
        </div>
      </a-form>
    </div>

  </div>
</template>

<script>
import { defineComponent, ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'CustomerCreate',
  setup() {
    const router = useRouter();
    const activeTab = ref('details');
    const formRef = ref(null);

    const form = reactive({
      company: '',
      vat: '',
      phonenumber: '',
      website: '',
      groups: [],
      currency: 'System Default',
      default_language: 'system_default',
      address: '',
      city: '',
      state: '',
      zip: '',
      country: undefined,
      
      // Billing Address
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: undefined,

      // Shipping Address
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: undefined,
      
      latitude: '',
      longitude: '',
      
      active: true
    });

    const groupOptions = [
      { value: 'High Budget', label: 'High Budget' },
      { value: 'Low Budget', label: 'Low Budget' },
      { value: 'VIP', label: 'VIP' },
      { value: 'Wholesaler', label: 'Wholesaler' }
    ];

    const countryOptions = [
      { value: 'United States', label: 'United States' },
      { value: 'United Kingdom', label: 'United Kingdom' },
      { value: 'Germany', label: 'Germany' },
      { value: 'France', label: 'France' },
      { value: 'Australia', label: 'Australia' },
      { value: 'Canada', label: 'Canada' },
      { value: 'India', label: 'India' },
      { value: 'Japan', label: 'Japan' },
      { value: 'Brazil', label: 'Brazil' },
      { value: 'South Africa', label: 'South Africa' },
      { value: 'Singapore', label: 'Singapore' },
      { value: 'Spain', label: 'Spain' },
      { value: 'Italy', label: 'Italy' },
      { value: 'Netherlands', label: 'Netherlands' }
    ];

    const sameAsCustomerInfo = () => {
      form.billing_street = form.address;
      form.billing_city = form.city;
      form.billing_state = form.state;
      form.billing_zip = form.zip;
      form.billing_country = form.country;
      message.success('Billing address filled from customer info.');
    };

    const copyBillingAddress = () => {
      form.shipping_street = form.billing_street;
      form.shipping_city = form.billing_city;
      form.shipping_state = form.billing_state;
      form.shipping_zip = form.billing_zip;
      form.shipping_country = form.billing_country;
      message.success('Shipping address copied from billing address.');
    };

    const saveCustomer = async () => {
      formRef.value.validate().then(async () => {
        try {
          const payload = {
            ...form,
            groups: Array.isArray(form.groups) ? form.groups.join(',') : form.groups
          };
          await axios.post('/clients', payload);
          message.success('Customer created successfully.');
          router.push('/admin/customers');
        } catch (err) {
          message.error(err.response?.data?.message || 'Failed to save customer.');
        }
      }).catch(() => {
        message.error('Please enter all required fields.');
      });
    };

    const saveAndCreateContact = async () => {
      formRef.value.validate().then(async () => {
        try {
          const payload = {
            ...form,
            groups: Array.isArray(form.groups) ? form.groups.join(',') : form.groups
          };
          const response = await axios.post('/clients', payload);
          message.success('Customer saved successfully.');
          // Redirect with new_contact = true query parameter to trigger contact drawer
          router.push({ path: `/admin/customers/${response.data.id}`, query: { new_contact: 'true' } });
        } catch (err) {
          message.error(err.response?.data?.message || 'Failed to save customer.');
        }
      }).catch(() => {
        message.error('Please enter all required fields.');
      });
    };

    return { 
      activeTab, 
      form, 
      formRef,
      groupOptions,
      countryOptions,
      sameAsCustomerInfo, 
      copyBillingAddress, 
      saveCustomer, 
      saveAndCreateContact 
    };
  }
});
</script>

<style scoped>
.add-customer-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
  width: 100%;
}

/* Card Styling */
.customer-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,.05);
  overflow: hidden;
}

/* Card Tabs Header */
.card-tabs {
  display: flex;
  background: #f6f8fa;
  border-bottom: 1px solid #e2e8f0;
}
.tab-btn {
  padding: 12px 24px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  background: none;
  border: none;
  border-right: 1px solid #e2e8f0;
  border-top: 3px solid transparent;
  cursor: pointer;
  outline: none;
  transition: all 0.15s;
  font-family: inherit;
}
.tab-btn:hover {
  background: #f1f5f9;
  color: #334155;
}
.tab-btn--active {
  background: #fff;
  color: #1e293b;
  font-weight: 600;
  border-top: 3px solid #e11d48; /* Red/Rose color matching Perfex logo brand */
  border-bottom: 1px solid #fff;
}

/* Card Body */
.card-body {
  padding: 24px;
}

/* Form layout constraint matching Perfex */
.form-grid-vertical {
  max-width: 650px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

/* Required star */
.required-asterisk {
  color: #ef4444;
  margin-right: 4px;
  font-weight: bold;
}

/* Add Group Button next to multi-select */
.add-group-btn {
  padding: 0 14px;
  font-size: 18px;
  font-weight: 600;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-color: #d9d9d9;
}
.add-group-btn:hover {
  border-color: #e11d48;
  color: #e11d48;
}

/* Help label (?) */
.help-label {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.help-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #94a3b8;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  cursor: help;
}

/* Action links */
.link-action {
  color: #2563eb;
  text-decoration: none;
  transition: color 0.15s;
}
.link-action:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

/* Card Footer */
.card-footer {
  background: #f8fafc;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.btn-save {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.1s;
}
.btn-save:hover {
  background: #0f172a;
}
.btn-ghost {
  background: #fff;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.1s;
}
.btn-ghost:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #1e293b;
}
.btn-outline {
  background: #fff;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  font-family: inherit;
  cursor: pointer;
}
.btn-outline:hover {
  background: #f8fafc;
}

/* Animations */
.animate-fade-in {
  animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Override Ant spacing details */
:deep(.ant-form-item) {
  margin-bottom: 16px !important;
}
:deep(.ant-form-item-label) {
  padding-bottom: 4px !important;
}
:deep(.ant-form-item-label > label) {
  font-size: 13px !important;
  font-weight: 500 !important;
  color: #475569 !important;
}
:deep(.ant-select-selector), :deep(.ant-input) {
  border-radius: 4px !important;
  border-color: #cbd5e1 !important;
}
:deep(.ant-select-focused .ant-select-selector), :deep(.ant-input:focus) {
  border-color: #e11d48 !important;
  box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.1) !important;
}
</style>
