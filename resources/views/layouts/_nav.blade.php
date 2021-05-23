<nav class="navbar navbar-expand-md navbar-dark" id="headerNav">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ Settings::get('site_name') }}
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('about') }}">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('gallery') }}">Gallery</a>
                </li>

                <li class="nav-item dropdown">
                    <a id="projectDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Projects
                    </a>

                    <div class="dropdown-menu" aria-labelledby="projectDropdown">
                        @if(isset($visibleProjects) && $visibleProjects->count())
                            @foreach($visibleProjects as $project)
                                <a class="dropdown-item" href="{{ $project->url }}">
                                    {{ $project->name }}
                                </a>
                            @endforeach
                        @endif
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a id="commDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Commissions
                    </a>

                    <div class="dropdown-menu" aria-labelledby="commDropdown">
                        @foreach(Config::get('itinerare.comm_types') as $type=>$values)
                            <span class="dropdown-item">
                                {{ ucfirst($type) }} Commissions
                            </span>
                            <a class="dropdown-item" href="{{ url('commissions/'.$type) }}">
                                Info
                            </a>
                            <a class="dropdown-item" href="{{ url('commissions/'.$type.'/tos') }}">
                                Terms of Service
                            </a>
                            <a class="dropdown-item" href="{{ url('commissions/'.$type.'/queue') }}">
                                Queue Status
                            </a>
                            {!! !$loop->last ? '<div class="dropdown-divider"></div>' : '' !!}
                        @endforeach
                    </div>
                </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-crown"></i></a>
                    </li>
                    @if (Settings::get('is_registration_open') == 1)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('admin') }}"><i class="fas fa-crown"></i></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
