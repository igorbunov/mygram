@extends('template')

@section('content')
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <div class="div-center">
        <div class="content">
            <h3>Регистрация</h3>
            <hr />

            <p>На ваш email: <b>{{ $email }}</b> выслана ссылка подтверждения.</p>
            <p>Пройдите по ней для завершения регистрации.</p>
        </div>
    </div>
@stop