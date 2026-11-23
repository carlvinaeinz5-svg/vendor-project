<footer class="footer">
    <div class="footer-inner">
        <section class="footer-about">
            <a class="footer-brand" href="{{ route('marketplace') }}"><span class="brand-mark">N</span> Nakawa Market</a>
            <p>Your local marketplace for fresh produce, household goods, and textiles from Nakawa vendors.</p>
            <span class="footer-location">Proudly connecting Nakawa, Kampala</span>
        </section>

        <nav class="footer-links" aria-label="Quick links">
            <h2>Explore</h2>
            <a href="{{ route('marketplace') }}">Shop the marketplace</a>
            <a href="{{ route('about') }}">About Nakawa Market</a>
            <a href="{{ route('cart') }}">Your cart</a>
            @auth
                <a href="{{ route('orders') }}">My orders</a>
                <a href="{{ route('settings.index') }}">Account settings</a>
            @else
                <a href="{{ route('login') }}">Sign in</a>
                <a href="{{ route('register') }}">Create an account</a>
            @endauth
        </nav>

        <section class="footer-contact">
            <h2>Contact us</h2>
            <a href="mailto:vendorcustomer@co.ug">vendorcustomer@co.ug</a>
            <a href="tel:0747944559">0747 944 559</a>
            <span>Nakawa, Kampala, Uganda</span>
            <p>Payment, delivery, and collection are arranged directly with each vendor.</p>
        </section>
    </div>
    <div class="footer-bottom">
        <span>&copy; {{ now()->year }} Nakawa Market Online</span>
        <span>Local vendors. A market full of possibilities.</span>
    </div>
</footer>
