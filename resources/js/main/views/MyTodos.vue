<template>
  <div class="todos-page">
    <!-- Header -->
    <div class="todos-header">
      <div class="todos-header-left">
        <h1 class="todos-title">My To Do's</h1>
      </div>
      <button class="btn-primary" @click="showTodoDrawer = true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        To Do
      </button>
    </div>

    <!-- Section: Unfinished -->
    <div class="todos-section">
      <h2 class="todos-section-title">Unfinished to do's</h2>
      <div class="todos-list">
        <div v-for="(t, i) in unfinishedTodos" :key="'u'+i" class="todo-item">
          <div class="todo-check-col">
            <input type="checkbox" class="todo-checkbox" />
          </div>
          <div class="todo-body">
            <p class="todo-desc">{{ t.description }}</p>
            <span class="todo-date">{{ t.date }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Section: Latest finished -->
    <div class="todos-section">
      <h2 class="todos-section-title">Latest finished to do's</h2>
      <div class="todos-list">
        <div v-for="(t, i) in finishedTodos" :key="'f'+i" class="todo-item todo-item--done">
          <div class="todo-check-col">
            <div class="todo-checked-mark">
              <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
          </div>
          <div class="todo-body">
            <p class="todo-desc todo-desc--done">{{ t.description }}</p>
            <span class="todo-date">{{ t.date }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TODO DRAWER ── -->
    <transition name="drawer-fade">
      <div v-if="showTodoDrawer" class="drawer-overlay" @click.self="showTodoDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showTodoDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-emerald-600"></div>
                <span class="text-sm font-bold text-slate-800">Add New Todo</span>
              </div>
              <button class="drawer-close" @click="showTodoDrawer = false">×</button>
            </div>
            <div class="drawer-body">
              <div class="form-group">
                <label class="input-lbl">Description</label>
                <textarea class="input-ctrl textarea" rows="6" v-model="todoForm.description" placeholder="Describe the to-do..."></textarea>
              </div>
            </div>
            <div class="drawer-footer">
              <button class="btn-cancel" @click="showTodoDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showTodoDrawer = false">Save</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script>
import { defineComponent, ref } from 'vue';

export default defineComponent({
  name: 'MyTodos',
  setup() {
    const showTodoDrawer = ref(false);
    const todoForm = ref({ description: '' });

    const unfinishedTodos = ref([
      { description: 'ONE with such a nice soft.', date: '2026-06-19 12:00:18' },
      { description: "Exactly as we needn't.", date: '2026-06-19 12:00:18' },
    ]);

    const finishedTodos = ref([
      { description: 'Presently the Rabbit say to.', date: '2026-06-19 12:00:18' },
      { description: 'Alice the moment she appeared.', date: '2026-06-19 12:00:18' },
      { description: 'Tell her to carry it further. So she began.', date: '2026-06-19 12:00:18' },
      { description: "Alice; 'but.", date: '2026-06-19 12:00:18' },
      { description: "Between yourself and me.' 'That's the.", date: '2026-06-19 12:00:18' },
    ]);

    return {
      showTodoDrawer, todoForm,
      unfinishedTodos, finishedTodos,
    };
  },
});
</script>

<style scoped>
.todos-page { font-family: inherit; color: #1e293b; }
.todos-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px;
}
.todos-header-left { display: flex; flex-direction: column; gap: 2px; }
.todos-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }

.todos-section { margin-bottom: 28px; }
.todos-section-title {
  font-size: 13px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.5px;
  margin: 0 0 12px 0; padding-bottom: 8px;
  border-bottom: 1px solid #e2e8f0;
}

.todos-list { display: flex; flex-direction: column; }
.todo-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 12px 16px; border-radius: 10px;
  background: #fff; border: 1px solid #f1f5f9;
  margin-bottom: 6px; transition: all 0.1s;
}
.todo-item:hover { border-color: #e2e8f0; }
.todo-item--done { background: #fafbfc; }
.todo-check-col { padding-top: 2px; }
.todo-checkbox {
  width: 16px; height: 16px; border-radius: 4px;
  accent-color: #6366f1; cursor: pointer;
}
.todo-checked-mark {
  width: 20px; height: 20px; border-radius: 50%;
  background: #f0fdf4; display: flex; align-items: center; justify-content: center;
}
.todo-body { flex: 1; min-width: 0; }
.todo-desc { font-size: 13px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0; line-height: 1.4; }
.todo-desc--done { color: #94a3b8; text-decoration: line-through; }
.todo-date { font-size: 11px; font-weight: 500; color: #94a3b8; }

/* Drawer (shared with SeoOptimisation styles — reuse as needed) */
.drawer-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,0.35);
  z-index: 1000; display: flex; justify-content: flex-end;
}
.drawer-panel {
  width: 400px; max-width: 90vw; background: #fff; height: 100%;
  display: flex; flex-direction: column; box-shadow: -4px 0 20px rgba(0,0,0,0.1);
}
.drawer-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;
}
.drawer-close {
  background: none; border: none; font-size: 22px; color: #94a3b8;
  cursor: pointer; line-height: 1; padding: 0 4px;
}
.drawer-close:hover { color: #475569; }
.drawer-body { flex: 1; overflow-y: auto; padding: 20px; }
.drawer-footer {
  display: flex; align-items: center; justify-content: flex-end;
  gap: 10px; padding: 14px 20px; border-top: 1px solid #e2e8f0; flex-shrink: 0;
}
.drawer-fade-enter-active, .drawer-fade-leave-active { transition: opacity 0.15s; }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }
.drawer-slide-enter-active, .drawer-slide-leave-active { transition: transform 0.2s ease; }
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }

/* Form controls */
.form-group { margin-bottom: 16px; }
.input-lbl { display: block; font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 5px; }
.input-ctrl {
  width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 12px; font-weight: 500; color: #1e293b;
  background: #fff; outline: none; font-family: inherit; box-sizing: border-box;
}
.input-ctrl:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.1); }
.textarea { resize: vertical; min-height: 100px; line-height: 1.5; }
.btn-cancel {
  padding: 8px 18px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 12px; font-weight: 600; color: #64748b; background: #fff;
  cursor: pointer; font-family: inherit; transition: all 0.1s;
}
.btn-cancel:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-primary {
  padding: 8px 18px; border: none; border-radius: 8px;
  font-size: 12px; font-weight: 700; color: #fff; cursor: pointer;
  font-family: inherit;
  background: linear-gradient(135deg, #6366f1, #7c3aed);
  transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;
}
.btn-primary:hover { box-shadow: 0 2px 8px rgba(99,102,241,0.35); transform: translateY(-1px); }
</style>
