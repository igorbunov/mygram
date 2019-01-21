@extends('template')

@section('content')
    <div class="main-container">
        <header id="header" class="header">
            <div class="container">
                <div class="row header-top-row">
                    <div class="sidenav-trigger" style="margin: 6px 0 0 12px;">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="header-top-row-title">
                        <span class="h1 programm-text">mygram</span>
                    </div>
                    <div style="margin: 6px 12px 0 0;">
                        <a id="logout-btn" href="{{ url('logout') }}" class="btn btn-dark btn-sm active" role="button" aria-pressed="true">Выйти</a>
                    </div>
                </div>

                <div class="row">
                    <div class="d-flex programm-header-info">
                        <div class="p-2">
                            @if(isset($accountPicture))
                                <img class="main-profile-icon rounded-circle" src="{{$accountPicture}}" />
                            @endif
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
                                @if($currentTariff != null and $currentTariff['tariff_list_id'] == 2)
                                    <li class="menu__item">
                                        <a href="{{ url('chatbot') }}" @if($activePage == 'chatbot') class="active_link" @endif>Чат бот</a>
                                    </li>
                                @endif
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('tariffs') }}" @if($activePage == 'tariffs') class="active_link" @endif>Тарифы</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('limits') }}" @if($activePage == 'limits') class="active_link" @endif>Лимиты</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('about') }}" @if($activePage == 'about') class="active_link" @endif>О компании</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('support') }}" @if($activePage == 'support') class="active_link" @endif>Поддержка</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('agreement') }}" @if($activePage == 'agreement') class="active_link" @endif>Соглашение</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('contacts') }}" @if($activePage == 'contacts') class="active_link" @endif>Контакты</a>--}}
                                    {{--</li>--}}
                                    {{--<li class="menu__item">--}}
                                        {{--<a href="{{ url('return') }}" @if($activePage == 'return') class="active_link" @endif>Условия возврата</a>--}}
                                    {{--</li>--}}
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
                    @if($currentTariff != null and $currentTariff['tariff_list_id'] == 2)
                        <li class="menu__item">
                            <a href="{{ url('chatbot') }}" @if($activePage == 'chatbot') class="active_link" @endif>Чат бот</a>
                        </li>
                    @endif
                    {{--<li style="margin-top: 100px;">--}}
                        {{--<a href="{{ url('logout') }}" class="btn btn-dark btn-sm active" role="button" aria-pressed="true">Выйти</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('tariffs') }}" @if($activePage == 'tariffs') class="active_link" @endif>Тарифы</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('limits') }}" @if($activePage == 'limits') class="active_link" @endif>Лимиты</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('about') }}" @if($activePage == 'about') class="active_link" @endif>О компании</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('support') }}" @if($activePage == 'support') class="active_link" @endif>Поддержка</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('agreement') }}" @if($activePage == 'agreement') class="active_link" @endif>Соглашение</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('contacts') }}" @if($activePage == 'contacts') class="active_link" @endif>Контакты</a>--}}
                    {{--</li>--}}
                    {{--<li class="menu__item">--}}
                        {{--<a href="{{ url('return') }}" @if($activePage == 'return') class="active_link" @endif>Условия возврата</a>--}}
                    {{--</li>--}}
                </ul>
            </nav>
            <a href="{{ url('logout') }}" class="btn btn-dark btn-sm active" style="margin-top:200px;" role="button" aria-pressed="true">Выйти</a>
        </div>
    </ul>

@stop