<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#admin-navbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand navbar-brand-autodark" href="{{ route('admin.dashboard') }}">
            {{ config('app.name', 'Study Groups') }}
        </a>

        <div class="navbar-nav flex-row order-md-last">
            <!-- Language Switcher -->
            <div class="nav-item dropdown me-3">
                <a href="#" class="nav-link d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="nav-link-icon me-1">🌐</span>
                    <span class="d-none d-md-inline">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('admin.locale.switch', ['locale' => 'en']) }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        English
                    </a>
                    <a href="{{ route('admin.locale.switch', ['locale' => 'ar']) }}" class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                        العربية
                    </a>
                </div>
            </div>
            
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar avatar-sm" style="background-color:#467fcf;">{{ substr(auth()->user()?->name ?? 'A', 0, 1) }}</span>
                    <span class="ms-2 d-none d-md-block">{{ auth()->user()?->name ?? 'Admin' }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="admin-navbar">
            <div class="navbar-nav">
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                    <span class="nav-link-title">@lang('admin.dashboard')</span>
                </a>
            </div>
        </div>
    </div>
</header>
