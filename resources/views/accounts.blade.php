@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
            @foreach ($accounts as $account)
                <section class="account-link-clickable account @if($account->is_active) active @else deactivated @endif"
                         data-account-id="{{ $account->id }}">

                    <div class="row" style="display: flex; justify-content: space-between;">
                        <div class="p-2 account-link @if($account->is_active) active @else deactivated @endif">
                            <span>@</span>{{ $account->nickname }}
                        </div>
                        @if(!empty($account->proxy_ip))
                            <div style="padding: 2px 22px 0 0;font-size: 10px;color: yellow;">proxy ip</div>
                        @endif
                    </div>

                    <div class="row account-middle-row">
                        <div style="flex-grow: 1;">
                            <img src="{{ $account->picture }}" class="rounded-circle mini-profile-picture"/>
                        </div>

                        @if($account->is_active == 1)
                            <div>
                                <div class="btn-dark my-btn refresh-account-btn" data-account-id="{{ $account->id }}">
                                    <i class="fas fa-sync"></i>
                                </div>
                            </div>
                            <div>
                                <div class="btn-dark my-btn btn-info account-deactivate" data-account-id="{{ $account->id }}">
                                    <i class="fas fa-trash"></i>
                                </div>
                            </div>
                        @elseif($account->is_confirmed == 1)
                            <div>
                                <button type="button" style="margin-right: 20px;" class="btn btn-basic account-activate" data-account-id="{{ $account->id }}">Активировать</button>
                            </div>
                        @elseif($account->is_confirmed == 0 and 'sended' == $account->verify_code)
                            {{--<div style="color: darkblue;">--}}
                                {{--<h3>Введите код из смс</h3>--}}
                            {{--</div>--}}
                            <div>
                                <button type="button" class="btn btn-basic account-enter-code"
                                data-account-id="{{ $account->id }}">Ввести код</button>
                            </div>
                        @elseif($account->is_confirmed == 0 and 'sended' != $account->verify_code)
                            <div>
                                <button type="button" class="btn btn-basic account-relogin"
                                data-account-id="{{ $account->id }}">Ввести данные</button>
                            </div>
                        @endif
                    </div>

                    @if($account->is_confirmed == 0 and 'sended' != $account->verify_code)
                        <div class="row" style="padding-left: 36px;">
                            <h4>Не верный логин/пароль</h4>
                        </div>
                    @endif
                    @if($account->is_confirmed == 0 and 'sended' == $account->verify_code)
                        <div class="row" style="padding-left: 36px;">
                            <h4>Введите код из смс</h4>
                        </div>
                    @endif

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
                    <input type="hidden" id="add-account-account-id" value="0" />
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

            <div id="enter-confirm-code-form">
                <form>
                    <input type="hidden" id="add-account-kode-account-id" value="0" />
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="account-password" class="my-label">Код из смс</label>
                            <input type="text" class="form-control my-text-input" id="account-sms-code" />

                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-success" id="add-account-code-submit">Сохранить</button>
                        </div>
                    </div>

                </form>
            </div>
        @endif
    </div>
@stop