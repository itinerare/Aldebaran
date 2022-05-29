@extends('layouts.app')

@section('title')
    Admin:
    @yield('admin-title')
@endsection

@section('head-scripts')
    <script src="{{ asset('js/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/jquery.tinymce.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui-timepicker-addon.js') }}"></script>
    @yield('admin-head-scripts')
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
                    @if(config('aldebaran.settings.commissions.enabled'))
                        @if(isset($commissionClasses) && $commissionClasses->count())
                            Queues:
                            @foreach($commissionClasses as $class)
                                <a href="{{ url('admin/commissions/'.$class->slug) }}">{{ $class->name }} Queue</a> ・
                            @endforeach
                        @endif
                        <a href="{{ url('admin/ledger') }}">Ledger</a><br/>
                    @endif
                    Gallery Data:
                    <a href="{{ url('admin/data/projects') }}">Projects</a> ・
                    <a href="{{ url('admin/data/pieces') }}">Pieces</a> ・
                    <a href="{{ url('admin/data/tags') }}">Tags</a> ・
                    <a href="{{ url('admin/data/programs') }}">Media/Programs</a><br/>
                    @if(config('aldebaran.settings.commissions.enabled'))
                        Commission Data:
                        <a href="{{ url('admin/data/commission-categories') }}">Commission Categories</a> ・
                        <a href="{{ url('admin/data/commission-types') }}">Commission Types</a><br/>
                    @endif
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
<script>
    $(function() {
        tinymce.init({
            selector: '.wysiwyg',
            height: 500,
            menubar: false,
            convert_urls: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | code',
            content_css: [
                '{{ asset('css/app.css') }}',
                '{{ asset('css/aldebaran.css') }}'
            ],
            target_list: false
        });
    });
</script>
@endsection
