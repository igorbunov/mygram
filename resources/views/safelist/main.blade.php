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
            <div class="row">
                <div style="display: flex; padding: 5px 15px;">
                    @if($status == \App\Safelist::STATUS_UPDATING)
                        <div class="btn-dark refresh-follow-list disabled" data-account-id="{{ $accountId }}">
                            <i class="fas fa-sync"></i>
                            <span style="margin-left: 20px;">Загрузка списка ...</span>
                        </div>
                    @else
                        <div class="btn-dark refresh-follow-list" data-account-id="{{ $accountId }}">
                            <i class="fas fa-sync"></i>
                            <span style="margin-left: 20px;">Загрузить подписки</span>
                        </div>
                    @endif
                    <div class="btn-dark" style="padding: 14px;border-radius: 5px;margin-left: 5px;">
                        Очистить список избранных
                    </div>
                </div>
            </div>
            <div class="row">
                <div style="display: flex; padding: 5px 15px;">
                    <input type="text" placeholder="Найти аккаунт" class="search-field">
                    <i class="fas fa-search" style="margin: 16px 0 0 -26px"></i>
                </div>
                <div style="padding: 15px">
                    Все
                    <i class="fas fa-toggle-off toggle-btn" id="toggle-off"></i>
                    <i class="fas fa-toggle-on toggle-btn" id="toggle-on"></i>
                    Только выбранные
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 d-flex flex-row">
                    <div class="p-2">Всего подписок: {{ $totalSubscriptions }}</div>
                    <div class="p-2">В белом списке: {{ $selectedAccounts }}</div>
                </div>
            </div>
        </section>

        <section id="safelist" data-account-id="{{ $accountId }}">
            @foreach($safelist as $listUser)
                <div>
                    <div>
                        <img style="width: 50px; height: 50px;" class="rounded-circle" src="{{ $listUser['picture'] }}" />
                    </div>
                    <div>
                        {{ $listUser['username'] }}
                    </div>
                    @if($listUser['is_in_safelist'] == 1)
                        <div class="checkbox-checked">
                            <i class="fa fa-check"></i>
                        </div>
                    @else
                        <div class="checkbox-unchecked">
                            <i class="fa fa-check"></i>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>
@stop