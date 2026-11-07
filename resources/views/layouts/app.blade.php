<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'KafWeb' }} - Sistem Kafe</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Warm Cafe Theme Colors */
        :root {
            --cream: #FFF8F0;
            --warm-beige: #E8DCC4;
            --coffee-brown: #6B4423;
            --dark-wood: #3E2723;
            --terracotta: #C2674A;
            --sage-green: #9BA17B;
            --soft-shadow: rgba(107, 68, 35, 0.08);
        }
        
        body {
            background-color: var(--cream);
            color: var(--dark-wood);
        }
    </style>
</head>
<body class="antialiased">
    @yield('content')
</body>
</html>
