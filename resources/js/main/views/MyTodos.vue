<template>
  <div class="todos-page">
    <!-- Header -->
    <div class="todos-header">
      <div class="todos-header-left">
        <div class="todos-title-row">
          <div class="todos-icon-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="20" height="20">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
          </div>
          <h1 class="todos-title">My To-Do Studio</h1>
        </div>
        <p class="todos-subtitle">Organize, prioritize, and accomplish your daily tasks with drag-and-drop ease.</p>
      </div>

      <div class="todos-header-right">
        <button class="btn-add-todo" @click="openDrawer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>New To-Do</span>
        </button>
      </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="todos-kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon-box total">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Total Tasks</span>
          <span class="kpi-value">{{ allTodos.length }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon-box pending">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Pending</span>
          <span class="kpi-value warning">{{ unfinishedTodos.length }}</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon-box done">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Completed</span>
          <span class="kpi-value success">{{ finishedTodos.length }}</span>
        </div>
      </div>

      <div class="kpi-card progress-card">
        <div class="kpi-header-row">
          <span class="kpi-label">Completion Rate</span>
          <span class="kpi-perc">{{ completionPercentage }}%</span>
        </div>
        <div class="progress-track">
          <div class="progress-bar" :style="{ width: completionPercentage + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="todos-controls-bar">
      <div class="flex items-center gap-2">
        <div class="todo-search-box">
          <svg class="todo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="searchQuery"
            type="text"
            class="todo-search-input"
            placeholder="Search your to-dos..."
          />
          <button v-if="searchQuery" class="clear-search" @click="searchQuery = ''">×</button>
        </div>

        <!-- Staff Filter Selector -->
        <select
          v-model="selectedStaffFilter"
          class="h-10 px-3 text-xs font-semibold theme-input-ctrl cursor-pointer bg-white rounded-xl border border-slate-200"
        >
          <option value="all">👥 All Staff Tasks</option>
          <option v-for="s in staffMembers" :key="s.id" :value="s.id">
            👤 {{ s.name || s.full_name || s.email }}
          </option>
        </select>
      </div>

      <div class="filter-tabs">
        <button
          class="filter-tab"
          :class="{ active: activeFilter === 'all' }"
          @click="activeFilter = 'all'"
        >
          All <span class="tab-count">{{ allTodos.length }}</span>
        </button>
        <button
          class="filter-tab"
          :class="{ active: activeFilter === 'pending' }"
          @click="activeFilter = 'pending'"
        >
          Pending <span class="tab-count">{{ unfinishedTodos.length }}</span>
        </button>
        <button
          class="filter-tab"
          :class="{ active: activeFilter === 'completed' }"
          @click="activeFilter = 'completed'"
        >
          Completed <span class="tab-count">{{ finishedTodos.length }}</span>
        </button>
      </div>
    </div>

    <!-- Unfinished Tasks Section -->
    <div class="todos-card-panel" v-if="activeFilter === 'all' || activeFilter === 'pending'">
      <div class="panel-header">
        <div class="panel-header-title">
          <h2 class="panel-title">Pending Tasks</h2>
          <span class="badge-count warning">{{ filteredUnfinished.length }}</span>
        </div>
        <span class="drag-hint" v-if="filteredUnfinished.length > 1">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="8 18 12 22 16 18"/><polyline points="8 6 12 2 16 6"/><line x1="12" y1="2" x2="12" y2="22"/></svg>
          Drag handles to reorder
        </span>
      </div>

      <div class="todos-list">
        <VueDraggable
          v-model="unfinishedTodos"
          handle=".drag-grip"
          ghost-class="todo-ghost"
          @end="onDragEnd"
        >
          <div
            v-for="t in filteredUnfinished"
            :key="t.id"
            class="todo-row"
            :class="{ 'todo-row--editing': editingId === t.id, 'z-50 relative': openAssigneeMenuId == t.id }"
          >
            <!-- Drag handle -->
            <div class="drag-grip" title="Drag to reorder">
              <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
              </svg>
            </div>

            <!-- Custom Checkbox -->
            <label class="custom-checkbox-wrapper" @click.stop>
              <input type="checkbox" class="native-checkbox" :checked="t.done" @change="toggleTodo(t)" />
              <span class="checkbox-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" class="check-svg">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </span>
            </label>

            <!-- Body / Edit Input -->
            <div class="todo-row-body" @dblclick="startEdit(t)">
              <template v-if="editingId === t.id">
                <div class="edit-input-wrapper">
                  <input
                    ref="editInput"
                    v-model="editText"
                    class="todo-edit-input"
                    @keyup.enter="saveEdit(t)"
                    @keyup.escape="cancelEdit"
                    @blur="saveEdit(t)"
                  />
                  <span class="edit-hint">Press Enter to save • Esc to cancel</span>
                </div>
              </template>
              <template v-else>
                <p class="todo-text">{{ t.description }}</p>
                <div class="todo-meta flex items-center gap-2 flex-wrap mt-1.5">
                  <span class="todo-date-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ t.date || 'Today' }}
                  </span>

                  <!-- Premium Priority Pill -->
                  <span
                    class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full uppercase tracking-wider shadow-sm border"
                    :class="{
                      'bg-rose-50 text-rose-600 border-rose-200': t.priority === 'high',
                      'bg-amber-50 text-amber-600 border-amber-200': t.priority === 'medium' || !t.priority,
                      'bg-sky-50 text-sky-600 border-sky-200': t.priority === 'low'
                    }"
                  >
                    ● {{ t.priority || 'medium' }}
                  </span>

                  <!-- Premium Interactive Staff Assignee Selector Dropdown Card -->
                  <div class="relative inline-block" @click.stop>
                    <button
                      type="button"
                      class="inline-flex items-center gap-1.5 bg-white hover:bg-indigo-50/70 border border-slate-200/90 shadow-2xs px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-slate-700 transition-all hover:border-indigo-300 hover:shadow-xs cursor-pointer"
                      @click="toggleAssigneeMenu(t.id)"
                    >
                      <template v-if="getAssignedStaff(t.assigned_to)">
                        <div class="w-4 h-4 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-extrabold text-[8.5px] flex items-center justify-center shadow-2xs">
                          {{ getStaffInitials(getAssignedStaff(t.assigned_to).name) }}
                        </div>
                        <span class="font-bold text-slate-800 text-[11px] max-w-[130px] truncate">
                          {{ getAssignedStaff(t.assigned_to).name }}
                        </span>
                      </template>
                      <template v-else>
                        <div class="w-4 h-4 rounded-full bg-slate-200 text-slate-600 font-extrabold text-[8.5px] flex items-center justify-center">
                          👤
                        </div>
                        <span class="font-semibold text-slate-500 text-[11px]">Me (Unassigned)</span>
                      </template>

                      <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </button>

                    <!-- Floating Staff Selector Card -->
                    <div
                      v-if="openAssigneeMenuId === t.id"
                      class="absolute left-0 mt-1.5 w-64 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 z-50 p-2 text-left animate-in fade-in zoom-in-95 duration-150"
                      style="box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.15), 0 4px 12px -2px rgba(99, 102, 241, 0.1);"
                    >
                      <div class="px-2 py-1 border-b border-slate-100 mb-1 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Assign Member</span>
                        <span class="text-[10px] font-bold text-indigo-600">{{ filteredStaffList.length }} staff</span>
                      </div>

                      <!-- Search Staff Input -->
                      <div class="px-1 py-1 mb-1">
                        <input
                          v-model="staffSearchQuery"
                          type="text"
                          placeholder="🔍 Search staff..."
                          class="w-full h-7 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 focus:bg-white text-slate-700 font-medium"
                        />
                      </div>

                      <div class="max-h-48 overflow-y-auto space-y-0.5 custom-scrollbar">
                        <!-- Option: Unassigned / Me -->
                        <button
                          type="button"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-left text-xs font-semibold transition-colors cursor-pointer"
                          :class="!t.assigned_to ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                          @click="selectAssignee(t, null)"
                        >
                          <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 font-bold text-[10px] flex items-center justify-center">
                              👤
                            </div>
                            <div>
                              <p class="text-xs font-bold leading-tight">Me (Unassigned)</p>
                              <p class="text-[10px] text-slate-400 font-normal">Personal task</p>
                            </div>
                          </div>
                          <span v-if="!t.assigned_to" class="text-indigo-600 font-bold text-sm">✓</span>
                        </button>

                        <!-- Option: Staff Members -->
                        <button
                          v-for="s in filteredStaffList"
                          :key="s.id"
                          type="button"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-left text-xs font-semibold transition-colors cursor-pointer"
                          :class="t.assigned_to == s.id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                          @click="selectAssignee(t, s.id)"
                        >
                          <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-extrabold text-[10px] flex items-center justify-center flex-shrink-0 shadow-xs">
                              {{ getStaffInitials(s.name) }}
                            </div>
                            <div class="truncate">
                              <p class="text-xs font-bold leading-tight truncate text-slate-800">{{ s.name }}</p>
                              <p class="text-[10px] text-slate-400 font-normal truncate">{{ s.email }}</p>
                            </div>
                          </div>
                          <span v-if="t.assigned_to == s.id" class="text-indigo-600 font-bold text-sm flex-shrink-0">✓</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <span class="double-click-tip">Double-click to edit</span>
                </div>
              </template>
            </div>

            <!-- Actions -->
            <div class="todo-row-actions">
              <button class="action-btn edit" title="Edit Task" @click="startEdit(t)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="action-btn delete" title="Delete Task" @click="deleteTodo(t)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </button>
            </div>
          </div>
        </VueDraggable>

        <div v-if="filteredUnfinished.length === 0" class="empty-panel-state">
          <div class="empty-icon-box">
            <svg viewBox="0 24 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <p class="empty-title">{{ searchQuery ? 'No matching tasks found' : 'All pending tasks completed!' }}</p>
          <p class="empty-sub">{{ searchQuery ? 'Try a different search query' : 'Great job! Click "+ New To-Do" to add more tasks.' }}</p>
        </div>
      </div>
    </div>

    <!-- Completed Tasks Section -->
    <div class="todos-card-panel completed-panel" v-if="activeFilter === 'all' || activeFilter === 'completed'">
      <div class="panel-header" @click="isCompletedExpanded = !isCompletedExpanded" style="cursor: pointer;">
        <div class="panel-header-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" :style="{ transform: isCompletedExpanded ? 'rotate(90deg)' : 'none', transition: 'transform 0.2s' }">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
          <h2 class="panel-title">Completed Tasks</h2>
          <span class="badge-count success">{{ filteredFinished.length }}</span>
        </div>
      </div>

      <div class="todos-list" v-if="isCompletedExpanded">
        <div
          v-for="t in filteredFinished"
          :key="t.id"
          class="todo-row todo-row--done"
        >
          <!-- Checked Checkbox -->
          <label class="custom-checkbox-wrapper checked" @click="toggleTodo(t)">
            <span class="checkbox-box checked">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" class="check-svg">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
          </label>

          <!-- Body -->
          <div class="todo-row-body">
            <p class="todo-text done">{{ t.description }}</p>
            <span class="todo-date-badge done">Completed • {{ t.date || 'Recently' }}</span>
          </div>

          <!-- Actions -->
          <div class="todo-row-actions">
            <button class="action-btn restore" title="Reopen Task" @click="toggleTodo(t)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            </button>
            <button class="action-btn delete" title="Delete Task" @click="deleteTodo(t)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </div>
        </div>

        <div v-if="filteredFinished.length === 0" class="empty-panel-state">
          <p class="empty-sub">No completed tasks yet.</p>
        </div>
      </div>
    </div>

    <!-- ── SLIDE-OVER NEW TODO DRAWER ── -->
    <transition name="drawer-fade">
      <div v-if="showTodoDrawer" class="drawer-overlay" @click.self="showTodoDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showTodoDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="drawer-header-title">
                <div class="drawer-icon-box">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18"><path d="M12 5v14M5 12h14"/></svg>
                </div>
                <div>
                  <h3 class="drawer-title">Add New To-Do</h3>
                  <p class="drawer-sub">Create a task to stay on top of your daily priorities</p>
                </div>
              </div>
              <button class="drawer-close-btn" @click="showTodoDrawer = false" title="Close drawer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <div class="drawer-body">
              <div class="form-group">
                <div class="input-lbl-row">
                  <label class="input-lbl">Task Description <span class="req">*</span></label>
                  <span class="char-count">{{ todoForm.description ? todoForm.description.length : 0 }} chars</span>
                </div>
                <textarea
                  v-model="todoForm.description"
                  class="input-ctrl textarea"
                  rows="4"
                  placeholder="What needs to be done? E.g., Review project contract, Follow up with lead..."
                  ref="drawerTextarea"
                ></textarea>
                <p class="input-helper">Tip: Double-click tasks anytime on the main list to edit them directly.</p>
              </div>

              <!-- Assignee & Priority Controls -->
              <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="relative">
                  <label class="block text-xs font-bold text-slate-700 mb-1">Assign Task To</label>
                  <div class="relative w-full" @click.stop>
                    <button
                      type="button"
                      class="w-full h-10 px-3 text-xs font-semibold bg-white border border-slate-200 rounded-xl flex items-center justify-between cursor-pointer hover:border-indigo-400 transition-all shadow-2xs"
                      @click="showDrawerAssigneeMenu = !showDrawerAssigneeMenu"
                    >
                      <template v-if="getAssignedStaff(todoForm.assigned_to)">
                        <div class="flex items-center gap-2 truncate">
                          <div class="w-5 h-5 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-extrabold text-[9px] flex items-center justify-center flex-shrink-0 shadow-2xs">
                            {{ getStaffInitials(getAssignedStaff(todoForm.assigned_to).name) }}
                          </div>
                          <span class="font-bold text-slate-800 text-xs truncate">
                            {{ getAssignedStaff(todoForm.assigned_to).name }}
                          </span>
                        </div>
                      </template>
                      <template v-else>
                        <div class="flex items-center gap-2">
                          <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-600 font-extrabold text-[9px] flex items-center justify-center">
                            👤
                          </div>
                          <span class="font-semibold text-slate-600 text-xs">Assigned to Me</span>
                        </div>
                      </template>

                      <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </button>

                    <!-- Floating Staff Selector Card Dropdown -->
                    <div
                      v-if="showDrawerAssigneeMenu"
                      class="absolute left-0 right-0 mt-1.5 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 z-50 p-2 text-left animate-in fade-in zoom-in-95 duration-150"
                      style="box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.18), 0 4px 12px -2px rgba(99, 102, 241, 0.1);"
                    >
                      <div class="px-2 py-1 border-b border-slate-100 mb-1 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Select Assignee</span>
                        <span class="text-[10px] font-bold text-indigo-600">{{ filteredStaffList.length }} members</span>
                      </div>

                      <div class="px-1 py-1 mb-1">
                        <input
                          v-model="staffSearchQuery"
                          type="text"
                          placeholder="🔍 Search staff..."
                          class="w-full h-7 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-indigo-500 focus:bg-white text-slate-700 font-medium"
                        />
                      </div>

                      <div class="max-h-44 overflow-y-auto space-y-0.5 custom-scrollbar">
                        <!-- Option: Unassigned / Me -->
                        <button
                          type="button"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-left text-xs font-semibold transition-colors cursor-pointer"
                          :class="!todoForm.assigned_to ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                          @click="todoForm.assigned_to = null; showDrawerAssigneeMenu = false;"
                        >
                          <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 font-bold text-[10px] flex items-center justify-center">
                              👤
                            </div>
                            <div>
                              <p class="text-xs font-bold leading-tight">Assigned to Me</p>
                              <p class="text-[10px] text-slate-400 font-normal">Personal task</p>
                            </div>
                          </div>
                          <span v-if="!todoForm.assigned_to" class="text-indigo-600 font-bold text-sm">✓</span>
                        </button>

                        <!-- Option: Staff Members -->
                        <button
                          v-for="s in filteredStaffList"
                          :key="s.id"
                          type="button"
                          class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-xl text-left text-xs font-semibold transition-colors cursor-pointer"
                          :class="todoForm.assigned_to == s.id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                          @click="todoForm.assigned_to = s.id; showDrawerAssigneeMenu = false;"
                        >
                          <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-extrabold text-[10px] flex items-center justify-center flex-shrink-0 shadow-xs">
                              {{ getStaffInitials(s.name) }}
                            </div>
                            <div class="truncate">
                              <p class="text-xs font-bold leading-tight truncate text-slate-800">{{ s.name }}</p>
                              <p class="text-[10px] text-slate-400 font-normal truncate">{{ s.email }}</p>
                            </div>
                          </div>
                          <span v-if="todoForm.assigned_to == s.id" class="text-indigo-600 font-bold text-sm flex-shrink-0">✓</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-700 mb-1">Priority</label>
                  <select
                    v-model="todoForm.priority"
                    class="w-full h-10 px-3 text-xs font-semibold theme-input-ctrl cursor-pointer"
                  >
                    <option value="high">🔴 High Priority</option>
                    <option value="medium">🟡 Medium Priority</option>
                    <option value="low">🔵 Low Priority</option>
                  </select>
                </div>
              </div>

              <!-- Quick Presets / Suggestions -->
              <div class="preset-suggestions">
                <div class="preset-header">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                  <span class="preset-lbl">Quick Suggestions</span>
                </div>
                <div class="preset-chips">
                  <button
                    v-for="preset in presets"
                    :key="preset"
                    class="preset-chip"
                    @click="todoForm.description = preset"
                  >
                    <span class="preset-plus">+</span> {{ preset }}
                  </button>
                </div>
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showTodoDrawer = false">Cancel</button>
              <button class="btn-save" @click="saveNewTodo" :disabled="!todoForm.description.trim()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><polyline points="20 6 9 17 4 12"/></svg>
                Save To-Do
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { VueDraggable } from 'vue-draggable-plus';

