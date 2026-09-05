<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Tasks</h1>
        </div>
        <div class="flex items-center gap-2 mt-1 pl-4.5">
          <p class="text-xs text-[#A8AAAE] m-0">Manage and track your project tasks and workflow</p>
          <span class="text-[#DBDADE]">•</span>
          <router-link
            :to="{ name: 'admin.tasks.overview' }"
            class="text-xs font-semibold text-[#7367F0] hover:underline inline-flex items-center gap-1"
          >
            Tasks Overview
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="9 18 15 12 9 6"/></svg>
          </router-link>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <!-- View Toggle Buttons -->
        <div class="flex items-center bg-white p-1 border border-[#DBDADE] rounded-md shadow-2xs">
          <button
            class="px-2.5 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            :class="currentView === 'kanban' ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:text-[#4B465C]'"
            @click="currentView = 'kanban'"
            title="Kanban View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            <span>Kanban</span>
          </button>
          <button
            class="px-2.5 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            :class="currentView === 'table' ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:text-[#4B465C]'"
            @click="currentView = 'table'"
            title="Table View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>Table</span>
          </button>
        </div>

        <!-- New Task Button -->
        <button
          v-if="canCreateTask"
          @click="openCreate"
          class="btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-md cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>New Task</span>
        </button>
      </div>
    </div>

    <!-- Summary KPI Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        @click="filterByStatus(card.statusValue)"
        class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer flex items-center justify-between group"
        :class="{ 'ring-2 ring-[#7367F0] border-transparent': filters.status === card.statusValue }"
      >
        <div class="space-y-1">
          <span class="text-[11px] font-bold uppercase tracking-wider text-[#A8AAAE] block">{{ card.label }}</span>
          <div class="text-xl font-extrabold" :style="{ color: card.textColor }">
            {{ card.value }}
          </div>
          <div v-if="card.myTasks !== undefined" class="text-[10px] text-[#A8AAAE] font-semibold">
            My Tasks: <span class="text-[#4B465C] font-bold">{{ card.myTasks }}</span>
          </div>
        </div>
        <div
          class="w-11 h-11 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 flex-shrink-0"
          :style="{ backgroundColor: card.bgLight, color: card.color }"
          v-html="card.icon"
        ></div>
      </div>
    </div>

    <!-- ====== KANBAN VIEW ====== -->
    <div v-if="currentView === 'kanban'" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="col in kanbanColumns"
        :key="col.status"
        class="bg-white border border-[#EBE9F1] rounded-lg p-3.5 shadow-sm flex flex-col space-y-3"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
          <div class="flex items-center space-x-2">
            <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: col.color }"></div>
            <span class="text-xs font-bold text-[#4B465C]">{{ col.title }}</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]">
            {{ tasksByStatus(col.status).length }}
          </span>
        </div>

        <!-- Cards List & Drag Target -->
        <div
          class="space-y-2.5 flex-1 min-h-[260px] transition-colors rounded-lg p-1"
          :class="{ 'bg-[#7367F0]/5 border-2 border-dashed border-[#7367F0]': dragCol === col.status }"
          @dragover.prevent="dragCol = col.status"
          @dragenter.prevent
          @dragleave="dragCol = null"
          @drop="onDrop(col.status)"
        >
          <div
            v-for="t in tasksByStatus(col.status)"
            :key="t.id"
            class="bg-[#F8F7FA] border border-[#EBE9F1] hover:border-[#7367F0]/40 rounded-lg p-3 shadow-2xs hover:shadow-sm transition-all space-y-2.5 cursor-grab active:cursor-grabbing group"
            draggable="true"
            @dragstart="onDragStart(t)"
            @dragend="dragCol = null"
          >
            <!-- Top Card Badges -->
            <div class="flex items-center justify-between gap-1">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="priClass(t.priority)">
                {{ t.priority }}
              </span>
              <button
                @click="openEdit(t)"
                class="w-6 h-6 rounded hover:bg-white text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-colors cursor-pointer"
                title="Edit Task"
              >
                <svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
              </button>
            </div>

            <!-- Task Name -->
            <div
              class="text-xs font-bold text-[#4B465C] group-hover:text-[#7367F0] transition-colors cursor-pointer line-clamp-2 leading-snug"
              @click="openEdit(t)"
            >
              {{ t.name }}
            </div>

            <!-- Tags -->
            <div v-if="t.tags && parseTags(t.tags).length" class="flex flex-wrap gap-1">
              <span
                v-for="tag in parseTags(t.tags)"
                :key="tag"
                class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#7367F0]/10 text-[#7367F0]"
              >
                {{ tag }}
              </span>
            </div>

            <!-- Card Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-[#DBDADE]/50 text-[10px]">
              <span
                class="flex items-center gap-1 font-semibold"
                :class="isOverdue(t) ? 'text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded' : 'text-[#A8AAAE]'"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ fmtDate(t.due_date) }}
              </span>

              <div
                class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow-2xs"
                :class="t.assignee ? 'bg-gradient-to-tr from-[#7367F0] to-[#9F8ED6]' : 'bg-[#DBDADE] text-[#6F6B7D]'"
                :title="t.assignee?.name || 'Unassigned'"
              >
                {{ t.assignee ? t.assignee.name.charAt(0).toUpperCase() : '?' }}
              </div>
            </div>
          </div>

          <!-- Empty State Container -->
          <div
            v-if="!tasksByStatus(col.status).length"
            class="h-28 border-2 border-dashed border-[#DBDADE]/60 rounded-lg flex items-center justify-center text-[11px] text-[#A8AAAE] font-semibold"
          >
            Drop here
          </div>
        </div>

        <!-- Add Button -->
        <button
          v-if="canCreateTask"
          @click="openCreateForStatus(col.status)"
          class="w-full py-2 rounded-md border border-dashed border-[#DBDADE] hover:border-[#7367F0] hover:bg-[#7367F0]/5 text-[#6F6B7D] hover:text-[#7367F0] text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Add Task</span>
        </button>
      </div>
    </div>

    <!-- ====== TABLE VIEW ====== -->
    <div v-else class="space-y-4">
      <!-- Filter Bar -->
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
          <!-- Per page -->
          <div class="flex items-center space-x-2">
            <span class="text-xs text-[#A8AAAE] font-medium">Show</span>
            <div class="relative">
              <select
                v-model="perPage"
                @change="loadTasks"
                class="form-ctrl text-xs h-[36px] pl-3 pr-7 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
              >
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
              </div>
            </div>
          </div>

          <!-- Priority Filter -->
          <div class="relative">
            <select
              v-model="filters.priority"
              @change="loadTasks"
              class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
            >
              <option value="">All Priority</option>
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
              <option value="Urgent">Urgent</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>

          <!-- Staff Filter -->
          <div class="relative">
            <select
              v-model="filters.assigned_to"
              @change="loadTasks"
              class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
            >
              <option value="">All Staff</option>
              <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>
        </div>

        <!-- Search input -->
        <div class="relative w-full md:w-64">
          <input
            v-model="filters.search"
            @input="onSearch"
            type="text"
            placeholder="Search tasks..."
            class="form-ctrl text-xs h-[36px] pl-9 pr-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#A8AAAE]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto min-h-[320px]">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#6F6B7D]">
                <th class="py-3 px-3.5 text-center w-12">#</th>
                <th class="py-3 px-3.5 min-w-[240px]">Task Name</th>
                <th class="py-3 px-3.5">Status</th>
                <th class="py-3 px-3.5">Start Date</th>
                <th class="py-3 px-3.5">Due Date</th>
                <th class="py-3 px-3.5 min-w-[140px]">Assigned To</th>
                <th class="py-3 px-3.5 min-w-[110px]">Tags</th>
                <th class="py-3 px-3.5">Priority</th>
                <th class="py-3 px-3.5 text-center w-32">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F0F2] text-xs text-[#6F6B7D]">
              <tr v-if="loading">
                <td colspan="9" class="text-center py-16 text-[#A8AAAE]">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="animate-spin text-[#7367F0]"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    <span class="text-xs font-semibold">Loading tasks...</span>
                  </div>
                </td>
              </tr>

              <tr
                v-for="(t, idx) in tasks"
                :key="t.id"
                class="hover:bg-[#F8F7FA]/70 transition-colors group"
              >
                <!-- Index -->
                <td class="py-3.5 px-3.5 text-center text-[#A8AAAE] font-mono text-[11px]">
                  {{ idx + 1 + (page - 1) * (+perPage) }}
                </td>

                <!-- Name & Description -->
                <td class="py-3.5 px-3.5">
                  <div class="flex flex-col">
                    <span
                      class="font-bold text-[#4B465C] hover:text-[#7367F0] transition-colors"
                      :class="{ 'cursor-pointer': canEditTask }"
                      @click="canEditTask ? openEdit(t) : null"
                    >
                      {{ t.name }}
                    </span>
                    <span v-if="t.description" class="text-[11px] text-[#A8AAAE] line-clamp-1 mt-0.5">
                      {{ truncate(t.description, 55) }}
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-3.5 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-2xs"
                    :class="statusBadgeClass(t.status)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(t.status)"></span>
                    {{ t.status }}
                  </span>
                </td>

                <!-- Start Date -->
                <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                  {{ fmtDate(t.start_date) }}
                </td>

                <!-- Due Date -->
                <td class="py-3.5 px-3.5 whitespace-nowrap">
                  <span
                    class="font-semibold"
                    :class="isOverdue(t) ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px]' : 'text-[#6F6B7D]'"
                  >
                    {{ fmtDate(t.due_date) }}
                  </span>
                </td>

                <!-- Assigned To -->
                <td class="py-3.5 px-3.5">
                  <div v-if="t.assignee" class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-full bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                      {{ t.assignee.name.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-semibold text-[#4B465C] truncate max-w-[120px]">{{ t.assignee.name }}</span>
                  </div>
                  <span v-else class="text-[#A8AAAE]">—</span>
                </td>

                <!-- Tags -->
                <td class="py-3.5 px-3.5">
                  <div class="flex items-center gap-1 flex-wrap">
                    <span v-if="!t.tags || !parseTags(t.tags).length" class="text-[#A8AAAE]">—</span>
                    <span
                      v-for="tag in parseTags(t.tags)"
                      :key="tag"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </td>

                <!-- Priority -->
                <td class="py-3.5 px-3.5 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="priClass(t.priority)">
                    {{ t.priority }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-3.5 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <!-- Timer Button -->
                    <button
                      @click="toggleTimer(t)"
                      class="px-2 py-1 rounded border text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer"
                      :class="t.timerRunning ? 'border-rose-300 bg-rose-50 text-rose-600' : 'border-[#DBDADE] bg-[#F8F7FA] text-[#6F6B7D] hover:text-[#7367F0] hover:border-[#7367F0]'"
                      :title="t.timerRunning ? 'Stop Timer' : 'Start Timer'"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><circle cx="12" cy="12" r="10"/><polyline v-if="!t.timerRunning" points="10 8 16 12 10 16 10 8"/><rect v-else x="9" y="9" width="6" height="6"/></svg>
                      <span>{{ t.timerRunning ? 'Stop' : 'Start' }}</span>
                    </button>

                    <!-- Edit Button -->
                    <button
                      v-if="canEditTask"
                      @click="openEdit(t)"
                      class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                      title="Edit Task"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                    </button>

                    <!-- Delete Button -->
                    <button
                      v-if="canDeleteTask"
                      @click="deleteTask(t.id)"
                      class="w-7 h-7 rounded border border-transparent hover:border-rose-200 hover:bg-rose-50 text-[#A8AAAE] hover:text-rose-600 flex items-center justify-center transition-all cursor-pointer bg-transparent"
                      title="Delete Task"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && !tasks.length">
                <td colspan="9" class="text-center py-12 text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="mx-auto mb-2 opacity-50"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                  <p class="text-xs font-semibold m-0">No tasks found</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-5 py-3 border-t border-[#F1F0F2] text-xs text-[#6F6B7D]" v-if="totalPages > 1">
          <span class="text-[#A8AAAE]">Showing {{ tasks.length }} of {{ totalPages * (+perPage) }} entries</span>
          <div class="flex items-center space-x-2">
            <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page <= 1" @click="page--; loadTasks()">Previous</button>
            <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page >= totalPages" @click="page++; loadTasks()">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE / EDIT TASK RIGHT-SIDE DRAWER -->
    <a-drawer
      v-model:open="showModal"
      placement="right"
      :width="640"
      :destroyOnClose="true"
      class="vuexy-task-drawer"
      @close="closeModal"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">
              {{ editingId ? 'Edit Task' : 'Add New Task' }}
            </h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">
              {{ editingId ? 'Update task details, assignees, and deadlines' : 'Fill in the task details and assign to team members' }}
            </p>
          </div>
        </div>
      </template>

      <div class="p-1 space-y-6">
        <!-- 1. General Info & Controls -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full bg-[#7367F0]"></span>
              <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">General Information</span>
            </div>
            <div class="flex items-center gap-3">
              <label class="flex items-center space-x-1.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="form.is_public"
                  class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-3.5 h-3.5 cursor-pointer"
                />
                <span class="text-xs font-semibold text-[#4B465C]">Public Task</span>
              </label>
              <label class="flex items-center space-x-1.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="form.billable"
                  class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-3.5 h-3.5 cursor-pointer"
                />
                <span class="text-xs font-semibold text-[#4B465C]">Billable</span>
              </label>
            </div>
          </div>

          <div class="space-y-4">
            <!-- Subject -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Subject <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. Design Landing Page Mockups"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Priority & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Priority</label>
                <div class="relative">
                  <select
                    v-model="form.priority"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
                <div class="relative">
                  <select
                    v-model="form.status"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Testing">Testing</option>
                    <option value="Awaiting Feedback">Awaiting Feedback</option>
                    <option value="Complete">Complete</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Schedule & Rate Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#28C76F]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Schedule & Rate</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Hourly Rate -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Hourly Rate ($)</label>
              <input
                v-model="form.hourly_rate"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Repeat Every -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Repeat Every</label>
              <div class="relative">
                <select
                  v-model="form.repeat_every"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">No repeat</option>
                  <option value="1 week">1 Week</option>
                  <option value="2 weeks">2 Weeks</option>
                  <option value="1 month">1 Month</option>
                  <option value="3 months">3 Months</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Start Date -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Start Date <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Due Date -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>
          </div>
        </div>

        <!-- 3. Related To Entity Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#00CFE8]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Related Entity</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Related To</label>
              <div class="relative">
                <select
                  v-model="form.related_to_type"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">Select type...</option>
                  <option value="Customer">Customer</option>
                  <option value="Project">Project</option>
                  <option value="Invoice">Invoice</option>
                  <option value="Lead">Lead</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Related ID #</label>
              <input
                v-model="form.related_to_id"
                type="number"
                placeholder="e.g. 101"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>
          </div>
        </div>

        <!-- 4. Assignees & Followers Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#FF9F43]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Assignees & Followers</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Assignees -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Assignees</label>
              <div class="max-h-40 overflow-y-auto p-2 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md space-y-1.5">
                <label
                  v-for="u in staff"
                  :key="'assign-' + u.id"
                  class="flex items-center space-x-2 p-1.5 bg-white rounded border border-[#EBE9F1] hover:border-[#7367F0] cursor-pointer transition-colors"
                >
                  <input
                    type="checkbox"
                    :value="u.id"
                    v-model="form.assignees"
                    class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-3.5 h-3.5 cursor-pointer"
                  />
                  <div class="w-5 h-5 rounded-full bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-[9px] font-bold">
                    {{ u.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="text-xs font-medium text-[#4B465C] truncate">{{ u.name }}</span>
                </label>
                <div v-if="!staff.length" class="text-center py-3 text-xs text-[#A8AAAE]">No staff available</div>
              </div>
            </div>

            <!-- Followers -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Followers</label>
              <div class="max-h-40 overflow-y-auto p-2 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md space-y-1.5">
                <label
                  v-for="u in staff"
                  :key="'follow-' + u.id"
                  class="flex items-center space-x-2 p-1.5 bg-white rounded border border-[#EBE9F1] hover:border-[#7367F0] cursor-pointer transition-colors"
                >
                  <input
                    type="checkbox"
                    :value="u.id"
                    v-model="form.followers"
                    class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-3.5 h-3.5 cursor-pointer"
                  />
                  <div class="w-5 h-5 rounded-full bg-[#FF9F43]/10 text-[#FF9F43] flex items-center justify-center text-[9px] font-bold">
                    {{ u.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="text-xs font-medium text-[#4B465C] truncate">{{ u.name }}</span>
                </label>
                <div v-if="!staff.length" class="text-center py-3 text-xs text-[#A8AAAE]">No staff available</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Tags & Description Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#EA5455]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Tags & Description</span>
          </div>

          <div class="space-y-4">
            <!-- Tags Input -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Tags</label>
              <div class="p-2 bg-white border border-[#DBDADE] rounded-md flex flex-wrap items-center gap-1.5 min-h-[38px] focus-within:border-[#7367F0]">
                <span
                  v-for="(tag, i) in form.tagList"
                  :key="i"
                  class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#7367F0]/10 text-[#7367F0] flex items-center gap-1"
                >
                  {{ tag }}
                  <button type="button" @click="removeTag(i)" class="text-[#7367F0] hover:text-rose-600 cursor-pointer font-bold">&times;</button>
                </span>
                <input
                  v-model="tagInput"
                  @keydown.enter.prevent="addTag"
                  @keydown.,.prevent="addTag"
                  placeholder="Type tag and hit Enter..."
                  class="text-xs border-none outline-none flex-1 min-w-[140px] bg-transparent py-0.5"
                />
              </div>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Task Description</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Add detailed task requirements, notes or summary..."
                class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[90px] w-full resize-none"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Drawer Footer -->
      <template #footer>
        <div class="flex items-center justify-end space-x-3 py-2 px-1">
          <button
            type="button"
            class="btn-outline px-5 py-2.5 text-xs font-semibold cursor-pointer"
            @click="closeModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 cursor-pointer"
            :disabled="saving"
            @click="saveTask"
          >
            <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editingId ? 'Save Changes' : 'Create Task') }}
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { useAuthStore } from '../../store/authStore'

const authStore = useAuthStore()
const canCreateTask = computed(() => authStore.hasPermission('Tasks', 'create'))
const canEditTask   = computed(() => authStore.hasPermission('Tasks', 'edit'))
const canDeleteTask = computed(() => authStore.hasPermission('Tasks', 'delete'))

const BASE = '/api'
const tasks = ref([])
const stats = ref({})
const staff = ref([])
const loading = ref(false)
const saving = ref(false)
const perPage = ref('25')
const page = ref(1)
const totalPages = ref(1)
const showModal = ref(false)
const editingId = ref(null)
const currentView = ref('kanban')
const tagInput = ref('')
const attachedFiles = ref([])
const dragCol = ref(null)
const dragTaskId = ref(null)

const filters = reactive({ search: '', priority: '', assigned_to: '', status: '' })

const form = reactive({
  name: '', description: '', priority: 'Medium', status: 'Not Started',
  start_date: '', due_date: '', assigned_to: null,
  related_to_type: '', related_to_id: '',
  billable: false, is_public: false, hourly_rate: 0,
  repeat_every: '', tags: '', assignees: [], followers: [], tagList: [],
})

const kanbanColumns = [
  { title: 'Not Started', status: 'Not Started', color: '#A8AAAE' },
  { title: 'In Progress', status: 'In Progress', color: '#7367F0' },
  { title: 'Testing', status: 'Testing', color: '#FF9F43' },
  { title: 'Awaiting Feedback', status: 'Awaiting Feedback', color: '#F97316' },
  { title: 'Complete', status: 'Complete', color: '#28C76F' },
]

const summaryCards = computed(() => [
  {
    label: 'Not Started',
    value: stats.value.not_started || 0,
    myTasks: stats.value.my_not_started || 0,
    color: '#A8AAAE',
    textColor: '#4B465C',
    bgLight: 'rgba(168, 170, 174, 0.12)',
    statusValue: 'Not Started',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/></svg>',
  },
  {
    label: 'In Progress',
    value: stats.value.in_progress || 0,
    myTasks: stats.value.my_in_progress || 0,
    color: '#7367F0',
    textColor: '#7367F0',
    bgLight: 'rgba(115, 103, 240, 0.12)',
    statusValue: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  },
  {
    label: 'Testing',
    value: stats.value.testing || 0,
    myTasks: stats.value.my_testing || 0,
    color: '#FF9F43',
    textColor: '#FF9F43',
    bgLight: 'rgba(255, 159, 67, 0.12)',
    statusValue: 'Testing',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5"/></svg>',
  },
  {
    label: 'Awaiting Feedback',
    value: stats.value.awaiting_feedback || 0,
    myTasks: stats.value.my_awaiting_feedback || 0,
    color: '#F97316',
    textColor: '#F97316',
    bgLight: 'rgba(249, 115, 22, 0.12)',
    statusValue: 'Awaiting Feedback',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>',
  },
  {
    label: 'Complete',
    value: stats.value.complete || 0,
    myTasks: stats.value.my_complete || 0,
    color: '#28C76F',
    textColor: '#28C76F',
    bgLight: 'rgba(40, 199, 111, 0.12)',
    statusValue: 'Complete',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><polyline points="20 6 9 17 4 12"/></svg>',
  },
])

function parseTags(str) {
  if (!str) return []
  try {
    const parsed = JSON.parse(str)
    return Array.isArray(parsed) ? parsed : [parsed]
  } catch {
    return str.split(',').map(t => t.trim()).filter(Boolean)
  }
}

function tasksByStatus(status) {
  return tasks.value.filter(t => t.status === status)
}

function statusBadgeClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]/10 text-[#6F6B7D] border border-[#A8AAAE]/20',
    'In Progress': 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    'Testing': 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    'Awaiting Feedback': 'bg-[#F97316]/10 text-[#F97316] border border-[#F97316]/20',
    'Complete': 'bg-[#28C76F]/10 text-[#28C76F] border border-[#28C76F]/20',
  }[s] || 'bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]'
}

function statusDotClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]',
    'In Progress': 'bg-[#7367F0]',
    'Testing': 'bg-[#FF9F43]',
    'Awaiting Feedback': 'bg-[#F97316]',
    'Complete': 'bg-[#28C76F]',
  }[s] || 'bg-[#6F6B7D]'
}

function priClass(p) {
  return {
    Low: 'bg-[#F1F0F2] text-[#6F6B7D]',
    Medium: 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    High: 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    Urgent: 'bg-rose-50 text-rose-600 border border-rose-200',
  }[p] || 'bg-[#7367F0]/10 text-[#7367F0]'
}

function isOverdue(t) {
  if (t.status === 'Complete') return false
  return t.due_date && new Date(t.due_date) < new Date()
}

function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function truncate(s, n) {
  return s?.length > n ? s.slice(0, n) + '...' : s
}

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) {
    form.tagList.push(val)
    form.tags = JSON.stringify(form.tagList)
  }
  tagInput.value = ''
}

