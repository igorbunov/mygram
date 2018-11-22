@extends('main_template')

@section('main_content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Ваши аккаунты</h3>
            </div>
        </div>

            @foreach ($accounts as $account)
                <section id="account" class="account @if($account->is_active) active @else deactivated @endif">
                    <div class="row">
                        <div class="col-lg-8 account-link" data-account-id="{{ $account->id }}">
                            <h4>{{ $account->nickname }}</h4>
                        </div>
{{--                        @if($currentTariff != null)--}}
                            {{--<div class="col-lg-1">--}}
                                {{--<div class="sync-account" data-nickname="{{ $account->nickname }}"><i class="fa fa-sync"></i></div>--}}
                            {{--</div>--}}
                            {{--<div class="col-lg-1">--}}
                                {{--<div class="delete-account" data-nickname="{{ $account->nickname }}"><i class="fa fa-trash"></i></div>--}}
                            {{--</div>--}}
                        {{--@endif--}}
                        <div class="col-lg-4 ml-auto">
                            @if($account->is_active == 1)
                                <button class="account-deactivate"
                                        data-account-id="{{ $account->id }}">Деактивировать</button>
                            @else
                                <button class="account-activate"
                                        data-account-id="{{ $account->id }}">Активировать</button>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div>{{ $account->publications }}</div>
                        </div>
                        <div class="col-lg-3">
                            <div>{{ $account->subscribers }}</div>
                        </div>
                        <div class="col-lg-3">
                            <div>{{ $account->subscriptions }}</div>
                        </div>
                    </div>
                </section>
            @endforeach

        @if($currentTariff != null)
            <div class="row">
                <div class="col-lg-12">
                    <button id="add-account-btn">Добавить аккаунт</button>
                </div>
            </div>

            <div id="add-account-form">
                <form method="POST" action="accounts">
                    {{ csrf_field() }}


                    <div class="row">
                        <div class="col-lg-12">
                            <label for="account-name">Аккаунт</label>
                            <input type="text" id="account-name" name="account_name"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="account-password">Пароль</label>
                            <input type="password" id="account-password" name="account_password"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <input type="submit" value="Сохранить">
                        </div>
                    </div>

                </form>
            </div>
        @endif
    </div>
@stop