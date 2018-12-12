@extends('template')

@section('content')
    <div class="container" style="background-color: white; height: 100%; max-width: 900px;">
        <a href="{{ url('login') }}">Вход</a>
        <a href="{{ url('register') }}">Регистрация</a>
    </div>
@stop

@extends('template')

{{--@section('content')--}}
    {{--<div class="main-container">--}}
        {{--<header id="header" class="header">--}}
            {{--<div class="container">--}}
                {{--<div class="row">--}}
                    {{--<div class="col col-md-1 sidenav-trigger">--}}
                        {{--<i class="fas fa-bars"></i>--}}
                    {{--</div>--}}
                    {{--<div class="col col-md-9">--}}
                        {{--<span class="h1 programm-text">mygram</span>--}}
                    {{--</div>--}}
                    {{--<div class="col col-md-1 col-md-auto"></div>--}}
                    {{--<div class="col col-md-1">--}}
                        {{--<a href="{{ url('login') }}" class="btn btn-dark btn-sm active" role="button" aria-pressed="true">Войти</a>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</header>--}}

        {{--<section id="main-block" class="main-block">--}}
            {{--<div class="container">--}}
                {{--<h1 class="current-page">{{ $title }}</h1>--}}
                {{--<div class="row">--}}
                    {{--<div class="col-lg-3 items-to-hide">--}}
                        {{--<nav>--}}
                            {{--<ul class="menu">--}}
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
                            {{--</ul>--}}
                        {{--</nav>--}}
                    {{--</div>--}}
                    {{--<div class="col-lg-9">--}}
                        {{--@yield('main_content')--}}
                    {{--</div>--}}


                {{--</div>--}}
            {{--</div>--}}
        {{--</section>--}}
    {{--</div>--}}

    {{--<div id="sidenav-overlay" class="sidenav-overlay"></div>--}}

    {{--<ul id="slide-out" class="sidenav">--}}
        {{--<div class="slide-out-close-menu">--}}
            {{--<i class="fas fa-bars slide-close-menu"></i>--}}
        {{--</div>--}}
        {{--<div class="col-lg-3">--}}
            {{--<nav>--}}
                {{--<ul class="menu">--}}
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
                {{--</ul>--}}
            {{--</nav>--}}
        {{--</div>--}}
    {{--</ul>--}}
{{--@stop--}}