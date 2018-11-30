@extends('template')

@section('content')
    <div class="main-container">
        <header id="header" class="header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-1"></div>
                    <div class="col-lg-3">
                        <h1 class="h1 programm-text">mygram</h1>
                    </div>

                    <div class="d-flex programm-header-info">
                        <div class="mr-auto p-2">
                            <img  class="rounded-circle" src="{{$accountPicture}}" />
                            {{--<img  class="rounded-circle" src="https://instagram.fiev7-2.fna.fbcdn.net/vp/71cca77edaee7865d7d20b6e22f72b33/5CA66227/t51.2885-19/s150x150/43028258_2000942369928882_4304595538935808000_n.jpg" />--}}
                            {{--<i class="fas fa-grin-tears profile-logo"></i>--}}
                        </div>
                        <div class="p-2">

                            {{ session('user_email') }}<br/>
                            @if($currentTariff != null)
                                Тариф: {{ $currentTariff['name'] }}<br/>
                                Аккаунтов: {{ $currentTariff['accounts_count'] }}<br/>
                                Действует до: {{ $currentTariff['dt_end'] }}<br/>
                            @endif
                            <div class="d-flex justify-content-end">
                                <a href="{{ url('logout') }}" class="btn btn-dark btn-sm active" role="button" aria-pressed="true">Выйти</a><br/>
                            </div>
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

                    <div class="col-lg-3 items-to-show">
                        <nav>
                            <ul class="menu">
                                <li class="menu__item">
                                    <a href="{{ url('accounts') }}" @if($activePage == 'accounts') class="active_link" @endif>Аккаунты</a>
                                </li>
                                <li class="menu__item">
                                    <a href="{{ url('tasks') }}" @if($activePage == 'tasks') class="active_link" @endif>Задачи</a>
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
                </div>
            </div>
        </section>


    </div>
@stop