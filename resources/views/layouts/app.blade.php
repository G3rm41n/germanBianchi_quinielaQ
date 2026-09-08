<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Agencia N°5801 — Plataforma de simulación de jugadas de lotería con fines académicos.')">
    <title>@yield('title', 'Inicio') — Agencia N°5801</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-wrapper">

    {{-- ===== Navbar ===== --}}
    <nav class="navbar" role="navigation" aria-label="Navegación principal">
        <div class="container">
            <div class="navbar-inner">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="navbar-brand" aria-label="Agencia N°5801 - Inicio">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo Agencia N°5801">
                    <span class="navbar-brand-text">Agencia <span>N°5801</span></span>
                </a>

                {{-- Nav links --}}
                @auth
                    <ul class="navbar-nav" aria-label="Menú de usuario">
                        <li>
                            <a href="{{ route('home') }}"
                               class="{{ request()->routeIs('home') ? 'active' : '' }}">
                                Inicio
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}"
                                   class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                    Backoffice
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('jugadas.index') }}"
                                   class="{{ request()->routeIs('jugadas.*') ? 'active' : '' }}">
                                    Mis Jugadas
                                </a>
                            </li>
                        @endif
                    </ul>

                    {{-- User actions --}}
                    <div class="navbar-actions">
                        <div class="navbar-user">
                            <span class="navbar-user-name">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" id="form-logout">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" id="btn-logout"
                                        aria-label="Cerrar sesión">
                                    Salir
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="navbar-actions">
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" id="nav-btn-login">
                            Ingresar
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm" id="nav-btn-register">
                            Registrarse
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ===== Contenido principal ===== --}}
    <main class="main-content" role="main">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="container" style="padding-top: 1.25rem;">
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container" style="padding-top: 1.25rem;">
                <div class="alert alert-error" role="alert">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">Agencia N°5801</div>
                <div class="footer-disclaimer">
                    ⚠️ Plataforma de simulación transaccional con fines exclusivamente académicos.
                    No constituye una agencia de apuestas real ni posee validez legal.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
