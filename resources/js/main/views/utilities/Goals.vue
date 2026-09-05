<template>
  <div class="goals-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Goals Tracking</h1>
        <p class="text-xs text-slate-500 mt-0.5">Define business goals and monitor performance achievements</p>
      </div>
      <button v-if="canCreate" class="btn-primary" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14" class="inline mr-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Goal
      </button>
    </div>

    <div class="goals-card">
      <div class="goals-toolbar">
        <a-input v-model:value="search" placeholder="Search..." style="width:260px" allow-clear>
          <template #prefix>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </template>
        </a-input>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="filteredGoals"
        :columns="columns"
        :loading="loading"
        :pagination="{ pageSize: 10, showSizeChanger: true }"
        rowKey="id"
        size="middle"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'staff_member'">
            {{ record.staff?.name || record.staff_member_name || '—' }}
          </template>
          <template v-else-if="column.key === 'achievement'">
            {{ formatValue(record.current_value) }} / {{ formatValue(record.target_value) }}
          </template>
          <template v-else-if="column.key === 'goal_type'">
            {{ formatGoalType(record.goal_type) }}
          </template>
          <template v-else-if="column.key === 'progress'">
            <div class="progress-cell">
              <div class="progress-bar-bg">
                <div class="progress-bar-fill" :style="{ width: getPercentage(record) + '%' }" :class="progressClass(record)"></div>
              </div>
              <span class="progress-pct">{{ getPercentage(record) }}%</span>
            </div>
          </template>
          <template v-else-if="column.key === 'actions'">
            <div class="flex items-center gap-1">
              <a-button type="link" size="small" @click="openViewModal(record)">View</a-button>
              <a-button v-if="canEdit" type="link" size="small" @click="openEditDrawer(record)">Edit</a-button>
              <a-popconfirm v-if="canDelete" title="Delete this goal?" @confirm="deleteGoal(record.id)">
                <a-button type="link" size="small" danger>Delete</a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list" v-if="!loading">
        <div 
          v-for="g in filteredGoals" 
          :key="'m-gl-' + g.id"
          class="mobile-row-card"
          @click="openViewModal(g)"
        >
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
              {{ formatGoalType(g.goal_type) }}
            </span>
            <div class="flex items-center gap-2">
              <button @click.stop="openViewModal(g)" class="text-xs font-semibold text-indigo-600 hover:underline">View</button>
              <button v-if="canEdit" @click.stop="openEditDrawer(g)" class="text-xs font-semibold text-slate-600 hover:underline">Edit</button>
            </div>
          </div>

          <div class="font-bold text-sm text-slate-800 pt-1">
            {{ g.subject }}
          </div>
          <div class="text-xs text-slate-500">
            👤 {{ g.staff?.name || g.staff_member_name || 'All Staff' }}
          </div>

          <div class="space-y-1.5 pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs text-slate-600">
              <span>Achievement:</span>
              <span class="font-bold text-slate-800">{{ formatValue(g.current_value) }} / {{ formatValue(g.target_value) }}</span>
            </div>
            <div class="progress-cell">
              <div class="progress-bar-bg flex-1">
                <div class="progress-bar-fill" :style="{ width: getPercentage(g) + '%' }" :class="progressClass(g)"></div>
              </div>
              <span class="progress-pct font-bold text-xs">{{ getPercentage(g) }}%</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
              <span>📅 Start: {{ g.start_date }}</span>
              <span>End: {{ g.end_date }}</span>
            </div>
          </div>
        </div>

        <div v-if="!filteredGoals.length" class="text-center p-6 text-slate-400 text-xs font-semibold">
          No goals found
        </div>
      </div>
    </div>

    <!-- View Goal Details Modal -->
    <a-modal
      v-model:open="showViewModal"
      title="Goal Details"
      :footer="null"
      :width="520"
      centered
    >
      <div v-if="viewGoalData" class="space-y-4 pt-1">
        <!-- Header Summary -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-indigo-600 bg-indigo-100/70 px-2.5 py-0.5 rounded-full">
              {{ formatGoalType(viewGoalData.goal_type) }}
            </span>
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full" :class="getPercentage(viewGoalData) >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
              {{ getPercentage(viewGoalData) >= 100 ? 'Achieved' : 'In Progress' }}
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-800 m-0">{{ viewGoalData.subject }}</h3>
          <p class="text-xs text-slate-500 mt-1 mb-0" v-if="viewGoalData.description">{{ viewGoalData.description }}</p>
        </div>

        <!-- Achievement Progress Card -->
        <div class="border border-slate-200 rounded-xl p-4 space-y-2">
          <div class="flex items-center justify-between text-xs text-slate-600">
            <span class="font-medium">Achievement Progress</span>
            <span class="font-bold text-slate-800">{{ formatValue(viewGoalData.current_value) }} / {{ formatValue(viewGoalData.target_value) }}</span>
          </div>
          <div class="progress-cell">
            <div class="progress-bar-bg flex-1" style="height: 10px; border-radius: 5px;">
              <div class="progress-bar-fill" :style="{ width: getPercentage(viewGoalData) + '%' }" :class="progressClass(viewGoalData)"></div>
            </div>
            <span class="progress-pct text-xs font-bold">{{ getPercentage(viewGoalData) }}%</span>
          </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
            <span class="text-slate-400 block mb-0.5">Assigned Staff</span>
            <span class="font-semibold text-slate-700">{{ viewGoalData.staff?.name || viewGoalData.staff_member_name || 'All Staff Members' }}</span>
          </div>
          <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
            <span class="text-slate-400 block mb-0.5">Timeline</span>
            <span class="font-semibold text-slate-700">{{ viewGoalData.start_date }} → {{ viewGoalData.end_date }}</span>
          </div>
        </div>

        <!-- Notification Preferences -->
        <div class="border-t border-slate-100 pt-3 space-y-2 text-xs">
          <div class="font-semibold text-slate-700">Notification Preferences:</div>
          <div class="flex items-center gap-2 text-slate-600">
            <span class="w-2 h-2 rounded-full" :class="viewGoalData.notify_when_achieve ? 'bg-emerald-500' : 'bg-slate-300'"></span>
            <span>Notify staff members when goal achieve: <strong>{{ viewGoalData.notify_when_achieve ? 'Yes' : 'No' }}</strong></span>
          </div>
          <div class="flex items-center gap-2 text-slate-600">
            <span class="w-2 h-2 rounded-full" :class="viewGoalData.notify_when_fail ? 'bg-emerald-500' : 'bg-slate-300'"></span>
            <span>Notify staff members when goal failed: <strong>{{ viewGoalData.notify_when_fail ? 'Yes' : 'No' }}</strong></span>
          </div>
        </div>

        <!-- Footer actions -->
        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
          <a-button @click="showViewModal = false">Close</a-button>
          <a-button v-if="canEdit" type="primary" @click="switchToEdit(viewGoalData)">
            Edit Goal
          </a-button>
        </div>
      </div>
    </a-modal>

    <!-- Create / Edit Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Goal' : 'Add New Goal'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="goalForm" @finish="saveGoal">
        <a-form-item label="Subject" name="subject" :rules="[{ required: true, message: 'Subject is required' }]">
          <a-input v-model:value="goalForm.subject" placeholder="Goal subject" />
        </a-form-item>

        <a-form-item label="Goal Type" name="goal_type" :rules="[{ required: true, message: 'Goal type is required' }]">
          <a-select v-model:value="goalForm.goal_type" style="width:100%">
            <a-select-option value="invoice_amount">Invoice Total Amount ($)</a-select-option>
            <a-select-option value="lead_conversion">Lead Conversions (count)</a-select-option>
            <a-select-option value="tickets_solved">Support Tickets Solved (count)</a-select-option>
            <a-select-option value="payments_amount">Recorded Payments Total ($)</a-select-option>
            <a-select-option value="customer_number">Increase Customer Number</a-select-option>
            <a-select-option value="contracts_by_type">Make Contracts By Type</a-select-option>
            <a-select-option value="total_income">Achieve Total Income</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Staff Member" name="staff_member">
          <a-select v-model:value="goalForm.staff_member" style="width:100%" allow-clear placeholder="All staff members">
            <a-select-option v-for="s in staffMembers" :key="s.id" :value="s.id">{{ s.name }}</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Achievement">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <a-input-number v-model:value="goalForm.target_value" :min="0" style="width:100%" placeholder="Target" />
            <a-input-number v-model:value="goalForm.current_value" :min="0" style="width:100%" placeholder="Current" />
          </div>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Start Date" name="start_date" :rules="[{ required: true, message: 'Required' }]">
            <a-date-picker v-model:value="goalForm.start_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>

          <a-form-item label="End Date" name="end_date" :rules="[{ required: true, message: 'Required' }]">
            <a-date-picker v-model:value="goalForm.end_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>
        </div>

        <a-form-item label="Description" name="description">
          <a-textarea v-model:value="goalForm.description" :rows="3" placeholder="Optional description" />
        </a-form-item>

        <a-form-item>
          <a-checkbox v-model:checked="goalForm.notify_when_achieve">Notify staff members when goal achieve</a-checkbox>
        </a-form-item>

        <a-form-item>
          <a-checkbox v-model:checked="goalForm.notify_when_fail">Notify staff members when goal failed to achieve</a-checkbox>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Save</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';
