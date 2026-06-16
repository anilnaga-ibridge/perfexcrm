<template>
  <div class="tasks-page">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Tasks</h1>
      <div class="header-actions">
        <!-- View Toggle -->
        <div class="view-toggle">
          <button
            class="toggle-btn"
            :class="{ active: currentView === 'kanban' }"
            @click="currentView = 'kanban'"
            title="Kanban View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <rect x="3" y="3" width="7" height="9"/>
              <rect x="14" y="3" width="7" height="5"/>
              <rect x="14" y="12" width="7" height="9"/>
              <rect x="3" y="16" width="7" height="5"/>
            </svg>
            Kanban
          </button>
          <button
            class="toggle-btn"
            :class="{ active: currentView === 'table' }"
            @click="currentView = 'table'"
            title="Table View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <line x1="8" y1="6" x2="21" y2="6"/>
              <line x1="8" y1="12" x2="21" y2="12"/>
              <line x1="8" y1="18" x2="21" y2="18"/>
              <line x1="3" y1="6" x2="3.01" y2="6"/>
              <line x1="3" y1="12" x2="3.01" y2="12"/>
              <line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            Table
          </button>
        </div>
        <button class="btn-primary" @click="openCreateDrawer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Task
        </button>
      </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="summary-cards">
      <div class="summary-card" v-for="s in summaryCards" :key="s.label" @click="filterByStatus(s.statusValue)">
        <div class="summary-label">{{ s.label }}</div>
        <div class="summary-value" :class="s.class">{{ s.value }}</div>
      </div>
    </div>

    <!-- Toolbar for Filters and Search -->
    <div class="table-card" style="margin-bottom: 20px;">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.priority" size="small" style="width:130px" placeholder="All Priorities" allowClear @change="loadTasks">
            <a-select-option value="Low">Low</a-select-option>
            <a-select-option value="Medium">Medium</a-select-option>
            <a-select-option value="High">High</a-select-option>
            <a-select-option value="Urgent">Urgent</a-select-option>
          </a-select>
          <a-select v-model:value="filters.assigned_to" size="small" style="width:150px" placeholder="Assignee" allowClear @change="loadTasks">
            <a-select-option v-for="s in staffOptions" :key="s.id" :value="s.id">{{ s.name }}</a-select-option>
          </a-select>
        </div>
        <div class="toolbar-right">
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search tasks..."
            size="small"
            style="width:260px"
            @search="loadTasks"
          />
        </div>
      </div>
    </div>

    <!-- Kanban View -->
    <div v-if="currentView === 'kanban'" class="kanban-board">
      <div v-for="col in kanbanColumns" :key="col.status" class="kanban-column">
        <div class="column-header">
          <span class="column-title">{{ col.title }}</span>
          <span class="column-badge">{{ getTasksByStatus(col.status).length }}</span>
        </div>
        <div class="column-body">
          <div v-for="task in getTasksByStatus(col.status)" :key="task.id" class="kanban-card">
            <div class="card-header-row">
              <span class="priority-badge" :class="priorityClass(task.priority)">{{ task.priority }}</span>
              <a-dropdown :trigger="['click']">
                <button class="card-actions-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="openEditDrawer(task)">Edit Task</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteTask(task.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
            <h4 class="task-card-title" @click="openEditDrawer(task)">{{ task.name }}</h4>
            <p class="task-card-desc">{{ truncateText(task.description, 80) }}</p>
            <div class="task-card-footer">
              <div class="due-date" :class="{ overdue: isOverdue(task) }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ formatDate(task.due_date) }}
              </div>
              <div class="assignee-avatar" v-if="task.assignee" :title="task.assignee.name">
                {{ getInitials(task.assignee.name) }}
              </div>
              <div class="assignee-avatar unassigned" v-else title="Unassigned">
                ?
              </div>
            </div>
            <div class="move-column-row">
              <a-select :value="task.status" size="small" style="width:100%" @change="(val) => updateStatus(task, val)">
                <a-select-option v-for="c in kanbanColumns" :key="c.status" :value="c.status">
                  Move to: {{ c.title }}
                </a-select-option>
              </a-select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-else class="table-card">
      <a-table
        :dataSource="tasks"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 800 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <a class="task-name-link" @click="openEditDrawer(record)">{{ record.name }}</a>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="statusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>

          <template v-if="column.key === 'priority'">
            <span class="priority-badge" :class="priorityClass(record.priority)">{{ record.priority }}</span>
          </template>

          <template v-if="column.key === 'assignee'">
            <span v-if="record.assignee">{{ record.assignee.name }}</span>
            <span class="text-muted" v-else>Unassigned</span>
          </template>

          <template v-if="column.key === 'start_date'">
            <span>{{ formatDate(record.start_date) }}</span>
          </template>

          <template v-if="column.key === 'due_date'">
            <span :class="{ 'text-danger': isOverdue(record) }">{{ formatDate(record.due_date) }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-dropdown :trigger="['click']">
                <button class="action-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="openEditDrawer(record)">Edit Task</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteTask(record.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
            <p>No tasks found</p>
          </div>
        </template>
      </a-table>
    </div>

    <!-- Task Drawer Form -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Task' : 'New Task'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="taskForm" @finish="saveTask">
        <a-form-item label="Task Name" name="name" :rules="[{required:true,message:'Required'}]">
          <a-input v-model:value="taskForm.name" placeholder="e.g. Design Proposals Page UI" />
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea v-model:value="taskForm.description" :rows="4" placeholder="Task details and sub-tasks..." />
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Priority" name="priority" :rules="[{required:true,message:'Required'}]">
            <a-select v-model:value="taskForm.priority" style="width:100%">
              <a-select-option value="Low">Low</a-select-option>
              <a-select-option value="Medium">Medium</a-select-option>
              <a-select-option value="High">High</a-select-option>
              <a-select-option value="Urgent">Urgent</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Status" name="status" :rules="[{required:true,message:'Required'}]">
            <a-select v-model:value="taskForm.status" style="width:100%">
              <a-select-option value="Not Started">Not Started</a-select-option>
              <a-select-option value="In Progress">In Progress</a-select-option>
              <a-select-option value="Testing">Testing</a-select-option>
              <a-select-option value="Awaiting Feedback">Awaiting Feedback</a-select-option>
              <a-select-option value="Complete">Complete</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Start Date" name="start_date">
            <a-date-picker v-model:value="taskForm.start_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>

          <a-form-item label="Due Date" name="due_date">
            <a-date-picker v-model:value="taskForm.due_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>
        </div>

        <a-form-item label="Assign To" name="assigned_to">
          <a-select v-model:value="taskForm.assigned_to" style="width:100%" placeholder="Select staff member..." allowClear>
            <a-select-option v-for="s in staffOptions" :key="s.id" :value="s.id">{{ s.name }}</a-select-option>
          </a-select>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Related To (Type)" name="related_to_type">
            <a-select v-model:value="taskForm.related_to_type" style="width:100%" placeholder="Type..." allowClear>
              <a-select-option value="client">Client</a-select-option>
              <a-select-option value="lead">Lead</a-select-option>
              <a-select-option value="project">Project</a-select-option>
              <a-select-option value="invoice">Invoice</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Related ID" name="related_to_id">
            <a-input-number v-model:value="taskForm.related_to_id" style="width:100%" placeholder="e.g. 5" />
          </a-form-item>
        </div>

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

export default defineComponent({
  name: 'TasksPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const currentView = ref('kanban'); // kanban or table
    const tasks = ref([]);
    const stats = ref({});
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);
    const staffOptions = ref([]);

    const filters = reactive({ search: '', status: '', priority: '', assigned_to: null });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} tasks` });

    const taskForm = reactive({
      name: '',
      description: '',
      priority: 'Medium',
      status: 'Not Started',
      start_date: null,
      due_date: null,
      assigned_to: null,
      related_to_type: null,
      related_to_id: null,
    });

    const columns = [
      { title: 'Task Name', key: 'name' },
      { title: 'Priority',  key: 'priority',  width: 100 },
      { title: 'Status',    key: 'status',    width: 150 },
      { title: 'Assignee',  key: 'assignee',  width: 150 },
      { title: 'Start Date',key: 'start_date',width: 120 },
      { title: 'Due Date',  key: 'due_date',  width: 120 },
      { title: '',          key: 'actions',   width: 60, align: 'center' },
    ];

    const kanbanColumns = [
      { title: 'Not Started',       status: 'Not Started' },
      { title: 'In Progress',       status: 'In Progress' },
      { title: 'Testing',           status: 'Testing' },
      { title: 'Awaiting Feedback', status: 'Awaiting Feedback' },
      { title: 'Complete',          status: 'Complete' },
    ];

    const summaryCards = computed(() => [
      { label: 'Total Tasks',        value: stats.value.total || 0, statusValue: '' },
      { label: 'Not Started',       value: stats.value.not_started || 0, statusValue: 'Not Started', class: 'text-muted' },
      { label: 'In Progress',       value: stats.value.in_progress || 0, statusValue: 'In Progress', class: 'text-primary' },
      { label: 'Testing',           value: stats.value.testing || 0, statusValue: 'Testing', class: 'text-warning' },
      { label: 'Awaiting Feedback', value: stats.value.awaiting_feedback || 0, statusValue: 'Awaiting Feedback', class: 'text-danger' },
      { label: 'Complete',          value: stats.value.complete || 0, statusValue: 'Complete', class: 'text-success' },
    ]);

    const loadTasks = async () => {
      loading.value = true;
      try {
        // When in kanban view, load all tasks matching other filters
        const loadAll = currentView.value === 'kanban';
        const res = await axios.get('/api/tasks', {
          params: {
            search: filters.search,
            status: filters.status,
            priority: filters.priority,
            assigned_to: filters.assigned_to,
            all: loadAll,
            per_page: pagination.pageSize,
            page: pagination.current,
          }
        });
        
        if (loadAll) {
          tasks.value = res.data.tasks || [];
        } else {
          tasks.value = res.data.tasks?.data || [];
          pagination.total = res.data.tasks?.total || 0;
        }
        stats.value = res.data.stats || {};
      } catch (e) {
        message.error('Failed to load tasks');
      } finally {
        loading.value = false;
      }
    };

    const loadStaff = async () => {
      try {
        const res = await axios.get('/api/staff', { params: { per_page: 200 } });
        staffOptions.value = res.data.staff?.data || [];
      } catch (e) {
        console.error('Failed to load staff options', e);
      }
    };

    const getTasksByStatus = (status) => {
      return tasks.value.filter(t => t.status === status);
    };

    const filterByStatus = (statusVal) => {
      filters.status = statusVal;
      loadTasks();
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(taskForm, {
        name: '',
        description: '',
        priority: 'Medium',
        status: 'Not Started',
        start_date: new Date().toISOString().split('T')[0],
        due_date: null,
        assigned_to: null,
        related_to_type: null,
        related_to_id: null,
      });
      loadStaff();
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(taskForm, {
        name: record.name,
        description: record.description || '',
        priority: record.priority || 'Medium',
        status: record.status || 'Not Started',
        start_date: record.start_date,
        due_date: record.due_date,
        assigned_to: record.assigned_to,
        related_to_type: record.related_to_type || null,
        related_to_id: record.related_to_id || null,
      });
      loadStaff();
      showDrawer.value = true;
    };

    const saveTask = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/tasks/${editingId.value}`, taskForm);
          message.success('Task updated successfully');
        } else {
          await axios.post('/api/tasks', taskForm);
          message.success('Task created successfully');
        }
        showDrawer.value = false;
        loadTasks();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save task');
        }
      } finally {
        saving.value = false;
      }
    };

    const updateStatus = async (task, newStatus) => {
      try {
        await axios.put(`/api/tasks/${task.id}`, { status: newStatus });
        message.success('Task status updated');
        loadTasks();
      } catch {
        message.error('Failed to update task status');
      }
    };

    const deleteTask = async (id) => {
      try {
        await axios.delete(`/api/tasks/${id}`);
        message.success('Task deleted');
        loadTasks();
      } catch {
        message.error('Failed to delete task');
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadTasks();
    };

    const statusColor = (s) => ({
      'Not Started':       'default',
      'In Progress':       'blue',
      'Testing':           'warning',
      'Awaiting Feedback': 'error',
      'Complete':          'success',
    }[s] || 'default');

    const priorityClass = (p) => ({
      Low:    'priority-low',
      Medium: 'priority-medium',
      High:   'priority-high',
      Urgent: 'priority-urgent',
    }[p] || 'priority-medium');

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const isOverdue = (task) => {
      if (task.status === 'Complete') return false;
      return task.due_date && new Date(task.due_date) < new Date();
    };

    const getInitials = (name) => {
      if (!name) return '?';
      const parts = name.split(' ');
      return parts.map(p => p[0]).slice(0, 2).join('').toUpperCase();
    };

    const truncateText = (str, len) => {
      if (!str) return '';
      if (str.length <= len) return str;
      return str.substring(0, len) + '...';
    };

    onMounted(() => {
      loadTasks();
      loadStaff();
    });

    return {
      loading, saving, currentView, tasks, stats, filters, pagination, columns,
      kanbanColumns, summaryCards, taskForm, showDrawer, isEdit, staffOptions,
      loadTasks, getTasksByStatus, filterByStatus, openCreateDrawer, openEditDrawer,
      saveTask, updateStatus, deleteTask, handleTableChange, statusColor,
      priorityClass, formatDate, isOverdue, getInitials, truncateText
    };
  }
});
</script>