function removeTag(i) {
  form.tagList.splice(i, 1)
  form.tags = JSON.stringify(form.tagList)
}

function filterByStatus(s) {
  filters.status = (filters.status === s) ? '' : s
  if (currentView.value !== 'kanban') loadTasks()
}

function onDragStart(task) {
  dragTaskId.value = task.id
}

function onDrop(newStatus) {
  const id = dragTaskId.value
  const task = tasks.value.find(t => t.id === id)
  if (id && task && task.status !== newStatus) {
    updateStatus(task, newStatus)
  }
  dragTaskId.value = null
  dragCol.value = null
}

async function loadStaff() {
  try {
    const r = await axios.get(`${BASE}/staff?per_page=500`)
    staff.value = r.data.staff?.data || []
  } catch {
    staff.value = []
  }
}

async function loadTasks() {
  loading.value = true
  const all = currentView.value === 'kanban'
  try {
    const params = {
      search: filters.search,
      priority: filters.priority,
      assigned_to: filters.assigned_to,
      status: filters.status,
      all,
      per_page: perPage.value,
      page: page.value,
    }
    const r = await axios.get(`${BASE}/tasks`, { params })
    if (all) {
      tasks.value = r.data.tasks || []
    } else {
      tasks.value = r.data.tasks?.data || []
      totalPages.value = r.data.tasks?.last_page || 1
    }
    stats.value = r.data.stats || {}
  } catch {
    tasks.value = []
    stats.value = {}
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; loadTasks() }, 350)
}

