@extends('template')

@section('content')
    <div class="container" style="background-color: white; height: 100%; max-width: 900px;">
        <a href="{{ url('login') }}">Вход</a>
        <a href="{{ url('register') }}">Регистрация</a>
    </div>
@stop

@extends('template')