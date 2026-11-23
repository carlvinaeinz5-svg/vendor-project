<header class="navbar">
    <a class="brand" href="{{ route('marketplace') }}" aria-label="Nakawa Market home">
        <span class="brand-mark">N</span>
        <span>Nakawa <strong>Market</strong><small>Local finds, all in one place</small></span>
    </a>

    <button class="nav-toggle" id="nav-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" hidden>
        <span class="nav-toggle-icon" aria-hidden="true">☰</span>
        <span class="sr-only">Toggle navigation</span>
    </button>

    <nav class="primary-navigation" id="primary-navigation" aria-label="Main navigation">
        <a class="{{ request()->routeIs('marketplace', 'product.show') ? 'active' : '' }}" href="{{ route('marketplace') }}">Marketplace</a>
        <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
        <a class="cart-link {{ request()->routeIs('cart') ? 'active' : '' }}" href="{{ route('cart') }}">
            Cart <span class="badge">{{ collect(session('cart', []))->sum('quantity') }}</span>
        </a>
        @auth
            <a class="{{ request()->routeIs('orders', 'orders.confirmation') ? 'active' : '' }}" href="{{ route('orders') }}">My Orders</a>
            @if(auth()->user()->role === 'vendor')
                <a class="{{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}">Vendor</a>
            @elseif(auth()->user()->role === 'admin')
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin</a>
            @endif
            <a class="nav-profile-link" href="{{ route('settings.index') }}" aria-label="Profile settings">
                @if(auth()->user()->profile_image_path)
                    <img class="nav-profile-image" src="{{ asset('storage/'.auth()->user()->profile_image_path) }}" alt="">
                @else
                    <span class="nav-profile-placeholder" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
            </a>
            <a class="nav-settings-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}" aria-label="Settings" title="Settings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3.2 13.4 5l2.3-.2.7 2.2 2.1 1-.7 2.2.7 2.2-2.1 1-.7 2.2-2.3-.2L12 17.2l-1.4-1.8-2.3.2-.7-2.2-2.1-1 .7-2.2-.7-2.2 2.1-1 .7-2.2 2.3.2L12 3.2Z" />
                    <circle cx="12" cy="10.2" r="2.4" />
                </svg>
                <span class="sr-only">Settings</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-logout" type="submit">Logout</button></form>
        @else
            <a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
        @endauth
    </nav>
</header>

<script>
    (() => {
        const toggle = document.getElementById('nav-toggle');
        const navigation = document.getElementById('primary-navigation');

        if (!toggle || !navigation) {
            return;
        }

        const mobile = window.matchMedia('(max-width: 1024px)');
        const syncNavigation = () => {
            toggle.hidden = !mobile.matches;
            navigation.classList.toggle('is-collapsed', mobile.matches && toggle.getAttribute('aria-expanded') !== 'true');

            if (!mobile.matches) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        };

        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(expanded));
            navigation.classList.toggle('is-collapsed', !expanded);
        });

        navigation.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (mobile.matches) {
                    toggle.setAttribute('aria-expanded', 'false');
                    navigation.classList.add('is-collapsed');
                }
            });
        });

        mobile.addEventListener('change', syncNavigation);
        syncNavigation();
    })();
</script>
