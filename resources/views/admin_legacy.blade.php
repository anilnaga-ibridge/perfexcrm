<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageTitle ?? 'Module' }} — {{ $moduleName ?? 'CRM' }}</title>

  <!-- Fonts: Outfit + Inter (same as Vue SPA) -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Font Awesome (CI modules need it) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

  <!-- Bootstrap 3 CSS (CI modules render Bootstrap 3 markup) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" />

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap.min.css" />

  <!-- Bootstrap Select -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" />

  <!-- Bootstrap Datepicker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />

  <!-- Handsontable -->
  <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet" />

  <!-- jQuery (needed by all CI plugins — load first, before any inline scripts) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

  <style>
    /* ── CSS Variables (lavender default, matches Vue themeStore) ────── */
    :root {
      --theme-bg: #bcb3e2;
      --theme-primary: #9f8ed6;
      --theme-primary-hover: #8d7bc8;
      --theme-text-dark: #5f4f8d;
      --theme-accent: #e8a7b0;
      --shadow-dark-rgb: rgba(70, 50, 110, 0.22);
      --shadow-light-rgb: rgba(255, 255, 255, 0.85);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      max-width: 100%;
      overflow-x: hidden;
      height: 100%;
    }

    body {
      font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      font-size: 14px;
      color: #334155;
      background: var(--theme-bg);
      transition: background 0.3s ease;
    }

    /* ── App Shell ─────────────────────────────────────────────────── */
    .crm-app-shell {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* ── Sidebar ──────────────────────────────────────────────────── */
    .crm-sidebar {
      width: 240px;
      min-width: 240px;
      background: #faf6f0;
      border-radius: 28px;
      margin: 16px 8px 16px 16px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      box-shadow:
        12px 12px 24px var(--shadow-dark-rgb),
        -12px -12px 24px var(--shadow-light-rgb),
        inset 2px 2px 4px rgba(255, 255, 255, 0.5);
      display: flex;
      flex-direction: column;
      height: calc(100vh - 32px);
      overflow: hidden;
      flex-shrink: 0;
      z-index: 30;
    }

    /* Logo */
    .crm-sidebar__logo {
      height: 64px;
      display: flex;
      align-items: center;
      padding: 0 16px;
      border-bottom: 1px solid rgba(163, 149, 127, 0.12);
      flex-shrink: 0;
    }
    .crm-sidebar__logo-inner {
      display: flex;
      align-items: center;
      overflow: hidden;
      background: #fff;
      border-radius: 10px;
      padding: 6px 14px;
    }
    .crm-logo-img { height: 28px; max-width: 130px; object-fit: contain; }

    /* Profile */
    .crm-sidebar__profile {
      padding: 14px 12px;
      border-bottom: 1px solid rgba(163, 149, 127, 0.12);
      flex-shrink: 0;
    }
    .crm-profile-card { display: flex; align-items: center; gap: 12px; }
    .crm-profile-avatar {
      width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
      border: 2px solid #fff;
      box-shadow: 2px 2px 5px rgba(163, 149, 127, 0.2);
    }
    .crm-profile-info { display: flex; flex-direction: column; min-width: 0; }
    .crm-profile-name {
      font-size: 13px; font-weight: 700;
      color: var(--theme-text-dark);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .crm-profile-email {
      font-size: 11px; font-weight: 500;
      color: var(--theme-text-dark); opacity: 0.55;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Nav */
    .crm-sidebar__nav {
      flex: 1;
      overflow-y: auto;
      padding: 10px 8px;
      scrollbar-width: thin;
      scrollbar-color: rgba(163, 149, 127, 0.2) transparent;
    }
    .crm-nav-item {
      display: flex; align-items: center; width: 100%;
      padding: 10px 14px; margin-bottom: 4px; gap: 12px;
      font-size: 13.5px; font-weight: 700;
      color: var(--theme-text-dark); opacity: 0.75;
      text-decoration: none; border: none; background: none;
      cursor: pointer; text-align: left;
      border-radius: 999px; position: relative;
      transition: all 0.25s ease; line-height: 1.5;
    }
    .crm-nav-item:hover {
      background: rgba(188, 179, 226, 0.15); opacity: 1;
    }
    .crm-nav-item--active {
      background: #ffffff !important;
      color: var(--theme-text-dark) !important;
      opacity: 1;
      box-shadow:
        inset 3px 3px 6px rgba(100, 90, 130, 0.12),
        inset -3px -3px 6px rgba(255, 255, 255, 0.95),
        1px 2px 4px rgba(100, 90, 130, 0.05);
    }
    .crm-nav-item--active::after {
      content: '';
      position: absolute; right: 12px;
      width: 5px; height: 5px;
      background: var(--theme-primary);
      border-radius: 50%;
    }
    .crm-nav-icon {
      width: 20px; height: 20px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; opacity: 0.7; transition: all 0.2s;
    }
    .crm-nav-icon svg { width: 20px; height: 20px; }
    .crm-nav-item--active .crm-nav-icon { opacity: 1; }

    /* Section divider */
    .crm-nav-divider {
      height: 1px;
      background: rgba(163, 149, 127, 0.12);
      margin: 8px 14px;
    }
    .crm-nav-heading {
      font-size: 10px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.08em;
      color: var(--theme-text-dark); opacity: 0.4;
      padding: 8px 14px 4px;
    }

    /* ── Main ─────────────────────────────────────────────────────── */
    .crm-main {
      flex: 1; display: flex; flex-direction: column;
      overflow: hidden; min-width: 0;
    }

    /* Header */
    .crm-header {
      height: 64px;
      background: #faf6f0;
      border-radius: 28px;
      margin: 16px 16px 0 8px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      box-shadow:
        12px 12px 24px var(--shadow-dark-rgb),
        -12px -12px 24px var(--shadow-light-rgb),
        inset 2px 2px 4px rgba(255, 255, 255, 0.5);
      display: flex; align-items: center;
      justify-content: space-between;
      padding: 0 24px; flex-shrink: 0;
      z-index: 20; gap: 16px;
    }
    .crm-header__left { display: flex; align-items: center; gap: 12px; }
    .crm-breadcrumb {
      display: flex; align-items: center; gap: 8px;
      font-size: 14px; font-weight: 600;
      color: var(--theme-text-dark);
    }
    .crm-breadcrumb a {
      color: var(--theme-text-dark); text-decoration: none;
      opacity: 0.6; transition: opacity 0.2s;
    }
    .crm-breadcrumb a:hover { opacity: 1; }
    .crm-breadcrumb__sep { opacity: 0.3; }
    .crm-header__right { display: flex; align-items: center; gap: 8px; }
    .crm-header-btn {
      background: #faf6f0; border: none; cursor: pointer;
      color: var(--theme-text-dark);
      width: 36px; height: 36px;
      display: flex; align-items: center; justify-content: center;
      border-radius: 50%;
      box-shadow:
        3px 3px 6px rgba(163, 149, 127, 0.15),
        -3px -3px 6px rgba(255, 255, 255, 0.9);
      transition: all 0.2s ease;
    }
    .crm-header-btn svg { width: 18px; height: 18px; }
    .crm-header-btn:hover { color: var(--theme-primary); transform: translateY(-1px); }

    /* Page Content */
    .crm-page-content {
      flex: 1; overflow-y: auto;
      padding: 24px 18px;
      background: var(--theme-bg);
      transition: background 0.3s ease;
    }

    /* ── CI Content Wrapper (neomorphic card) ─────────────────────── */
    .ci-content-wrapper {
      background: #faf6f0;
      border: 1px solid rgba(255, 255, 255, 0.6);
      border-radius: 24px;
      padding: 0;
      position: relative;
      overflow: hidden;
      box-shadow:
        12px 12px 24px var(--shadow-dark-rgb),
        -12px -12px 24px var(--shadow-light-rgb),
        inset 2px 2px 4px rgba(255, 255, 255, 0.5);
      transition: all 0.3s ease;
    }
    .ci-content-inner {
      padding: 24px;
      position: relative;
      z-index: 1;
    }

    /* ── Bootstrap 3 → App Theme Overrides ─────────────────────────── */
    .panel_s, .panel {
      background-color: transparent !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: none !important;
      margin-bottom: 0 !important;
    }
    .panel-heading {
      background: rgba(163, 149, 127, 0.06) !important;
      border-radius: 16px 16px 0 0 !important;
      border-bottom: 1px solid rgba(163, 149, 127, 0.12) !important;
      color: var(--theme-text-dark) !important;
      font-weight: 600 !important;
    }
    .panel-body { padding: 20px !important; }
    .form-control {
      border-radius: 8px !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: none !important;
      height: 38px; font-size: 13px;
      color: var(--theme-text-dark) !important;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus {
      border-color: var(--theme-primary) !important;
      box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.15) !important;
    }
    .btn {
      border-radius: 8px !important;
      font-weight: 600; padding: 8px 16px; font-size: 13px;
      transition: all 0.2s;
    }
    .btn-primary {
      background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
      border: none !important; color: #fff !important;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
    }
    .btn-success {
      background: linear-gradient(135deg, #10b981, #059669) !important;
      border: none !important; color: #fff !important;
    }
    .btn-info {
      background: linear-gradient(135deg, #0ea5e9, #0284c7) !important;
      border: none !important; color: #fff !important;
    }
    .btn-warning {
      background: linear-gradient(135deg, #f59e0b, #d97706) !important;
      border: none !important; color: #fff !important;
    }
    .btn-danger {
      background: linear-gradient(135deg, #ef4444, #dc2626) !important;
      border: none !important; color: #fff !important;
    }
    .btn-default {
      background: #fff !important;
      border: 1px solid #e2e8f0 !important;
      color: var(--theme-text-dark) !important;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06) !important;
    }
    .btn-default:hover {
      background: #f8fafc !important;
      border-color: var(--theme-primary) !important;
      color: var(--theme-primary) !important;
    }
    table.dataTable {
      border: none !important;
    }
    table.dataTable thead th {
      border-bottom: 2px solid rgba(163, 149, 127, 0.15) !important;
      color: var(--theme-text-dark) !important;
      font-weight: 600 !important;
      font-size: 12px !important;
    }
    table.dataTable tbody td {
      color: var(--theme-text-dark) !important;
      border-bottom: 1px solid rgba(163, 149, 127, 0.08) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: var(--theme-primary) !important;
      border-color: var(--theme-primary) !important;
      color: #fff !important;
      border-radius: 8px !important;
    }
    .alert {
      border-radius: 12px !important;
      border: none !important;
      font-weight: 500;
    }
    .nav-tabs > li > a {
      border-radius: 8px 8px 0 0 !important;
      color: var(--theme-text-dark) !important;
      font-weight: 600;
      font-size: 13px;
    }
    .nav-tabs > li.active > a,
    .nav-tabs > li.active > a:focus,
    .nav-tabs > li.active > a:hover {
      background: #fff !important;
      border-color: var(--theme-primary) !important;
      color: var(--theme-primary) !important;
      border-bottom-color: #fff !important;
    }
    .selectpicker, .bootstrap-select {
      border-radius: 8px !important;
    }
    .bootstrap-select .btn-default {
      background-color: #fff !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      box-shadow: none !important;
      height: 38px !important;
      font-size: 13px !important;
      color: var(--theme-text-dark) !important;
    }
    .bootstrap-select .dropdown-menu {
      border-radius: 8px !important;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important;
      border: 1px solid #e2e8f0 !important;
    }
    label, .control-label {
      color: var(--theme-text-dark) !important;
      font-weight: 600 !important;
      font-size: 13px !important;
    }
    h1, h2, h3, h4, h5, h6 {
      color: var(--theme-text-dark) !important;
    }
    a { color: var(--theme-primary) !important; }
    a:hover { color: var(--theme-primary-hover) !important; }
    .text-muted { color: #94a3b8 !important; }

    /* ── Responsive ───────────────────────────────────────────────── */
    @media (max-width: 900px) {
      .crm-sidebar { display: none; }
      .crm-header { margin: 8px; border-radius: 16px; }
      .crm-page-content { padding: 12px 8px; }
    }
  </style>

  <!-- Module CSS (loaded by init_head) -->
  @isset($moduleCss)
    {!! $moduleCss !!}
  @endisset
</head>
<body>
  <div class="crm-app-shell">
    <!-- Sidebar -->
    <aside class="crm-sidebar">
      <!-- Logo -->
      <div class="crm-sidebar__logo">
        <div class="crm-sidebar__logo-inner">
          <img src="{{ asset('logo.png') }}" alt="CRM" class="crm-logo-img" onerror="this.style.display='none'" />
        </div>
      </div>

      <!-- User Profile -->
      @php
        $currentUser = auth('web')->user() ?? auth('sanctum')->user();
      @endphp
      @if($currentUser)
      <div class="crm-sidebar__profile">
        <div class="crm-profile-card">
          @if($currentUser->profile_image)
            <img src="{{ asset('uploads/staff/profile_image/' . $currentUser->profile_image) }}" alt="{{ $currentUser->name }}" class="crm-profile-avatar" />
          @else
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Avatar" class="crm-profile-avatar" />
          @endif
          <div class="crm-profile-info">
            <span class="crm-profile-name">{{ $currentUser->name }}</span>
            <span class="crm-profile-email">{{ $currentUser->email }}</span>
          </div>
        </div>
      </div>
      @endif

      <!-- Navigation -->
      <nav class="crm-sidebar__nav">
        <a href="{{ url('admin/dashboard') }}" class="crm-nav-item">
          <span class="crm-nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </span>
          Dashboard
        </a>

        <div class="crm-nav-divider"></div>
        <div class="crm-nav-heading">Modules</div>

        <!-- Active module menus -->
        @if(isset($moduleMenus) && count($moduleMenus) > 0)
          @foreach($moduleMenus as $menu)
            <a href="{{ $menu['href'] ?? '#' }}" class="crm-nav-item {{ ($menu['active'] ?? false) ? 'crm-nav-item--active' : '' }}">
              <span class="crm-nav-icon">
                <i class="{{ $menu['icon'] ?? 'fa fa-cube' }}"></i>
              </span>
              {{ $menu['name'] ?? 'Menu Item' }}
            </a>
          @endforeach
        @endif

        <div class="crm-nav-divider"></div>

        <!-- Back to Dashboard -->
        <a href="{{ url('admin/dashboard') }}" class="crm-nav-item">
          <span class="crm-nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          </span>
          Back to Dashboard
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="crm-main">
      <!-- Header -->
      <header class="crm-header">
        <div class="crm-header__left">
          <div class="crm-breadcrumb">
            <a href="{{ url('admin/dashboard') }}">Home</a>
            <span class="crm-breadcrumb__sep">/</span>
            <span>{{ $moduleName ?? 'Module' }}</span>
            @if(isset($pageTitle) && $pageTitle !== $moduleName)
              <span class="crm-breadcrumb__sep">/</span>
              <span>{{ $pageTitle }}</span>
            @endif
          </div>
        </div>
        <div class="crm-header__right">
          <a href="{{ url('admin/dashboard') }}" class="crm-header-btn" title="Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </a>
        </div>
      </header>

      <!-- Page Content -->
      <main class="crm-page-content">
        <div class="ci-content-wrapper">
          <div class="ci-content-inner">
            {!! $htmlContent !!}
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- iBridge CRM JS Compat Layer -->
  <script>
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var admin_url = '{{ url("admin") }}/';
    var site_url = '{{ url("/") }}/';

    function alert_float(type, message) {
      var cls = type === 'danger' ? 'alert-danger' : (type === 'success' ? 'alert-success' : 'alert-info');
      var $a = $('<div class="alert ' + cls + '" style="position:fixed;top:20px;right:20px;z-index:99999;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);max-width:400px;">' + message + '</div>');
      $('body').append($a);
      setTimeout(function() { $a.fadeOut('slow', function() { $(this).remove(); }); }, 3000);
    }

    function init_selectpicker() {
      if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker({ liveSearch: true, size: 8 });
      }
    }

    function init_datepicker() {
      if ($.fn.datepicker) {
        $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true });
      }
    }

    $(document).ready(function() {
      init_selectpicker();
      init_datepicker();
    });

    function initDataTable(table, url, not_sortable, not_searchable, fnServerParams, default_order) {
      var $table = $(table);
      if ($table.length === 0) return;
      if ($.fn.DataTable.isDataTable(table)) { $table.DataTable().destroy(); }

      var order = [];
      if (default_order && default_order.length > 1) { order.push([default_order[0], default_order[1]]); }

      var columnDefs = [];
      if (not_sortable) { columnDefs.push({ "orderable": false, "targets": not_sortable }); }
      if (not_searchable) { columnDefs.push({ "searchable": false, "targets": not_searchable }); }

      return $table.DataTable({
        "processing": true, "serverSide": true,
        "ajax": {
          "url": url, "type": "POST",
          "data": function(d) {
            if (typeof fnServerParams === 'function') { fnServerParams(d); }
            else if (typeof fnServerParams === 'object') { $.extend(d, fnServerParams); }
          }
        },
        "order": order, "columnDefs": columnDefs,
        "language": {
          "emptyTable": "No data available", "info": "Showing _START_ to _END_ of _TOTAL_",
          "loadingRecords": "Loading...", "processing": "Processing...",
          "search": "Search:", "zeroRecords": "No matching records found",
          "paginate": { "first": "First", "last": "Last", "next": "Next", "previous": "Previous" }
        }
      });
    }
  </script>

  <!-- Module JS (loaded by init_tail) -->
  @isset($moduleJs)
    {!! $moduleJs !!}
  @endisset
</body>
</html>
