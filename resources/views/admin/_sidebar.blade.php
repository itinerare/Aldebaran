<ul>
    <li class="sidebar-header"><a href="{{ url('admin') }}" class="card-link">Admin Home</a></li>

    @if (config('aldebaran.commissions.enabled') && isset($commissionClasses) && $commissionClasses->count())
        <li class="sidebar-section">
            <div class="sidebar-section-header">Queues</div>
            @foreach ($commissionClasses as $class)
                <div class="sidebar-item"><a href="{{ url('admin/commissions/' . $class->slug) }}" class="{{ set_active('admin/commissions/' . $class->slug . '*') }}">{{ $class->name }}
                        Queue</a>
                </div>
                <div class="sidebar-item"><a href="{{ url('admin/commissions/quotes/' . $class->slug) }}" class="{{ set_active('admin/commissions/quotes/' . $class->slug . '*') }}">{{ $class->name }}
                        Quotes Queue</a>
                </div>
            @endforeach
            <div class="sidebar-item"><a href="{{ url('admin/ledger') }}" class="{{ set_active('admin/ledger*') }}">Ledger</a></div>
        </li>
    @endif

    <li class="sidebar-section">
        <div class="sidebar-section-header">Gallery Data</div>
        <div class="sidebar-item"><a href="{{ url('admin/data/projects') }}" class="{{ set_active('admin/data/projects*') }}">Projects</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/pieces') }}" class="{{ set_active('admin/data/pieces*') }}">Pieces</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/tags') }}" class="{{ set_active('admin/data/tags*') }}">Tags</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/programs') }}" class="{{ set_active('admin/data/programs*') }}">Media & Programs</a></div>
    </li>

    @if (config('aldebaran.commissions.enabled'))
        <li class="sidebar-section">
            <div class="sidebar-section-header">Commission Data</div>
            <div class="sidebar-item"><a href="{{ url('admin/data/commissions/classes') }}" class="{{ set_active('admin/data/commissions/classes*') }}">Classes</a></div>
            <div class="sidebar-item"><a href="{{ url('admin/data/commissions/categories') }}" class="{{ set_active('admin/data/commissions/categories*') }}">Categories</a></div>
            <div class="sidebar-item"><a href="{{ url('admin/data/commissions/types') }}" class="{{ set_active('admin/data/commissions/types*') }}">Types</a></div>
        </li>
    @endif

    @if (config('aldebaran.settings.email_features'))
        <li class="sidebar-section">
            <div class="sidebar-section-header">Mailing Lists</div>
            <div class="sidebar-item"><a href="{{ url('admin/mailing-lists') }}" class="{{ set_active('admin/mailing-lists*') }}">Mailing Lists</a></div>
        </li>
    @endif

    <li class="sidebar-section">
        <div class="sidebar-section-header">Maintenance</div>
        <div class="sidebar-item"><a href="{{ url('admin/pages') }}" class="{{ set_active('admin/pages*') }}">Text
                Pages</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/changelog') }}" class="{{ set_active('admin/changelog*') }}">Changelog</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/site-settings') }}" class="{{ set_active('admin/site-settings*') }}">Site Settings</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/site-images') }}" class="{{ set_active('admin/site-images*') }}">Site Images</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/account-settings') }}" class="{{ set_active('admin/account-settings*') }}">Account Settings</a></div>
    </li>

</ul>
