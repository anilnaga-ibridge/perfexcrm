<template>
  <div class="seo-page">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
      <div class="flex items-center gap-2 text-sm">
        <router-link to="/admin/projects" class="breadcrumb-link">Projects</router-link>
        <span class="text-slate-400">/</span>
        <span class="breadcrumb-current">#2 SEO Optimization — Simonis, Ledner and Mraz</span>
      </div>
    </div>

    <!-- Project Header -->
    <div class="project-header">
      <div class="project-header-left">
        <div class="project-title-row">
          <h1 class="project-title">Simonis, Ledner and Mraz <span class="text-slate-400 font-normal">—</span> SEO Optimization</h1>
          <span class="status-badge status-in-progress">In Progress</span>
        </div>
        <div class="project-meta">
          <span>Project #2</span>
          <span class="meta-sep">•</span>
          <span>Simonis, Ledner and Mraz</span>
          <span class="meta-sep">•</span>
          <span>Fixed Rate — $2,500.00</span>
        </div>
      </div>
      <div class="project-header-actions">
        <button class="btn-primary" @click="showNewTask = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Task
        </button>
        <button class="btn-secondary" @click="showInvoiceModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          Invoice Project
        </button>
        <div class="more-dropdown" @click.stop>
          <button class="btn-more" @click="moreOpen = !moreOpen">
            More
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div v-if="moreOpen" class="more-menu" @click="moreOpen = false">
            <button @click="moreOpen = false">Unpin Project</button>
            <button @click="moreOpen = false">Edit Project</button>
            <button @click="moreOpen = false">Copy Project</button>
            <div class="menu-divider"></div>
            <button @click="moreOpen = false">Mark as Not Started</button>
            <button @click="moreOpen = false">Mark as On Hold</button>
            <button @click="moreOpen = false">Mark as Cancelled</button>
            <button @click="moreOpen = false">Mark as Finished</button>
            <div class="menu-divider"></div>
            <button @click="moreOpen = false">Export project data</button>
            <button @click="moreOpen = false">View project as customer</button>
            <div class="menu-divider"></div>
            <button class="text-rose-600" @click="moreOpen = false">Delete Project</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Horizontal Tabs + Content -->
    <div class="project-layout">
      <!-- Horizontal Tab Strip -->
      <div class="h-tab-strip">
        <div
          v-for="tab in tabs"
          :key="tab.key"
          :class="['h-tab', { 'h-tab--active': activeTab === tab.key }]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </div>
      </div>

      <!-- Main Content Area -->
      <main class="project-content">

        <!-- ==================== OVERVIEW TAB ==================== -->
        <div v-if="activeTab === 'overview'" class="overview-tab">

          <!-- Export Button -->
          <div class="overview-toolbar">
            <button class="btn-ghost">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export project data
            </button>
          </div>

          <!-- Project Details Card -->
          <div class="content-card">
            <h3 class="content-card-title">Overview</h3>
            <div class="details-grid">
              <div class="detail-item">
                <span class="detail-label">Project #</span>
                <span class="detail-value">2</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Customer</span>
                <span class="detail-value">Simonis, Ledner and Mraz</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Billing Type</span>
                <span class="detail-value">Fixed Rate</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Total Rate</span>
                <span class="detail-value">$2,500.00</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Status</span>
                <span class="detail-value"><span class="badge badge-info">In Progress</span></span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Date Created</span>
                <span class="detail-value">2026-06-19</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Start Date</span>
                <span class="detail-value">2026-06-19</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Deadline</span>
                <span class="detail-value">2026-08-19</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Total Logged Hours</span>
                <span class="detail-value">00:00</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Tags</span>
                <span class="detail-value"><span class="tag">wordpress</span></span>
              </div>
            </div>
            <div class="description-block">
              <span class="detail-label">Description</span>
              <p class="description-text">Mock Turtle in a hurry. 'No, I'll look first,' she said, as politely as she was out of it, and fortunately was just saying to herself, (not in a great interest in questions of eating and drinking. 'They lived on treacle,' said the Duchess, 'as pigs have to turn into.<br><br>However, the Multiplication Table doesn't signify: let's try Geography. London is the reason and all of you, and don't speak a word till I've finished.' So they had to run back into the wood. 'It's the oldest rule in the.</p>
            </div>
          </div>

          <!-- SEO Stats Cards -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-title">SEO Optimization</span>
                <span class="stat-count">11 <span class="text-slate-400">/ 15 Open Tasks</span></span>
              </div>
              <div class="stat-progress">
                <div class="progress-track">
                  <div class="progress-fill progress-fill-blue" style="width: 26.67%"></div>
                </div>
                <span class="progress-label">26.67%</span>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-card-header">
                <span class="stat-title">Days Left</span>
                <span class="stat-count">60 <span class="text-slate-400">/ 61 Days</span></span>
              </div>
              <div class="stat-progress">
                <div class="progress-track">
                  <div class="progress-fill progress-fill-green" style="width: 98.36%"></div>
                </div>
                <span class="progress-label">98.36%</span>
              </div>
            </div>
          </div>

          <!-- Expenses -->
          <div class="content-card">
            <h3 class="content-card-title">Expenses</h3>
            <div class="expense-items">
              <div class="expense-item">
                <span class="expense-label">Total Expenses</span>
                <span class="expense-value">$397.00</span>
              </div>
              <div class="expense-item">
                <span class="expense-label">Billable Expenses</span>
                <span class="expense-value">$0.00</span>
              </div>
              <div class="expense-item">
                <span class="expense-label">Billed Expenses</span>
                <span class="expense-value">$0.00</span>
              </div>
              <div class="expense-item">
                <span class="expense-label">Unbilled Expenses</span>
                <span class="expense-value">$0.00</span>
              </div>
            </div>
          </div>

          <!-- Total Logged Hours -->
          <div class="content-card">
            <h3 class="content-card-title">
              Total Logged Hours
              <span class="text-xs font-normal text-slate-400 ml-1">This Week</span>
            </h3>
            <div class="hours-placeholder">
              <span class="text-2xl font-extrabold text-slate-300">00:00</span>
            </div>
          </div>
        </div>

        <!-- ==================== TASKS TAB ==================== -->
        <div v-if="activeTab === 'tasks'">
          <!-- Sub-navigation: Tasks / Tasks Overview -->
          <div class="subnav-tabs">
            <button :class="['subnav-tab', { 'subnav-tab--active': taskView === 'list' }]" @click="taskView = 'list'">Tasks</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': taskView === 'overview' }]" @click="taskView = 'overview'">Tasks Overview</button>
          </div>

          <!-- ── TASKS LIST VIEW ── -->
          <template v-if="taskView === 'list'">
            <!-- Stats Bar -->
            <div class="task-stats-bar">
              <div v-for="s in taskStats" :key="s.label" class="task-stat-pill">
                <span class="stat-counts"><strong>{{ s.count }}</strong> {{ s.label }}</span>
                <span class="stat-mine">My Tasks: {{ s.my }}</span>
              </div>
            </div>

            <!-- Search -->
            <div class="task-toolbar">
              <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="taskSearch" placeholder="Search..." />
              </div>
            </div>

            <!-- Tasks Table -->
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th class="th-id">#</th>
                    <th class="th-name">Name</th>
                    <th class="th-status">Status</th>
                    <th class="th-date">Start Date</th>
                    <th class="th-date">Due Date</th>
                    <th class="th-assigned">Assigned to</th>
                    <th class="th-tags">Tags</th>
                    <th class="th-priority">Priority</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in filteredTasks" :key="t.id">
                    <td class="td-id">{{ t.id }}</td>
                    <td class="td-name">
                      <span class="task-name">{{ t.name }}</span>
                      <div class="task-actions">
                        <button class="task-action-link">Start Timer</button>
                        <span class="action-sep">|</span>
                        <button class="task-action-link">Edit</button>
                        <span class="action-sep">|</span>
                        <button class="task-action-link text-rose-500">Delete</button>
                      </div>
                    </td>
                    <td><span :class="['status-pill', statusClass(t.status)]">{{ t.status }}</span></td>
                    <td class="td-date">{{ t.start_date }}</td>
                    <td class="td-date">{{ t.due_date }}</td>
                    <td class="td-assigned">{{ t.assigned_to || '—' }}</td>
                    <td class="td-tags">
                      <span v-for="tag in t.tags" :key="tag" class="mini-tag">{{ tag }}</span>
                      <span v-if="!t.tags.length">—</span>
                    </td>
                    <td>
                      <span :class="['priority-pill', priorityClass(t.priority)]">{{ t.priority }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-footer">Showing 1 to {{ filteredTasks.length }} of {{ filteredTasks.length }} entries</div>
          </template>

          <!-- ── TASKS OVERVIEW VIEW ── -->
          <template v-if="taskView === 'overview'">
            <div class="overview-top-bar">
              <button class="btn-ghost" @click="taskView = 'list'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="15 18 9 12 15 6"/></svg>
                Back to Project
              </button>
            </div>

            <div class="overview-month-bar">
              <span class="text-sm font-bold text-slate-700">June 2026 - SEO Optimization</span>
            </div>

            <!-- Search -->
            <div class="task-toolbar">
              <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="taskSearch" placeholder="Search..." />
              </div>
            </div>

            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th class="th-name-wide">Name</th>
                    <th class="th-date">Start Date</th>
                    <th class="th-date">Due Date</th>
                    <th class="th-status">Status</th>
                    <th class="th-num">Attachments</th>
                    <th class="th-num">Comments</th>
                    <th class="th-checklist">Checklist</th>
                    <th class="th-hours">Logged Time</th>
                    <th class="th-ontime">Finished on time?</th>
                    <th class="th-assigned">Assigned to</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in filteredTasks" :key="t.id">
                    <td class="td-name">
                      <span class="task-name">{{ t.name }}</span>
                      <div class="related-to">Related To: #2 - SEO Optimization - Simonis, Ledner and Mraz</div>
                    </td>
                    <td class="td-date">{{ t.start_date }}</td>
                    <td class="td-date">{{ t.due_date }}</td>
                    <td><span :class="['status-pill', statusClass(t.status)]">{{ t.status }}</span></td>
                    <td class="td-num">{{ t.attachments }}</td>
                    <td class="td-num">{{ t.comments }}</td>
                    <td class="td-checklist">{{ t.checklist_done }}/{{ t.checklist_total }}</td>
                    <td class="td-hours">{{ t.logged_time }}</td>
                    <td class="td-ontime">{{ t.finished_on_time ? 'Yes' : '' }}</td>
                    <td class="td-assigned">{{ t.assigned_to || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-footer">Showing 1 to {{ filteredTasks.length }} of {{ filteredTasks.length }} entries</div>
          </template>
        </div>

        <!-- ==================== TIMESHEETS TAB ==================== -->
        <div v-if="activeTab === 'timesheets'">
          <div class="timesheet-toolbar">
            <div class="search-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="timesheetSearch" placeholder="Search..." />
            </div>
            <button class="btn-primary" @click="showTimesheetDrawer = true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Timesheet
            </button>
          </div>

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Task</th>
                  <th>Timesheet Tags</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Note</th>
                  <th class="th-hours">Time (h)</th>
                  <th class="th-hours">Time (decimal)</th>
                  <th class="th-num">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="timesheetEntries.length === 0">
                  <td colspan="9" class="empty-state">No entries found</td>
                </tr>
                <tr v-for="(e, i) in timesheetEntries" :key="i">
                  <td>{{ e.member }}</td>
                  <td>{{ e.task }}</td>
                  <td><span v-for="t in e.tags" :key="t" class="mini-tag">{{ t }}</span></td>
                  <td class="td-date">{{ e.start_time }}</td>
                  <td class="td-date">{{ e.end_time }}</td>
                  <td>{{ e.note }}</td>
                  <td class="td-hours">{{ e.hours }}</td>
                  <td class="td-hours">{{ e.decimal }}</td>
                  <td class="td-num">
                    <button class="task-action-link text-rose-500">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="table-footer">Showing 0 to 0 of 0 entries</div>
        </div>

        <!-- ==================== MILESTONES TAB ==================== -->
        <div v-if="activeTab === 'milestones'">
          <div class="timesheet-toolbar">
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
              <input type="checkbox" v-model="excludeCompleted" />
              Exclude Completed Tasks
            </label>
            <div class="flex items-center gap-3">
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="milestoneSearch" placeholder="Search..." />
              </div>
              <button class="btn-primary" @click="showMilestoneDrawer = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Milestone
              </button>
            </div>
          </div>

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-name">Name</th>
                  <th class="th-date">Start Date</th>
                  <th class="th-date">Due date</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(m, i) in filteredMilestones" :key="i">
                  <td class="td-name">
                    <span class="task-name">{{ m.name }}</span>
                    <div class="task-actions">
                      <button class="task-action-link text-rose-500">Delete</button>
                    </div>
                  </td>
                  <td class="td-date">{{ m.start_date }}</td>
                  <td class="td-date">{{ m.due_date }}</td>
                  <td class="text-slate-500 text-[11px]">{{ m.description || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            Showing 1 to {{ filteredMilestones.length }} of {{ filteredMilestones.length }} entries
            <div class="flex items-center gap-1 mt-1">
              <button class="pag-btn pag-prev" disabled>Previous</button>
              <button class="pag-btn pag-num pag-active">1</button>
              <button class="pag-btn pag-next" disabled>Next</button>
            </div>
          </div>
        </div>

        <!-- ==================== FILES TAB ==================== -->
        <div v-if="activeTab === 'files'">
          <!-- Upload Area -->
          <div class="upload-area">
            <div class="upload-dropzone">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24" class="text-slate-300"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span class="upload-text">Drop files here to upload</span>
            </div>
            <div class="upload-options">
              <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                <input type="checkbox" />
                Visible to Customer
              </label>
              <button class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="12 2 12 6 16 6 12 10 8 6 12 6"/><path d="M2 22h20"/><path d="M6 22V12h12v10"/></svg>
                Choose from Google Drive
              </button>
              <button class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="text-blue-600"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                Choose from Dropbox
              </button>
            </div>
          </div>

          <!-- Toolbar -->
          <div class="timesheet-toolbar">
            <div class="flex items-center gap-2">
              <button class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
              </button>
              <button class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Bulk Actions
              </button>
              <button class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download all
              </button>
            </div>
            <div class="search-wrap" style="max-width:240px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="fileSearch" placeholder="Search..." />
            </div>
          </div>

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:30px"><input type="checkbox" /></th>
                  <th class="th-name">Filename</th>
                  <th>File type</th>
                  <th>Last Activity</th>
                  <th class="th-num">Total Comments</th>
                  <th>Visible to Customer</th>
                  <th>Uploaded by</th>
                  <th class="th-date">Date uploaded</th>
                  <th class="th-num">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(f, i) in filteredFiles" :key="i">
                  <td><input type="checkbox" /></td>
                  <td class="td-name">
                    <span class="task-name">{{ f.name }}</span>
                  </td>
                  <td><span class="mini-tag">{{ f.type }}</span></td>
                  <td class="text-slate-500 text-[11px]">{{ f.last_activity }}</td>
                  <td class="td-num">{{ f.comments }}</td>
                  <td class="td-num">
                    <svg v-if="f.visible" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    <span v-else class="text-slate-300">—</span>
                  </td>
                  <td class="text-slate-600 text-[11px] font-medium">{{ f.uploaded_by }}</td>
                  <td class="td-date">{{ f.date_uploaded }}</td>
                  <td class="td-num">
                    <button class="text-indigo-600 hover:underline bg-none border-none text-[11px] font-semibold cursor-pointer px-1">...</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            Showing 1 to {{ filteredFiles.length }} of {{ filteredFiles.length }} entries
            <div class="flex items-center gap-1 mt-1">
              <button class="pag-btn pag-prev" disabled>Previous</button>
              <button class="pag-btn pag-num pag-active">1</button>
              <button class="pag-btn pag-next" disabled>Next</button>
            </div>
          </div>
        </div>

        <!-- ==================== DISCUSSIONS TAB ==================== -->
        <div v-if="activeTab === 'discussions'">
          <div class="timesheet-toolbar">
            <button class="btn-ghost" @click="showDiscussionDrawer = true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Create Discussion
            </button>
            <div class="search-wrap" style="max-width:240px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="discussionSearch" placeholder="Search..." />
            </div>
          </div>

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Last Activity</th>
                  <th class="th-num">Total Comments</th>
                  <th>Visible to Customer</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(d, i) in filteredDiscussions" :key="i">
                  <td class="td-name">
                    <span class="task-name">{{ d.subject }}</span>
                    <div class="flex items-center gap-2 mt-1">
                      <button class="text-indigo-600 hover:underline bg-none border-none text-[11px] font-semibold cursor-pointer">Edit</button>
                      <span class="text-slate-300">|</span>
                      <button class="text-red-500 hover:underline bg-none border-none text-[11px] font-semibold cursor-pointer">Delete</button>
                    </div>
                  </td>
                  <td class="text-slate-500 text-[11px]">{{ d.last_activity }}</td>
                  <td class="td-num">{{ d.comments }}</td>
                  <td class="td-num">
                    <span v-if="d.visible" class="text-emerald-600 text-[11px] font-semibold visible-badge">Visible</span>
                    <span v-else class="text-slate-400 text-[11px] not-visible-badge">Not Visible</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            Showing 1 to {{ filteredDiscussions.length }} of {{ filteredDiscussions.length }} entries
            <div class="flex items-center gap-1 mt-1">
              <button class="pag-btn pag-prev" disabled>Previous</button>
              <button class="pag-btn pag-num pag-active">1</button>
              <button class="pag-btn pag-next" disabled>Next</button>
            </div>
          </div>
        </div>

        <!-- ==================== TICKETS TAB ==================== -->
        <div v-if="activeTab === 'tickets'">
          <div class="timesheet-toolbar">
            <button class="btn-ghost" @click="showTicketDrawer = true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              New Ticket
            </button>
            <div class="search-wrap" style="max-width:240px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="ticketSearch" placeholder="Search..." />
            </div>
          </div>
          <div class="table-wrap">
            <div class="empty-state">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><rect x="2" y="3" width="20" height="18" rx="2"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="14" y2="13"/><line x1="8" y1="17" x2="12" y2="17"/></svg>
              <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              <button class="btn-primary mt-3" @click="showTicketDrawer = true">Create Ticket</button>
            </div>
          </div>
        </div>

        <!-- ==================== SALES TAB ==================== -->
        <div v-if="activeTab === 'sales'">
          <!-- Sub-navigation -->
          <div class="subnav-tabs">
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'proposals' }]" @click="salesView = 'proposals'">Proposals</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'estimates' }]" @click="salesView = 'estimates'">Estimates</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'invoices' }]" @click="salesView = 'invoices'">Invoices</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'subscriptions' }]" @click="salesView = 'subscriptions'">Subscriptions</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'expenses' }]" @click="salesView = 'expenses'">Expenses</button>
            <button :class="['subnav-tab', { 'subnav-tab--active': salesView === 'credit_notes' }]" @click="salesView = 'credit_notes'">Credit Notes</button>
          </div>

          <!-- ── PROPOSALS ── -->
          <template v-if="salesView === 'proposals'">
            <div class="timesheet-toolbar">
              <button class="btn-ghost">View Financial Stats</button>
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="proposalSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><polyline points="17 1 21 5 17 9"/><path d="M3 11V5a2 2 0 0 1 2-2h8"/><path d="M22 18v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="M18 18v-2a2 2 0 0 0-2-2H4"/></svg>
                <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              </div>
            </div>
          </template>

          <!-- ── ESTIMATES ── -->
          <template v-if="salesView === 'estimates'">
            <!-- Stats cards -->
            <div class="flex flex-wrap gap-3 mb-4">
              <div class="stat-card"><span class="stat-label">Draft (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Sent (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Expired (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Declined (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Accepted (0%)</span><span class="stat-value">0 / 0</span></div>
            </div>
            <div class="timesheet-toolbar">
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="estimateSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><polyline points="17 1 21 5 17 9"/><path d="M3 11V5a2 2 0 0 1 2-2h8"/><path d="M22 18v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="M18 18v-2a2 2 0 0 0-2-2H4"/></svg>
                <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              </div>
            </div>
          </template>

          <!-- ── INVOICES ── -->
          <template v-if="salesView === 'invoices'">
            <div class="flex flex-wrap gap-3 mb-4">
              <div class="stat-card"><span class="stat-label">Unpaid (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Paid (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Partially Paid (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Overdue (0%)</span><span class="stat-value">0 / 0</span></div>
              <div class="stat-card"><span class="stat-label">Draft (0%)</span><span class="stat-value">0 / 0</span></div>
            </div>
            <div class="timesheet-toolbar">
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="invoiceSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><polyline points="17 1 21 5 17 9"/><path d="M3 11V5a2 2 0 0 1 2-2h8"/><path d="M22 18v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="M18 18v-2a2 2 0 0 0-2-2H4"/></svg>
                <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              </div>
            </div>
          </template>

          <!-- ── SUBSCRIPTIONS ── -->
          <template v-if="salesView === 'subscriptions'">
            <div class="timesheet-toolbar">
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="subscriptionSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><polyline points="17 1 21 5 17 9"/><path d="M3 11V5a2 2 0 0 1 2-2h8"/><path d="M22 18v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="M18 18v-2a2 2 0 0 0-2-2H4"/></svg>
                <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              </div>
            </div>
          </template>

          <!-- ── EXPENSES ── -->
          <template v-if="salesView === 'expenses'">
            <!-- Stats -->
            <div class="expense-stats-row">
              <div class="expense-stat-item"><span class="expense-stat-label">Total</span><span class="expense-stat-value">$416.85</span></div>
              <div class="expense-stat-item"><span class="expense-stat-label">Billable</span><span class="expense-stat-value">$0.00</span></div>
              <div class="expense-stat-item"><span class="expense-stat-label">Non Billable</span><span class="expense-stat-value">$416.85</span></div>
              <div class="expense-stat-item"><span class="expense-stat-label">Not Invoiced</span><span class="expense-stat-value">$0.00</span></div>
              <div class="expense-stat-item"><span class="expense-stat-label">Billed</span><span class="expense-stat-value">$0.00</span></div>
            </div>
            <div class="timesheet-toolbar">
              <button class="btn-ghost" @click="showExpenseDrawer = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Record Expense
              </button>
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="expenseSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Category</th>
                    <th class="th-num">Amount</th>
                    <th>Name</th>
                    <th>Receipt</th>
                    <th class="th-date">Date</th>
                    <th>Invoice</th>
                    <th>Reference #</th>
                    <th>Payment Mode</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(e, i) in filteredExpenses" :key="i">
                    <td><span class="mini-tag">{{ e.category }}</span></td>
                    <td class="td-num font-semibold text-slate-800">{{ e.amount }}</td>
                    <td class="text-[11px] text-slate-600">{{ e.name }}</td>
                    <td class="td-num">
                      <svg v-if="e.receipt" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                      <span v-else class="text-slate-300">—</span>
                    </td>
                    <td class="td-date">{{ e.date }}</td>
                    <td class="text-[11px] text-slate-500">{{ e.invoice || '—' }}</td>
                    <td class="text-[11px] text-slate-500">{{ e.ref }}</td>
                    <td><span class="mini-tag">{{ e.payment_mode }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-footer">
              Showing 1 to {{ filteredExpenses.length }} of {{ filteredExpenses.length }} entries
              <div class="flex items-center gap-1 mt-1">
                <button class="pag-btn pag-prev" disabled>Previous</button>
                <button class="pag-btn pag-num pag-active">1</button>
                <button class="pag-btn pag-next" disabled>Next</button>
              </div>
            </div>
          </template>

          <!-- ── CREDIT NOTES ── -->
          <template v-if="salesView === 'credit_notes'">
            <div class="timesheet-toolbar">
              <div class="search-wrap" style="max-width:240px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" class="search-input" v-model="creditNoteSearch" placeholder="Search..." />
              </div>
            </div>
            <div class="table-wrap">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><polyline points="17 1 21 5 17 9"/><path d="M3 11V5a2 2 0 0 1 2-2h8"/><path d="M22 18v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/><path d="M18 18v-2a2 2 0 0 0-2-2H4"/></svg>
                <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
              </div>
            </div>
          </template>
        </div>

        <!-- ==================== CONTRACTS TAB ==================== -->
        <div v-if="activeTab === 'contracts'">
          <div class="timesheet-toolbar">
            <button class="btn-ghost">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              New Contract
            </button>
            <div class="search-wrap" style="max-width:240px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="contractSearch" placeholder="Search..." />
            </div>
          </div>
          <div class="table-wrap">
            <div class="empty-state">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="text-slate-300"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span class="text-sm text-slate-400 mt-2 font-medium">No entries found</span>
            </div>
          </div>
        </div>

        <!-- ==================== NOTES TAB ==================== -->
        <div v-if="activeTab === 'notes'">
          <div class="timesheet-toolbar">
            <button class="btn-ghost" @click="showNoteDrawer = true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Create Note
            </button>
            <div class="search-wrap" style="max-width:240px">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
              <input type="text" class="search-input" v-model="noteSearch" placeholder="Search..." />
            </div>
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="th-name">Note Title</th>
                  <th>Note Content</th>
                  <th class="th-date">Date Added</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(n, i) in filteredNotes" :key="i">
                  <td class="td-name">
                    <span class="task-name">{{ n.title }}</span>
                  </td>
                  <td class="text-[11px] text-slate-600 leading-relaxed max-w-md truncate">{{ n.content }}</td>
                  <td class="td-date">{{ n.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="table-footer">
            Showing 1 to {{ filteredNotes.length }} of {{ filteredNotes.length }} entries
            <div class="flex items-center gap-1 mt-1">
              <button class="pag-btn pag-prev" disabled>Previous</button>
              <button class="pag-btn pag-num pag-active">1</button>
              <button class="pag-btn pag-next" disabled>Next</button>
            </div>
          </div>
        </div>

        <!-- ==================== ACTIVITY TAB ==================== -->
        <div v-if="activeTab === 'activity'">
          <div class="activity-feed">
            <div v-for="(a, i) in activities" :key="i" class="activity-item">
              <div class="activity-line"></div>
              <div class="activity-dot"></div>
              <div class="activity-content">
                <div class="activity-time">{{ a.time }}</div>
                <div class="activity-heading">
                  <span class="activity-user">{{ a.user }}</span>
                  <span class="text-slate-500">- {{ a.action }}</span>
                </div>
                <div v-if="a.detail" class="activity-detail">{{ a.detail }}</div>
                <div v-if="a.visible" class="activity-visible">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Visible to customer
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== GANTT TAB ==================== -->
        <div v-if="activeTab === 'gantt'" class="gantt-tab">
          <VueApexCharts type="rangeBar" height="520" :options="ganttOptions" :series="ganttSeries"></VueApexCharts>
        </div>

        <!-- ==================== OTHER TABS PLACEHOLDERS ==================== -->
        <div v-else class="tab-placeholder">
          <div class="placeholder-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
          </div>
          <h3 class="placeholder-title">{{ currentTabLabel }}</h3>
          <p class="placeholder-desc">This section is under development.</p>
        </div>
      </main>
    </div>

    <!-- ==================== NEW TASK MODAL ==================== -->
    <transition name="fade">
      <div v-if="showNewTask" class="modal-overlay" @click.self="showNewTask = false">
        <div class="modal-card modal-card-large">
          <div class="modal-head">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-indigo-600"></div>
              <span class="modal-title">Add New Task</span>
            </div>
            <button class="modal-close" @click="showNewTask = false">×</button>
          </div>
          <div class="modal-body">
            <!-- Checkboxes Row -->
            <div class="task-checkboxes">
              <label class="task-checkbox-label"><input type="checkbox" v-model="taskForm.public" /> Public</label>
              <label class="task-checkbox-label"><input type="checkbox" v-model="taskForm.billable" /> Billable</label>
              <label class="task-checkbox-label"><input type="checkbox" v-model="taskForm.visible_to_customer" /> Visible to customer</label>
            </div>

            <!-- Attach Files -->
            <div class="form-group">
              <label class="input-lbl">Attach Files</label>
              <input type="file" class="input-ctrl" multiple />
            </div>

            <!-- Subject -->
            <div class="form-group">
              <label class="input-lbl"><span class="required">*</span> Subject</label>
              <input type="text" class="input-ctrl" v-model="taskForm.subject" placeholder="Task subject" required />
            </div>

            <div class="grid grid-cols-2 gap-6">
              <!-- Milestone -->
              <div class="form-group">
                <label class="input-lbl">Milestone</label>
                <select class="input-ctrl" v-model="taskForm.milestone">
                  <option value="Planning">Planning</option>
                </select>
              </div>
              <!-- Priority -->
              <div class="form-group">
                <label class="input-lbl">Priority</label>
                <select class="input-ctrl" v-model="taskForm.priority">
                  <option value="Low">Low</option>
                  <option value="Medium" selected>Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>
              <!-- Start Date -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Start Date</label>
                <input type="date" class="input-ctrl" v-model="taskForm.start_date" required />
              </div>
              <!-- Due Date -->
              <div class="form-group">
                <label class="input-lbl">Due Date</label>
                <input type="date" class="input-ctrl" v-model="taskForm.due_date" />
              </div>
              <!-- Repeat every -->
              <div class="form-group">
                <label class="input-lbl">Repeat every</label>
                <select class="input-ctrl" v-model="taskForm.repeat">
                  <option value="">None</option>
                  <option value="1 week">1 week</option>
                  <option value="2 weeks">2 weeks</option>
                  <option value="1 month">1 month</option>
                </select>
              </div>
              <!-- Related To -->
              <div class="form-group">
                <label class="input-lbl">Related To</label>
                <select class="input-ctrl" v-model="taskForm.related_to">
                  <option value="project" selected>Project</option>
                </select>
              </div>
            </div>

            <!-- Project -->
            <div class="form-group">
              <label class="input-lbl">Project</label>
              <select class="input-ctrl" v-model="taskForm.project_id">
                <option value="2">#2 - SEO Optimization - Simonis, Ledner and Mraz</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <!-- Assignees -->
              <div class="form-group">
                <label class="input-lbl">Assignees</label>
                <div class="multi-select-tags">
                  <span v-for="a in assignees" :key="a" :class="['tag-select', { 'tag-selected': taskForm.assignees.includes(a) }]" @click="toggleAssignee(a)">{{ a }}</span>
                </div>
              </div>
              <!-- Followers -->
              <div class="form-group">
                <label class="input-lbl">Followers</label>
                <div class="multi-select-tags">
                  <span v-for="f in followers" :key="f" :class="['tag-select', { 'tag-selected': taskForm.followers.includes(f) }]" @click="toggleFollower(f)">{{ f }}</span>
                </div>
              </div>
            </div>

            <!-- Tags -->
            <div class="form-group">
              <label class="input-lbl">Tags</label>
              <input type="text" class="input-ctrl" v-model="taskForm.tags" placeholder="Tag" />
            </div>

            <!-- Task Description -->
            <div class="form-group">
              <label class="input-lbl">Task Description</label>
              <textarea class="input-ctrl textarea" rows="4" v-model="taskForm.description" placeholder="Add Description"></textarea>
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn-cancel" @click="showNewTask = false">Cancel</button>
            <button class="btn-primary" @click="showNewTask = false">Save Task</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ==================== INVOICE PROJECT MODAL ==================== -->
    <transition name="fade">
      <div v-if="showInvoiceModal" class="modal-overlay" @click.self="showInvoiceModal = false">
        <div class="modal-card modal-card-large">
          <div class="modal-head">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-emerald-600"></div>
              <span class="modal-title">Project Invoice Info</span>
            </div>
            <button class="modal-close" @click="showInvoiceModal = false">×</button>
          </div>
          <div class="modal-body">
            <div class="invoice-options">
              <label class="invoice-option">
                <input type="radio" name="invoice_type" v-model="invoiceType" value="single" checked />
                <span>Single line <span class="text-slate-400">[ Fixed Rate ]</span></span>
              </label>
              <label class="invoice-option">
                <input type="radio" name="invoice_type" v-model="invoiceType" value="task" />
                <span>Task per item</span>
              </label>
              <label class="invoice-option">
                <input type="radio" name="invoice_type" v-model="invoiceType" value="timesheets" />
                <span>All timesheets individually</span>
              </label>
            </div>

            <div class="invoice-tasks-section">
              <label class="flex items-center gap-2 font-semibold text-slate-700 mb-3 cursor-pointer">
                <input type="checkbox" v-model="selectAllTasks" @change="toggleAllTasks" />
                See tasks that will be billed on this invoice
              </label>

              <div v-if="selectAllTasks" class="tasks-checklist">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="selectAllTasks" @change="toggleAllTasks" />
                  <span class="font-bold text-indigo-600">Select all tasks</span>
                </label>
                <label v-for="t in invoiceTasks" :key="t.name" class="task-check-item">
                  <input type="checkbox" v-model="t.selected" />
                  <span>{{ t.name }}</span>
                  <span :class="['status-pill', 'status-' + t.status.toLowerCase().replace(/\s+/g, '-')]">{{ t.status }}</span>
                </label>
              </div>
            </div>

            <div class="invoice-note bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 font-medium">
              All billed tasks will be marked as finished
            </div>
          </div>
          <div class="modal-foot">
            <button class="btn-cancel" @click="showInvoiceModal = false">Cancel</button>
            <button class="btn-primary" @click="showInvoiceModal = false">Generate Invoice</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ==================== TIMESHEET DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showTimesheetDrawer" class="drawer-overlay" @click.self="showTimesheetDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showTimesheetDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-indigo-600"></div>
                <span class="text-sm font-bold text-slate-800">Timesheet</span>
              </div>
              <button class="drawer-close" @click="showTimesheetDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <!-- Tags -->
              <div class="form-group">
                <label class="input-lbl">Tags</label>
                <input type="text" class="input-ctrl" v-model="tsForm.tags" placeholder="Tag" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Start Time -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Start Time</label>
                  <input type="datetime-local" class="input-ctrl" v-model="tsForm.start_time" required />
                </div>
                <!-- End Time -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> End Time</label>
                  <input type="datetime-local" class="input-ctrl" v-model="tsForm.end_time" required />
                </div>
              </div>

              <div class="form-group">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                  <input type="checkbox" v-model="tsForm.use_duration" />
                  Enter time duration instead
                </label>
              </div>

              <!-- Task -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Task</label>
                <select class="input-ctrl" v-model="tsForm.task">
                  <option value="" disabled>Select task...</option>
                  <option v-for="t in tasks" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </div>

              <!-- Member -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Member</label>
                <select class="input-ctrl" v-model="tsForm.member">
                  <option value="" disabled>Select member...</option>
                  <option v-for="m in allMembers" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>

              <!-- Note -->
              <div class="form-group">
                <label class="input-lbl">Note</label>
                <textarea class="input-ctrl textarea" rows="3" v-model="tsForm.note" placeholder="Note about this timesheet entry..."></textarea>
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showTimesheetDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showTimesheetDrawer = false">Save Timesheet</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- ==================== MILESTONE DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showMilestoneDrawer" class="drawer-overlay" @click.self="showMilestoneDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showMilestoneDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-amber-600"></div>
                <span class="text-sm font-bold text-slate-800">New Milestone</span>
              </div>
              <button class="drawer-close" @click="showMilestoneDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <!-- Name -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Name</label>
                <input type="text" class="input-ctrl" v-model="msForm.name" placeholder="Milestone name" required />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Start Date -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Start Date</label>
                  <input type="date" class="input-ctrl" v-model="msForm.start_date" required />
                </div>
                <!-- Due date -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Due date</label>
                  <input type="date" class="input-ctrl" v-model="msForm.due_date" required />
                </div>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label class="input-lbl">Description</label>
                <textarea class="input-ctrl textarea" rows="4" v-model="msForm.description" placeholder="Milestone description..."></textarea>
              </div>

              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                  <input type="checkbox" v-model="msForm.show_to_customer" />
                  Show description to customer
                </label>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                  <input type="checkbox" v-model="msForm.hide_from_customer" />
                  Hide from customer
                </label>
              </div>

              <!-- Order -->
              <div class="form-group mt-4">
                <label class="input-lbl">Order</label>
                <input type="number" class="input-ctrl" v-model.number="msForm.order" min="1" style="max-width:100px" />
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showMilestoneDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showMilestoneDrawer = false">Save Milestone</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- ==================== DISCUSSION DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showDiscussionDrawer" class="drawer-overlay" @click.self="showDiscussionDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showDiscussionDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-cyan-600"></div>
                <span class="text-sm font-bold text-slate-800">Create Discussion</span>
              </div>
              <button class="drawer-close" @click="showDiscussionDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <!-- Subject -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Subject</label>
                <input type="text" class="input-ctrl" v-model="discForm.subject" placeholder="Discussion subject" required />
              </div>

              <!-- Description -->
              <div class="form-group">
                <label class="input-lbl">Description</label>
                <textarea class="input-ctrl textarea" rows="5" v-model="discForm.description" placeholder="Write a description..."></textarea>
              </div>

              <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                <input type="checkbox" v-model="discForm.visible_to_customer" />
                Visible to Customer
              </label>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showDiscussionDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showDiscussionDrawer = false">Save Discussion</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- ==================== TICKET DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showTicketDrawer" class="drawer-overlay" @click.self="showTicketDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showTicketDrawer" class="drawer-panel drawer-panel-wide">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-rose-600"></div>
                <span class="text-sm font-bold text-slate-800">Ticket Information</span>
              </div>
              <button class="drawer-close" @click="showTicketDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <!-- Subject -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Subject</label>
                <input type="text" class="input-ctrl" v-model="ticketForm.subject" placeholder="Ticket subject" required />
              </div>

              <!-- Contact with details -->
              <div class="form-group">
                <label class="input-lbl">Contact</label>
                <select class="input-ctrl" v-model="ticketForm.contact">
                  <option value="" disabled>Select contact...</option>
                  <option v-for="c in ticketContacts" :key="c.name" :value="c.name">{{ c.name }}</option>
                </select>
              </div>

              <div v-if="ticketForm.contact" class="contact-detail-card">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="avatar-circle">
                      {{ ticketForm.contact.charAt(0) }}
                    </div>
                    <div>
                      <div class="text-xs font-bold text-slate-800">{{ ticketForm.contact }}</div>
                      <div class="text-[10px] text-slate-500">{{ selectedContact.email }}</div>
                    </div>
                  </div>
                  <button class="text-xs text-indigo-600 font-semibold hover:underline bg-none border-none cursor-pointer" @click="showContactDetail = !showContactDetail">{{ showContactDetail ? 'Hide' : 'View' }} Details</button>
                </div>
                <transition name="quick-fade">
                  <div v-if="showContactDetail" class="contact-detail-body">
                    <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">{{ ticketForm.contact }}</span></div>
                    <div class="detail-row"><span class="detail-label">Email address</span><span class="detail-value">{{ selectedContact.email }}</span></div>
                  </div>
                </transition>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Department -->
                <div class="form-group">
                  <label class="input-lbl">Department</label>
                  <select class="input-ctrl" v-model="ticketForm.department">
                    <option value="" disabled>Select department</option>
                    <option>CC</option>
                    <option>Sales</option>
                    <option>Support</option>
                  </select>
                </div>

                <!-- Tags -->
                <div class="form-group">
                  <label class="input-lbl">Tags</label>
                  <input type="text" class="input-ctrl" v-model="ticketForm.tags" placeholder="Tag" />
                </div>
              </div>

              <!-- Assign ticket -->
              <div class="form-group">
                <label class="input-lbl">Assign ticket (default is current user)</label>
                <select class="input-ctrl" v-model="ticketForm.assigned">
                  <option v-for="m in allMembers" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Priority -->
                <div class="form-group">
                  <label class="input-lbl">Priority</label>
                  <select class="input-ctrl" v-model="ticketForm.priority">
                    <option>Low</option>
                    <option selected>Medium</option>
                    <option>High</option>
                    <option>Urgent</option>
                  </select>
                </div>

                <!-- Service -->
                <div class="form-group">
                  <label class="input-lbl">Service</label>
                  <select class="input-ctrl" v-model="ticketForm.service">
                    <option value="" disabled>Select service</option>
                    <option>SEO Audit</option>
                    <option>Keyword Research</option>
                    <option>Technical SEO</option>
                  </select>
                </div>
              </div>

              <!-- Project -->
              <div class="form-group">
                <label class="input-lbl">Project</label>
                <select class="input-ctrl" v-model="ticketForm.project">
                  <option>#2 - SEO Optimization</option>
                </select>
              </div>

              <!-- Ticket Body -->
              <div class="form-group">
                <label class="input-lbl">Ticket Body</label>
                <textarea class="input-ctrl textarea" rows="6" v-model="ticketForm.body" placeholder="Describe the issue..."></textarea>
              </div>

              <!-- Attachments -->
              <div class="form-group">
                <label class="input-lbl">Attachments</label>
                <div class="file-input-wrap">
                  <input type="file" class="file-input" />
                  <span class="text-xs text-slate-400">No file chosen</span>
                </div>
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showTicketDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showTicketDrawer = false">Save Ticket</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- ==================== EXPENSE DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showExpenseDrawer" class="drawer-overlay" @click.self="showExpenseDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showExpenseDrawer" class="drawer-panel drawer-panel-wide">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-emerald-600"></div>
                <span class="text-sm font-bold text-slate-800">Add New Expense</span>
              </div>
              <button class="drawer-close" @click="showExpenseDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <div class="grid grid-cols-2 gap-4">
                <!-- Attach Receipt -->
                <div class="form-group">
                  <label class="input-lbl">Attach Receipt</label>
                  <div class="file-input-wrap">
                    <input type="file" class="file-input" />
                    <span class="text-xs text-slate-400">No file chosen</span>
                  </div>
                </div>
                <!-- Name -->
                <div class="form-group">
                  <label class="input-lbl">Name</label>
                  <input type="text" class="input-ctrl" v-model="expenseForm.name" placeholder="Expense name" />
                </div>
              </div>

              <!-- Note -->
              <div class="form-group">
                <label class="input-lbl">Note</label>
                <textarea class="input-ctrl textarea" rows="3" v-model="expenseForm.note" placeholder="Note..."></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Category -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Expense Category</label>
                  <select class="input-ctrl" v-model="expenseForm.category">
                    <option value="" disabled>Select category</option>
                    <option>Automobile Expense</option>
                    <option>Travel</option>
                    <option>Office Supplies</option>
                  </select>
                </div>
                <!-- Date -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Expense Date</label>
                  <input type="date" class="input-ctrl" v-model="expenseForm.date" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Amount -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Amount</label>
                  <input type="number" step="0.01" class="input-ctrl" v-model="expenseForm.amount" placeholder="0.00" />
                </div>
                <!-- Tax 1 -->
                <div class="form-group">
                  <label class="input-lbl">Tax 1</label>
                  <select class="input-ctrl" v-model="expenseForm.tax1">
                    <option>No Tax</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Tax 2 -->
                <div class="form-group">
                  <label class="input-lbl">Tax 2</label>
                  <select class="input-ctrl" v-model="expenseForm.tax2">
                    <option>No Tax</option>
                  </select>
                </div>
                <!-- Customer -->
                <div class="form-group">
                  <label class="input-lbl">Customer</label>
                  <select class="input-ctrl" v-model="expenseForm.customer">
                    <option>Simonis, Ledner and Mraz</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <!-- Billable -->
                <div class="form-group">
                  <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer pt-5">
                    <input type="checkbox" v-model="expenseForm.billable" />
                    Billable
                  </label>
                </div>
                <!-- Reference # -->
                <div class="form-group">
                  <label class="input-lbl">Reference #</label>
                  <input type="text" class="input-ctrl" v-model="expenseForm.ref" placeholder="Reference number" />
                </div>
              </div>

              <!-- Payment Mode -->
              <div class="form-group">
                <label class="input-lbl">Payment Mode</label>
                <select class="input-ctrl" v-model="expenseForm.payment_mode">
                  <option value="" disabled>Select payment mode</option>
                  <option>Bank</option>
                  <option>Cash</option>
                  <option>Check</option>
                  <option>Credit Card</option>
                </select>
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showExpenseDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showExpenseDrawer = false">Save Expense</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>

    <!-- ==================== NOTE DRAWER ==================== -->
    <transition name="drawer-fade">
      <div v-if="showNoteDrawer" class="drawer-overlay" @click.self="showNoteDrawer = false">
        <transition name="drawer-slide">
          <div v-if="showNoteDrawer" class="drawer-panel">
            <div class="drawer-header">
              <div class="flex items-center space-x-2">
                <div class="w-1.5 h-4 rounded-full bg-violet-600"></div>
                <span class="text-sm font-bold text-slate-800">New Note</span>
              </div>
              <button class="drawer-close" @click="showNoteDrawer = false">×</button>
            </div>

            <div class="drawer-body">
              <div class="form-group">
                <label class="input-lbl">Note Title</label>
                <input type="text" class="input-ctrl" v-model="noteForm.title" placeholder="Note title" />
              </div>
              <div class="form-group">
                <label class="input-lbl">Note Content</label>
                <textarea class="input-ctrl textarea" rows="8" v-model="noteForm.content" placeholder="Write your note..."></textarea>
              </div>
            </div>

            <div class="drawer-footer">
              <button class="btn-cancel" @click="showNoteDrawer = false">Cancel</button>
              <button class="btn-primary" @click="showNoteDrawer = false">Save Note</button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

export default defineComponent({
  name: 'SeoOptimisation',
  components: { VueApexCharts },
  setup() {
    const activeTab = ref('overview');
    const taskView = ref('list');
    const taskSearch = ref('');
    const showNewTask = ref(false);
    const showInvoiceModal = ref(false);
    const showTimesheetDrawer = ref(false);
    const timesheetSearch = ref('');
    const moreOpen = ref(false);
    const invoiceType = ref('single');
    const selectAllTasks = ref(false);

    const tabs = [
      { key: 'overview', label: 'Overview' },
      { key: 'tasks', label: 'Tasks' },
      { key: 'timesheets', label: 'Timesheets' },
      { key: 'milestones', label: 'Milestones' },
      { key: 'files', label: 'Files' },
      { key: 'discussions', label: 'Discussions' },
      { key: 'gantt', label: 'Gantt' },
      { key: 'tickets', label: 'Tickets' },
      { key: 'contracts', label: 'Contracts' },
      { key: 'sales', label: 'Sales' },
      { key: 'notes', label: 'Notes' },
      { key: 'activity', label: 'Activity' },
    ];

    const taskForm = ref({
      public: false,
      billable: false,
      visible_to_customer: false,
      subject: '',
      milestone: 'Planning',
      priority: 'Medium',
      start_date: '2026-06-19',
      due_date: '',
      repeat: '',
      related_to: 'project',
      project_id: 2,
      assignees: [],
      followers: [],
      tags: '',
      description: '',
    });

    const assignees = ['Llewellyn Heaney', 'Scot Cremin', 'Ralph Luelwitz', 'Santino Hoppe'];
    const followers = ['Scot Cremin', 'Santino Hoppe', 'Reyes Spencer', 'Ralph Luelwitz', 'Mike Bogan', 'Luciano Effertz', 'Llewellyn Heaney', 'Karl Swift', 'Cleveland Kshlerin', 'Brown Muller'];

    const invoiceTasks = ref([
      { name: 'Add a Test Case for the GCI Website', status: 'Awaiting Feedback', selected: false },
      { name: 'Make a video introducing the various projects', status: 'Not Started', selected: false },
      { name: 'Fix mimetype declaration on Android', status: 'Awaiting Feedback', selected: false },
      { name: 'Use CSS to create mw-ui-icon-arrow-down from mw-ui-icon-arrow-up', status: 'Testing', selected: false },
      { name: 'Research commonly asked questions and write a FAQ', status: 'Testing', selected: false },
      { name: 'Reword and clarify some API help messages', status: 'Not Started', selected: false },
      { name: 'Summarize your Experience in GCI', status: 'In Progress', selected: false },
      { name: 'Fix an open issue in our software', status: 'Awaiting Feedback', selected: false },
      { name: 'Make Wallpapers', status: 'In Progress', selected: false },
      { name: 'Find/fix 10 broken links in the RTEMS Wiki', status: 'Not Started', selected: false },
      { name: 'Research on improved audio latency in Android', status: 'Not Started', selected: false },
      { name: 'Write a blog post about Ubuntu GNOME', status: 'Complete', selected: false },
      { name: 'New feature implementation for the site', status: 'Complete', selected: false },
      { name: 'Build Marble Maps for Android', status: 'Complete', selected: false },
      { name: 'Define the layout', status: 'Complete', selected: false },
    ]);

    const toggleAssignee = (name) => {
      const idx = taskForm.value.assignees.indexOf(name);
      if (idx > -1) taskForm.value.assignees.splice(idx, 1);
      else taskForm.value.assignees.push(name);
    };

    const toggleFollower = (name) => {
      const idx = taskForm.value.followers.indexOf(name);
      if (idx > -1) taskForm.value.followers.splice(idx, 1);
      else taskForm.value.followers.push(name);
    };

    const toggleAllTasks = () => {
      invoiceTasks.value.forEach(t => t.selected = selectAllTasks.value);
    };

    const currentTabLabel = computed(() => {
      const t = tabs.find(t => t.key === activeTab.value);
      return t ? t.label : '';
    });

    const taskStats = [
      { label: 'Not Started', count: 4, my: 0 },
      { label: 'In Progress', count: 2, my: 1 },
      { label: 'Testing', count: 2, my: 0 },
      { label: 'Awaiting Feedback', count: 3, my: 1 },
      { label: 'Complete', count: 4, my: 2 },
    ];

    const tasks = ref([
      { id: 20, name: 'Add a Test Case for the GCI Website', status: 'Awaiting Feedback', start_date: '2026-06-19', due_date: '2026-07-03', assigned_to: '', tags: [], priority: 'High', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 29, name: 'Make a video introducing the various projects', status: 'Not Started', start_date: '2026-06-19', due_date: '2026-07-03', assigned_to: '', tags: [], priority: 'Urgent', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 30, name: 'Write a blog post about Ubuntu GNOME', status: 'Complete', start_date: '2026-06-19', due_date: '2026-07-03', assigned_to: '', tags: ['logo', 'review'], priority: 'Low', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 3, logged_time: '00:00', finished_on_time: true },
      { id: 28, name: 'New feature implementation for the site', status: 'Complete', start_date: '2026-06-19', due_date: '2026-07-10', assigned_to: '', tags: ['follow up', 'logo'], priority: 'Low', attachments: 0, comments: 1, checklist_done: 0, checklist_total: 2, logged_time: '00:00', finished_on_time: true },
      { id: 19, name: 'Fix mimetype declaration on Android', status: 'Awaiting Feedback', start_date: '2026-06-19', due_date: '2026-07-17', assigned_to: '', tags: [], priority: 'High', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 31, name: 'Use CSS to create mw-ui-icon-arrow-down from mw-ui-icon-arrow-up', status: 'Testing', start_date: '2026-06-19', due_date: '2026-07-17', assigned_to: '', tags: [], priority: 'Medium', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 3, logged_time: '00:00', finished_on_time: false },
      { id: 22, name: 'Research commonly asked questions and write a FAQ', status: 'Testing', start_date: '2026-06-19', due_date: '2026-07-24', assigned_to: '', tags: ['logo', 'bug'], priority: 'Low', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 24, name: 'Reword and clarify some API help messages', status: 'Not Started', start_date: '2026-06-19', due_date: '2026-07-24', assigned_to: '', tags: ['important', 'bug'], priority: 'High', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 25, name: 'Summarize your Experience in GCI', status: 'In Progress', start_date: '2026-06-19', due_date: '2026-07-24', assigned_to: '', tags: [], priority: 'Urgent', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 4, logged_time: '00:00', finished_on_time: false },
      { id: 26, name: 'Build Marble Maps for Android', status: 'Complete', start_date: '2026-06-19', due_date: '2026-07-24', assigned_to: '', tags: ['todo', 'wordpress'], priority: 'Medium', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 2, logged_time: '00:00', finished_on_time: true },
      { id: 32, name: 'Define the layout', status: 'Complete', start_date: '2026-06-19', due_date: '2026-07-24', assigned_to: '', tags: ['follow up', 'logo'], priority: 'Low', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 3, logged_time: '00:00', finished_on_time: true },
      { id: 27, name: 'Fix an open issue in our software', status: 'Awaiting Feedback', start_date: '2026-06-19', due_date: '2026-07-31', assigned_to: '', tags: [], priority: 'Urgent', attachments: 0, comments: 1, checklist_done: 1, checklist_total: 2, logged_time: '00:00', finished_on_time: false },
      { id: 18, name: 'Make Wallpapers', status: 'In Progress', start_date: '2026-06-19', due_date: '2026-08-07', assigned_to: '', tags: [], priority: 'Urgent', attachments: 0, comments: 1, checklist_done: 0, checklist_total: 3, logged_time: '00:00', finished_on_time: false },
      { id: 21, name: 'Find/fix 10 broken links in the RTEMS Wiki', status: 'Not Started', start_date: '2026-06-19', due_date: '2026-08-07', assigned_to: '', tags: [], priority: 'Medium', attachments: 0, comments: 1, checklist_done: 2, checklist_total: 3, logged_time: '00:00', finished_on_time: false },
      { id: 23, name: 'Research on improved audio latency in Android', status: 'Not Started', start_date: '2026-06-19', due_date: '2026-08-07', assigned_to: '', tags: [], priority: 'Low', attachments: 0, comments: 1, checklist_done: 0, checklist_total: 3, logged_time: '00:00', finished_on_time: false },
    ]);

    const filteredTasks = computed(() => {
      if (!taskSearch.value) return tasks.value;
      const q = taskSearch.value.toLowerCase();
      return tasks.value.filter(t =>
        t.name.toLowerCase().includes(q) ||
        t.status.toLowerCase().includes(q) ||
        t.priority.toLowerCase().includes(q) ||
        t.tags.some(tag => tag.toLowerCase().includes(q))
      );
    });

    const timesheetEntries = ref([]);

    const allMembers = ['Llewellyn Heaney', 'Scot Cremin', 'Ralph Luelwitz', 'Santino Hoppe', 'Reyes Spencer', 'Mike Bogan'];

    const excludeCompleted = ref(false);
    const milestoneSearch = ref('');
    const showMilestoneDrawer = ref(false);

    const milestones = ref([
      { name: 'Design', start_date: '2026-07-03', due_date: '2026-07-08', description: '' },
      { name: 'Development', start_date: '2026-07-10', due_date: '2026-07-15', description: '' },
      { name: 'Integration Test', start_date: '2026-07-17', due_date: '2026-07-20', description: '' },
      { name: 'Planning', start_date: '2026-06-26', due_date: '2026-06-29', description: '' },
      { name: 'Production Test', start_date: '2026-07-24', due_date: '2026-07-28', description: '' },
    ]);

    const filteredMilestones = computed(() => {
      let list = milestones.value;
      if (milestoneSearch.value) {
        const q = milestoneSearch.value.toLowerCase();
        list = list.filter(m => m.name.toLowerCase().includes(q));
      }
      return list;
    });

    const msForm = ref({
      name: '',
      start_date: '2026-06-19',
      due_date: '',
      description: '',
      show_to_customer: false,
      hide_from_customer: false,
      order: 6,
    });

    const showDiscussionDrawer = ref(false);
    const discussionSearch = ref('');

    const discussions = ref([
      { subject: 'Should we add blue color?', last_activity: 'No Activity', comments: 0, visible: true },
      { subject: 'Feedback for the mockup', last_activity: 'No Activity', comments: 0, visible: true },
      { subject: 'What is left for the project?', last_activity: 'No Activity', comments: 0, visible: false },
    ]);

    const filteredDiscussions = computed(() => {
      if (!discussionSearch.value) return discussions.value;
      const q = discussionSearch.value.toLowerCase();
      return discussions.value.filter(d => d.subject.toLowerCase().includes(q));
    });

    const discForm = ref({
      subject: '',
      description: '',
      visible_to_customer: false,
    });

    const showTicketDrawer = ref(false);
    const ticketSearch = ref('');
    const showContactDetail = ref(false);

    const ticketContacts = ref([
      { name: 'Porter Bartoletti', email: 'reichel.brycen@example.net' },
    ]);

    const selectedContact = computed(() => {
      return ticketContacts.value.find(c => c.name === ticketForm.value.contact) || { name: '', email: '' };
    });

    const ticketForm = ref({
      subject: '',
      contact: 'Porter Bartoletti',
      department: 'CC',
      tags: '',
      assigned: 'Llewellyn Heaney',
      priority: 'Medium',
      service: '',
      project: '#2 - SEO Optimization',
      body: '',
    });

    const contractSearch = ref('');

    const salesView = ref('proposals');
    const proposalSearch = ref('');
    const estimateSearch = ref('');
    const invoiceSearch = ref('');
    const subscriptionSearch = ref('');
    const expenseSearch = ref('');
    const creditNoteSearch = ref('');

    const showExpenseDrawer = ref(false);

    const expenses = ref([
      { category: 'Automobile Expense', amount: '$416.85', name: "THERE again!' said Alice desperately.", receipt: true, date: '2026-05-19', invoice: '', ref: '783', payment_mode: 'Bank' },
    ]);

    const filteredExpenses = computed(() => {
      if (!expenseSearch.value) return expenses.value;
      const q = expenseSearch.value.toLowerCase();
      return expenses.value.filter(e => e.category.toLowerCase().includes(q) || e.name.toLowerCase().includes(q));
    });

    const expenseForm = ref({
      name: '',
      note: '',
      category: '',
      date: '2026-06-19',
      amount: '',
      tax1: 'No Tax',
      tax2: 'No Tax',
      customer: 'Simonis, Ledner and Mraz',
      billable: false,
      ref: '',
      payment_mode: '',
    });

    const showNoteDrawer = ref(false);
    const noteSearch = ref('');

    const notes = ref([
      { title: 'Lobster Quadrille', content: "The Mock Turtle sighed deeply, and drew the back of one flapper across his eyes. 'I wasn't asleep,' he said to herself, rather sharply; 'I advise you to set them free, Exactly as we were. My.", date: '2026-06-19 10:00:28' },
    ]);

    const filteredNotes = computed(() => {
      if (!noteSearch.value) return notes.value;
      const q = noteSearch.value.toLowerCase();
      return notes.value.filter(n => n.title.toLowerCase().includes(q) || n.content.toLowerCase().includes(q));
    });

    const noteForm = ref({
      title: '',
      content: '',
    });

    const statusColor = (s) => ({
      'Not Started': '#94a3b8',
      'In Progress': '#3b82f6',
      'Testing': '#f59e0b',
      'Awaiting Feedback': '#8b5cf6',
      'Complete': '#10b981',
    }[s] || '#94a3b8');

    const ganttSeries = computed(() => {
      const taskData = tasks.value.map(t => ({
        x: t.name,
        y: [new Date(t.start_date).getTime(), new Date(t.due_date).getTime()],
        fillColor: statusColor(t.status),
      }));
      const milestoneData = milestones.value.map(m => ({
        x: m.name,
        y: [new Date(m.start_date).getTime(), new Date(m.start_date).getTime() + 86400000],
        fillColor: '#d97706',
      }));
      return [
        { name: 'Tasks', data: taskData },
        { name: 'Milestones', data: milestoneData },
      ];
    });

    const ganttOptions = computed(() => ({
      chart: { type: 'rangeBar', toolbar: { show: false }, zoom: { enabled: false } },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '50%',
          rangeBarGroupRows: true,
        },
      },
      colors: ['#6366f1', '#d97706'],
      fill: { type: 'solid' },
      xaxis: {
        type: 'datetime',
        labels: { format: 'MMM yyyy', style: { fontSize: '10px', fontWeight: 600, colors: '#64748b' } },
        axisBorder: { show: false },
        axisTicks: { show: false },
      },
      yaxis: {
        labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#1e293b' } },
      },
      grid: {
        borderColor: '#f1f5f9',
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: false } },
      },
      dataLabels: { enabled: false },
      tooltip: {
        x: { format: 'MMM dd, yyyy' },
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '11px',
        fontWeight: 600,
        labels: { colors: '#64748b' },
        markers: { width: 10, height: 10, radius: 2 },
      },
    }));

    const activities = ref([
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created discussion', detail: 'Should we add blue color?', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created discussion', detail: 'Feedback for the mockup', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created discussion', detail: 'What is left for the project?', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created new milestone', detail: 'Planning', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created new milestone', detail: 'Design', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created new milestone', detail: 'Development', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created new milestone', detail: 'Integration Test', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created new milestone', detail: 'Production Test', visible: true },
      { time: '3 hrs ago', user: 'Llewellyn Heaney', action: 'Created the project', detail: '', visible: false },
    ]);

    const fileSearch = ref('');

    const files = ref([
      { name: 'perfex_crm.png', type: 'image/png', last_activity: 'No Activity', comments: 0, visible: true, uploaded_by: 'Llewellyn Heaney', date_uploaded: '2016-09-26 23:02:26' },
    ]);

    const filteredFiles = computed(() => {
      if (!fileSearch.value) return files.value;
      const q = fileSearch.value.toLowerCase();
      return files.value.filter(f => f.name.toLowerCase().includes(q) || f.type.toLowerCase().includes(q));
    });

    const tsForm = ref({
      tags: '',
      start_time: '',
      end_time: '',
      use_duration: false,
      task: '',
      member: '',
      note: '',
    });

    const statusClass = (s) => 'status-' + s.toLowerCase().replace(/\s+/g, '-');
    const priorityClass = (p) => 'priority-' + p.toLowerCase();

    return {
      activeTab, tabs, taskView, taskSearch, taskStats, tasks, filteredTasks,
      showNewTask, showInvoiceModal, showTimesheetDrawer, timesheetSearch,
      timesheetEntries, allMembers, tsForm,
      showMilestoneDrawer, excludeCompleted, milestoneSearch,
      milestones, filteredMilestones, msForm,
      fileSearch, files, filteredFiles,
      showDiscussionDrawer, discussionSearch,
      discussions, filteredDiscussions, discForm,
      showTicketDrawer, ticketSearch, showContactDetail,
      ticketContacts, selectedContact, ticketForm,
      contractSearch,
      salesView, proposalSearch, estimateSearch, invoiceSearch,
      subscriptionSearch, expenseSearch, creditNoteSearch,
      showExpenseDrawer, expenses, filteredExpenses, expenseForm,
      showNoteDrawer, noteSearch, notes, filteredNotes, noteForm,
      activities,
      ganttSeries, ganttOptions,
      moreOpen,
      taskForm, assignees, followers, invoiceType,
      selectAllTasks, invoiceTasks, toggleAssignee, toggleFollower,
      toggleAllTasks, currentTabLabel, statusClass, priorityClass,
    };
  },
});
</script>

<style scoped>
.seo-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #1e293b;
}

.breadcrumb-bar {
  margin-bottom: 16px;
}
.breadcrumb-link { color: #6366f1; font-weight: 600; text-decoration: none; font-size: 13px; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-current { color: #64748b; font-weight: 600; font-size: 13px; }

/* ── Project Header ───────────────────────────────────────────── */
.project-header {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.project-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.project-title {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.02em;
}
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.status-in-progress { background: #dbeafe; color: #1d4ed8; }
.project-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
}
.meta-sep { color: #cbd5e1; }

.project-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* ── Buttons ──────────────────────────────────────────────────── */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #6366f1, #7c3aed);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.35); }
.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: transparent;
  color: #64748b;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.btn-ghost:hover { background: #f8fafc; color: #475569; }
.btn-cancel {
  padding: 8px 18px;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.btn-cancel:hover { background: #e2e8f0; }

/* ── More Dropdown ────────────────────────────────────────────── */
.more-dropdown { position: relative; }
.btn-more {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 8px 14px;
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.btn-more:hover { background: #f8fafc; }
.more-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 4px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  min-width: 220px;
  z-index: 50;
  padding: 6px;
  display: flex;
  flex-direction: column;
}
.more-menu button {
  display: block;
  width: 100%;
  text-align: left;
  padding: 8px 14px;
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 500;
  color: #334155;
  cursor: pointer;
  border-radius: 6px;
  font-family: inherit;
}
.more-menu button:hover { background: #f8fafc; }
.menu-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }
.text-rose-600 { color: #e11d48; }

/* ── Layout ───────────────────────────────────────────────────── */
.project-layout {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Horizontal Tab Strip */
.h-tab-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 2px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 4px;
  overflow: hidden;
}
.h-tab {
  padding: 9px 18px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.1s;
  white-space: nowrap;
}
.h-tab:hover { background: #f8fafc; color: #334155; }
.h-tab--active {
  background: #eef2ff;
  color: #4f46e5;
  font-weight: 700;
}

/* Content */
.project-content {
  flex: 1;
  min-width: 0;
}

/* ── Overview Tab ─────────────────────────────────────────────── */
.overview-toolbar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 16px;
}

.content-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 16px;
}
.content-card-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 32px;
}
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.detail-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #94a3b8;
}
.detail-value {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.badge {
  display: inline-flex;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}
.badge-info { background: #dbeafe; color: #1d4ed8; }

.tag {
  display: inline-flex;
  padding: 2px 8px;
  background: #f1f5f9;
  color: #475569;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.description-block {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}
.description-text {
  margin-top: 6px;
  font-size: 12px;
  color: #64748b;
  line-height: 1.6;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px 20px;
}
.stat-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.stat-title {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
}
.stat-count {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
}
.stat-progress {
  display: flex;
  align-items: center;
  gap: 10px;
}
.progress-track {
  flex: 1;
  height: 8px;
  background: #f1f5f9;
  border-radius: 999px;
  overflow: hidden;
}
.progress-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.3s ease;
}
.progress-fill-blue { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.progress-fill-green { background: linear-gradient(90deg, #10b981, #34d399); }
.progress-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  white-space: nowrap;
}

/* Expenses */
.expense-items { display: flex; flex-direction: column; gap: 10px; }
.expense-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
  border-bottom: 1px solid #f8fafc;
}
.expense-item:last-child { border-bottom: none; padding-bottom: 0; }
.expense-label { font-size: 12px; font-weight: 600; color: #64748b; }
.expense-value { font-size: 13px; font-weight: 700; color: #1e293b; }

.hours-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* ── Other Tabs Placeholder ───────────────────────────────────── */
.tab-placeholder {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 60px 24px;
  text-align: center;
}
.placeholder-icon { color: #cbd5e1; margin-bottom: 12px; }
.placeholder-title { font-size: 16px; font-weight: 700; color: #64748b; margin-bottom: 6px; }
.placeholder-desc { font-size: 13px; color: #94a3b8; }

/* ── Modal ────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(2px);
}
.modal-card {
  background: #fff;
  border-radius: 16px;
  width: 90%;
  max-width: 520px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08);
}
.modal-card-large { max-width: 700px; }
.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-shrink: 0;
}
.modal-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.modal-close {
  background: none;
  border: none;
  font-size: 22px;
  color: #94a3b8;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
.modal-close:hover { color: #475569; }
.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}
.modal-foot {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-shrink: 0;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Form Controls ────────────────────────────────────────────── */
.form-group { margin-bottom: 16px; }
.input-lbl {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 4px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.required { color: #ef4444; }
.input-ctrl {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12.5px;
  color: #1e293b;
  outline: none;
  font-family: inherit;
  background: #fff;
  box-sizing: border-box;
  transition: border-color 0.15s;
}
.input-ctrl:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.1); }
.textarea { resize: vertical; min-height: 80px; }

.grid { display: grid; }
.grid-cols-2 { grid-template-columns: 1fr 1fr; }
.gap-6 { gap: 16px; }

/* Task Checkboxes */
.task-checkboxes {
  display: flex;
  gap: 20px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f1f5f9;
}
.task-checkbox-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
}

/* Multi-select Tags */
.multi-select-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.tag-select {
  display: inline-flex;
  padding: 4px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.1s;
}
.tag-select:hover { border-color: #6366f1; color: #4f46e5; }
.tag-selected { background: #eef2ff; border-color: #6366f1; color: #4f46e5; }

/* ── Invoice Modal ────────────────────────────────────────────── */
.invoice-options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.invoice-option {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
}
.invoice-tasks-section {
  margin-bottom: 16px;
}
.tasks-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
  padding: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  max-height: 300px;
  overflow-y: auto;
}
.task-check-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
}
.status-pill {
  display: inline-flex;
  padding: 1px 6px;
  border-radius: 999px;
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  margin-left: auto;
}
.status-not-started { background: #f1f5f9; color: #64748b; }
.status-awaiting-feedback { background: #fef3c7; color: #d97706; }
.status-testing { background: #dbeafe; color: #2563eb; }
.status-in-progress { background: #dbeafe; color: #1d4ed8; }
.status-complete { background: #d1fae5; color: #059669; }
.invoice-note { margin-top: 12px; }

/* ── Tasks Tab ────────────────────────────────────────────────── */
.subnav-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 16px;
  background: #f1f5f9;
  border-radius: 10px;
  padding: 3px;
  width: fit-content;
}
.subnav-tab {
  padding: 6px 16px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.subnav-tab--active {
  background: #fff;
  color: #0f172a;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.task-stats-bar {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.task-stat-pill {
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 16px;
  min-width: 120px;
}
.stat-counts { font-size: 12px; color: #64748b; }
.stat-counts strong { font-size: 18px; color: #0f172a; margin-right: 4px; }
.stat-mine { font-size: 10px; color: #94a3b8; font-weight: 500; margin-top: 2px; }

.task-toolbar {
  margin-bottom: 12px;
}
.search-wrap {
  position: relative;
  max-width: 320px;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 8px 12px 8px 36px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  color: #1e293b;
  outline: none;
  font-family: inherit;
  background: #fff;
  box-sizing: border-box;
}
.search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.1); }

.table-wrap {
  overflow-x: auto;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
.data-table th {
  padding: 10px 12px;
  font-weight: 700;
  color: #64748b;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  white-space: nowrap;
  background: #f8fafc;
}
.data-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.data-table tbody tr:hover { background: #f8fafc; }
.data-table tbody tr:last-child td { border-bottom: none; }

.th-id { width: 40px; }
.th-name { width: auto; min-width: 200px; }
.th-name-wide { width: auto; min-width: 220px; }
.th-status { width: 110px; }
.th-date { width: 95px; white-space: nowrap; }
.th-assigned { width: 120px; }
.th-tags { width: 100px; }
.th-priority { width: 80px; }
.th-num { width: 70px; text-align: center; }
.th-checklist { width: 80px; text-align: center; }
.th-hours { width: 80px; text-align: center; }
.th-ontime { width: 90px; text-align: center; }

.td-id { font-weight: 700; color: #94a3b8; font-size: 11px; }
.td-name { }
.td-date { white-space: nowrap; color: #64748b; font-size: 11px; }
.td-assigned { color: #64748b; font-size: 11px; }
.td-tags { }
.td-num { text-align: center; color: #64748b; font-size: 12px; }
.td-checklist { text-align: center; font-weight: 600; color: #475569; }
.td-hours { text-align: center; color: #64748b; }
.td-ontime { text-align: center; color: #059669; font-weight: 600; }

.task-name { font-weight: 600; color: #1e293b; display: block; }
.task-actions { display: flex; gap: 2px; margin-top: 4px; }
.task-action-link {
  background: none;
  border: none;
  font-size: 10.5px;
  font-weight: 600;
  color: #6366f1;
  cursor: pointer;
  padding: 0;
  font-family: inherit;
}
.task-action-link:hover { text-decoration: underline; }
.action-sep { color: #cbd5e1; font-size: 10px; }

.mini-tag {
  display: inline-flex;
  padding: 1px 6px;
  background: #f1f5f9;
  color: #64748b;
  border-radius: 3px;
  font-size: 10px;
  font-weight: 600;
  margin: 1px;
}
.priority-pill {
  display: inline-flex;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
}
.priority-low { background: #f1f5f9; color: #64748b; }
.priority-medium { background: #fef3c7; color: #d97706; }
.priority-high { background: #fee2e2; color: #dc2626; }
.priority-urgent { background: #fecaca; color: #b91c1c; }

.related-to { font-size: 10px; color: #94a3b8; margin-top: 2px; }

.table-footer {
  padding: 10px 16px;
  font-size: 11px;
  color: #94a3b8;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 0 0 10px 10px;
}

.overview-top-bar { margin-bottom: 12px; }
.overview-month-bar {
  margin-bottom: 12px;
  padding: 10px 0;
}

/* ── Timesheets Tab ──────────────────────────────────────────── */
.timesheet-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  gap: 12px;
}
.timesheet-toolbar .search-wrap { max-width: 280px; flex: 1; }
.empty-state {
  text-align: center;
  padding: 40px 16px !important;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 500;
}

/* ── Drawer ──────────────────────────────────────────────────── */
.drawer-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  justify-content: flex-end;
  backdrop-filter: blur(2px);
}
.drawer-panel {
  width: 480px;
  max-width: 90vw;
  height: 100%;
  background: #fff;
  display: flex;
  flex-direction: column;
  box-shadow: -4px 0 20px rgba(0,0,0,0.08);
}
.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-shrink: 0;
}
.drawer-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #94a3b8;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
.drawer-close:hover { color: #475569; }
.drawer-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-shrink: 0;
}

/* Drawer transitions */
.drawer-fade-enter-active, .drawer-fade-leave-active { transition: opacity 0.2s ease; }
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }
.drawer-slide-enter-active, .drawer-slide-leave-active { transition: transform 0.25s ease; }
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }

/* ── Pagination ──────────────────────────────────────────────── */
.pag-btn {
  padding: 4px 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  background: #fff;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.1s;
}
.pag-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.pag-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pag-active { background: #6366f1; color: #fff; border-color: #6366f1; }
.pag-active:hover { background: #4f46e5; }

/* ── Contact Detail Card ─────────────────────────────────────── */
.contact-detail-card {
  margin-top: -8px;
  margin-bottom: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 12px;
}
.avatar-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.contact-detail-body {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.detail-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.detail-label { font-size: 11px; font-weight: 600; color: #64748b; min-width: 90px; }
.detail-value { font-size: 11px; font-weight: 500; color: #1e293b; }
.quick-fade-enter-active, .quick-fade-leave-active { transition: all 0.15s ease; }
.quick-fade-enter-from, .quick-fade-leave-to { opacity: 0; transform: translateY(-4px); }

/* ── File Input ──────────────────────────────────────────────── */
.file-input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.file-input {
  font-size: 12px;
  color: #64748b;
}
.file-input::file-selector-button {
  padding: 5px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.1s;
}
.file-input::file-selector-button:hover { background: #f8fafc; border-color: #cbd5e1; }

/* ── Expense Stats ───────────────────────────────────────────── */
.expense-stats-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}
.expense-stat-item {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  min-width: 100px;
}
.expense-stat-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.expense-stat-value { font-size: 16px; font-weight: 800; color: #1e293b; }

/* ── Stat Card (estimates/invoices) ──────────────────────────── */
.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 16px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.stat-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 13px; font-weight: 700; color: #1e293b; }

/* ── Empty State ─────────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  gap: 4px;
}

/* ── Drawer Panel Wide ───────────────────────────────────────── */
.drawer-panel-wide { max-width: 560px; }

/* ── Gantt Tab ───────────────────────────────────────────────── */
.gantt-tab {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
}
.gantt-tab :deep(.apexcharts-canvas) { font-family: inherit; }
.gantt-tab :deep(.apexcharts-tooltip) { font-family: inherit; }

/* ── Activity Feed ───────────────────────────────────────────── */
.activity-feed {
  display: flex;
  flex-direction: column;
  padding: 0 0 12px 0;
}
.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 0 14px 0;
  position: relative;
  border-bottom: 1px solid #f1f5f9;
}
.activity-item:last-child { border-bottom: none; }
.activity-line {
  position: absolute;
  left: 5px;
  top: 28px;
  bottom: 0;
  width: 1px;
  background: #e2e8f0;
}
.activity-item:last-child .activity-line { display: none; }
.activity-dot {
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background: #cbd5e1;
  flex-shrink: 0;
  margin-top: 3px;
  position: relative;
  z-index: 1;
}
.activity-content { flex: 1; min-width: 0; }
.activity-time { font-size: 10px; font-weight: 600; color: #94a3b8; margin-bottom: 2px; }
.activity-heading { font-size: 12px; font-weight: 600; color: #1e293b; }
.activity-user { color: #6366f1; }
.activity-detail { font-size: 12px; color: #64748b; margin-top: 2px; }
.activity-visible {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 600;
  color: #94a3b8;
  margin-top: 4px;
}

/* ── Upload Area ─────────────────────────────────────────────── */
.upload-area {
  background: #fff;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
.upload-dropzone {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.upload-text { font-size: 13px; font-weight: 600; color: #94a3b8; }
.upload-options {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: center;
}
</style>
