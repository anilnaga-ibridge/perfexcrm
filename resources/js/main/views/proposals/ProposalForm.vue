<template>
  <div class="proposal-form-page space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link to="/admin/proposals" class="hover:text-indigo-600 font-semibold">Proposals</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ editMode ? 'Edit Proposal' : 'New Proposal' }}</span>
      </div>
      <router-link to="/admin/proposals" class="text-xs text-indigo-600 hover:underline">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
      <!-- Title Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h2 class="text-base font-bold text-slate-800">
          {{ editMode ? 'Edit Proposal #' + (form.number || '') : 'New Proposal' }}
        </h2>
      </div>

      <div class="p-6 space-y-6">
        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- Left Column -->
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span class="text-rose-500">*</span> Subject
              </label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-9" 
                v-model="form.subject" 
                placeholder="Proposal Title/Subject" 
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span class="text-rose-500">*</span> Related
              </label>
              <select class="form-ctrl text-xs h-9 bg-white" v-model="form.to">
                <option value="" disabled>Nothing selected</option>
                <option v-for="target in targets" :key="target" :value="target">{{ target }}</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  <span class="text-rose-500">*</span> Date
                </label>
                <div class="relative">
                  <input type="date" class="form-ctrl text-xs h-9 pr-8" v-model="form.date" />
                </div>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Open Till</label>
                <div class="relative">
                  <input type="date" class="form-ctrl text-xs h-9 pr-8" v-model="form.open_till" />
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  <span class="text-rose-500">*</span> Currency
                </label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.currency">
                  <option value="USD">USD $</option>
                  <option value="EUR">EUR €</option>
                  <option value="GBP">GBP £</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Discount Type</label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.discount_type">
                  <option value="No discount">No discount</option>
                  <option value="Before Tax">Before Tax</option>
                  <option value="After Tax">After Tax</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tags</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-9" 
                v-model="form.tags" 
                placeholder="Tag" 
              />
            </div>

            <div class="pt-2">
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Allow Comments</label>
              <a-switch v-model:checked="form.allow_comments" size="default" />
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.status">
                  <option value="draft">Draft</option>
                  <option value="open">Open</option>
                  <option value="sent">Sent</option>
                  <option value="accepted">Accepted</option>
                  <option value="declined">Declined</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Assigned</label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.assigned">
                  <option v-for="staff in staffOptions" :key="staff" :value="staff">{{ staff }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                <span class="text-rose-500">*</span> To
              </label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-9" 
                v-model="form.recipient_name" 
                placeholder="Customer or recipient name"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Address</label>
              <textarea 
                class="form-ctrl text-xs p-2.5 min-h-[96px]" 
                rows="4" 
                v-model="form.address"
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-9" v-model="form.city" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-9" v-model="form.state" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.country">
                  <option value="">Nothing selected</option>
                  <option value="United States">United States</option>
                  <option value="United Kingdom">United Kingdom</option>
                  <option value="Germany">Germany</option>
                  <option value="France">France</option>
                  <option value="Japan">Japan</option>
                  <option value="Australia">Australia</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-9" v-model="form.zip" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  <span class="text-rose-500">*</span> Email
                </label>
                <input type="email" class="form-ctrl text-xs h-9" v-model="form.email" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Phone</label>
                <input type="text" class="form-ctrl text-xs h-9" v-model="form.phone" />
              </div>
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
              <!-- Row to add new items (matches image layout) -->
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
                  <select class="form-ctrl text-xs bg-white h-8" v-model="newItem.tax">
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
                  <button class="add-check-btn bg-slate-800 hover:bg-slate-900 text-white rounded flex items-center justify-center" @click="addItem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                </td>
              </tr>

              <!-- Listed, added proposal items -->
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
                  <select class="border rounded text-[11px] h-7 bg-white text-slate-600 px-1" v-model="discountUnit">
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

        </div>

        <!-- Action Bar Footer -->
        <div class="border-t border-slate-200 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs text-slate-500 rounded-b-lg -mx-6 -mb-6 mt-6">
          <div class="italic">
            Include proposal items with merge field anywhere in proposal content as <strong>{proposal_items}</strong>
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
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProposalsStore } from '../../store/proposalsStore';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'ProposalForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const proposalsStore = useProposalsStore();

    const editMode = ref(false);
    const proposalId = ref(null);
    const discountUnit = ref('%');

    // Default Client Options
    const targets = ref([
      'Nader-Abernathy', 'Mertz-Bergnaum', 'Schroeder and Sons', 'Bayer Group',
      'Halvorson LLC', 'Kub Group', 'Morar-Runte', 'Torphy and Partners',
      'John Doe (Lead)', 'Sarah Connor (Lead)', 'Bruce Wayne (Lead)'
    ]);

    // Staff Dropdown Options
    const staffOptions = [
      'Tom Kunze',
      'Armando Turcotte',
      'Elias Konopelski',
      'Tamara Howell',
      'Marcus Lesch',
      'Rosie Trantow'
    ];

    // Predefined items dropdown template values
    const predefinedItems = [
      { name: 'Consulting SLA Services', rate: 1500 },
      { name: 'App Development Module v1.0', rate: 8500 },
      { name: 'Cloud Infrastructure Audit Pack', rate: 3200 },
      { name: 'UI/UX Interactive Mockup Designs', rate: 2400 }
    ];

    const selectedPredefined = ref('');

    // Master Form state matching screenshot pre-fills
    const form = ref({
      subject: '',
      to: '',
      date: '2026-06-15',
      open_till: '2026-06-22',
      currency: 'USD',
      discount_type: 'No discount',
      tags: '',
      allow_comments: true,
      status: 'draft',
      assigned: 'Tom Kunze',
      recipient_name: '',
      address: '',
      city: '',
      state: '',
      country: '',
      zip: '',
      email: '',
      phone: '',
      qty_show: 'Qty',
      items: [],
      discount_percent: 0,
      adjustment: 0
    });

    // Sub-item template state
    const newItem = ref({
      name: '',
      long_description: '',
      qty: 1,
      unit: 'Unit',
      rate: 0,
      tax: 0,
      optional: false
    });

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

      // Clear new item state
      newItem.value = {
        name: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax: 0,
        optional: false
      };
      message.success('Item added to proposal.');
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

    // Live Totals calculation
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
      if (!form.value.subject) {
        message.error('Subject is required.');
        return false;
      }
      if (!form.value.to) {
        message.error('Related Client/Lead must be selected.');
        return false;
      }
      if (!form.value.recipient_name) {
        message.error('Recipient Name is required.');
        return false;
      }
      if (!form.value.email) {
        message.error('Recipient Email is required.');
        return false;
      }
      return true;
    };

    const save = () => {
      if (!validateForm()) return;

      const payload = {
        ...form.value,
        amount: totals.value.total
      };

      if (editMode.value) {
        proposalsStore.updateProposal(proposalId.value, payload);
        message.success('Proposal updated successfully.');
      } else {
        proposalsStore.addProposal(payload);
        message.success('Proposal created successfully.');
      }

      const relId = route.query.rel_id;
      if (relId) {
        router.push({ name: 'admin.customers.view', params: { id: relId }, query: { tab: 'proposals' } });
      } else {
        router.push('/admin/proposals');
      }
    };

    const saveAndSend = () => {
      if (!validateForm()) return;
      
      const payload = {
        ...form.value,
        amount: totals.value.total,
        status: 'sent'
      };

      if (editMode.value) {
        proposalsStore.updateProposal(proposalId.value, payload);
      } else {
        proposalsStore.addProposal(payload);
      }
      message.loading('Sending email to recipient...', 1.5).then(() => {
        message.success('Proposal saved & successfully emailed to recipient.');
        const relId = route.query.rel_id;
        if (relId) {
          router.push({ name: 'admin.customers.view', params: { id: relId }, query: { tab: 'proposals' } });
        } else {
          router.push('/admin/proposals');
        }
      });
    };

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    onMounted(async () => {
      if (route.params.id) {
        editMode.value = true;
        proposalId.value = parseInt(route.params.id);
        
        const match = proposalsStore.proposals.find(p => p.id === proposalId.value);
        if (match) {
          form.value = JSON.parse(JSON.stringify(match));
        } else {
          message.error('Proposal details not found.');
          router.push('/admin/proposals');
        }
      } else {
        const relId = route.query.rel_id;
        const relType = route.query.rel_type || 'customer';
        if (relId && relType === 'customer') {
          try {
            const res = await axios.get(`/clients/${relId}`);
            const client = res.data;
            if (client) {
              if (!targets.value.includes(client.company)) {
                targets.value.push(client.company);
              }
              form.value.to = client.company;
              form.value.recipient_name = client.company;
              form.value.address = client.address || '';
              form.value.city = client.city || '';
              form.value.state = client.state || '';
              form.value.country = client.country || '';
              form.value.zip = client.zip || '';
              form.value.email = client.email || '';
              form.value.phone = client.phone || '';
            }
          } catch (err) {
            console.error('Failed to load client details for prefill', err);
          }
        } else {
          // Pre-fill related values when related select changes
          form.value.to = targets.value[0];
        }
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
      targets,
      staffOptions,
      save,
      saveAndSend,
      editMode,
      fmtCur,
      discountUnit
    };
  }
});
</script>

<style scoped>
.proposal-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

/* Styles aligned to mirror Perfex CRM Form fields */
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

.btn-primary {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 6px 16px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  transition: background 0.12s;
}
.btn-primary:hover {
  background: #1d4ed8;
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

/* Save / Action Bar Buttons */
.btn-primary-sm {
  background: #1e293b;
  color: #fff;
  border: 1px solid #1e293b;
  border-radius: 4px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-primary-sm:hover {
  background: #0f172a;
}
.btn-outline-sm {
  background: #fff;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.12s;
}
.btn-outline-sm:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #1e293b;
}
</style>
