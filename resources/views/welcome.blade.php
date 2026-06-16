<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perfex CRM</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            height: 100%;
        }

        .loading-app-container {
            height: 100%;
            width: 100%;
            display: flex;
            position: fixed;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            z-index: 9999;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4f46e5;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="loading-app-container">
            <div class="loader"></div>
        </div>
    </div>

    <script>
        window.config = {
            'path': '{{ url('/') }}',
            'perPage': 10,
            'product_name': 'Perfex CRM Clone',
            'product_version': '1.0.0',
            'theme_mode': 'light',
            'app_env': '{{ app()->environment() }}',
        };
    </script>

    @vite(['resources/js/app.js'])
</body>
</html>