function openCreate() {
  if (!canCreateTask.value) return
  editingId.value = null
  Object.assign(form, {
    name: '', description: '', priority: 'Medium', status: 'Not Started',
    start_date: new Date().toISOString().slice(0, 10), due_date: '',
    assigned_to: null, related_to_type: '', related_to_id: '',
    billable: false, is_public: false, hourly_rate: 0,
    repeat_every: '', tags: '', assignees: [], followers: [], tagList: [],
  })
  tagInput.value = ''
  loadStaff()
  showModal.value = true
}

function openCreateForStatus(status) {
  if (!canCreateTask.value) return
  openCreate()
  form.status = status
}

function openEdit(task) {
  if (!canEditTask.value) return
  editingId.value = task.id
  const tagList = parseTags(task.tags)
  Object.assign(form, {
    name: task.name,
    description: task.description || '',
    priority: task.priority || 'Medium',
    status: task.status || 'Not Started',
    start_date: task.start_date?.slice?.(0, 10) || '',
    due_date: task.due_date?.slice?.(0, 10) || '',
    assigned_to: task.assigned_to || null,
    related_to_type: task.related_to_type || '',
    related_to_id: task.related_to_id || '',
    billable: !!task.billable,
    is_public: !!task.is_public,
    hourly_rate: task.hourly_rate || 0,
    repeat_every: task.repeat_every || '',
    tags: task.tags || '',
    assignees: task.assignees || [],
    followers: task.followers || [],
    tagList,
  })
  tagInput.value = ''
  loadStaff()
  showModal.value = true
}

