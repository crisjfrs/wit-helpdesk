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
                    <div class="sidebar-brand-mark">T</div>
                    <div class="sidebar-brand-copy">
                        <h2>TicketOne</h2>
                        <p>Helpdesk Dashboard</p>
                    </div>
                </div>
            </div>
            <ul class="sidebar-menu">
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
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <nav class="navbar">
                <div class="navbar-left">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="navbar-right">
                    <div class="notification-badge" onclick="window.location.href='{{ route('notifications.index') }}'">
                        <i class="fas fa-bell fa-lg" style="color: var(--primary);"></i>
                        <span class="badge" id="notification-count">0</span>
                    </div>
                    <div class="d-flex align-center gap-2">
                        <span>{{ auth()->user()->name }}</span>
                        <span class="badge badge-primary">{{ ucfirst(auth()->user()->role) }}</span>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                        </form>
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