import { useAuthStore } from '../../store/authStore';

export default defineComponent({
  name: 'GoalsPage',
  setup() {
    const authStore = useAuthStore();
    const canCreate = computed(() => authStore.hasPermission('Goals', 'create'));
    const canEdit   = computed(() => authStore.hasPermission('Goals', 'edit'));
    const canDelete = computed(() => authStore.hasPermission('Goals', 'delete'));
    const loading = ref(false);
    const saving = ref(false);
    const goals = ref([]);
    const staffMembers = ref([]);
    const showDrawer = ref(false);
    const showViewModal = ref(false);
    const viewGoalData = ref(null);
    const isEdit = ref(false);
    const editingId = ref(null);
    const search = ref('');

    const openViewModal = (record) => {
      viewGoalData.value = record;
      showViewModal.value = true;
    };

    const switchToEdit = (record) => {
      showViewModal.value = false;
      openEditDrawer(record);
    };

    const columns = [
      { title: 'Subject', dataIndex: 'subject', key: 'subject', sorter: (a, b) => a.subject?.localeCompare(b.subject) },
      { title: 'Staff Member', key: 'staff_member', sorter: (a, b) => (a.staff_member_name || '').localeCompare(b.staff_member_name || '') },
      { title: 'Achievement', key: 'achievement' },
      { title: 'Start Date', dataIndex: 'start_date', key: 'start_date', sorter: (a, b) => a.start_date?.localeCompare(b.start_date) },
      { title: 'End Date', dataIndex: 'end_date', key: 'end_date', sorter: (a, b) => a.end_date?.localeCompare(b.end_date) },
      { title: 'Goal Type', key: 'goal_type' },
      { title: 'Progress', key: 'progress', sorter: (a, b) => getPercentage(a) - getPercentage(b) },
      { title: 'Actions', key: 'actions', width: 140 },
    ];

    const goalForm = reactive({
      subject: '',
      goal_type: undefined,
      staff_member: undefined,
      target_value: null,
      current_value: 0,
      start_date: null,
      end_date: null,
      description: '',
      notify_when_achieve: true,
      notify_when_fail: true,
    });

    const filteredGoals = computed(() => {
      if (!search.value) return goals.value;
      const q = search.value.toLowerCase();
      return goals.value.filter(g =>
        (g.subject || '').toLowerCase().includes(q) ||
        (g.goal_type || '').toLowerCase().includes(q) ||
        (g.staff?.name || g.staff_member_name || '').toLowerCase().includes(q)
      );
    });

    const loadGoals = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/goals', { params: { per_page: 200 } });
        goals.value = res.data.goals?.data || [];
      } catch (e) {
        message.error('Failed to load goals');
      } finally {
        loading.value = false;
      }
    };

    const loadStaff = async () => {
      try {
        const res = await axios.get('/api/staff');
        staffMembers.value = res.data.staff || [];
      } catch { /* ignore */ }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(goalForm, {
        subject: '',
        goal_type: undefined,
        staff_member: undefined,
        target_value: null,
        current_value: 0,
        start_date: new Date().toISOString().split('T')[0],
        end_date: null,
        description: '',
        notify_when_achieve: true,
        notify_when_fail: true,
      });
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(goalForm, {
        subject: record.subject,
        goal_type: record.goal_type,
        staff_member: record.staff_member || record.staff_member_id || undefined,
        target_value: record.target_value,
        current_value: record.current_value || 0,
        start_date: record.start_date,
        end_date: record.end_date,
        description: record.description || '',
        notify_when_achieve: record.notify_when_achieve !== undefined ? record.notify_when_achieve : true,
        notify_when_fail: record.notify_when_fail !== undefined ? record.notify_when_fail : true,
      });
      showDrawer.value = true;
    };

    const saveGoal = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/goals/${editingId.value}`, goalForm);
          message.success('Goal updated');
        } else {
          await axios.post('/api/goals', goalForm);
          message.success('Goal created');
        }
        showDrawer.value = false;
        loadGoals();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save goal');
        }
      } finally {
        saving.value = false;
      }
    };

    const deleteGoal = async (id) => {
      try {
        await axios.delete(`/api/goals/${id}`);
        message.success('Goal deleted');
        loadGoals();
      } catch {
        message.error('Failed to delete goal');
      }
    };

    const getPercentage = (goal) => {
      if (!goal.target_value || goal.target_value <= 0) return 0;
      const pct = (goal.current_value / goal.target_value) * 100;
      return Math.min(Math.round(pct), 100);
    };

    const progressClass = (goal) => {
      const pct = getPercentage(goal);
      if (pct >= 100) return 'bg-success';
      if (pct >= 50) return 'bg-primary';
      return 'bg-warning';
    };

    const formatGoalType = (type) => ({
      invoice_amount: 'Invoice Total Amount ($)',
      lead_conversion: 'Lead Conversions (count)',
      tickets_solved: 'Support Tickets Solved (count)',
      payments_amount: 'Recorded Payments Total ($)',
      customer_number: 'Increase Customer Number',
      contracts_by_type: 'Make Contracts By Type',
      total_income: 'Achieve Total Income',
    }[type] || type);

    const formatValue = (val) => {
      if (val === undefined || val === null) return '0';
      return parseFloat(val).toLocaleString();
    };

    onMounted(() => {
      loadGoals();
      loadStaff();
    });

    return {
      canCreate, canEdit, canDelete,
      loading, saving, goals, staffMembers, showDrawer, showViewModal, viewGoalData, isEdit, goalForm, search, columns,
      filteredGoals, openCreateDrawer, openEditDrawer, openViewModal, switchToEdit, saveGoal, deleteGoal,
      getPercentage, progressClass, formatGoalType, formatValue,
    };
  }
});
</script>

<style scoped>
.goals-page {
  font-family: 'Inter', -apple-system, sans-serif;
}
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
}
.btn-primary:hover { background: #0b5ed7; }

.goals-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}

.goals-toolbar {
  margin-bottom: 16px;
}

.progress-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}
.progress-bar-bg {
  width: 80px;
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  overflow: hidden;
}
.progress-bar-fill {
  height: 100%;
  border-radius: 3px;
}
.bg-primary { background: #3b82f6; }
.bg-success { background: #10b981; }
.bg-warning { background: #f59e0b; }
.progress-pct {
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  white-space: nowrap;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
:deep(.ant-table-cell) { font-size: 13px; }

/* Mobile Cards List Hidden by Default on Desktop */
.mobile-cards-list {
  display: none;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
  }
  .goals-toolbar .ant-input {
    width: 100% !important;
  }
  :deep(.ant-table-wrapper) {
    display: none !important;
  }
  .mobile-cards-list {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
  }
}
</style>