export default defineComponent({
  name: 'MyTodos',
  components: { VueDraggable },
  setup() {
    const route = useRoute();
    const showTodoDrawer = ref(false);
    const allTodos = ref([]);
    const editingId = ref(null);
    const editText = ref('');
    const editInput = ref(null);
    const drawerTextarea = ref(null);
    const searchQuery = ref('');
    const activeFilter = ref('all');
    const isCompletedExpanded = ref(true);

    const todoForm = ref({
      description: '',
      assigned_to: null,
      priority: 'medium',
      due_date: ''
    });

    const notifyTodosChanged = () => {
      if (typeof window !== 'undefined' && window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('refresh-todos'));
      }
    };

    const saveNewTodo = async () => {
      if (!todoForm.value.description.trim()) return;
      try {
        const res = await axios.post('/api/todos', {
          description: todoForm.value.description.trim(),
          assigned_to: todoForm.value.assigned_to ? parseInt(todoForm.value.assigned_to) : null,
          priority: todoForm.value.priority,
          due_date: todoForm.value.due_date
        });
        allTodos.value.push(res.data);
        todoForm.value.description = '';
        todoForm.value.assigned_to = null;
        todoForm.value.priority = 'medium';
        todoForm.value.due_date = '';
        showTodoDrawer.value = false;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to save todo', e);
      }
    };

    const presets = [
      'Follow up with client regarding proposal',
      'Review pending contracts & agreements',
      'Prepare weekly progress report',
      'Schedule team sync meeting',
      'Audit open invoices & payments'
    ];

    const isDone = (t) => t.done === true || t.done === 1 || t.done === '1' || t.done === 'true';

    const unfinishedTodos = computed({
      get: () => allTodos.value.filter(t => !isDone(t)),
      set: (val) => {
        const doneItems = allTodos.value.filter(t => isDone(t));
        allTodos.value = [...val, ...doneItems];
      },
    });

    const finishedTodos = computed(() => allTodos.value.filter(t => isDone(t)));

    const completionPercentage = computed(() => {
      if (allTodos.value.length === 0) return 0;
      return Math.round((finishedTodos.value.length / allTodos.value.length) * 100);
    });

    const staffMembers = ref([]);
    const selectedStaffFilter = ref('all');

    const matchesSearch = (item) => {
      if (!item) return false;
      if (selectedStaffFilter.value !== 'all') {
        const filterId = parseInt(selectedStaffFilter.value);
        if (item.assigned_to != filterId && item.staff_id != filterId) return false;
      }
      if (!searchQuery.value.trim()) return true;
      return item.description && item.description.toLowerCase().includes(searchQuery.value.trim().toLowerCase());
    };

    const filteredUnfinished = computed(() => {
      return unfinishedTodos.value.filter(t => matchesSearch(t));
    });

    const filteredFinished = computed(() => {
      return finishedTodos.value.filter(t => matchesSearch(t));
    });

    const extractStaffList = (raw) => {
      if (!raw) return [];
      if (Array.isArray(raw)) return raw;
      if (Array.isArray(raw.data)) return raw.data;
      if (raw.staff && Array.isArray(raw.staff.data)) return raw.staff.data;
      if (raw.staff && Array.isArray(raw.staff)) return raw.staff;
      if (raw.data && Array.isArray(raw.data.data)) return raw.data.data;
      if (raw.data && raw.data.staff && Array.isArray(raw.data.staff.data)) return raw.data.staff.data;
      return [];
    };

    const fetchStaffMembers = async () => {
      try {
        const res = await axios.get('/api/staff', { params: { per_page: 200 } });
        let list = extractStaffList(res.data);
        if (list.length === 0) {
          const resFallback = await axios.get('/api/staff');
          list = extractStaffList(resFallback.data);
        }
        staffMembers.value = list;
      } catch (e) {
        console.error('Failed to load staff list', e);
      }
    };

    const fetchTodos = async () => {
      try {
        const res = await axios.get('/api/todos');
        allTodos.value = res.data || [];
      } catch (e) {
        console.error('Failed to load todos', e);
      }
    };

    const openDrawer = () => {
      showTodoDrawer.value = true;
      nextTick(() => {
        if (drawerTextarea.value) drawerTextarea.value.focus();
      });
    };

    const openAssigneeMenuId = ref(null);
    const showDrawerAssigneeMenu = ref(false);
    const staffSearchQuery = ref('');

    const toggleAssigneeMenu = (todoId) => {
      if (openAssigneeMenuId.value == todoId) {
        openAssigneeMenuId.value = null;
      } else {
        openAssigneeMenuId.value = todoId;
        staffSearchQuery.value = '';
      }
    };

    const getAssignedStaff = (staffId) => {
      if (!staffId) return null;
      return staffMembers.value.find(s => s.id == staffId) || null;
    };

    const getStaffInitials = (name) => {
      if (!name) return 'U';
      return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    };

    const filteredStaffList = computed(() => {
      if (!staffSearchQuery.value.trim()) return staffMembers.value;
      const q = staffSearchQuery.value.trim().toLowerCase();
      return staffMembers.value.filter(s =>
        (s.name && s.name.toLowerCase().includes(q)) ||
        (s.email && s.email.toLowerCase().includes(q))
      );
    });

    const selectAssignee = async (item, staffId) => {
      openAssigneeMenuId.value = null;
      await updateTodoAssignee(item, staffId);
    };

    onMounted(() => {
      fetchTodos();
      fetchStaffMembers();
      if (route.query.new === 'true') {
        openDrawer();
      }
      if (typeof window !== 'undefined') {
        window.addEventListener('click', () => {
          openAssigneeMenuId.value = null;
        });
      }
    });

    const updateTodoAssignee = async (item, val) => {
      const staffId = (val === '' || val === 'null' || val === null || val === 'undefined') ? null : parseInt(val);
      try {
        const res = await axios.put('/api/todos/' + item.id, { assigned_to: staffId });
        item.assigned_to = staffId;
        if (res.data?.assigned_to_staff) {
          item.assigned_to_staff = res.data.assigned_to_staff;
        }
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to update assignee', e);
      }
    };

    const updateTodoPriority = async (item, priority) => {
      try {
        await axios.put('/api/todos/' + item.id, { priority });
        item.priority = priority;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to update priority', e);
      }
    };

    const toggleTodo = async (item) => {
      try {
        await axios.put('/api/todos/' + item.id, { done: !item.done });
        item.done = !item.done;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to toggle todo', e);
      }
    };

    const deleteTodo = async (item) => {
      try {
        await axios.delete('/api/todos/' + item.id);
        allTodos.value = allTodos.value.filter(t => t.id !== item.id);
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to delete todo', e);
      }
    };

    const startEdit = (item) => {
      editingId.value = item.id;
      editText.value = item.description;
      nextTick(() => {
        if (editInput.value) editInput.value.focus();
      });
    };

    const saveEdit = async (item) => {
      if (!editText.value.trim()) { cancelEdit(); return; }
      if (editText.value.trim() === item.description) { cancelEdit(); return; }
      try {
        await axios.put('/api/todos/' + item.id, { description: editText.value.trim() });
        item.description = editText.value.trim();
        cancelEdit();
      } catch (e) {
        console.error('Failed to update todo', e);
      }
    };

    const cancelEdit = () => {
      editingId.value = null;
      editText.value = '';
    };

    const onDragEnd = async () => {
      const order = unfinishedTodos.value.map(t => t.id);
      try {
        await axios.put('/api/todos-reorder', { order });
      } catch (e) {
        console.error('Failed to reorder todos', e);
      }
    };

    return {
      showTodoDrawer, todoForm, unfinishedTodos, finishedTodos, allTodos,
      completionPercentage, searchQuery, activeFilter, isCompletedExpanded,
      filteredUnfinished, filteredFinished, matchesSearch, presets, drawerTextarea,
      editingId, editText, editInput, openDrawer,
      saveNewTodo, toggleTodo, deleteTodo,
      startEdit, saveEdit, cancelEdit, onDragEnd,
      staffMembers, selectedStaffFilter, updateTodoAssignee, updateTodoPriority,
      openAssigneeMenuId, showDrawerAssigneeMenu, staffSearchQuery, filteredStaffList,
      getAssignedStaff, getStaffInitials, selectAssignee, toggleAssigneeMenu,
    };
  },
});
</script>

