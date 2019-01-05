@include('chatbot.pagination')

@if(($start + $limit) < $chatBotAccountsTotal)
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $start + $limit }}, всего {{ $chatBotAccountsTotal }}</div>
@else
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $chatBotAccountsTotal }}, всего {{ $chatBotAccountsTotal }}</div>
@endif

<section id="chatbotaccounts-list">
    @foreach($chatBotAccounts as $listUser)
        <div onclick="onChatbotAccountClick(this);">
            <div>
                <img style="width: 50px; height: 50px;" class="rounded-circle" src="{{ $listUser->picture }}" />
            </div>
            <div class="safelist-nickname">
                {{ $listUser->username }}
            </div>
            @if($listUser->is_sended == 0)
                <div class="my-checkbox checkbox-checked">
                    <i class="fa fa-check"></i>
                </div>
            @else
                <div class="my-checkbox checkbox-unchecked">
                    <i class="fa fa-check"></i>
                </div>
            @endif
        </div>
    @endforeach
</section>

@include('chatbot.pagination')