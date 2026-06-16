<template>
  <div class="estimate-form-page space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="cancelLink" class="hover:text-indigo-600 font-semibold">Estimates</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ editMode ? 'Edit Estimate' : 'New Estimate' }}</span>
      </div>
      <router-link :to="cancelLink" class="text-xs text-indigo-600 hover:underline font-semibold">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
      <!-- Title Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h2 class="text-base font-bold text-slate-800">
          {{ editMode ? 'Edit Estimate #' + (form.number || '') : 'Create New Estimate' }}
        </h2>
      </div>

      <div class="p-6 space-y-6">
        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- Left Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span class="text-rose-500">*</span> Customer
              </label>
              <select class="form-ctrl text-xs h-9 bg-white cursor-pointer" v-model="form.client" @change="e => handleClientChange(e.target.value)">
                <option value="" disabled>Select and begin typing</option>
                <option v-for="c in clients" :key="c.id" :value="c.company">{{ c.company }}</option>
              </select>
            </div>

            <!-- Address Details Heading & Edit Trigger -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700">Address Details</label>
                <button 
                  type="button" 
                  class="text-indigo-600 hover:text-indigo-800 cursor-pointer flex items-center gap-1 text-[11px] font-bold" 
                  @click="openAddressModal"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit billing & shipping
                </button>
              </div>

              <!-- Bill To / Ship To Addresses -->
              <div class="grid grid-cols-2 gap-4 text-xs text-slate-500 py-3 bg-slate-50/50 rounded p-3 border border-slate-100">
                <div>
                  <div class="font-bold text-slate-700 mb-1 flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11" class="text-indigo-600"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Bill To
                  </div>
                  <div>{{ form.billing_street || '--' }}</div>
                  <div>{{ form.billing_city || '--' }}, {{ form.billing_state || '--' }}</div>
                  <div>{{ form.billing_country || '--' }}, {{ form.billing_zip || '--' }}</div>
                </div>
                <div v-if="form.show_shipping_details">
                  <div class="font-bold text-slate-700 mb-1 flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11" class="text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Ship to
                  </div>
                  <div>{{ form.shipping_street || '--' }}</div>
                  <div>{{ form.shipping_city || '--' }}, {{ form.shipping_state || '--' }}</div>
                  <div>{{ form.shipping_country || '--' }}, {{ form.shipping_zip || '--' }}</div>
                </div>
                <div v-else class="text-slate-400 italic flex items-center justify-center border-l pl-3">
                  Shipping details not shown in estimate.
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span class="text-rose-500">*</span> Estimate Number
              </label>
              <div class="flex">
                <span class="inline-flex items-center px-3 rounded-l border border-r-0 border-slate-300 bg-slate-50 text-slate-500 text-xs">
                  EST-
                </span>
                <input 
                  type="text" 
                  class="form-ctrl text-xs h-9 rounded-l-none" 
                  v-model="estimateNumberSuffix" 
                  placeholder="000011" 
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  <span class="text-rose-500">*</span> Estimate Date
                </label>
                <input type="date" class="form-ctrl text-xs h-9 pr-8" v-model="form.date" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Expiry Date</label>
                <input type="date" class="form-ctrl text-xs h-9 pr-8" v-model="form.expiry" />
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tags</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-9" 
                v-model="form.tags" 
                placeholder="Tag" 
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  <span class="text-rose-500">*</span> Currency
                </label>
                <select class="form-ctrl text-xs h-9 bg-white cursor-pointer" v-model="form.currency">
                  <option value="USD">USD $</option>
                  <option value="EUR">EUR €</option>
                  <option value="GBP">GBP £</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                <select class="form-ctrl text-xs h-9 bg-white cursor-pointer" v-model="form.status">
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="accepted">Accepted</option>
                  <option value="declined">Declined</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Reference #</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-9" 
                v-model="form.reference_no" 
                placeholder="Reference number" 
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sale Agent</label>
                <select class="form-ctrl text-xs h-9 bg-white cursor-pointer" v-model="form.sale_agent">
                  <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Discount Type</label>
                <select class="form-ctrl text-xs h-9 bg-white cursor-pointer" v-model="form.discount_type">
                  <option value="No discount">No discount</option>
                  <option value="Before Tax">Before Tax</option>
                  <option value="After Tax">After Tax</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Admin Note</label>
              <textarea 
                class="form-ctrl text-xs p-2.5 min-h-[72px]" 
                rows="3" 
                v-model="form.admin_note"
                placeholder="Admin note"
              ></textarea>
            </div>
          </div>

        </div>

        <!-- Items Table Container -->
        <div class="space-y-4">
          
          <!-- Add Item header toolbar -->
          <div class="flex justify-between items-center pb-2 border-b">
            <div class="flex items-center space-x-2 w-72">
              <a-select 
                placeholder="Add Item" 
                style="width: 100%" 
                size="large"
                allow-clear
                show-search
                @change="handlePredefinedItemSelect"
              >
                <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                  {{ item.name }} ({{ fmtCur(item.rate) }})
                </a-select-option>
              </a-select>
              <button class="btn-outline h-9 flex items-center justify-center font-bold text-base cursor-pointer" @click="addPredefinedItem">+</button>
            </div>
            
            <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
              <span>Show quantity as:</span>
              <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Qty" v-model="form.qty_show" /> Qty</label>
              <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Hours" v-model="form.qty_show" /> Hours</label>
              <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Qty/Hours" v-model="form.qty_show" /> Qty/Hours</label>
            </div>
          </div>

          <!-- Items Table -->
          <table class="items-table text-xs">
            <thead>
              <tr>
                <th class="w-8 flex items-center justify-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></th>
                <th>Item</th>
                <th>Description</th>
                <th class="w-20">Qty</th>
                <th class="w-24">Rate</th>
                <th class="w-28">Tax</th>
                <th class="w-28 text-right">Amount</th>
                <th class="w-12 text-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="text-slate-400"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></th>
              </tr>
            </thead>
            <tbody>
              <!-- Row to add new items -->
              <tr class="add-item-row bg-slate-50/70">
                <td></td>
                <td class="p-3">
                  <textarea 
                    class="form-ctrl text-xs p-2 min-h-[58px]" 
                    rows="2" 
                    v-model="newItem.name" 
                    placeholder="Description"
                  ></textarea>
                  <label class="flex items-center gap-1.5 mt-2 text-[10px] text-slate-500 font-semibold cursor-pointer">
                    <input type="checkbox" v-model="newItem.optional" />
                    This item is optional
                  </label>
                </td>
                <td class="p-3">
                  <textarea 
                    class="form-ctrl text-xs p-2 min-h-[58px]" 
                    rows="2" 
                    v-model="newItem.long_description" 
                    placeholder="Long description"
                  ></textarea>
                </td>
                <td class="p-3">
                  <input type="number" class="form-ctrl text-xs text-center" v-model="newItem.qty" />
                  <input type="text" class="form-ctrl text-[10px] text-center mt-1.5 placeholder-slate-300" v-model="newItem.unit" placeholder="Unit" />
                </td>
                <td class="p-3">
                  <input type="number" class="form-ctrl text-xs text-right" v-model="newItem.rate" placeholder="Rate" />
                </td>
                <td class="p-3">
                  <select class="form-ctrl text-xs bg-white h-8 cursor-pointer" v-model="newItem.tax">
                    <option :value="0">No Tax</option>
                    <option :value="5">5.00%</option>
                    <option :value="10">10.00%</option>
                    <option :value="18">18.00%</option>
                  </select>
                </td>
                <td class="p-3 text-right font-semibold text-slate-400">
                  {{ fmtCur(newItem.qty * newItem.rate) }}
                </td>
                <td class="p-3 text-center">
                  <button class="add-check-btn bg-slate-800 hover:bg-slate-900 text-white rounded flex items-center justify-center cursor-pointer" @click="addItem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                </td>
              </tr>

              <!-- Listed, added estimate items -->
              <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row">
                <td class="text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="p-3">
                  <div class="font-bold text-slate-800">{{ item.name }}</div>
                  <span v-if="item.optional" class="text-[9px] font-semibold bg-slate-100 text-slate-500 rounded px-1.5 py-0.5 mt-1 inline-block">Optional</span>
                </td>
                <td class="p-3 text-slate-500">{{ item.long_description || '—' }}</td>
                <td class="p-3 font-semibold">{{ item.qty }} <span class="text-[10px] text-slate-400 font-normal">({{ item.unit }})</span></td>
                <td class="p-3">{{ fmtCur(item.rate) }}</td>
                <td class="p-3 text-slate-500">{{ item.tax > 0 ? item.tax + '%' : 'None' }}</td>
                <td class="p-3 text-right font-bold text-slate-700">{{ fmtCur(item.amount) }}</td>
                <td class="p-3 text-center">
                  <button class="text-rose-500 hover:text-rose-700 font-bold text-base hover:bg-rose-50 rounded w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer" @click="removeItem(idx)">×</button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Subtotal / Adjustment / Totals Aligned right -->
          <div class="flex justify-end pt-3">
            <div class="w-72 space-y-2 text-xs">
              <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Sub Total :</span>
                <span class="font-bold text-slate-800">{{ fmtCur(totals.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between items-center py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Discount</span>
                <div class="flex items-center space-x-1.5">
                  <input 
                    type="number" 
                    min="0" 
                    max="100" 
                    class="border rounded text-xs text-right w-14 h-7 pr-1" 
                    v-model="form.discount_percent" 
                  />
                  <select class="border rounded text-[11px] h-7 bg-white text-slate-600 px-1 cursor-pointer" v-model="discountUnit">
                    <option value="%">%</option>
                  </select>
                </div>
                <span class="font-bold text-rose-500">-{{ fmtCur(totals.discount) }}</span>
              </div>

              <div class="flex justify-between items-center py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Adjustment</span>
                <input 
                  type="number" 
                  class="border rounded text-xs text-right w-20 h-7 pr-1" 
                  v-model="form.adjustment" 
                />
                <span class="font-bold text-slate-800">{{ fmtCur(form.adjustment) }}</span>
              </div>

              <div v-if="totals.tax > 0" class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Tax Total:</span>
                <span class="font-bold text-slate-800">{{ fmtCur(totals.tax) }}</span>
              </div>

              <div class="flex justify-between text-sm font-bold text-slate-800 pt-2">
                <span>Total :</span>
                <span class="text-lg text-slate-800 font-extrabold">{{ fmtCur(totals.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Bottom Textareas: Client Note & Terms -->
          <div class="grid grid-cols-1 gap-6 pt-4 border-t">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Client Note</label>
              <textarea 
                class="form-ctrl text-xs p-2.5 min-h-[96px]" 
                rows="4" 
                v-model="form.client_note"
                placeholder="Client note"
              ></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Terms & Conditions</label>
              <textarea 
                class="form-ctrl text-xs p-2.5 min-h-[96px]" 
                rows="4" 
                v-model="form.terms"
                placeholder="Terms and conditions"
              ></textarea>
            </div>
          </div>

        </div>

        <!-- Action Bar Footer -->
        <div class="border-t border-slate-200 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs text-slate-500 rounded-b-lg -mx-6 -mb-6 mt-6">
          <div class="italic">
            Generate and save estimates for clients
          </div>
          <div class="flex items-center space-x-2">
            <button class="btn-outline-sm border rounded px-3 py-1.5 font-bold hover:bg-slate-50 bg-white cursor-pointer" @click="saveAndSend">
              Save & Send
            </button>
            <button class="btn-primary-sm bg-slate-800 hover:bg-slate-900 text-white rounded px-4.5 py-1.5 font-bold cursor-pointer" @click="save">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Address Edit Modal -->
    <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
      <div class="modal-card">
        <div class="modal-head">
          <span class="modal-title font-bold text-slate-800 text-sm">Billing & Shipping Address</span>
          <button class="modal-close cursor-pointer" @click="closeAddressModal">×</button>
        </div>
        <div class="modal-body grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
          <!-- Billing Address Column -->
          <div class="space-y-4">
            <h3 class="font-bold border-b pb-1 text-xs uppercase tracking-wider text-slate-400">Billing Address</h3>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Street</label>
              <textarea class="form-ctrl text-xs p-2" rows="2" v-model="addressForm.billing_street" placeholder="Street"></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.billing_city" placeholder="City" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.billing_state" placeholder="State" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Zip Code</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.billing_zip" placeholder="Zip Code" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.billing_country" placeholder="Country" />
            </div>
          </div>

          <!-- Shipping Address Column -->
          <div class="space-y-4">
            <div class="flex justify-between items-center border-b pb-1">
              <h3 class="font-bold text-xs uppercase tracking-wider text-slate-400">Shipping Address</h3>
              <label class="flex items-center gap-1.5 cursor-pointer text-xs font-bold text-slate-600">
                <input type="checkbox" v-model="addressForm.show_shipping_details" class="cursor-pointer" />
                Show shipping details in estimate
              </label>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Street</label>
              <textarea class="form-ctrl text-xs p-2" rows="2" v-model="addressForm.shipping_street" :disabled="!addressForm.show_shipping_details" placeholder="Street"></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.shipping_city" :disabled="!addressForm.show_shipping_details" placeholder="City" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.shipping_state" :disabled="!addressForm.show_shipping_details" placeholder="State" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Zip Code</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.shipping_zip" :disabled="!addressForm.show_shipping_details" placeholder="Zip Code" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
              <input type="text" class="form-ctrl text-xs h-8" v-model="addressForm.shipping_country" :disabled="!addressForm.show_shipping_details" placeholder="Country" />
            </div>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn-ghost cursor-pointer" @click="closeAddressModal">Cancel</button>
          <button class="btn-primary cursor-pointer bg-slate-800 hover:bg-slate-900 text-white rounded px-4 py-1.5 font-bold" @click="saveAddresses">Apply</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useEstimatesStore } from '../../store/estimatesStore';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'EstimateForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const estimatesStore = useEstimatesStore();

    const editMode = ref(false);
    const estimateId = ref(null);
    const estimateNumberSuffix = ref('');
    const discountUnit = ref('%');

    const clients = ref([]);

    const loadClients = async () => {
      try {
        const res = await axios.get('/clients', { params: { per_page: 500 } });
        clients.value = res.data.clients?.data || res.data || [];
        
        // If client_id is passed as query parameter, auto pre-fill
        if (route.query.client_id) {
          const matchedClient = clients.value.find(c => c.id === Number(route.query.client_id));
          if (matchedClient) {
            form.value.client = matchedClient.company;
            handleClientChange(matchedClient.company);
          }
        }
      } catch (err) {
        console.error('Failed to load clients list', err);
      }
    };

    const agentOptions = [
      'Tom Kunze',
      'Armando Turcotte',
      'Elias Konopelski',
      'Tamara Howell',
      'Marcus Lesch',
      'Rosie Trantow'
    ];

    const predefinedItems = [
      { name: 'Consulting SLA Services', rate: 1600 },
      { name: 'App Development Module v1.0', rate: 5800 },
      { name: 'Cloud Infrastructure Audit Pack', rate: 12400 },
      { name: 'UI/UX Interactive Mockup Designs', rate: 2100 }
    ];

    const selectedPredefined = ref('');

    const form = ref({
      client: '',
      date: '2026-06-15',
      expiry: '2026-06-22',
      currency: 'USD',
      discount_type: 'No discount',
      tags: '',
      status: 'draft',
      sale_agent: 'Tom Kunze',
      reference_no: '',
      admin_note: '',
      client_note: '',
      terms: 'Mouse had changed his mind, and was immediately suppressed by the time they were playing the Queen ordering off her knowledge, as there was the Rabbit in a great letter, nearly as large as the other. As soon as there was enough of me left to make myself useful, and looking anxiously round to see.',
      qty_show: 'Qty',
      items: [],
      discount_percent: 0,
      adjustment: 0,
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      show_shipping_details: true,
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: ''
    });

    const newItem = ref({
      name: '',
      long_description: '',
      qty: 1,
      unit: 'Unit',
      rate: 0,
      tax: 0,
      optional: false
    });

    const handleClientChange = (companyName) => {
      const c = clients.value.find(cl => cl.company === companyName);
      if (c) {
        form.value.billing_street = c.address || '';
        form.value.billing_city = c.city || '';
        form.value.billing_state = c.state || '';
        form.value.billing_zip = c.zip || '';
        form.value.billing_country = c.country || '';
        
        form.value.shipping_street = c.address || '';
        form.value.shipping_city = c.city || '';
        form.value.shipping_state = c.state || '';
        form.value.shipping_zip = c.zip || '';
        form.value.shipping_country = c.country || '';
      }
    };

    const addItem = () => {
      if (!newItem.value.name) {
        message.warning('Please input the Item name.');
        return;
      }
      
      const qty = Number(newItem.value.qty || 1);
      const rate = Number(newItem.value.rate || 0);
      const amount = qty * rate;

      form.value.items.push({
        name: newItem.value.name,
        long_description: newItem.value.long_description,
        qty,
        unit: newItem.value.unit || 'Unit',
        rate,
        tax: Number(newItem.value.tax || 0),
        amount,
        optional: newItem.value.optional
      });

      newItem.value = {
        name: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax: 0,
        optional: false
      };
      message.success('Item added to estimate.');
    };

    const removeItem = (idx) => {
      form.value.items.splice(idx, 1);
      message.info('Item removed.');
    };

    const handlePredefinedItemSelect = (val) => {
      selectedPredefined.value = val;
    };

    const addPredefinedItem = () => {
      if (!selectedPredefined.value) {
        message.warning('Select a predefined item first.');
        return;
      }
      const match = predefinedItems.find(i => i.name === selectedPredefined.value);
      if (match) {
        form.value.items.push({
          name: match.name,
          long_description: 'Predefined system service item',
          qty: 1,
          unit: 'Unit',
          rate: match.rate,
          tax: 0,
          amount: match.rate,
          optional: false
        });
        message.success('Predefined item loaded.');
      }
    };

    const totals = computed(() => {
      let subtotal = 0;
      let taxTotal = 0;

      form.value.items.forEach(item => {
        const itemSub = item.qty * item.rate;
        subtotal += itemSub;
        if (item.tax > 0) {
          taxTotal += itemSub * (item.tax / 100);
        }
      });

      const discPct = Number(form.value.discount_percent || 0);
      const discountAmt = subtotal * (discPct / 100);
      const adjustmentAmt = Number(form.value.adjustment || 0);

      const grandTotal = Math.max(0, subtotal - discountAmt + taxTotal + adjustmentAmt);

      return {
        subtotal,
        discount: discountAmt,
        tax: taxTotal,
        total: grandTotal
      };
    });

    const validateForm = () => {
      if (!form.value.client) {
        message.error('Client selection is required.');
        return false;
      }
      if (!estimateNumberSuffix.value) {
        message.error('Estimate Number is required.');
        return false;
      }
      return true;
    };

    const parseDate = (dStr) => {
      if (!dStr) return '';
      const d = new Date(dStr);
      if (isNaN(d.getTime())) return '';
      return d.toISOString().split('T')[0];
    };

    const formatDate = (dStr) => {
      if (!dStr) return '';
      const d = new Date(dStr);
      if (isNaN(d.getTime())) return dStr;
      return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    };

    const cancelLink = computed(() => {
      const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
      const cId = route.query.client_id || matchedClient?.id;
      if (cId) {
        return { name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } };
      }
      return '/admin/estimates';
    });

    const save = () => {
      if (!validateForm()) return;

      const payload = {
        ...form.value,
        number: `EST-${estimateNumberSuffix.value}`,
        amount: totals.value.total,
        date: formatDate(form.value.date),
        expiry: formatDate(form.value.expiry)
      };

      if (editMode.value) {
        estimatesStore.updateEstimate(estimateId.value, payload);
        message.success('Estimate updated successfully.');
      } else {
        estimatesStore.addEstimate(payload);
        message.success('Estimate created successfully.');
      }
      
      const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
      const cId = route.query.client_id || matchedClient?.id;
      if (cId) {
        router.push({ name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } });
      } else {
        router.push('/admin/estimates');
      }
    };

    const saveAndSend = () => {
      if (!validateForm()) return;
      
      const payload = {
        ...form.value,
        number: `EST-${estimateNumberSuffix.value}`,
        amount: totals.value.total,
        status: 'sent',
        date: formatDate(form.value.date),
        expiry: formatDate(form.value.expiry)
      };

      if (editMode.value) {
        estimatesStore.updateEstimate(estimateId.value, payload);
      } else {
        estimatesStore.addEstimate(payload);
      }
      message.loading('Sending email to recipient...', 1.5).then(() => {
        message.success('Estimate saved & successfully emailed to client.');
        
        const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
        const cId = route.query.client_id || matchedClient?.id;
        if (cId) {
          router.push({ name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } });
        } else {
          router.push('/admin/estimates');
        }
      });
    };

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    const showAddressModal = ref(false);
    const addressForm = ref({
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      show_shipping_details: true,
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: ''
    });

    const openAddressModal = () => {
      addressForm.value = {
        billing_street: form.value.billing_street || '',
        billing_city: form.value.billing_city || '',
        billing_state: form.value.billing_state || '',
        billing_zip: form.value.billing_zip || '',
        billing_country: form.value.billing_country || '',
        show_shipping_details: form.value.show_shipping_details !== undefined ? form.value.show_shipping_details : true,
        shipping_street: form.value.shipping_street || '',
        shipping_city: form.value.shipping_city || '',
        shipping_state: form.value.shipping_state || '',
        shipping_zip: form.value.shipping_zip || '',
        shipping_country: form.value.shipping_country || ''
      };
      showAddressModal.value = true;
    };

    const closeAddressModal = () => {
      showAddressModal.value = false;
    };

    const saveAddresses = () => {
      form.value.billing_street = addressForm.value.billing_street;
      form.value.billing_city = addressForm.value.billing_city;
      form.value.billing_state = addressForm.value.billing_state;
      form.value.billing_zip = addressForm.value.billing_zip;
      form.value.billing_country = addressForm.value.billing_country;
      form.value.show_shipping_details = addressForm.value.show_shipping_details;
      form.value.shipping_street = addressForm.value.shipping_street;
      form.value.shipping_city = addressForm.value.shipping_city;
      form.value.shipping_state = addressForm.value.shipping_state;
      form.value.shipping_zip = addressForm.value.shipping_zip;
      form.value.shipping_country = addressForm.value.shipping_country;
      showAddressModal.value = false;
      message.success('Billing & Shipping addresses updated.');
    };

    onMounted(async () => {
      await loadClients();
      if (route.params.id) {
        editMode.value = true;
        estimateId.value = parseInt(route.params.id);
        
        const match = estimatesStore.estimates.find(e => e.id === estimateId.value);
        if (match) {
          form.value = JSON.parse(JSON.stringify(match));
          if (form.value.show_shipping_details === undefined) {
            form.value.show_shipping_details = true;
          }
          form.value.date = parseDate(match.date);
          form.value.expiry = parseDate(match.expiry);
          if (match.number && match.number.startsWith('EST-')) {
            estimateNumberSuffix.value = match.number.replace('EST-', '');
          } else {
            estimateNumberSuffix.value = match.number || '';
          }
        } else {
          message.error('Estimate details not found.');
          router.push('/admin/estimates');
        }
      } else {
        if (!route.query.client_id && clients.value.length > 0) {
          form.value.client = clients.value[0].company;
          handleClientChange(clients.value[0].company);
        }
        const nextId = estimatesStore.estimates.length > 0 ? Math.max(...estimatesStore.estimates.map(e => e.id)) + 1 : 1;
        estimateNumberSuffix.value = String(nextId).padStart(6, '0');
      }
    });

    return {
      form,
      newItem,
      addItem,
      removeItem,
      predefinedItems,
      handlePredefinedItemSelect,
      addPredefinedItem,
      totals,
      clients,
      agentOptions,
      save,
      saveAndSend,
      editMode,
      fmtCur,
      discountUnit,
      handleClientChange,
      estimateNumberSuffix,
      showAddressModal,
      addressForm,
      openAddressModal,
      closeAddressModal,
      saveAddresses,
      cancelLink
    };
  }
});
</script>

