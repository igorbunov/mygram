@include('chatbot.pagination')

@if(($start + $limit) < $chatBotAccountsTotal)
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $start + $limit }}, всего {{ $chatBotAccountsTotal }}</div>
@else
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $chatBotAccountsTotal }}, всего {{ $chatBotAccountsTotal }}</div>
@endif

<section id="chatbotaccounts-list">
    @foreach($chatBotAccounts as $listUser)
        <div class="chatbot-account-container">
            <a href="https://www.instagram.com/{{ $listUser->username }}/" target="_blank">
                <div>
                    <img style="width: 80px; height: 80px;" class="rounded-circle" src="{{ $listUser->picture }}" />
                </div>
            </a>
            <div onclick="onChatbotAccountClick(this);">
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
        </div>
    @endforeach
</section>

@include('chatbot.pagination')