<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Expenses Categories</h2>
      <button class="btn-primary" @click="openNewCategoryDrawer">New Category</button>
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
          :dataSource="filteredCategories"
          :columns="categoryColumns"
          :pagination="{ pageSize: pageSize, total: filteredCategories.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="category-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <span class="category-name">{{ record.name }}</span>
            </template>
            <template v-if="column.key === 'description'">
              <span class="text-muted">{{ record.description }}</span>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editCategory(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteCategory(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      placement="right"
      :width="500"
      :footer-style="{ padding: '16px 24px', background: '#fafafa', borderTop: '1px solid #f1f5f9' }"
      :header-style="{ padding: '20px 24px', background: '#ffffff', borderBottom: '1px solid #f1f5f9' }"
      @close="resetForm"
    >
      <template #title>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl text-white flex items-center justify-center shadow-md shrink-0 theme-primary-grad">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M7 7h10M7 12h10M7 17h10M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-base font-extrabold text-slate-800 m-0 leading-snug">{{ editingId ? 'Edit Category' : 'New Category' }}</h3>
            <p class="text-xs text-slate-400 font-medium m-0 mt-0.5 leading-normal">Configure expense category details & description</p>
          </div>
        </div>
      </template>

      <form @submit.prevent="saveCategory" class="space-y-5 p-1">
        <!-- Category Name -->
        <div>
          <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-1.5">
            Category Name <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.name"
            placeholder="Enter category name..."
            class="w-full h-11 px-4 text-xs font-semibold theme-input-ctrl text-slate-800"
            required
          />
        </div>

        <!-- Category Description Input Box -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider">
              Category Description
            </label>
            <!-- Preset Helper Chips -->
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-all cursor-pointer"
                @click="insertStandardDesc"
                title="Insert Standard Expenses Description"
              >
                + Standard
              </button>
              <button
                type="button"
                class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-all cursor-pointer"
                @click="insertOperatingDesc"
                title="Insert Operating Expenses Description"
              >
                + Operating
              </button>
            </div>
          </div>

          <textarea
            v-model="form.description"
            rows="6"
            placeholder="Enter detailed category description, accounting notes, or expense guidelines..."
            class="w-full p-4 text-xs font-semibold theme-input-ctrl text-slate-800 placeholder-slate-400 resize-y leading-relaxed"
          ></textarea>
        </div>
      </form>

      <template #footer>
        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all cursor-pointer border border-slate-200/80"
            @click="openDrawer = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-7 py-2.5 text-xs font-bold text-white rounded-xl cursor-pointer shadow-md transition-all flex items-center gap-2 theme-primary-grad"
            @click="saveCategory"
            :disabled="saving"
          >
            <svg v-if="saving" class="animate-spin" fill="none" viewBox="0 0 24 24" width="14" height="14"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editingId ? 'Update Category' : 'Add Category') }}
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ExpensesCategoriesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const expenseCategories = ref([
      { id: 2, name: 'Automobile Expense', description: 'Cat, and vanished again. Alice waited till she too began dreaming after a few yards off. The Cat.' },
      { id: 5, name: 'Insurance', description: 'King very decidedly, and the little dears came jumping merrily along hand in hand with Dinah, and.' },
      { id: 8, name: 'IT and Internet Expenses', description: 'I eat or drink something or other; but the Hatter said, tossing his head contemptuously. \'I dare.' },
      { id: 6, name: 'Meals', description: 'Dodo had paused as if he had a little scream, half of fright and half believed herself in the.' },
      { id: 3, name: 'Parking', description: 'Mock Turtle, capering wildly about. \'Change lobsters again!\' yelled the Gryphon answered, very.' },
      { id: 4, name: 'Telephone', description: 'Alice. \'Nothing WHATEVER?\' persisted the King. \'Then it wasn\'t very civil of you to get in?\'.' },
      { id: 7, name: 'Travel Expense', description: 'I\'m angry. Therefore I\'m mad.\' \'I call it sad?\' And she began nursing her child again, singing a.' },
      { id: 1, name: 'Trips', description: 'Five, \'and I\'ll tell you what year it is?\' \'Of course twinkling begins with an M, such as.' }
    ]);

    const form = reactive({
      name: '',
      description: ''
    });

    const categoryColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Name', dataIndex: 'name', key: 'name' },
      { title: 'Description', dataIndex: 'description', key: 'description' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredCategories = computed(() => {
      // Sort alphabetically by Name
      const sorted = [...expenseCategories.value].sort((a, b) => a.name.localeCompare(b.name));
      if (!search.value) return sorted;
      return sorted.filter(c => 
        c.name.toLowerCase().includes(search.value.toLowerCase()) ||
        c.description.toLowerCase().includes(search.value.toLowerCase())
      );
    });

    const openNewCategoryDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editCategory = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.description = record.description || '';
      openDrawer.value = true;
    };

    const deleteCategory = (id) => {
      expenseCategories.value = expenseCategories.value.filter(c => c.id !== id);
      message.success('Expense category deleted');
    };

    const saveCategory = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = expenseCategories.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.description = form.description.trim();
          }
          message.success('Expense category updated');
        } else {
          const maxId = expenseCategories.value.reduce((max, c) => c.id > max ? c.id : max, 0);
          expenseCategories.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            description: form.description.trim()
          });
          message.success('Expense category added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving expense category');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.description = '';
    };

    const insertStandardDesc = () => {
      const template = `Standard recurring business expenses including general operational costs, utility bills, and routine maintenance items.`;
      form.description = form.description ? form.description + '\n\n' + template : template;
      message.info('Standard description inserted');
    };

    const insertOperatingDesc = () => {
      const template = `Core operating expenses related to daily workflow execution, software subscriptions, communication, and administrative overhead.`;
      form.description = form.description ? form.description + '\n\n' + template : template;
      message.info('Operating description inserted');
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      expenseCategories,
      form,
      categoryColumns,
      filteredCategories,
      openNewCategoryDrawer,
      editCategory,
      deleteCategory,
      saveCategory,
      resetForm,
      insertStandardDesc,
      insertOperatingDesc
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
.category-id {
  color: #64748b;
  font-family: monospace;
}
.category-name {
  font-weight: 600;
  color: #1e293b;
}
.text-muted {
  color: #64748b;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}

/* Dynamic Theme Utility Classes */
.theme-primary-btn {
  background: var(--theme-primary, #6366f1) !important;
  color: #ffffff !important;
}
.theme-primary-btn:hover {
  background: var(--theme-primary-hover, #4f46e5) !important;
}
.theme-primary-grad {
  background: linear-gradient(135deg, var(--theme-primary, #6366f1) 0%, var(--theme-primary-hover, #4f46e5) 100%) !important;
  color: #ffffff !important;
}
.theme-input-ctrl {
  background-color: rgba(248, 250, 252, 0.8);
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}
.theme-input-ctrl:focus {
  background-color: #ffffff;
  border-color: var(--theme-primary, #6366f1) !important;
  box-shadow: 0 0 0 4px var(--theme-primary-light, rgba(99, 102, 241, 0.15)) !important;
  outline: none;
}
</style>
