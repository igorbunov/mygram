@extends('template')

@section('content')
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <div class="div-center">
        <div class="content">
            <h3>Смена пароля</h3>
            <hr />
            <form method="POST" action="{{ url('forgot_finish') }}">
                {{ csrf_field() }}

                @isset($code)
                    <input type="hidden" name="code" value="{{ $code }}" />
                @endisset

                <div class="form-group">
                    <label for="pass1">Пароль</label>
                    <input type="password" class="form-control" id="pass1" placeholder="password" name="pass1">
                    <small id="emailHelp" class="form-text text-muted">Придумайте новый пароль для входа на сайт.</small>
                </div>

                <div class="form-group">
                    <label for="pass2">Пароль (подтверждение)</label>
                    <input type="password" class="form-control" id="pass2" placeholder="password confirm" name="pass2">
                </div>

                <br/>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@stop