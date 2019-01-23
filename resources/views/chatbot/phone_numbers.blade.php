<div>
    <div class="input-group phones-search">
        <input data-is-today="{{$isToday}}"
               class="form-control py-2 border-right-0 border"
               type="search"
               onkeyup="searchPhoneByEnter(event);"
               value="{{ $query }}"
               placeholder="поиск по номеру"
               id="search-by-phone">
        <span class="input-group-append">
              <div class="input-group-text bg-white" onclick="searchPhone();"><i class="fa fa-search"></i></div>
        </span>
    </div>

    @if(count($phones) == 0)
        <div style="font-weight: bold; padding: 20px 0;">Ничего не найдено</div>
    @endif


    @foreach($phones as $i => $phoneRow)
        @if($i != $total)
            <div style="border-bottom: 1px solid white;" data-number="{{ $phoneRow->p }}">
        @else
            <div data-number="{{ $phoneRow->p }}">
        @endif
            <div class="chatbot-taken-phones-header">
                <div><span>@</span>{{ $phoneRow->n }}</div>

                @if($isToday)
                    <div>{{ $phoneRow->tm }}</div>
                @else
                    <div>{{ $phoneRow->dt }}</div>
                @endif
            </div>

            <div class="chatbot-taken-phones">
                <i><div>{{ $phoneRow->t }}:</div></i>
                <div>{{ $phoneRow->p }}</div>
            </div>
        </div>

    @endforeach

</div>