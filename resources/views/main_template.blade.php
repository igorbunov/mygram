@extends('template')

@section('content')
    <div class="main-container">
        <header id="header" class="header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-3">
                        <h1 class="h1">mygram</h1>
                    </div>
                    <div class="col-lg-2 ml-auto">
                        <i class="fas fa-grin-tears profile-logo"></i>
                    </div>

                    <div class="col-lg-3">
                        {{ session('user_email') }}<br/>
                        <a href="{{ url('logout') }}">Выйти</a><br/>

                        @if($currentTariff != null)
                            Тариф: {{ $currentTariff['name'] }}<br/>
                            Аккаунтов: {{ $currentTariff['accounts_count'] }}<br/>
                            Действует до: {{ $currentTariff['dt_end'] }}<br/>
                        @endif
                    </div>
                    <div class="col-lg-1"></div>
                </div>
            </div>
        </header>

        <section id="main-block" class="main-block">
            <div class="container">
                <h1 class="current-page">{{ $title }}</h1>
                <div class="row">
                    <div class="col-lg-3">
                        <nav>
                            <ul class="menu">
                                <li class="menu__item">
                                    <a href="{{ url('accounts') }}" @if($activePage == 'accounts') class="active_link" @endif>Аккаунты</a>
                                </li>
                                <li class="menu__item">
                                    <a href="{{ url('tasks') }}" @if($activePage == 'tasks') class="active_link" @endif>Задачи</a>
                                </li>
                            </ul>
                        </nav>
                        <nav>
                            <ul class="menu-bottom">
                                <li class="menu-bottom__item">
                                    <a href="{{ url('tariffs') }}" @if($activePage == 'tariffs') class="active_link" @endif>Тарифы</a>
                                </li>
                                <li class="menu-bottom__item">
                                    <a href="{{ url('limits') }}" @if($activePage == 'limits') class="active_link" @endif>Лимиты</a>
                                </li>
                                <li class="menu-bottom__item">
                                    <a href="{{ url('about') }}" @if($activePage == 'about') class="active_link" @endif>О компании</a>
                                </li>
                                <li class="menu-bottom__item">
                                    <a href="{{ url('support') }}" @if($activePage == 'support') class="active_link" @endif>Поддержка</a>
                                </li>
                                <li class="menu-bottom__item">
                                    <a href="{{ url('agreement') }}" @if($activePage == 'agreement') class="active_link" @endif>Соглашение</a>
                                </li>
                                <li class="menu-bottom__item">
                                    <a href="{{ url('contacts') }}" @if($activePage == 'contacts') class="active_link" @endif>Контакты</a>
                                </li>
                                <li class="menu-bottom__item">
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
@stop