@extends('template')

@section('content')
    <script src='https://use.fontawesome.com/b9bdbd120a.js'></script>

    <div class="div-center">
        <div class="content">
            <h3>Вход</h3>
            <hr />
            <form method="POST" action="{{ url('login') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="email">Електронная почта</label>
                    <input type="email" class="form-control" id="email" placeholder="email" name="email">
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" class="form-control" id="password" placeholder="password" name="password">
                </div>

                <button type="submit" class="btn btn-primary">Войти</button>
                <hr />

                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('register') }}">Регистрация</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('forgot') }}">Забыл пароль</a>
                    </li>
                </ul>
            </form>
        </div>
    </div>
@stop