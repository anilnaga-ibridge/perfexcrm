<template>
  <div class="goals-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Goals Tracking</h1>
        <p class="text-xs text-slate-500 mt-0.5">Define business goals and monitor performance achievements</p>
      </div>
      <button class="btn-primary" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14" class="inline mr-1"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Goal
      </button>
    </div>

    <!-- Goals Grid Cards -->
    <div class="goals-grid" v-if="goals.length > 0">
      <div v-for="g in goals" :key="g.id" class="goal-card">
        <div class="goal-header">
          <h3 class="goal-subject">{{ g.subject }}</h3>
          <div class="goal-actions">
            <a-dropdown :trigger="['click']">
              <button class="action-btn">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
              </button>
              <template #overlay>
                <a-menu>
                  <a-menu-item key="edit" @click="openEditDrawer(g)">Edit Goal</a-menu-item>
                  <a-menu-divider />
                  <a-menu-item key="delete" @click="deleteGoal(g.id)" danger>Delete</a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>

        <div class="goal-type-lbl">{{ formatGoalType(g.goal_type) }}</div>

        <!-- Progress bar visualizer -->
        <div class="progress-section">
          <div class="progress-labels">
            <span class="progress-pct">{{ getPercentage(g) }}%</span>
            <span class="progress-vals">{{ formatValue(g.current_value) }} / {{ formatValue(g.target_value) }}</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill" :style="{ width: getPercentage(g) + '%' }" :class="progressClass(g)"></div>
          </div>
        </div>

        <div class="goal-dates">
          <span>Start: <strong>{{ formatDate(g.start_date) }}</strong></span>
          <span>End: <strong>{{ formatDate(g.end_date) }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div class="empty-state" style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:60px 20px" v-else>
      <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p class="text-sm text-slate-400 font-semibold mt-2">No goals set yet. Set your first sales or conversion goal!</p>
    </div>

    <!-- Goals Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Goal' : 'New Goal'"
      placement="right"
      :width="440"
    >
      <a-form layout="vertical" :model="goalForm" @finish="saveGoal">
        <a-form-item label="Subject / Name" name="subject" :rules="[{required:true,message:'Required'}]">
          <a-input v-model:value="goalForm.subject" placeholder="e.g. Q2 Invoice Collections" />
        </a-form-item>

        <a-form-item label="Goal Type" name="goal_type" :rules="[{required:true,message:'Required'}]">
          <a-select v-model:value="goalForm.goal_type" style="width:100%">
            <a-select-option value="invoice_amount">Invoice Total Amount ($)</a-select-option>
            <a-select-option value="lead_conversion">Lead Conversions (count)</a-select-option>
            <a-select-option value="tickets_solved">Support Tickets Solved (count)</a-select-option>
            <a-select-option value="payments_amount">Recorded Payments Total ($)</a-select-option>
          </a-select>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Start Date" name="start_date" :rules="[{required:true,message:'Required'}]">
            <a-date-picker v-model:value="goalForm.start_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>

          <a-form-item label="End Date" name="end_date" :rules="[{required:true,message:'Required'}]">
            <a-date-picker v-model:value="goalForm.end_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Target Value" name="target_value" :rules="[{required:true,message:'Required'}]">
            <a-input-number v-model:value="goalForm.target_value" :min="0" style="width:100%" placeholder="10000" />
          </a-form-item>

          <a-form-item label="Current Achievement" name="current_value">
            <a-input-number v-model:value="goalForm.current_value" :min="0" style="width:100%" placeholder="0" />
          </a-form-item>
        </div>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Save Goal</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'GoalsPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const goals = ref([]);
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);

    const goalForm = reactive({
      subject: '',
      goal_type: 'invoice_amount',
      start_date: null,
      end_date: null,
      target_value: null,
      current_value: 0,
    });

    const loadGoals = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/goals', { params: { per_page: 100 } });
        goals.value = res.data.goals?.data || [];
      } catch (e) {
        message.error('Failed to load business goals');
      } finally {
        loading.value = false;
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(goalForm, {
        subject: '',
        goal_type: 'invoice_amount',
        start_date: new Date().toISOString().split('T')[0],
        end_date: null,
        target_value: null,
        current_value: 0,
      });
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(goalForm, {
        subject: record.subject,
        goal_type: record.goal_type,
        start_date: record.start_date,
        end_date: record.end_date,
        target_value: record.target_value,
        current_value: record.current_value || 0,
      });
      showDrawer.value = true;
    };

    const saveGoal = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/goals/${editingId.value}`, goalForm);
          message.success('Goal updated successfully');
        } else {
          await axios.post('/api/goals', goalForm);
          message.success('Goal created successfully');
        }
        showDrawer.value = false;
        loadGoals();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save business goal');
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
      invoice_amount:  'Invoice Total Amount ($)',
      lead_conversion: 'Lead Conversions (count)',
      tickets_solved:  'Support Tickets Solved (count)',
      payments_amount: 'Recorded Payments Total ($)',
    }[type] || type);

    const formatValue = (val) => {
      if (val === undefined || val === null) return '0';
      return parseFloat(val).toLocaleString();
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(() => {
      loadGoals();
    });

    return {
      loading, saving, goals, showDrawer, isEdit, goalForm,
      openCreateDrawer, openEditDrawer, saveGoal, deleteGoal,
      getPercentage, progressClass, formatGoalType, formatValue, formatDate
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

/* Goals Grid */
.goals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}
.goal-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.goal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.goal-subject {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.goal-type-lbl {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
}

.progress-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin: 8px 0;
}
.progress-labels {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
}
.progress-pct {
  font-weight: 700;
  color: #0d6efd;
}
.progress-vals {
  color: #64748b;
  font-weight: 500;
}
.progress-bar-bg {
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}
.progress-bar-fill {
  height: 100%;
  border-radius: 4px;
}
.bg-primary { background: #3b82f6; }
.bg-success { background: #10b981; }
.bg-warning { background: #f59e0b; }

.goal-dates {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #64748b;
  border-top: 1px solid #f1f5f9;
  padding-top: 8px;
  margin-top: auto;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 4px;
}
.action-btn:hover { background: #f1f5f9; color: #475569; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
</style>