<style scoped>
.estimate-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

.form-ctrl {
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 6px 12px;
  width: 100%;
  color: #334155;
  outline: none;
  font-family: inherit;
  transition: border-color 0.12s;
}
.form-ctrl:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f650;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  border: 1px solid #e2e8f0;
}
.items-table th {
  background: #f8fafc;
  padding: 10px 12px;
  font-size: 11.5px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: .02em;
  text-align: left;
  border-bottom: 2px solid #e2e8f0;
}
.items-table td {
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
  padding: 8px 12px;
}

.add-check-btn {
  width: 32px;
  height: 32px;
  border: none;
  cursor: pointer;
  margin: 0 auto;
}
.add-check-btn:hover {
  background: #0f172a;
}

button {
  cursor: pointer;
}

/* Modal overlay and card styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-card {
  background: #ffffff;
  border-radius: 8px;
  width: 100%;
  max-width: 640px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 2rem);
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.modal-head {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
}

.modal-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 20px;
  font-weight: 700;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}
.modal-close:hover {
  color: #475569;
}

.modal-body {
  padding: 20px;
  overflow-y: auto;
}

.modal-foot {
  padding: 12px 20px;
  border-t: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  background: #f8fafc;
}

.btn-ghost {
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  border-radius: 4px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}
.btn-ghost:hover {
  background: #f8fafc;
  color: #1e293b;
}
</style>