async function saveTask() {
  if (!form.name) return message.error('Subject is required')
  if (!form.start_date) return message.error('Start date is required')
  saving.value = true
  try {
    const payload = { ...form, tags: form.tags || '' }
    if (editingId.value) {
      await axios.put(`${BASE}/tasks/${editingId.value}`, payload)
      message.success('Task updated successfully')
    } else {
      await axios.post(`${BASE}/tasks`, payload)
      message.success('Task created successfully')
    }
    closeModal()
    loadTasks()
  } catch (e) {
    const errs = e.response?.data?.errors
    message.error(errs ? Object.values(errs).flat().join(', ') : 'Failed to save task')
  } finally {
    saving.value = false
  }
}

async function updateStatus(task, newStatus) {
  try {
    await axios.put(`${BASE}/tasks/${task.id}`, { status: newStatus })
    message.success('Status updated')
    loadTasks()
  } catch {
    message.error('Failed to update status')
  }
}

async function deleteTask(id) {
  if (!canDeleteTask.value) return
  if (!confirm('Delete this task?')) return
  try {
    await axios.delete(`${BASE}/tasks/${id}`)
    message.success('Task deleted successfully')
    loadTasks()
  } catch {
    message.error('Failed to delete task')
  }
}

function toggleTimer(task) {
  task.timerRunning = !task.timerRunning
  message.success(task.timerRunning ? 'Timer started' : 'Timer stopped')
}

