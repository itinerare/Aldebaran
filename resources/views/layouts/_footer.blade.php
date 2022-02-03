<div class="float-right align-self-center">
    <h1>
        <a href="/feeds"><i class="fas fa-rss-square" data-toggle="tooltip" title="RSS Feeds"></i></a>
    </h1>
</div>

<div class="copyright">
    &copy; {{ Settings::get('site_name') }} {{ Carbon\Carbon::now()->year }} ・ <a href="https://github.com/itinerare/Aldebaran">Aldebaran v{{ Config::get('aldebaran.settings.version') }}</a> ・ <a href="{{ url('changelog') }}">Changelog</a> ・ <a href="{{ url('privacy') }}">Privacy Policy</a><br/>
    <small>This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and <a href="https://policies.google.com/terms">Terms of Service</a> apply.</small>
</div>