<style scoped>
.todos-page {
  font-family: inherit;
  color: #1e293b;
  max-width: 1040px;
  margin: 0 auto;
  padding-bottom: 40px;
}

/* Header */
.todos-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
  gap: 16px;
  flex-wrap: wrap;
}

.todos-header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.todos-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.todos-icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: var(--theme-primary, #6366f1);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(159, 142, 214, 0.3);
}

.todos-title {
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.01em;
}

.todos-subtitle {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.btn-add-todo {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  background: var(--theme-primary, #6366f1);
  color: #ffffff;
  font-size: 13px;
  font-weight: 500;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(159, 142, 214, 0.3);
  transition: all 0.2s ease;
  font-family: inherit;
}

.btn-add-todo:hover {
  background: var(--theme-primary-hover, #4f46e5);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(159, 142, 214, 0.4);
}

/* KPI Cards */
.todos-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
  margin-bottom: 22px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 10px -2px rgba(15, 23, 42, 0.03);
}

.kpi-icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon-box.total { background: #f1f5f9; color: #475569; }
.kpi-icon-box.pending { background: #fef3c7; color: #d97706; }
.kpi-icon-box.done { background: #dcfce7; color: #16a34a; }

.kpi-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kpi-label {
  font-size: 11.5px;
  font-weight: 500;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.kpi-value {
  font-size: 18px;
  font-weight: 600;
  color: #0f172a;
}

.kpi-value.warning { color: #d97706; }
.kpi-value.success { color: #16a34a; }

.progress-card {
  flex-direction: column;
  align-items: stretch;
  justify-content: center;
  gap: 8px;
}

.kpi-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.kpi-perc {
  font-size: 13px;
  font-weight: 600;
  color: var(--theme-primary, #6366f1);
}

.progress-track {
  width: 100%;
  height: 7px;
  background: #f1f5f9;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: var(--theme-primary, #6366f1);
  border-radius: 9999px;
  transition: width 0.4s ease;
}

/* Controls Bar */
.todos-controls-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.todo-search-box {
  position: relative;
  width: 320px;
  max-width: 100%;
  display: flex;
  align-items: center;
}

.todo-search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
  z-index: 2;
  display: block;
  width: 15px;
  height: 15px;
}

.todo-search-input {
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding-left: 40px !important;
  padding-right: 34px !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 400;
  color: #1e293b;
  outline: none;
  font-family: inherit;
  box-sizing: border-box;
  transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.todo-search-input:focus {
  border-color: var(--theme-primary, #6366f1);
  box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.2);
}

.clear-search {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #64748b;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: all 0.15s ease;
  z-index: 2;
}

.clear-search:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.filter-tabs {
  display: inline-flex;
  align-items: center;
  height: 38px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 3px;
  border-radius: 10px;
  gap: 3px;
  box-sizing: border-box;
}

.filter-tab {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 100%;
  padding: 0 12px;
  border-radius: 7px;
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: inherit;
  box-sizing: border-box;
}

.filter-tab:hover {
  color: #1e293b;
}

.filter-tab.active {
  background: #f1f5f9;
  color: var(--theme-primary, #4f46e5);
  font-weight: 600;
}

.tab-count {
  font-size: 11px;
  padding: 1px 6px;
  border-radius: 9999px;
  background: #e2e8f0;
  color: #475569;
}

.filter-tab.active .tab-count {
  background: rgba(159, 142, 214, 0.15);
  color: var(--theme-primary, #4338ca);
}

/* Card Panel */
.todos-card-panel {
  background: #ffffff;
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 16px;
  overflow: visible;
  box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(0, 0, 0, 0.02);
  margin-bottom: 24px;
  transition: all 0.2s ease;
  position: relative;
}

.todos-card-panel:hover {
  border-color: rgba(99, 102, 241, 0.25);
  box-shadow: 0 8px 25px -4px rgba(99, 102, 241, 0.08);
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: linear-gradient(180deg, #fcfdfe 0%, #f8fafc 100%);
  border-bottom: 1px solid #f1f5f9;
}

.panel-header-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.panel-title {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.01em;
}

.badge-count {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 10px;
  border-radius: 9999px;
  letter-spacing: 0.02em;
}

.badge-count.warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.badge-count.success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

.drag-hint {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 500;
  color: #94a3b8;
  background: #ffffff;
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

/* Todos List */
.todos-list {
  display: flex;
  flex-direction: column;
}

.todo-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
  background: #ffffff;
  transition: all 0.2s ease;
  position: relative;
}

.todo-row:last-child {
  border-bottom: none;
}

.todo-row:hover {
  background: #f8fafc;
  transform: translateX(2px);
}

.todo-row--done {
  background: #fafbfc;
  opacity: 0.85;
}

.drag-grip {
  color: #cbd5e1;
  cursor: grab;
  display: flex;
  align-items: center;
  padding: 6px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.drag-grip:hover {
  color: var(--theme-primary, #6366f1);
  background: rgba(99, 102, 241, 0.12);
  transform: scale(1.1);
}

.drag-grip:active {
  cursor: grabbing;
}

.todo-ghost {
  opacity: 0.35;
  background: rgba(99, 102, 241, 0.1) !important;
  border: 2px dashed #6366f1 !important;
}

/* Custom Checkbox */
.custom-checkbox-wrapper {
  display: flex;
  align-items: center;
  cursor: pointer;
  position: relative;
}

.native-checkbox {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.checkbox-box {
  width: 22px;
  height: 22px;
  border-radius: 7px;
  border: 2px solid #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  background: #ffffff;
}

.custom-checkbox-wrapper:hover .checkbox-box {
  border-color: var(--theme-primary, #6366f1);
  transform: scale(1.08);
}

.native-checkbox:checked + .checkbox-box, .checkbox-box.checked {
  background: #10b981;
  border-color: #10b981;
}

.check-svg {
  width: 12px;
  height: 12px;
  color: #ffffff;
  opacity: 0;
  transform: scale(0.5);
  transition: all 0.15s ease;
}

.native-checkbox:checked + .checkbox-box .check-svg, .checkbox-box.checked .check-svg {
  opacity: 1;
  transform: scale(1);
}

.todo-row-body {
  flex: 1;
  min-width: 0;
}

.todo-text {
  font-size: 13.5px;
  font-weight: 500;
  color: #1e293b;
  margin: 0 0 2px 0;
  line-height: 1.45;
  word-break: break-word;
}

.todo-text.done {
  color: #94a3b8;
  text-decoration: line-through;
  font-weight: 400;
}

.todo-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.todo-date-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 400;
  color: #94a3b8;
}

.todo-date-badge.done {
  color: #10b981;
  font-weight: 500;
}

.double-click-tip {
  font-size: 10.5px;
  color: #cbd5e1;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.todo-row:hover .double-click-tip {
  opacity: 1;
}

/* Edit input */
.edit-input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.todo-edit-input {
  width: 100%;
  padding: 7px 11px;
  border: 1.5px solid var(--theme-primary, #6366f1);
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 500;
  color: #0f172a;
  background: #ffffff;
  outline: none;
  font-family: inherit;
  box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.2);
}

.edit-hint {
  font-size: 10.5px;
  font-weight: 500;
  color: var(--theme-primary, #6366f1);
}

/* Actions */
.todo-row-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.15s ease;
}

.todo-row:hover .todo-row-actions {
  opacity: 1;
}

.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s ease;
}

.action-btn.edit { color: #475569; }
.action-btn.edit:hover { background: rgba(159, 142, 214, 0.12); color: var(--theme-primary, #4f46e5); }

.action-btn.delete { color: #94a3b8; }
.action-btn.delete:hover { background: #fff1f2; color: #e11d48; }

.action-btn.restore { color: #059669; }
.action-btn.restore:hover { background: #ecfdf5; color: #047857; }

/* Empty state */
.empty-panel-state {
  padding: 36px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: #f8fafc;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
}

.empty-title {
  font-size: 14px;
  font-weight: 500;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.empty-sub {
  font-size: 12.5px;
  color: #94a3b8;
  margin: 0;
}

/* Drawer */
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(5px);
  z-index: 1000;
  display: flex;
  justify-content: flex-end;
}

.drawer-panel {
  width: 450px;
  max-width: 92vw;
  background: #ffffff;
  height: 100%;
  display: flex;
  flex-direction: column;
  box-shadow: -12px 0 48px rgba(15, 23, 42, 0.18);
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  background: #fafafa;
  border-bottom: 1px solid #f1f5f9;
}

.drawer-header-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.drawer-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: rgba(159, 142, 214, 0.15);
  color: var(--theme-primary, #4f46e5);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.drawer-title {
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 2px 0;
  letter-spacing: -0.01em;
}

.drawer-sub {
  font-size: 12px;
  color: #64748b;
  margin: 0;
}

.drawer-close-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.drawer-close-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #cbd5e1;
}

.drawer-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.form-group {
  margin-bottom: 24px;
}

.input-lbl-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.input-lbl {
  font-size: 12.5px;
  font-weight: 500;
  color: #334155;
  margin: 0;
}

.char-count {
  font-size: 11px;
  color: #94a3b8;
}

.req { color: #e11d48; }

.input-ctrl {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 400;
  color: #0f172a;
  background: #ffffff;
  outline: none;
  font-family: inherit;
  box-sizing: border-box;
  transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.input-ctrl:focus {
  border-color: var(--theme-primary, #6366f1);
  box-shadow: 0 0 0 3.5px rgba(159, 142, 214, 0.2);
}

.textarea {
  resize: vertical;
  min-height: 110px;
  line-height: 1.5;
}

.input-helper {
  font-size: 11.5px;
  color: #94a3b8;
  margin: 6px 0 0 0;
}

.preset-suggestions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 12px;
  padding: 14px 16px;
}

.preset-header {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--theme-primary, #6366f1);
}

.preset-lbl {
  font-size: 11.5px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.preset-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.preset-chip {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 400;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s ease;
  font-family: inherit;
  text-align: left;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.preset-plus {
  color: var(--theme-primary, #6366f1);
  font-weight: 600;
}

.preset-chip:hover {
  background: rgba(159, 142, 214, 0.1);
  border-color: var(--theme-primary, #9f8ed6);
  color: var(--theme-primary, #4338ca);
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.drawer-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding: 18px 24px;
  border-top: 1px solid #f1f5f9;
  background: #fafafa;
}

.btn-cancel {
  padding: 9px 18px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
  background: #ffffff;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s ease;
}

.btn-cancel:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
  color: #0f172a;
}

.btn-save {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9.5px 20px;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #ffffff;
  background: var(--theme-primary, #6366f1);
  cursor: pointer;
  font-family: inherit;
  box-shadow: 0 4px 12px rgba(159, 142, 214, 0.3);
  transition: all 0.15s ease;
}

.btn-save:hover:not(:disabled) {
  background: var(--theme-primary-hover, #4f46e5);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(159, 142, 214, 0.4);
}

.btn-save:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  box-shadow: none;
}

.drawer-fade-enter-active, .drawer-fade-leave-active { transition: opacity 0.2s ease; }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }
.drawer-slide-enter-active, .drawer-slide-leave-active { transition: transform 0.25s ease; }
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }
</style>
