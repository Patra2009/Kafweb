<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FBF7F1">
    <meta name="description" content="Pesan menu favoritmu langsung dari meja KafWeb, tanpa antre.">
    <title>KafWeb, Pesan dari Meja</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    @vite(['resources/css/customer.css', 'resources/js/customer/main.js'])
</head>
<body class="bg-cream-50 font-sans text-bark-800 antialiased">
<div id="app" aria-busy="true">
    <div class="grid min-h-[100dvh] place-items-center">
        <div class="flex flex-col items-center gap-3">
            <i class="ph ph-coffee text-3xl text-clay-600" aria-hidden="true"></i>
            <p class="text-sm font-medium text-bark-600">Menyiapkan menu katamu...</p>
        </div>
    </div>
</div>
<noscript>
    <div class="m-4 rounded-2xl border border-berry-200 bg-berry-50 p-4 text-center text-sm font-medium text-berry-600">
        Aplikasi ini membutuhkan JavaScript aktif untuk menampilkan menu dan menerima pesanan.
    </div>
</noscript>
</body>
</html>
