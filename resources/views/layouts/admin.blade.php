<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Backoffice administrativo — Agencia N°5801">
    <title>@yield('title', 'Panel Admin') — Backoffice N°5801</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--color-bg); color: var(--color-text);">

<div class="admin-layout">

    {{-- ===== Sidebar ===== --}}
    <aside class="admin-sidebar" role="navigation" aria-label="Menú de administración">
        <div class="admin-sidebar-header">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="admin-sidebar-logo">
            <div class="admin-sidebar-title">N°5801 Admin</div>
        </div>

        <nav class="admin-sidebar-nav">
            <a href="{{ route('admin.dashboard') }}"
               class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               id="admin-nav-panel"
               aria-label="Panel principal">
                📊 Panel
            </a>
            <a href="#" class="admin-sidebar-link" id="admin-nav-jugadas" aria-label="Gestión de jugadas" style="opacity:0.45; cursor:not-allowed;">
                🎯 Jugadas
            </a>
            <a href="#" class="admin-sidebar-link" id="admin-nav-precios" aria-label="Gestión de precios" style="opacity:0.45; cursor:not-allowed;">
                💰 Precios
            </a>
            <a href="#" class="admin-sidebar-link" id="admin-nav-audit" aria-label="Audit log" style="opacity:0.45; cursor:not-allowed;">
                📋 Audit Log
            </a>

            <hr class="divider">

            <a href="#" class="admin-sidebar-link text-danger" id="admin-nav-limpiar"
               aria-label="Limpiar base de datos" style="opacity:0.45; cursor:not-allowed;">
                🗑️ Limpiar BD
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" id="admin-form-logout">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm btn-block" id="admin-btn-logout">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== Main Area ===== --}}
    <div class="admin-main">
        {{-- Top bar --}}
        <div class="admin-topbar">
            <div>
                <span style="font-family: var(--font-display); font-weight: 600; font-size: 0.95rem;">
                    @yield('page_title', 'Panel de Administración')
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="text-muted" style="font-size: 0.85rem;">
                    {{ auth()->user()->name }}
                </span>
                <span class="badge badge-procesado" style="font-size: 0.7rem;">Admin</span>
            </div>
        </div>

        {{-- Flash messages --}}
        <div class="admin-page" style="padding-bottom: 0;">
            @if(session('success'))
                <div class="alert alert-success" role="alert">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif
        </div>

        {{-- Content --}}
        <div class="admin-page" role="main">
            @yield('content')
        </div>
    </div>

</div>

</body>
</html>
