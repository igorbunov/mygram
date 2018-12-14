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