@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <section id="safelist-instruction" class="safelist-container">
            <div class="row">
                <div class="col-lg-12">
                    <p>Белый список - это ваши подписки, от которых задание на массовую отписку никогда не отпишется.</p>
                    <p>Вначале необходимо получить ваши подписки. Для этого нажмите на кнопку "Загрузить подписки" и подождите пока список полностью прогрузится.</p>
                    <p>После чего можно будет выбрать избранные аккаунты, от которых не будет происходить отписка.</p>
                </div>
            </div>
            <div style="display: flex; padding: 5px 15px; justify-content: space-between;">
                @if($status == \App\Safelist::STATUS_UPDATING)
                    <div class="btn-dark refresh-follow-list disabled" data-account-id="{{ $accountId }}">
                        <i class="fas fa-sync"></i>
                        <span style="margin-left: 20px;">Загрузка списка ...</span>
                    </div>
                    <div class="btn-dark my-btn refresh-follow-list" data-account-id="{{ $accountId }}">
                        ...
                    </div>
                @else
                    <div class="btn-dark refresh-follow-list" data-account-id="{{ $accountId }}">
                        <i class="fas fa-sync"></i>
                        <span style="margin-left: 20px;">Загрузить подписки</span>
                    </div>
                    <div class="btn-dark my-btn refresh-follow-list" data-account-id="{{ $accountId }}">
                        <i class="fas fa-sync"></i>
                    </div>
                @endif
                <div class="safelist-toggle-on-off">
                    <span>Все</span>
                    @if($is_all == 0)
                        <i class="fas fa-toggle-off toggle-btn" id="toggle-off" style="display: none;"></i>
                        <i class="fas fa-toggle-on toggle-btn" id="toggle-on"></i>
                    @else
                        <i class="fas fa-toggle-off toggle-btn" id="toggle-off"></i>
                        <i class="fas fa-toggle-on toggle-btn" id="toggle-on" style="display: none;"></i>
                    @endif
                    <span>Только выбранные</span>
                </div>
                <div class="btn-dark my-btn btn-info clear-safelist-users">
                    <i class="fas fa-trash"></i>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 d-flex flex-row">
                    <div class="p-2">Всего подписок: {{ $totalSubscriptions }}</div>
                    <div class="p-2">В белом списке: {{ $selectedAccounts }}</div>
                </div>
            </div>
        </section>

        <div id="safelist-container">
            @include('safelist.safelist_item')
        </div>
    </div>
@stop