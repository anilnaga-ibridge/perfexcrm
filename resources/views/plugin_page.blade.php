<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $pageTitle }} — {{ $module->name }}</title>
  <!-- jQuery 3.6.0 (required for Bootstrap and DataTables) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap 3.4.1 CSS and JS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
  <!-- DataTables CSS and JS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap.min.css">
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Handsontable spreadsheet library for bulk-entry views -->
  <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">
  <!-- Bootstrap Select (for .selectpicker select dropdowns) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
  <!-- Bootstrap Datepicker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8fafc;
      color: #1e293b;
      min-height: 100vh;
      padding: 24px;
    }
    #wrapper {
      margin: 0;
      padding: 0;
      width: 100%;
    }
    .content {
      padding: 0;
    }
    .panel_s {
      margin-bottom: 24px;
      background-color: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
      padding: 24px;
    }
    .panel-body {
      padding: 0;
    }
    .hr-color {
      border-top: 1px solid #f1f5f9;
      margin-top: 16px;
      margin-bottom: 16px;
    }
    .mb-4 { margin-bottom: 1.5rem !important; }
    .mb-5 { margin-bottom: 2rem !important; }
    .mleft5 { margin-left: 5px !important; }
    .mright5 { margin-right: 5px !important; }
    .form-group label {
      font-weight: 600;
      color: #475569;
      font-size: 13px;
      margin-bottom: 6px;
    }
    .form-control {
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      box-shadow: none;
      height: 38px;
      font-size: 13px;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
      outline: 0;
    }
    .btn {
      border-radius: 8px;
      font-weight: 600;
      padding: 8px 16px;
      font-size: 13px;
    }
    .btn-primary {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border: none;
      color: #fff;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: #fff;
    }
    .btn-info {
      background: #0284c7;
      border: none;
      color: #fff;
    }
    .btn-info:hover {
      background: #0369a1;
      color: #fff;
    }
    /* bootstrap-select override styling to fit modern UI */
    .bootstrap-select .btn-default {
      background-color: #fff !important;
      border: 1px solid #e2e8f0 !important;
      color: #1e293b !important;
      border-radius: 8px !important;
      box-shadow: none !important;
      height: 38px !important;
      font-size: 13px !important;
    }
    .bootstrap-select .dropdown-menu {
      border-radius: 8px !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
      border: 1px solid #e2e8f0 !important;
    }
  </style>
</head>
<body>
  {!! $htmlContent !!}

  <!-- iBridge CRM Core JS Compatibility Layer -->
  <script>
    // Setup jQuery to automatically include CSRF token for all Laravel AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var admin_url = '{{ url("admin") }}/';
    var site_url = '{{ url("/") }}/';
    
    function alert_float(type, message) {
        var alertClass = type === 'danger' ? 'alert-danger' : (type === 'success' ? 'alert-success' : 'alert-info');
        var $alert = $('<div class="alert ' + alertClass + '" style="position:fixed;top:20px;right:20px;z-index:99999;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">' + message + '</div>');
        $('body').append($alert);
        setTimeout(function() {
            $alert.fadeOut('slow', function() { $(this).remove(); });
        }, 3000);
    }

    function init_selectpicker() {
        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker({
                liveSearch: true,
                size: 8
            });
        }
    }

    function init_datepicker() {
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        }
    }

    // Auto-initialize select pickers and datepickers on load
    $(document).ready(function() {
        init_selectpicker();
        init_datepicker();
    });

    function initDataTable(table, url, not_sortable, not_searchable, fnServerParams, default_order) {
        var $table = $(table);
        if ($table.length === 0) return;
        
        // Handle DataTable if it's already initialized to prevent re-initialization errors
        if ($.fn.DataTable.isDataTable(table)) {
            $table.DataTable().destroy();
        }
        
        var order = [];
        if (default_order && default_order.length > 1) {
            order.push([default_order[0], default_order[1]]);
        }
        
        var columnDefs = [];
        if (not_sortable) {
            columnDefs.push({ "orderable": false, "targets": not_sortable });
        }
        if (not_searchable) {
            columnDefs.push({ "searchable": false, "targets": not_searchable });
        }

        var dt = $table.DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": url,
                "type": "POST", // Prefer POST for DataTables
                "data": function(d) {
                    if (typeof fnServerParams === 'function') {
                        fnServerParams(d);
                    } else if (typeof fnServerParams === 'object') {
                        $.extend(d, fnServerParams);
                    }
                }
            },
            "order": order,
            "columnDefs": columnDefs,
            "language": {
                "emptyTable": "No data available in table",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "lengthMenu": "Show _MENU_ entries",
                "loadingRecords": "Loading...",
                "processing": "Processing...",
                "search": "Search:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
        return dt;
    }
  </script>
</body>
</html>
