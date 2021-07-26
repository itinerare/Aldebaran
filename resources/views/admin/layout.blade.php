@extends('layouts.app')

@section('title')
    Admin:
    @yield('admin-title')
@endsection

@section('sidebar')
    @include('admin._sidebar')
@endsection

@section('content')
    <div class="mobile-show mb-3">
        <div class="card">
            <h5 class="card-header inventory-header">
                Mobile Navigation
                <a class="small inventory-collapse-toggle collapse-toggle collapsed" href="#mobileNav" data-toggle="collapse">Show</a></h3>
            </h5>
            <div class="card-body inventory-body collapse" id="mobileNav">
                <h5>
                    <a href="{{ url('admin') }}">Admin Home</a><br/>
                    @if(isset($commissionClasses) && $commissionClasses->count())
                        Queues:
                        @foreach($commissionClasses as $class)
                            <a href="{{ url('admin/commissions/'.$class->slug) }}">{{ $class->name }} Queue</a> ・
                        @endforeach
                    @endif
                    <a href="{{ url('admin/ledger') }}">Ledger</a><br/>
                    Gallery Data:
                    <a href="{{ url('admin/data/projects') }}">Projects</a> ・
                    <a href="{{ url('admin/data/pieces') }}">Pieces</a> ・
                    <a href="{{ url('admin/data/tags') }}">Tags</a> ・
                    <a href="{{ url('admin/data/programs') }}">Programs</a><br/>
                    Commission Data:
                    <a href="{{ url('admin/data/commission-categories') }}">Commission Categories</a> ・
                    <a href="{{ url('admin/data/commission-types') }}">Commission Types</a><br/>
                    Maintenance:
                    <a href="{{ url('admin/pages') }}">Text Pages</a> ・
                    <a href="{{ url('admin/changelog') }}">Changelog</a> ・
                    <a href="{{ url('admin/site-settings') }}">Site Settings</a> ・
                    <a href="{{ url('admin/site-images') }}">Site Images</a> ・
                    <a href="{{ url('admin/account-settings') }}">Account Settings</a><br/>
                </h5>
            </div>
        </div>
    </div>
    @yield('admin-content')
@endsection

@section('scripts')
@parent
@endsection
