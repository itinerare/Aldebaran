<ul>
    <li class="sidebar-header"><a href="{{ url('admin') }}" class="card-link">Admin Home</a></li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Queues</div>
        @foreach(Config::get('itinerare.comm_types') as $type=>$values)
            <div class="sidebar-item"><a href="{{ url('admin/commissions/'.$type) }}" class="{{ set_active('admin/commissions/'.$type.'*') }}">{{ ucfirst($type) }} Queue</a></div>
        @endforeach
        <div class="sidebar-item"><a href="{{ url('admin/ledger') }}" class="{{ set_active('admin/ledger*') }}">Ledger</a></div>
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Gallery Data</div>
        <div class="sidebar-item"><a href="{{ url('admin/data/projects') }}" class="{{ set_active('admin/data/projects*') }}">Projects</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/pieces') }}" class="{{ set_active('admin/data/pieces*') }}">Pieces</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/tags') }}" class="{{ set_active('admin/data/tags*') }}">Tags</a></div>
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Commission Data</div>
        <div class="sidebar-item"><a href="{{ url('admin/data/commission-categories') }}" class="{{ set_active('admin/data/commission-categories*') }}">Commission Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/data/commission-types') }}" class="{{ set_active('admin/data/commission-types*') }}">Commission Types</a></div>
    </li>

    <li class="sidebar-section">
        <div class="sidebar-section-header">Maintenance</div>
        <div class="sidebar-item"><a href="{{ url('admin/pages') }}" class="{{ set_active('admin/pages*') }}">Text Pages</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/changelog') }}" class="{{ set_active('admin/changelog*') }}">Changelog</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/site-settings') }}" class="{{ set_active('admin/site-settings*') }}">Site Settings</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/site-images') }}" class="{{ set_active('admin/site-images*') }}">Site Images</a></div>
        <div class="sidebar-item"><a href="{{ url('admin/account-settings') }}" class="{{ set_active('admin/account-settings*') }}">Account Settings</a></div>
    </li>

</ul>
