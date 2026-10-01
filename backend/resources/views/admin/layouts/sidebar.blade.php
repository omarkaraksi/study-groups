<aside class="col-12 col-lg-3 col-xl-2">
    <div class="sticky-top pt-3">
        <div class="list-group list-group-transparent">
            <a class="list-group-item list-group-item-action d-flex align-items-center {{ request()->routeIs('admin.study-groups.*') ? 'active' : '' }}"
               href="{{ route('admin.study-groups.index') }}">
                <span class="nav-link-icon me-2">◆</span>
                Study Groups
            </a>
            <a class="list-group-item list-group-item-action d-flex align-items-center active"
               href="{{ route('admin.dashboard') }}">
                <span class="nav-link-icon me-2">⌂</span>
                Dashboard
            </a>
        </div>
    </div>
</aside>