function closeModal() {
  showModal.value = false
  editingId.value = null
}

watch(currentView, () => {
  if (currentView.value === 'kanban') filters.status = ''
  loadTasks()
})

onMounted(() => {
  loadTasks()
  loadStaff()
})
</script>

<style scoped>
/* Vuexy Form & Button Tokens */
.form-ctrl {
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  color: #4B465C;
  font-family: inherit;
  outline: none;
}
.form-ctrl:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.12) !important;
}

.btn-primary {
  background-color: #7367F0;
  color: #FFFFFF;
  border-radius: 6px !important;
  border: none;
  transition: all 0.2s ease-in-out;
}
.btn-primary:hover:not(:disabled) {
  background-color: #685DD8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(115, 103, 240, 0.35);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-outline {
  background-color: #FFFFFF;
  color: #6F6B7D;
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  transition: all 0.15s ease-in-out;
}
.btn-outline:hover:not(:disabled) {
  background-color: #F8F7FA;
  border-color: #C4C2C7;
  color: #4B465C;
}
.btn-outline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

:deep(.vuexy-task-drawer .ant-drawer-header) {
  padding: 16px 24px;
  border-bottom: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
:deep(.vuexy-task-drawer .ant-drawer-body) {
  padding: 24px;
  background-color: #F8F7FA;
}
:deep(.vuexy-task-drawer .ant-drawer-footer) {
  padding: 12px 24px;
  border-top: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
</style>
