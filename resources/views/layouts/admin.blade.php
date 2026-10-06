<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice - Agencia N°5801</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600|outfit:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-wrapper">
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="admin-sidebar-logo">
                <span class="admin-sidebar-title">Backoffice</span>
            </div>
            <nav class="admin-sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    📊 Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    👥 Clientes y Jugadas
                </a>
                <a href="{{ route('home') }}" class="admin-sidebar-link">
                    🌐 Ver Sitio Público
                </a>
            </nav>
            <div class="admin-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-block" style="color: var(--color-danger); justify-content: flex-start; padding-left: 0.5rem;">
                        🚪 Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <div style="font-family: var(--font-display); font-weight: 600; color: var(--color-text);">
                    Agencia N°5801 Admin
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.85rem; color: var(--color-muted);">{{ auth()->user()->name }}</span>
                </div>
            </header>

            <div class="admin-page">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif
                @if (session('info'))
                    <div class="alert alert-warning">{{ session('info') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
