<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Helpdesk System')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    @auth
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    @if(file_exists(public_path('images/logo-wit.png')))
                        <img src="{{ asset('images/logo-wit.png') }}" alt="WIT Helpdesk" class="sidebar-brand-logo" width="38" height="38">
                    @else
                        <div class="sidebar-brand-mark">W</div>
                    @endif
                    <div class="sidebar-brand-copy">
                        <h2>WIT Helpdesk</h2>
                        <p>Helpdesk Dashboard</p>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li class="sidebar-section-title">Utama</li>
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                
                @can('create', App\Models\Ticket::class)
                <li>
                    <a href="{{ route('tickets.create') }}" class="{{ request()->routeIs('tickets.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i> Buat Tiket
                    </a>
                </li>
                @endcan
                
                <li>
                    <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') && !request()->routeIs('tickets.create') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i> Tiket Saya
                    </a>
                </li>
                
                @if(auth()->user()->isAdmin())
                <li class="sidebar-section-title">Admin</li>
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Manajemen User
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Manajemen Kategori
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </li>
                @endif
                
                <li>
                    <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i> Notifikasi
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('profile.show') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i> Profil
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="sidebar-user-row">
                    <div class="sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="sidebar-user-copy">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form">
                        @csrf
                        <button type="submit" class="sidebar-logout-icon" aria-label="Logout" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <nav class="navbar">
                <div class="navbar-left">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p>@yield('page-subtitle', 'Ringkasan operasional')</p>
                </div>
                <div class="navbar-right">
                    <div class="notification-badge" onclick="window.location.href='{{ route('notifications.index') }}'">
                        <i class="fas fa-bell fa-lg" style="color: var(--primary);"></i>
                        <span class="badge" id="notification-count">0</span>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        @auth
        // Notification polling
        function updateNotificationCount() {
            $.ajax({
                url: '{{ route("notifications.count") }}',
                method: 'GET',
                success: function(data) {
                    $('#notification-count').text(data.count);
                    if (data.count > 0) {
                        $('#notification-count').show();
                    } else {
                        $('#notification-count').hide();
                    }
                }
            });
        }

        // Update every 30 seconds
        setInterval(updateNotificationCount, 30000);
        updateNotificationCount();
        @endauth

        // Password toggle function (global)
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-icon');
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
