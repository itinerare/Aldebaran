<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
        header("Permissions-Policy: interest-cohort=()");
    ?>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Settings::get('site_name') }} @if(View::hasSection('title'))・@yield('title') @endif</title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ Settings::get('site_name') }} @if(View::hasSection('title')) ・@yield('title') @endif">
    <meta name="description" content="@if(View::hasSection('meta-desc')) @yield('meta-desc') @else {{ Settings::get('site_desc') }} @endif">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url', 'http://localhost') }}">
    <meta property="og:image" content="@if(View::hasSection('meta-img')) @yield('meta-img') @else @if(screenshot(url()->current())){{ screenshot(url()->current()) }}@else{{ asset('images/assets/avatar.png') }}@endif @endif">
    <meta property="og:site_name" content="{{ Settings::get('site_name') }}" />
    <meta property="og:title" content="
        @if(View::hasSection('title'))
            @yield('title')
        @else
            {{ Settings::get('site_name') }}
        @endif
    ">
    <meta property="og:description" content="@if(View::hasSection('meta-desc')) @yield('meta-desc') @else {{ Settings::get('site_desc') }} @endif">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ config('app.url', 'http://localhost') }}">
    <meta property="twitter:image" content="@if(View::hasSection('meta-img')) @yield('meta-img') @else @if(screenshot(url()->current())){{ screenshot(url()->current()) }}@else{{ asset('images/assets/avatar.png') }}@endif @endif">
    <meta name="twitter:site" content="@{{ Settings::get('site_name') }}" />
    <meta property="twitter:title" content="
        @if(View::hasSection('title'))
            @yield('title')
        @else
            {{ Settings::get('site_name') }}
        @endif
    ">
    <meta property="twitter:description" content="@if(View::hasSection('meta-desc')) @yield('meta-desc') @else {{ Settings::get('site_desc') }} @endif">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap4-toggle.min.js') }}"></script>
    <script src="{{ asset('js/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/jquery.tinymce.min.js') }}"></script>
    <script src="{{ asset('js/lightbox.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('js/selectize.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui-timepicker-addon.js') }}"></script>
    <script src="{{ asset('js/croppie.min.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/itinerare.css') }}" rel="stylesheet">

    @if(file_exists(public_path(). '/css/custom.css'))
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @endif

    {{-- Font Awesome --}}
    <link href="{{ asset('css/all.min.css') }}" rel="stylesheet">

    {{-- jQuery UI --}}
    <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet">

    {{-- Bootstrap Toggle --}}
    <link href="{{ asset('css/bootstrap4-toggle.min.css') }}" rel="stylesheet">

    <link href="{{ asset('css/lightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-colorpicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui-timepicker-addon.css') }}" rel="stylesheet">
    <link href="{{ asset('css/croppie.css') }}" rel="stylesheet">
    <link href="{{ asset('css/selectize.bootstrap4.css') }}" rel="stylesheet">

    {!! RecaptchaV3::initJs() !!}
</head>
<body>
    <div id="app">
        @include('layouts._nav')

        <main class="container-fluid">
            <div class="row">

                <div class="sidebar col-lg-2" id="sidebar">
                    @if(View::hasSection('sidebar'))
                        @yield('sidebar')
                    @endif
                </div>
                <div class="main-content col-lg-8 p-4">
                    <div>
                        @include('flash::message')
                        @yield('content')
                    </div>

                    <div class="site-footer mt-4" id="footer">
                        @include('layouts._footer')
                    </div>
                </div>
            </div>

        </main>


        <div class="modal fade" id="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <span class="modal-title h5 mb-0"></span>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>

        @yield('scripts')
        <script>
            $(function() {
                $('[data-toggle="tooltip"]').tooltip({html: true});
                $('.cp').colorpicker();
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
                        '{{ asset('css/itinerare.css') }}'
                    ],
                    spoiler_caption: 'Toggle Spoiler',
                    target_list: false
                });
                var $mobileMenuButton = $('#mobileMenuButton');
                var $sidebar = $('#sidebar');
                $('#mobileMenuButton').on('click', function(e) {
                    e.preventDefault();
                    $sidebar.toggleClass('active');
                });
                    });
                });
        </script>
    </div>
</body>
</html>
