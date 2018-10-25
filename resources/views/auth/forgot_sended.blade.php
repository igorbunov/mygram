@extends('template')

@section('content')
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <div class="div-center">
        <div class="content">
            <h3>Восстановление пароля</h3>
            <hr />

            <p>На ваш email: <b>{{ $email }}</b> выслана ссылка для смены пароля.</p>
        </div>
    </div>
@stop