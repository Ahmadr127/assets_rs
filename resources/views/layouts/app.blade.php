<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Assets RS')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div x-data="{
            sidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === '1',
            userDropdownOpen: false
        }"
        x-init="$watch('sidebarCollapsed', v => localStorage.setItem('sidebarCollapsed', v ? '1' : '0'))"
        class="min-h-screen flex">
        <!-- Sidebar -->
        <div :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'w-20' : 'w-64'
            ]"
            class="fixed inset-y-0 left-0 z-50 bg-green-700 shadow-lg transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            
            <!-- Logo/Brand -->
            <div class="flex items-center justify-between h-20 px-4 border-b border-green-600">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="bg-white rounded-xl border border-green-200 shadow-sm p-2 flex-shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto object-contain">
                    </div>
                    <h1 x-show="!sidebarCollapsed" class="text-xl font-bold text-white tracking-wide truncate">Assets RS</h1>
                </div>
                
            </div>

            <!-- Sidebar Navigation -->
            <nav class="px-4 py-6">
                <div class="mb-6">
                    <h3 x-show="!sidebarCollapsed" class="text-xs font-semibold text-green-200 uppercase tracking-wider mb-3">MENU UTAMA</h3>
                </div>
                
                <ul class="space-y-2">
                    {{-- Dashboard Menu --}}
                    @if(auth()->check() && auth()->user()->hasPermission('menu_dashboard'))
                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('dashboard') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Dashboard">
                            <i class="fas fa-tachometer-alt w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                            <span x-show="!sidebarCollapsed">Dashboard</span>
                        </a>
                    </li>
                    @endif

                    {{-- Fixed Assets Menu --}}
                    @if(auth()->check() && auth()->user()->hasPermission('menu_fixed_assets'))
                    <li>
                        <a href="{{ route('fixed-assets.index') }}" class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('fixed-assets.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Fixed Assets">
                            <i class="fas fa-building w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                            <span x-show="!sidebarCollapsed">Fixed Assets</span>
                        </a>
                    </li>
                    @endif

                    {{-- Master Data Menu --}}
                    @if(auth()->check() && auth()->user()->hasPermission('menu_master_data'))
                    <li>
                        <div class="px-4 mt-4 mb-2">
                            <h3 x-show="!sidebarCollapsed" class="text-xs font-semibold text-green-200 uppercase tracking-wider">Master Data</h3>
                        </div>
                        <ul class="space-y-1">
                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_locations'))
                            <li>
                                <a href="{{ route('masters.locations.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.locations.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Lokasi">
                                    <i class="fas fa-location-dot w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Lokasi</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_statuses'))
                            <li>
                                <a href="{{ route('masters.statuses.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.statuses.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Status">
                                    <i class="fas fa-toggle-on w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Status</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_conditions'))
                            <li>
                                <a href="{{ route('masters.conditions.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.conditions.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Kondisi">
                                    <i class="fas fa-stethoscope w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Kondisi</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_vendors'))
                            <li>
                                <a href="{{ route('masters.vendors.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.vendors.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Vendor">
                                    <i class="fas fa-truck w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Vendor</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_brands'))
                            <li>
                                <a href="{{ route('masters.brands.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.brands.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Brand">
                                    <i class="fas fa-tags w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Brand</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_master_types'))
                            <li>
                                <a href="{{ route('masters.types.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('masters.types.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Tipe Asset">
                                    <i class="fas fa-shapes w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Tipe Asset</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    {{-- User Management Menu --}}
                    @if(auth()->check() && auth()->user()->hasPermission('menu_user_management'))
                    <li>
                        <div class="px-4 mt-4 mb-2">
                            <h3 x-show="!sidebarCollapsed" class="text-xs font-semibold text-green-200 uppercase tracking-wider">User Management</h3>
                        </div>
                        <ul class="space-y-1">
                            @if(auth()->check() && auth()->user()->hasPermission('menu_users'))
                            <li>
                                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('users.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Users">
                                    <i class="fas fa-users w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Users</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_roles'))
                            <li>
                                <a href="{{ route('roles.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('roles.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Roles">
                                    <i class="fas fa-user-shield w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Roles</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->check() && auth()->user()->hasPermission('menu_permissions'))
                            <li>
                                <a href="{{ route('permissions.index') }}" class="flex items-center px-4 py-2 text-white rounded-lg hover:bg-green-800 transition-colors {{ request()->routeIs('permissions.*') ? 'bg-green-800' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="Permissions">
                                    <i class="fas fa-key w-5" :class="sidebarCollapsed ? '' : 'mr-3'"></i>
                                    <span x-show="!sidebarCollapsed">Permissions</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                </ul>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:ml-0">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-6">
                    <div class="flex items-center">
                        <button @click="window.innerWidth >= 1024 ? sidebarCollapsed = !sidebarCollapsed : sidebarOpen = !sidebarOpen" class="mr-4 p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors" :title="window.innerWidth >= 1024 ? (sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar') : (sidebarOpen ? 'Close menu' : 'Open menu')">
                            <!-- Mobile icon -->
                            <i class="fas" :class="sidebarOpen ? 'fa-xmark' : 'fa-bars'" class="lg:hidden"></i>
                            <!-- Desktop icon -->
                            <i class="fas hidden lg:inline" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                        </button>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                            <p class="text-sm text-gray-500">Assets Management System</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Clock -->
                        <div class="hidden md:block text-sm text-gray-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ now()->format('d M Y, H:i') }}
                        </div>

                        @if(auth()->check())
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ auth()->user()->role->display_name ?? 'User' }}</div>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-gray-500" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                
                                <!-- User Info -->
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ auth()->user()->role->display_name ?? 'User' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">
                                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-tachometer-alt w-5 mr-2"></i>
                                        Dashboard
                                    </a>
                                </div>

                                <!-- Logout -->
                                <div class="border-t border-gray-200 py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                                            <i class="fas fa-sign-out-alt w-5 mr-2"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Login Button for Guest -->
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Login
                        </a>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 bg-gray-50">
                @yield('content')
            </main>
        </div>

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"></div>
    </div>

    <!-- Toast Container -->
    <x-toast-container />

    @stack('scripts')
</body>
</html>