<style scoped>
.tasks-page {
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
.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
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
  transition: background 0.12s;
}
.btn-primary:hover { background: #0b5ed7; }
.btn-primary svg { width: 14px; height: 14px; }

/* View Toggle */
.view-toggle {
  display: flex;
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 6px;
  padding: 2px;
}
.toggle-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  border-radius: 4px;
  padding: 5px 12px;
  font-size: 12.5px;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  font-weight: 500;
  transition: all 0.15s;
}
.toggle-btn svg {
  color: #94a3b8;
}
.toggle-btn.active {
  background: #f1f5f9;
  color: #0d6efd;
}
.toggle-btn.active svg {
  color: #0d6efd;
}

/* Summary Cards */
.summary-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
}
.summary-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 12px 18px;
  min-width: 100px;
  flex: 1;
  cursor: pointer;
  transition: box-shadow 0.15s, border-color 0.15s;
}
.summary-card:hover {
  border-color: #0d6efd;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
}
.summary-label {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 4px;
}
.summary-value {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}
.text-muted   { color: #64748b; }
.text-primary { color: #0d6efd; }
.text-danger  { color: #ef4444; }
.text-warning { color: #f59e0b; }
.text-success { color: #22c55e; }

/* Table */
.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 10px;
}
.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-outline {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 4px 12px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-outline:hover { background: #f8fafc; }

.task-name-link {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
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
.row-actions { display: flex; justify-content: center; }

/* Kanban Board Styling */
.kanban-board {
  display: grid;
  grid-template-columns: repeat(5, minmax(220px, 1fr));
  gap: 12px;
  overflow-x: auto;
  align-items: start;
  min-height: 500px;
  padding-bottom: 24px;
}
.kanban-column {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px;
  display: flex;
  flex-direction: column;
  max-height: 700px;
}
.column-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  padding: 2px 4px;
}
.column-title {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
}
.column-badge {
  background: #e2e8f0;
  color: #475569;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
}
.column-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
  overflow-y: auto;
  min-height: 150px;
}

/* Kanban Card */
.kanban-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 12px;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: transform 0.15s, box-shadow 0.15s;
}
.kanban-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.08);
}
.card-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.priority-badge {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: uppercase;
}
.priority-low    { background: #f1f5f9; color: #64748b; }
.priority-medium { background: #eff6ff; color: #3b82f6; }
.priority-high   { background: #fff7ed; color: #f97316; }
.priority-urgent { background: #fef2f2; color: #ef4444; }

.card-actions-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 2px;
  border-radius: 3px;
}
.card-actions-btn:hover { background: #f1f5f9; color: #475569; }

.task-card-title {
  font-size: 13.5px;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  cursor: pointer;
  line-height: 1.4;
}
.task-card-title:hover {
  color: #0d6efd;
}
.task-card-desc {
  font-size: 12px;
  color: #64748b;
  margin: 0;
  line-height: 1.4;
}
.task-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 4px;
}
.due-date {
  font-size: 11px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 4px;
}
.due-date.overdue {
  color: #ef4444;
  font-weight: 600;
}
.assignee-avatar {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #3b82f6;
  color: #fff;
  font-size: 9px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.assignee-avatar.unassigned {
  background: #e2e8f0;
  color: #64748b;
}

.move-column-row {
  margin-top: 6px;
  border-top: 1px solid #f1f5f9;
  padding-top: 6px;
}

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
