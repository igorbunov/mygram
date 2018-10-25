@extends('template')

@section('content')
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <div class="div-center">
        <div class="content">
            <h3>Восстановление пароля</h3>
            <hr />
            <form method="POST" action="{{ url('forgot') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="email">Електронная почта</label>
                    <input type="email" class="form-control" id="email" placeholder="email" name="email">
                    <small id="emailHelp" class="form-text text-muted">Вам на почту придет ссылка на восстановление пароля.</small>
                </div>

                <div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_SITE_KEY') }}"></div>

                <br/>
                <button type="submit" class="btn btn-primary">Восстановить</button>
                <hr />

                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('login') }}">Войти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('register') }}">Регистрация</a>
                    </li>
                </ul>

            </form>
        </div>
    </div>
@stop