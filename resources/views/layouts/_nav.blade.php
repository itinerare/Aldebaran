<nav class="navbar navbar-expand-md navbar-dark bg-dark" id="headerNav">
    <div class="container-fluid">
        <span class="skip"><a href="#content">To Main Content</a></span>
        <a class="navbar-brand" href="{{ url('/') }}" aria-label="To Home">
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

                @if(config('aldebaran.settings.navigation.gallery'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('gallery') }}">Gallery</a>
                    </li>
                @endif

                @if(isset($visibleProjects) && $visibleProjects->count())
                    @if(config('aldebaran.settings.navigation.projects_nav'))
                        @foreach($visibleProjects as $project)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $project->url }}">{{ $project->name }}</a>
                            </li>
                        @endforeach
                    @else
                        <li class="nav-item dropdown">
                            <a id="projectDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                Projects
                            </a>

                            <div class="dropdown-menu" aria-labelledby="projectDropdown">
                                @foreach($visibleProjects as $project)
                                    <a class="dropdown-item" href="{{ $project->url }}">
                                        {{ $project->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                    @endif
                @endif

                @if(config('aldebaran.settings.commissions.enabled') && isset($commissionClasses) && $commissionClasses->count())
                <li class="nav-item dropdown">
                    <a id="commDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Commissions
                    </a>

                    <div class="dropdown-menu" aria-labelledby="commDropdown">
                        @foreach($commissionClasses as $class)
                            @if($loop->count > 1)
                                <span class="dropdown-item">
                                    {{ ucfirst($class->name) }} Commissions
                                </span>
                            @endif
                            <a class="dropdown-item" href="{{ url('commissions/'.$class->slug) }}">
                                Info
                            </a>
                            <a class="dropdown-item" href="{{ url('commissions/'.$class->slug.'/tos') }}">
                                Terms of Service
                            </a>
                            <a class="dropdown-item" href="{{ url('commissions/'.$class->slug.'/queue') }}">
                                Queue Status
                            </a>
                            {!! !$loop->last ? '<div class="dropdown-divider"></div>' : '' !!}
                        @endforeach
                    </div>
                </li>
                @endif
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}" aria-label="Login"><i class="fas fa-crown"></i></a>
                    </li>
                    @if (Settings::get('is_registration_open') == 1)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}" aria-label="Register">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('admin') }}"  aria-label="Admin Panel"><i class="fas fa-crown"></i></a>
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
