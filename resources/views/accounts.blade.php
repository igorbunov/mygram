@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
            @foreach ($accounts as $account)
                <section class="account-link-clickable account @if($account->is_active) active @else deactivated @endif"
                         data-account-id="{{ $account->id }}">
                    <div class="row d-flex">
                        <div class="p-2 account-link @if($account->is_active) active @else deactivated @endif">
                            <span>@</span>{{ $account->nickname }}
                        </div>

                        <div class="p-2">
                            <img src="{{ $account->picture }}" class="rounded-circle mini-profile-picture"/>
                        </div>

                        @if($account->is_active == 1)
                            <div class="ml-auto p-2">
                                <div class="btn-dark my-btn refresh-account-btn" data-account-id="{{ $account->id }}">
                                    <i class="fas fa-sync"></i>
                                </div>
                            </div>
                            <div class="p-2">
                                <div class="btn-dark my-btn btn-info account-deactivate"
                                     data-account-id="{{ $account->id }}">
                                    <i class="fas fa-trash"></i>
                                </div>
                                {{--<button type="button" class="btn btn-info account-deactivate"--}}
                                        {{--data-account-id="{{ $account->id }}">Деактивировать</button>--}}
                            </div>
                        @elseif($account->is_confirmed == 1)
                            <div class="ml-auto p-2">
                                <button type="button" class="btn btn-basic account-activate"
                                        data-account-id="{{ $account->id }}">Активировать</button>
                            </div>
                        @elseif($account->is_confirmed == 0)
                            <div style="color: darkblue;">
                                <h3>Логин/пароль не верный</h3>
                            </div>
                        @endif

                    </div>

                    @if($account->is_confirmed == 1)
                        <div class="row">
                            <div class="col-lg-12 d-flex justify-content-around" style="color: black;">
                                <div>постов: {{ $account->publications }}</div>
                                <div>подпищиков: {{ $account->subscribers }}</div>
                                <div>подписок: {{ $account->subscriptions }}</div>
                            </div>
                        </div>
                    @endif
                </section>
            @endforeach

        @if($currentTariff != null)
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-around">
                    <div class="p-2">
                        <button type="button" class="btn btn-dark" id="add-account-btn">Добавить</button>
                    </div>
                    <div class="p-2 ml-auto">
                        @if($onlyActiveAccounts == true)
                            <button type="button" class="btn btn-dark" data-all="true" id="all-accounts-btn">Все аккаунты</button>
                        @else
                            <button type="button" class="btn btn-dark" data-all="false"  id="all-accounts-btn">Активные аккаунты</button>
                        @endif
                    </div>
                </div>
            </div>

            <div id="add-account-form">
                <form>
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="account-name" class="my-label">Аккаунт</label>
                            <input type="text" class="form-control my-text-input" id="account-name" name="account_name"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="account-password" class="my-label">Пароль</label>
                            <input type="password" class="form-control my-text-input" id="account-password" name="account_password"/>

                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-success" id="add-account-submit">Сохранить</button>
                        </div>
                    </div>

                </form>
            </div>
        @endif
    </div>
@stop