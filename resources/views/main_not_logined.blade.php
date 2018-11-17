@extends('template')

@section('content')
    <div class="container" style="border: 1px solid black; background-color: white; height: 100%; max-width: 900px;">
        <a style="font-size: 22px;" href="{{ url('login') }}">Вход</a>
        <a href="{{ url('register') }}">Регистрация</a>
    </div>
@stop