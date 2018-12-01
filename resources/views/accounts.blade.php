@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <div class="row">
            <div class="col-lg-12">
                <h3>Ваши аккаунты</h3>
            </div>
        </div>

            @foreach ($accounts as $account)
                <section class="account-link-clickable account @if($account->is_active) active @else deactivated @endif"
                         data-account-id="{{ $account->id }}">
                    {{--<div class="row d-flex justify-content-end account-title-group">--}}
                    <div class="row d-flex">
                        <div class="p-2 account-link @if($account->is_active) active @else deactivated @endif">
                            <span>@</span>{{ $account->nickname }}
                        </div>

                        <div class="p-2">
                            <img src="{{ $account->picture }}" class="rounded-circle mini-profile-picture"/>
                        </div>

                        @if($account->is_active == 1)
                            <div class="ml-auto p-2">
                                <div class="btn-dark refresh-account-btn" data-account-id="{{ $account->id }}">
                                    <i class="fas fa-sync"></i>
                                </div>
                            </div>
                        @endif

                        @if($account->is_active == 1)
                            <div class="p-2">
                                <button type="button" class="btn btn-basic account-deactivate"
                                        data-account-id="{{ $account->id }}">Деактивировать</button>
                            </div>
                        @else
                            <div class="ml-auto p-2">
                            <button type="button" class="btn btn-basic account-activate"
                                    data-account-id="{{ $account->id }}">Активировать</button>
                            </div>
                        @endif

                    </div>
                    <div class="row">
                        <div class="col-lg-12 d-flex justify-content-around">
                            <div>публикации: {{ $account->publications }}</div>
                            <div>подпищики: {{ $account->subscribers }}</div>
                            <div>подписки: {{ $account->subscriptions }}</div>
                        </div>
                    </div>
                </section>
            @endforeach

        @if($currentTariff != null)
            <div class="row">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-dark" id="add-account-btn">Добавить аккаунт</button>
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