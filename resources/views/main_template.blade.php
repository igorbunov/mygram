@extends('template')

@section('content')
    <div class="main-container">
        <header id="header" class="header">
            <div class="container">
                <div class="row">
                    <div class="col col-md-1 sidenav-trigger">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="col col-md-9">
                        <span class="h1 programm-text">mygram</span>
                    </div>
                    <div class="col col-md-1 col-md-auto"></div>
                    <div class="col col-md-1">
                        <a href="{{ url('logout') }}" class="btn btn-dark btn-sm active" role="button" aria-pressed="true">Выйти</a>
                    </div>
                </div>

                <div class="row">
                    <div class="d-flex programm-header-info">
                        <div class="p-2">
                            <img class="main-profile-icon rounded-circle" src="{{$accountPicture}}" />
                        </div>
                        <div class="mr-auto p-2">
                            {{ session('user_email') }}<br/>
                            @if($currentTariff != null)
                                Тариф: {{ $currentTariff['name'] }}<br/>
                                Аккаунтов: {{ $currentTariff['accounts_count'] }}<br/>
                                Действует до: {{ $currentTariff['dt_end'] }}<br/>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section id="main-block" class="main-block">
            <div class="container">
                <h1 class="current-page">{{ $title }}</h1>
                <div class="row">
                    <div class="col-lg-3 items-to-hide">
                        <nav>
                            <ul class="menu">
                                    <li class="menu__item">
                                        <a href="{{ url('accounts') }}" @if($activePage == 'accounts') class="active_link" @endif>Аккаунты</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('tasks') }}" @if($activePage == 'tasks') class="active_link" @endif>Задачи</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('safelist') }}" @if($activePage == 'safelist') class="active_link" @endif>Белый список</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('tariffs') }}" @if($activePage == 'tariffs') class="active_link" @endif>Тарифы</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('limits') }}" @if($activePage == 'limits') class="active_link" @endif>Лимиты</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('about') }}" @if($activePage == 'about') class="active_link" @endif>О компании</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('support') }}" @if($activePage == 'support') class="active_link" @endif>Поддержка</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('agreement') }}" @if($activePage == 'agreement') class="active_link" @endif>Соглашение</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('contacts') }}" @if($activePage == 'contacts') class="active_link" @endif>Контакты</a>
                                    </li>
                                    <li class="menu__item">
                                        <a href="{{ url('return') }}" @if($activePage == 'return') class="active_link" @endif>Условия возврата</a>
                                    </li>
                            </ul>
                        </nav>
                    </div>
                    <div class="col-lg-9">
                        @yield('main_content')
                    </div>


                </div>
            </div>
        </section>
    </div>

    <div id="sidenav-overlay" class="sidenav-overlay"></div>

    <ul id="slide-out" class="sidenav">
        <div class="slide-out-close-menu">
            <i class="fas fa-bars slide-close-menu"></i>
        </div>
        <div class="col-lg-3">
            <nav>
                <ul class="menu">
                    <li class="menu__item">
                        <a href="{{ url('accounts') }}" @if($activePage == 'accounts') class="active_link" @endif>Аккаунты</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('tasks') }}" @if($activePage == 'tasks') class="active_link" @endif>Задачи</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('safelist') }}" @if($activePage == 'safelist') class="active_link" @endif>Белый список</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('tariffs') }}" @if($activePage == 'tariffs') class="active_link" @endif>Тарифы</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('limits') }}" @if($activePage == 'limits') class="active_link" @endif>Лимиты</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('about') }}" @if($activePage == 'about') class="active_link" @endif>О компании</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('support') }}" @if($activePage == 'support') class="active_link" @endif>Поддержка</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('agreement') }}" @if($activePage == 'agreement') class="active_link" @endif>Соглашение</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('contacts') }}" @if($activePage == 'contacts') class="active_link" @endif>Контакты</a>
                    </li>
                    <li class="menu__item">
                        <a href="{{ url('return') }}" @if($activePage == 'return') class="active_link" @endif>Условия возврата</a>
                    </li>
                </ul>
            </nav>
        </div>
    </ul>

    {{--<a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>--}}

    <!-- Button trigger modal -->
    {{--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">--}}
        {{--Launch demo modal--}}
    {{--</button>--}}

    <!-- Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

@stop