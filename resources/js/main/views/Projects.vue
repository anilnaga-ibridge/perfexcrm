<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Projects</h1>
        </div>
        <p class="text-xs text-[#A8AAAE] mt-1 pl-4.5 mb-0">Manage your active, scheduled, and completed projects</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Export Button -->
        <button
          @click="exportPDF"
          class="btn-outline px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer shadow-sm"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span>Export CSV</span>
        </button>

        <!-- New Project Button -->
        <button
          v-if="canCreateProject"
          @click="openCreate"
          class="btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-md cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>New Project</span>
        </button>
      </div>
    </div>

    <!-- Summary KPI Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        @click="filterByStatus(card.filter)"
        class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer flex items-center justify-between group"
        :class="{ 'ring-2 ring-[#7367F0] border-transparent': statusFilter === card.filter }"
      >
        <div class="space-y-1">
          <span class="text-[11px] font-bold uppercase tracking-wider text-[#A8AAAE] block">{{ card.label }}</span>
          <div class="text-xl font-extrabold" :style="{ color: card.textColor }">
            {{ card.value }}
          </div>
          <span class="text-[10px] text-[#A8AAAE] block font-medium">Projects</span>
        </div>
        <div
          class="w-11 h-11 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 flex-shrink-0"
          :style="{ backgroundColor: card.bgLight, color: card.color }"
          v-html="card.icon"
        ></div>
      </div>
    </div>

    <!-- Filters + Search + View Controls -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
        <!-- Per page dropdown -->
        <div class="flex items-center space-x-2">
          <span class="text-xs text-[#A8AAAE] font-medium">Show</span>
          <div class="relative">
            <select
              v-model="perPage"
              @change="load"
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

        <!-- Status Filter Pills -->
        <div class="flex items-center space-x-1 overflow-x-auto py-1 max-w-full">
          <button
            v-for="s in statusFilters"
            :key="s.value"
            @click="filterByStatus(s.value)"
            class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all cursor-pointer whitespace-nowrap"
            :class="statusFilter === s.value
              ? 'bg-[#7367F0] text-white shadow-sm'
              : 'bg-white text-[#6F6B7D] hover:bg-[#F8F7FA] border border-[#DBDADE]'"
          >
            {{ s.label }}
          </button>
        </div>
      </div>

      <!-- Search + View Toggle -->
      <div class="flex items-center gap-2.5 w-full md:w-auto justify-between md:justify-end">
        <div class="relative w-full md:w-64">
          <input
            v-model="search"
            @input="onSearch"
            type="text"
            placeholder="Search projects..."
            class="form-ctrl text-xs h-[36px] pl-9 pr-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#A8AAAE]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
        </div>

        <!-- View Switcher -->
        <div class="flex items-center bg-[#F8F7FA] p-0.5 border border-[#DBDADE] rounded-md flex-shrink-0">
          <button
            @click="view = 'table'"
            class="p-1.5 rounded transition-all cursor-pointer"
            :class="view === 'table' ? 'bg-white text-[#7367F0] shadow-xs' : 'text-[#A8AAAE] hover:text-[#6F6B7D]'"
            title="Table View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          </button>
          <button
            @click="view = 'kanban'"
            class="p-1.5 rounded transition-all cursor-pointer"
            :class="view === 'kanban' ? 'bg-white text-[#7367F0] shadow-xs' : 'text-[#A8AAAE] hover:text-[#6F6B7D]'"
            title="Kanban View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
          </button>
          <button
            @click="view = 'gantt'"
            class="p-1.5 rounded transition-all cursor-pointer"
            :class="view === 'gantt' ? 'bg-white text-[#7367F0] shadow-xs' : 'text-[#A8AAAE] hover:text-[#6F6B7D]'"
            title="Gantt View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="3" y1="4" x2="21" y2="4"/><line x1="7" y1="8" x2="21" y2="8"/><line x1="4" y1="12" x2="21" y2="12"/><line x1="9" y1="16" x2="21" y2="16"/><line x1="6" y1="20" x2="21" y2="20"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- TABLE VIEW -->
    <div v-if="view === 'table'" class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#6F6B7D]">
              <th class="py-3 px-3.5 text-center w-12">#</th>
              <th class="py-3 px-3.5 min-w-[220px]">Project Name</th>
              <th class="py-3 px-3.5 min-w-[160px]">Customer</th>
              <th class="py-3 px-3.5 min-w-[120px]">Tags</th>
              <th class="py-3 px-3.5">Start Date</th>
              <th class="py-3 px-3.5">Deadline</th>
              <th class="py-3 px-3.5 min-w-[110px]">Members</th>
              <th class="py-3 px-3.5">Status</th>
              <th class="py-3 px-3.5 text-center w-28">Options</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F0F2] text-xs text-[#6F6B7D]">
            <tr v-if="loading">
              <td colspan="9" class="text-center py-16 text-[#A8AAAE]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="animate-spin text-[#7367F0]"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                  <span class="text-xs font-semibold">Loading projects...</span>
                </div>
              </td>
            </tr>

            <tr
              v-for="(proj, idx) in projects"
              :key="proj.id"
              class="hover:bg-[#F8F7FA]/70 transition-colors group"
            >
              <!-- Number -->
              <td class="py-3.5 px-3.5 text-center text-[#A8AAAE] font-mono text-[11px]">
                {{ idx + 1 + (page - 1) * (+perPage) }}
              </td>

              <!-- Project Name & Description -->
              <td class="py-3.5 px-3.5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#4B465C] hover:text-[#7367F0] transition-colors cursor-pointer" @click="viewProject(proj)">
                    {{ proj.name }}
                  </span>
                  <span v-if="proj.description" class="text-[11px] text-[#A8AAAE] line-clamp-1 mt-0.5">
                    {{ truncate(proj.description, 55) }}
                  </span>
                </div>
              </td>

              <!-- Customer -->
              <td class="py-3.5 px-3.5">
                <div class="flex items-center space-x-2">
                  <div class="w-6 h-6 rounded-full bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                    {{ proj.client?.company ? proj.client.company.charAt(0).toUpperCase() : '—' }}
                  </div>
                  <span class="font-semibold text-[#4B465C] truncate max-w-[150px]">
                    {{ proj.client?.company || '—' }}
                  </span>
                </div>
              </td>

              <!-- Tags -->
              <td class="py-3.5 px-3.5">
                <div class="flex items-center gap-1 flex-wrap">
                  <span v-if="!proj.tags || !parseTags(proj.tags).length" class="text-[#A8AAAE]">—</span>
                  <span
                    v-for="tag in parseTags(proj.tags)"
                    :key="tag"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20"
                  >
                    {{ tag }}
                  </span>
                </div>
              </td>

              <!-- Start Date -->
              <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                {{ fmtDate(proj.start_date) }}
              </td>

              <!-- Deadline -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <span
                  class="font-semibold"
                  :class="isOverdue(proj.deadline) ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px]' : 'text-[#6F6B7D]'"
                >
                  {{ fmtDate(proj.deadline) }}
                </span>
              </td>

              <!-- Members Avatar Stack -->
              <td class="py-3.5 px-3.5">
                <div class="flex items-center -space-x-1.5 overflow-hidden">
                  <div
                    v-for="m in (proj.members || []).slice(0, 3)"
                    :key="m.id"
                    class="w-6 h-6 rounded-full bg-gradient-to-tr from-[#7367F0] to-[#9F8ED6] text-white flex items-center justify-center text-[10px] font-bold border-2 border-white shadow-xs flex-shrink-0"
                    :title="m.name"
                  >
                    {{ m.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <div
                    v-if="(proj.members || []).length > 3"
                    class="w-6 h-6 rounded-full bg-[#EBE9F1] text-[#6F6B7D] flex items-center justify-center text-[9px] font-bold border-2 border-white flex-shrink-0"
                  >
                    +{{ proj.members.length - 3 }}
                  </div>
                  <span v-if="!(proj.members || []).length" class="text-[#A8AAAE] text-xs">—</span>
                </div>
              </td>

              <!-- Status -->
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-2xs"
                  :class="statusBadgeClass(proj.status)"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(proj.status)"></span>
                  {{ proj.status }}
                </span>
              </td>

              <!-- Options -->
              <td class="py-3.5 px-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    @click="viewProject(proj)"
                    class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="View Project"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button
                    v-if="canCreateProject"
                    @click="copyProject(proj)"
                    class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Copy Project"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  </button>
                  <button
                    v-if="canEditProject"
                    @click="editProject(proj)"
                    class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Edit Project"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button
                    v-if="canDeleteProject"
                    @click="deleteProject(proj)"
                    class="w-7 h-7 rounded border border-transparent hover:border-rose-200 hover:bg-rose-50 text-[#A8AAAE] hover:text-rose-600 flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Delete Project"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && !projects.length">
              <td colspan="9" class="text-center py-12 text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="mx-auto mb-2 opacity-50"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                <p class="text-xs font-semibold m-0">No projects found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3 border-t border-[#F1F0F2] text-xs text-[#6F6B7D]" v-if="totalPages > 1">
        <span class="text-[#A8AAAE]">Showing {{ projects.length }} of {{ totalPages * (+perPage) }} entries</span>
        <div class="flex items-center space-x-2">
          <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page <= 1" @click="page--; load()">Previous</button>
          <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page >= totalPages" @click="page++; load()">Next</button>
        </div>
      </div>
    </div>

    <!-- GANTT VIEW -->
    <div v-if="view === 'gantt'" class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm space-y-4">
      <div class="flex items-center justify-between border-b border-[#F1F0F2] pb-3">
        <div class="flex items-center space-x-2">
          <button class="w-8 h-8 rounded bg-[#F8F7FA] border border-[#DBDADE] hover:bg-[#EBE9F1] flex items-center justify-center text-[#6F6B7D] cursor-pointer" @click="ganttMonth--">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <span class="text-sm font-bold text-[#4B465C] min-w-[140px] text-center">{{ ganttMonthLabel }}</span>
          <button class="w-8 h-8 rounded bg-[#F8F7FA] border border-[#DBDADE] hover:bg-[#EBE9F1] flex items-center justify-center text-[#6F6B7D] cursor-pointer" @click="ganttMonth++">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>

        <!-- Month fast picker pills -->
        <div class="flex items-center gap-1 overflow-x-auto max-w-[60%] py-1">
          <button
            v-for="(m, i) in ganttMonths"
            :key="m.key"
            class="px-2.5 py-1 text-[11px] font-semibold rounded transition-all cursor-pointer whitespace-nowrap"
            :class="ganttMonthOffset === i ? 'bg-[#7367F0] text-white shadow-xs' : 'bg-[#F8F7FA] text-[#6F6B7D] hover:bg-[#EBE9F1]'"
            @click="ganttMonthOffset = i"
          >
            {{ m.label.split(' ')[0] }}
          </button>
        </div>
      </div>

      <!-- Gantt Rows Chart -->
      <div class="space-y-2.5 pt-2">
        <div
          v-for="proj in projects"
          :key="proj.id"
          class="flex items-center gap-3 h-9 hover:bg-[#F8F7FA] p-1 rounded-md transition-colors"
        >
          <div class="w-44 text-xs font-bold text-[#4B465C] truncate flex-shrink-0" :title="proj.name">
            {{ proj.name }}
          </div>
          <div class="flex-1 h-6 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md relative overflow-hidden">
            <div
              class="absolute top-0.5 bottom-0.5 rounded-sm flex items-center px-2 text-[10px] font-bold text-white shadow-xs transition-all"
              :style="ganttBarStyle(proj)"
              :class="ganttBarColorClass(proj.status)"
              :title="`${proj.name}: ${fmtDate(proj.start_date)} - ${fmtDate(proj.deadline)}`"
            >
              <span v-if="ganttBarWidth(proj) > 12" class="truncate">{{ proj.name }}</span>
            </div>
          </div>
        </div>

        <div v-if="!projects.length" class="text-center py-10 text-[#A8AAAE] text-xs font-semibold">
          No projects to display on schedule
        </div>
      </div>
    </div>

    <!-- KANBAN VIEW -->
    <div v-if="view === 'kanban'" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="col in kanbanCols"
        :key="col.status"
        class="bg-white border border-[#EBE9F1] rounded-lg p-3.5 shadow-sm flex flex-col space-y-3"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
          <div class="flex items-center space-x-2">
            <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: col.color }"></div>
            <span class="text-xs font-bold text-[#4B465C]">{{ col.status }}</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]">
            {{ projectsByStatus(col.status).length }}
          </span>
        </div>

        <!-- Column Cards Container -->
        <div class="space-y-2.5 flex-1 min-h-[220px]">
          <div
            v-if="!projectsByStatus(col.status).length"
            class="h-28 border-2 border-dashed border-[#DBDADE]/60 rounded-lg flex items-center justify-center text-[11px] text-[#A8AAAE] font-semibold"
          >
            No Projects
          </div>

          <div
            v-for="proj in projectsByStatus(col.status)"
            :key="proj.id"
            class="bg-[#F8F7FA] border border-[#EBE9F1] hover:border-[#7367F0]/40 rounded-lg p-3 shadow-2xs hover:shadow-sm transition-all space-y-2 group"
          >
            <div class="flex items-start justify-between gap-1">
              <h4 class="text-xs font-bold text-[#4B465C] group-hover:text-[#7367F0] transition-colors m-0 line-clamp-2">
                {{ proj.name }}
              </h4>
              <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="editProject(proj)" class="text-[#A8AAAE] hover:text-[#7367F0] cursor-pointer" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                </button>
                <button @click="deleteProject(proj)" class="text-[#A8AAAE] hover:text-rose-500 cursor-pointer" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            </div>

            <div class="text-[11px] text-[#A8AAAE] flex items-center gap-1 truncate">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span class="truncate">{{ proj.client?.company || 'No customer' }}</span>
            </div>

            <div class="flex items-center justify-between pt-1 border-t border-[#DBDADE]/50 text-[10px]">
              <span class="px-1.5 py-0.5 rounded bg-white text-[#6F6B7D] font-bold border border-[#DBDADE]/60">
                {{ proj.billing_type || 'Fixed' }}
              </span>
              <span v-if="proj.deadline" :class="isOverdue(proj.deadline) ? 'text-rose-600 font-bold' : 'text-[#A8AAAE]'">
                {{ fmtDate(proj.deadline) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Add Project Button -->
        <button
          v-if="canCreateProject"
          @click="openCreateForStatus(col.status)"
          class="w-full py-2 rounded-md border border-dashed border-[#DBDADE] hover:border-[#7367F0] hover:bg-[#7367F0]/5 text-[#6F6B7D] hover:text-[#7367F0] text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Add Project</span>
        </button>
      </div>
    </div>

    <!-- Project Insights ApexCharts Section -->
    <div class="space-y-3">
      <div class="flex items-center space-x-2">
        <div class="w-2.5 h-5 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
        <h3 class="text-sm font-bold text-[#4B465C] m-0">Project Insights</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Status Distribution</h4>
          <VueApexCharts type="donut" height="240" :options="pjStatusDonutOptions" :series="pjStatusDonutSeries"></VueApexCharts>
        </div>
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Budget vs Hours ($/h)</h4>
          <VueApexCharts type="bar" height="240" :options="pjBudgetOptions" :series="pjBudgetSeries"></VueApexCharts>
        </div>
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Billing Type Breakdown</h4>
          <VueApexCharts type="donut" height="240" :options="pjBillingDonutOptions" :series="pjBillingDonutSeries"></VueApexCharts>
        </div>
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Monthly Project Starts</h4>
          <VueApexCharts type="bar" height="240" :options="pjMonthlyOptions" :series="pjMonthlySeries"></VueApexCharts>
        </div>
      </div>
    </div>

    <!-- CREATE / EDIT PROJECT RIGHT-SIDE DRAWER -->
    <a-drawer
      v-model:open="showDrawer"
      placement="right"
      :width="640"
      :destroyOnClose="true"
      class="vuexy-project-drawer"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">
              {{ editing ? 'Edit Project' : 'Add New Project' }}
            </h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">
              {{ editing ? 'Update project scope, budget, and members' : 'Configure project settings and assign team members' }}
            </p>
          </div>
        </div>
      </template>

      <div class="p-1 space-y-6">
        <!-- 1. Primary Information Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#7367F0]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">General Information</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Project Name -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Project Name <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. Website Redesign & SEO"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Customer -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Customer <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <select
                  v-model="form.client_id"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">Select customer...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Billing Type -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Billing Type</label>
              <div class="relative">
                <select
                  v-model="form.billing_type"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="Fixed Rate">Fixed Rate</option>
                  <option value="Project Hours">Project Hours</option>
                  <option value="Task Hours">Task Hours</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
              <div class="relative">
                <select
                  v-model="form.status"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="Not Started">Not Started</option>
                  <option value="In Progress">In Progress</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Cancelled">Cancelled</option>
                  <option value="Finished">Finished</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Total Rate / Budget -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Total Rate ($)</label>
              <input
                v-model="form.budget"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Estimated Hours -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Estimated Hours</label>
              <input
                v-model="form.estimated_hours"
                type="number"
                min="0"
                step="0.5"
                placeholder="0"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>
          </div>
        </div>

        <!-- 2. Timeline & Progress Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#28C76F]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Timeline & Progress</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <!-- Deadline -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Deadline</label>
              <input
                v-model="form.deadline"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Progress calculation checkbox -->
            <div class="md:col-span-2">
              <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="form.progress_from_tasks"
                  class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-4 h-4 cursor-pointer"
                />
                <span class="text-xs font-medium text-[#4B465C]">Calculate progress automatically through tasks</span>
              </label>
            </div>

            <!-- Manual Progress Input & Visual Bar -->
            <div class="md:col-span-2 space-y-1.5" v-if="!form.progress_from_tasks">
              <div class="flex items-center justify-between text-xs">
                <label class="font-semibold text-[#4B465C]">Progress</label>
                <span class="font-bold text-[#7367F0]">{{ form.progress || 0 }}%</span>
              </div>
              <div class="flex items-center space-x-3">
                <input
                  type="range"
                  min="0"
                  max="100"
                  v-model.number="form.progress"
                  class="w-full accent-[#7367F0] cursor-pointer"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Members Assignment Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#00CFE8]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Assign Members</span>
          </div>

          <div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-48 overflow-y-auto p-2 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md">
              <label
                v-for="user in staff"
                :key="user.id"
                class="flex items-center space-x-2.5 p-2 bg-white rounded border border-[#EBE9F1] hover:border-[#7367F0] cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  :value="user.id"
                  v-model="form.member_ids"
                  class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-4 h-4 cursor-pointer"
                />
                <div class="w-5 h-5 rounded-full bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-[10px] font-bold">
                  {{ user.name?.charAt(0)?.toUpperCase() }}
                </div>
                <span class="text-xs font-medium text-[#4B465C] truncate">{{ user.name }}</span>
              </label>
              <div v-if="!staff.length" class="text-center py-4 text-xs text-[#A8AAAE] col-span-2">
                No staff members available
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Tags & Additional Details Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#FF9F43]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Tags & Description</span>
          </div>

          <div class="space-y-4">
            <!-- Tags Input with Chips -->
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
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Project Description</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Key deliverables, milestones, tech requirements..."
                class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[90px] w-full resize-none"
              ></textarea>
            </div>

            <!-- Send created email -->
            <div>
              <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="form.send_created_email"
                  class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-4 h-4 cursor-pointer"
                />
                <span class="text-xs font-medium text-[#4B465C]">Send project created notification email</span>
              </label>
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
            @click="closeDrawer"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 cursor-pointer"
            :disabled="saving"
            @click="save"
          >
            <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Create Project') }}
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import VueApexCharts from 'vue3-apexcharts'
import { useAuthStore } from '../store/authStore'

const authStore = useAuthStore()
const canCreateProject = computed(() => authStore.hasPermission('Projects', 'create'))
const canEditProject   = computed(() => authStore.hasPermission('Projects', 'edit'))
const canDeleteProject = computed(() => authStore.hasPermission('Projects', 'delete'))

const BASE = '/api'
const projects   = ref([])
const stats      = ref({})
const clients    = ref([])
const staff      = ref([])
const loading    = ref(false)
const saving     = ref(false)
const search     = ref('')
const statusFilter = ref('')
const perPage    = ref('25')
const page       = ref(1)
const totalPages = ref(1)
const showDrawer = ref(false)
const editing    = ref(null)
const view       = ref('table')
const tagInput   = ref('')
const ganttMonth = ref(new Date().getMonth())
const ganttMonthOffset = ref(0)
const ganttYear  = ref(new Date().getFullYear())

const form = reactive({
  name: '', client_id: '', description: '', billing_type: 'Fixed Rate',
  status: 'In Progress', start_date: '', deadline: '', budget: '',
  progress_from_tasks: false, progress: 0, estimated_hours: '',
  send_created_email: false, tags: '', member_ids: [],
  tagList: [],
})

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Not Started', value: 'Not Started' },
  { label: 'In Progress', value: 'In Progress' },
  { label: 'On Hold', value: 'On Hold' },
  { label: 'Cancelled', value: 'Cancelled' },
  { label: 'Finished', value: 'Finished' },
]

const kanbanCols = [
  { status: 'Not Started', color: '#A8AAAE' },
  { status: 'In Progress', color: '#7367F0' },
  { status: 'On Hold', color: '#FF9F43' },
  { status: 'Cancelled', color: '#EA5455' },
  { status: 'Finished', color: '#28C76F' },
]

const summaryCards = computed(() => [
  {
    label: 'Not Started',
    value: stats.value.not_started || 0,
    color: '#A8AAAE',
    textColor: '#4B465C',
    bgLight: 'rgba(168, 170, 174, 0.12)',
    filter: 'Not Started',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/></svg>',
  },
  {
    label: 'In Progress',
    value: stats.value.in_progress || 0,
    color: '#7367F0',
    textColor: '#7367F0',
    bgLight: 'rgba(115, 103, 240, 0.12)',
    filter: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  },
  {
    label: 'On Hold',
    value: stats.value.on_hold || 0,
    color: '#FF9F43',
    textColor: '#FF9F43',
    bgLight: 'rgba(255, 159, 67, 0.12)',
    filter: 'On Hold',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>',
  },
  {
    label: 'Cancelled',
    value: stats.value.cancelled || 0,
    color: '#EA5455',
    textColor: '#EA5455',
    bgLight: 'rgba(234, 84, 85, 0.12)',
    filter: 'Cancelled',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  },
  {
    label: 'Finished',
    value: stats.value.finished || 0,
    color: '#28C76F',
    textColor: '#28C76F',
    bgLight: 'rgba(40, 199, 111, 0.12)',
    filter: 'Finished',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><polyline points="20 6 9 17 4 12"/></svg>',
  },
])

const ganttMonths = computed(() => {
  const months = []
  for (let i = -5; i <= 6; i++) {
    const d = new Date(ganttYear.value, ganttMonth.value + i, 1)
    months.push({
      key: `${d.getFullYear()}-${d.getMonth()}`,
      label: d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
      year: d.getFullYear(),
      month: d.getMonth(),
    })
  }
  return months
})

const ganttMonthLabel = computed(() => {
  const d = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

function parseTags(tagsStr) {
  if (!tagsStr) return []
  try {
    const parsed = JSON.parse(tagsStr)
    return Array.isArray(parsed) ? parsed : [parsed]
  } catch {
    return tagsStr.split(',').map(t => t.trim()).filter(Boolean)
  }
}

function projectsByStatus(s) {
  return projects.value.filter(p => p.status === s)
}

function statusBadgeClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]/10 text-[#6F6B7D] border border-[#A8AAAE]/20',
    'In Progress': 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    'On Hold': 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    'Cancelled': 'bg-[#EA5455]/10 text-[#EA5455] border border-[#EA5455]/20',
    'Finished': 'bg-[#28C76F]/10 text-[#28C76F] border border-[#28C76F]/20',
  }[s] || 'bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]'
}

function statusDotClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]',
    'In Progress': 'bg-[#7367F0]',
    'On Hold': 'bg-[#FF9F43]',
    'Cancelled': 'bg-[#EA5455]',
    'Finished': 'bg-[#28C76F]',
  }[s] || 'bg-[#6F6B7D]'
}

function ganttBarColorClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]',
    'In Progress': 'bg-[#7367F0]',
    'On Hold': 'bg-[#FF9F43]',
    'Cancelled': 'bg-[#EA5455]',
    'Finished': 'bg-[#28C76F]',
  }[s] || 'bg-[#7367F0]'
}

function isOverdue(d) {
  return d && new Date(d) < new Date()
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
  statusFilter.value = s
  page.value = 1
  load()
}

async function loadStaff() {
  try {
    const res = await axios.get(`${BASE}/staff?per_page=500`)
    staff.value = res.data.staff?.data || []
  } catch {
    staff.value = []
  }
}

async function loadClients() {
  try {
    const res = await axios.get(`${BASE}/clients?per_page=1000`)
    clients.value = res.data.clients?.data || []
  } catch {
    clients.value = []
  }
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/projects`, { params })
    projects.value  = res.data.projects?.data || []
    totalPages.value = res.data.projects?.last_page || 1
    stats.value = res.data.stats || {}
  } catch {
    projects.value = []
    stats.value = { total: 0, not_started: 0, in_progress: 0, on_hold: 0, cancelled: 0, finished: 0 }
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  if (!canCreateProject.value) return
  editing.value = null
  Object.assign(form, {
    name: '', client_id: '', description: '', billing_type: 'Fixed Rate',
    status: 'In Progress', start_date: new Date().toISOString().slice(0, 10),
    deadline: '', budget: '', progress_from_tasks: false, progress: 0,
    estimated_hours: '', send_created_email: false, tags: '', member_ids: [], tagList: [],
  })
  tagInput.value = ''
  showDrawer.value = true
}

function openCreateForStatus(status) {
  if (!canCreateProject.value) return
  openCreate()
  form.status = status
}

function editProject(proj) {
  if (!canEditProject.value) return
  editing.value = proj
  const tagList = parseTags(proj.tags)
  Object.assign(form, {
    name: proj.name,
    client_id: proj.client_id || '',
    description: proj.description || '',
    billing_type: proj.billing_type || 'Fixed Rate',
    status: proj.status || 'In Progress',
    start_date: proj.start_date?.slice?.(0, 10) || '',
    deadline: proj.deadline?.slice?.(0, 10) || '',
    budget: proj.budget || '',
    progress_from_tasks: !!proj.progress_from_tasks,
    progress: proj.progress || 0,
    estimated_hours: proj.estimated_hours || '',
    send_created_email: !!proj.send_created_email,
    tags: proj.tags || '',
    member_ids: (proj.members || []).map(m => m.id),
    tagList,
  })
  tagInput.value = ''
  showDrawer.value = true
}

function viewProject(proj) {
  message.info(`Viewing Project: ${proj.name}`)
}

function copyProject(proj) {
  if (!canCreateProject.value) return
  editing.value = null
  Object.assign(form, {
    name: proj.name + ' (Copy)',
    client_id: proj.client_id || '',
    description: proj.description || '',
    billing_type: proj.billing_type || 'Fixed Rate',
    status: 'Not Started',
    start_date: new Date().toISOString().slice(0, 10),
    deadline: '',
    budget: proj.budget || '',
    progress_from_tasks: false,
    progress: 0,
    estimated_hours: proj.estimated_hours || '',
    send_created_email: false,
    tags: proj.tags || '',
    member_ids: (proj.members || []).map(m => m.id),
    tagList: parseTags(proj.tags),
  })
  tagInput.value = ''
  showDrawer.value = true
}

async function save() {
  if (!form.name) return message.error('Project name is required')
  if (!form.client_id) return message.error('Customer is required')
  if (!form.start_date) return message.error('Start date is required')
  saving.value = true
  try {
    const payload = { ...form, tags: form.tags || '' }
    if (editing.value) {
      await axios.put(`${BASE}/projects/${editing.value.id}`, payload)
      message.success('Project updated successfully')
    } else {
      await axios.post(`${BASE}/projects`, payload)
      message.success('Project created successfully')
    }
    closeDrawer()
    load()
  } catch {
    message.error('Failed to save project')
  } finally {
    saving.value = false
  }
}

async function deleteProject(proj) {
  if (!canDeleteProject.value) return
  if (!confirm(`Delete "${proj.name}"?`)) return
  try {
    await axios.delete(`${BASE}/projects/${proj.id}`)
    message.success('Project deleted successfully')
    load()
  } catch {
    projects.value = projects.value.filter(p => p.id !== proj.id)
  }
}

function exportPDF() {
  if (!projects.value.length) return message.warning('No projects to export')
  const headers = ['#', 'Project Name', 'Customer', 'Tags', 'Start Date', 'Deadline', 'Status']
  const rows = projects.value.map((p, i) => [
    i + 1, p.name, p.client?.company || '', p.tags || '',
    p.start_date || '', p.deadline || '', p.status || '',
  ])
  const csv = 'data:text/csv;charset=utf-8,' +
    [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n')
  const link = document.createElement('a')
  link.setAttribute('href', encodeURI(csv))
  link.setAttribute('download', 'projects_export.csv')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function closeDrawer() {
  showDrawer.value = false
  editing.value = null
}

function ganttBarStyle(proj) {
  if (!proj.start_date) return { display: 'none' }
  const start = new Date(proj.start_date)
  const end = proj.deadline ? new Date(proj.deadline) : new Date(start.getTime() + 30 * 86400000)
  const focusDate = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  const focusEnd = new Date(focusDate.getFullYear(), focusDate.getMonth() + 1, 0)
  const totalDays = (focusEnd - focusDate) / 86400000
  const barStart = Math.max(0, (start - focusDate) / 86400000)
  const barEnd = Math.min(totalDays, (end - focusDate) / 86400000)
  const width = ((barEnd - barStart) / totalDays) * 100
  const left = (barStart / totalDays) * 100
  if (width <= 0) return { display: 'none' }
  return { left: left + '%', width: width + '%' }
}

// ── ApexCharts options ─────────────────────────────────────
const STATUS_COLORS_PJ = {
  'Not Started': '#A8AAAE',
  'In Progress': '#7367F0',
  'On Hold': '#FF9F43',
  'Cancelled': '#EA5455',
  'Finished': '#28C76F',
}

const pjStatusDistribution = computed(() => {
  const counts = { 'Not Started': 0, 'In Progress': 0, 'On Hold': 0, 'Cancelled': 0, 'Finished': 0 }
  projects.value.forEach(p => { if (counts[p.status] !== undefined) counts[p.status]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ status: s, count: v }))
})

const pjStatusDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: pjStatusDistribution.value.map(s => s.status),
  colors: pjStatusDistribution.value.map(s => STATUS_COLORS_PJ[s.status] || '#7367F0'),
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '13px',
            fontWeight: 700,
            color: '#4B465C',
            formatter: () => String(pjStatusDistribution.value.reduce((a, b) => a + b.count, 0)),
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, labels: { colors: '#6F6B7D' } },
  stroke: { width: 0 },
}))
const pjStatusDonutSeries = computed(() => pjStatusDistribution.value.map(s => s.count))

const pjBudgetOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: {
    categories: projects.value.slice(0, 6).map(p => p.name.length > 14 ? p.name.slice(0, 14) + '...' : p.name),
    labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#6F6B7D' } },
  },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '10px', colors: '#6F6B7D' } } },
  colors: ['#7367F0', '#FF9F43'],
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#F1F0F2', strokeDashArray: 4 },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
  legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px', fontWeight: 600, labels: { colors: '#6F6B7D' } },
}))
const pjBudgetSeries = computed(() => [
  { name: 'Budget ($)', data: projects.value.slice(0, 6).map(p => Number(p.budget || 0)) },
  { name: 'Est. Hours ($)', data: projects.value.slice(0, 6).map(p => Number(p.estimated_hours || 0) * 50) },
])

const pjBillingDistribution = computed(() => {
  const counts = { 'Fixed Rate': 0, 'Project Hours': 0, 'Task Hours': 0 }
  projects.value.forEach(p => { if (counts[p.billing_type]) counts[p.billing_type]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ type: s, count: v }))
})

const pjBillingDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: pjBillingDistribution.value.map(b => b.type),
  colors: ['#7367F0', '#28C76F', '#FF9F43'],
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '13px',
            fontWeight: 700,
            color: '#4B465C',
            formatter: () => String(pjBillingDistribution.value.reduce((a, b) => a + b.count, 0)),
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, labels: { colors: '#6F6B7D' } },
  stroke: { width: 0 },
}))
const pjBillingDonutSeries = computed(() => pjBillingDistribution.value.map(b => b.count))

const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const pjMonthlyOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
  xaxis: { categories: MONTHS_SHORT, labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#6F6B7D' } } },
  yaxis: { labels: { style: { fontSize: '10px', colors: '#6F6B7D' } } },
  colors: ['#7367F0'],
  plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#F1F0F2', strokeDashArray: 4 },
}))
const pjMonthlySeries = computed(() => [
  { name: 'Projects Started', data: [3, 5, 2, 7, 4, 6, 8, 5, 3, 9, 4, 6] },
])

function ganttBarWidth(proj) {
  if (!proj.start_date) return 0
  const start = new Date(proj.start_date)
  const end = proj.deadline ? new Date(proj.deadline) : new Date(start.getTime() + 30 * 86400000)
  const focusDate = new Date(ganttYear.value, ganttMonth.value + ganttMonthOffset.value, 1)
  const focusEnd = new Date(focusDate.getFullYear(), focusDate.getMonth() + 1, 0)
  const totalDays = (focusEnd - focusDate) / 86400000
  const barStart = Math.max(0, (start - focusDate) / 86400000)
  const barEnd = Math.min(totalDays, (end - focusDate) / 86400000)
  return ((barEnd - barStart) / totalDays) * 100
}

onMounted(() => {
  load()
  loadClients()
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

:deep(.vuexy-project-drawer .ant-drawer-header) {
  padding: 16px 24px;
  border-bottom: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
:deep(.vuexy-project-drawer .ant-drawer-body) {
  padding: 24px;
  background-color: #F8F7FA;
}
:deep(.vuexy-project-drawer .ant-drawer-footer) {
  padding: 12px 24px;
  border-top: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
</style>
