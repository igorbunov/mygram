@include('safelist.pagination')

@if(($start + $limit) < $safelistTotal)
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $start + $limit }}, всего {{ $safelistTotal }}</div>
@else
<div style="display: flex;justify-content: flex-end;">записи с {{ $start }} по {{ $safelistTotal }}, всего {{ $safelistTotal }}</div>
@endif

<section id="safelist" data-account-id="{{ $accountId }}">
    @foreach($safelist as $listUser)
        <div class="chatbot-account-container">
            <a href="https://www.instagram.com/{{ $listUser['username'] }}/" target="_blank">
                <div>
                    <img style="width: 80px; height: 80px;" class="rounded-circle" src="{{ $listUser['picture'] }}" />
                </div>
            </a>
            <div onclick="onSafelistClick(this);">
                <div class="safelist-nickname">
                    {{ $listUser['username'] }}
                </div>
                @if($listUser['is_in_safelist'] == 1)
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

        {{--<div onclick="onSafelistClick(this);">--}}
            {{--<div>--}}
                {{--<img style="width: 50px; height: 50px;" class="rounded-circle" src="{{ $listUser['picture'] }}" />--}}
            {{--</div>--}}
            {{--<div class="safelist-nickname">--}}
                {{--{{ $listUser['username'] }}--}}
            {{--</div>--}}
            {{--@if($listUser['is_in_safelist'] == 1)--}}
                {{--<div class="my-checkbox checkbox-checked">--}}
                    {{--<i class="fa fa-check"></i>--}}
                {{--</div>--}}
            {{--@else--}}
                {{--<div class="my-checkbox checkbox-unchecked">--}}
                    {{--<i class="fa fa-check"></i>--}}
                {{--</div>--}}
            {{--@endif--}}
        {{--</div>--}}
    @endforeach
</section>

@include('safelist.pagination')