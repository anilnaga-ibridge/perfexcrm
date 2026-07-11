<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $pageTitle }} — {{ $module->name }}</title>
  <!-- Bootstrap 3.4.1 CSS for legacy layout compatibility -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Handsontable spreadsheet library for bulk-entry views -->
  <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">
  
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8fafc;
      color: #1e293b;
      min-height: 100vh;
      padding: 24px;
    }
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
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }
  </style>
</head>
<body>
  {!! $htmlContent !!}
</body>
</html>
