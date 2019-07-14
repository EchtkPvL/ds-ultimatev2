<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <a class="navbar-brand" href="{{ route('index') }}">
        <img src="{{ asset('images/logo.png') }}" height="30" class="d-inline-block align-top" alt="">
        DS-Ultimate
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            @if (isset($server))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('server', [$server]) }}">{{ucfirst(__('ui.titel.worldOverview'))}} <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ ucfirst(__('ui.server.worlds')) }}
                </a>
                <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
                    @foreach(\App\World::worldsCollection($server) as $worlds)
                        <li class="dropdown-submenu">
                            <a  class="dropdown-item" tabindex="-1" href="#">{!! (($worlds->get(0)->sortType() == "world")? ucfirst(__('Normale Welten')): ucfirst(__('Spezial Welten'))) !!}</a>
                            <ul class="dropdown-menu">
                                @foreach($worlds as $world)
                                    <li class="dropdown-item">
                                        {!! \App\Util\BasicFunctions::linkWorld($world, $world->displayName()) !!}
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>
            @endif
            @if (isset($worldData))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{__('ui.server.ranking')}}
                    </a>
                    <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
                        <li class="dropdown-submenu">
                            <a  class="dropdown-item" tabindex="-1" href="#">{{ __('ui.table.player') }}</a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item" name="submit" type="submit"  value="player">{{ ucfirst(__('ui.table.bashGes')) }}</button>
                                <button class="dropdown-item" name="submit" type="submit" value="ally">{{ ucfirst(__('ui.table.bashOff')) }}</button>
                                <button class="dropdown-item" name="submit" type="submit" value="village">{{ ucfirst(__('ui.table.bashDeff')) }}</button>
                            </div>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
        @if (isset($server))
            <form class="form-inline my-2 my-lg-0" action="{{ route('searchForm', [$server]) }}" method="POST" role="search">
                @csrf
                <input class="form-control mr-sm-2" name="search" type="search" placeholder="{{ __('ui.titel.search') }}" aria-label="Search">
                <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle form-control mr-sm-2" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ __('ui.titel.search') }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg-right" aria-labelledby="dropdownMenuButton" style="width: 100px">
                        <button class="dropdown-item" name="submit" type="submit"  value="player">{{ ucfirst(__('ui.table.player')) }}</button>
                        <button class="dropdown-item" name="submit" type="submit" value="ally">{{ ucfirst(__('ui.table.ally')) }}</button>
                        <button class="dropdown-item" name="submit" type="submit" value="village">{{ ucfirst(__('ui.table.village')) }}</button>
                    </div>
                </div>
            </form>
        @endif
        <div class="dropdown">
            <button class="btn btn-outline-dark dropdown-toggle form-control mr-sm-2" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ __('ui.language') }}
            </button>
            <div class="dropdown-menu dropdown-menu-lg-right" aria-labelledby="dropdownMenuButton" style="width: 100px">
                <a class="dropdown-item" href="{{ route('locale', 'de') }}"><span class="flag-icon flag-icon-de"></span> Deutsch</a>
                <a class="dropdown-item" href="{{ route('locale', 'en') }}"><span class="flag-icon flag-icon-gb"></span> English</a>
            </div>
        </div>
        @auth
            <div class="dropdown">
                <button class="btn btn-outline-dark dropdown-toggle form-control ml-2" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ Auth::user()->name }} <span class="caret"></span>
                </button>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    @can('dashboard_access')
                        <a class="dropdown-item" href="{{ route('admin.home') }}">
                            {{ __('Dashboard') }}
                        </a>
                    @endcan
                    @can('translation_access')
                        <a class="dropdown-item" href="{{ route('index') }}/translations">
                            {{ __('Übersetzungen') }}
                        </a>
                    @endcan

                    <a class="dropdown-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
