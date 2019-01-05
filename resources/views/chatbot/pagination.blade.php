@if($chatBotAccountsTotal > $limit)
    <div class="safelist-pagination">
        <ul class="pagination pagination-lg">
            @if ($start > 0)
                <li class="page-item" onclick="chatBotAccountsListPaginateBack(this, {{ $start }}, {{ $limit }});"><a class="page-link">Назад</a></li>
            @else
                <li class="page-item disabled"><a class="page-link">Назад</a></li>
            @endif

            @if (($chatBotAccountsTotal - $start) > $limit)
                <li class="page-item" onclick="chatBotAccountsListPaginateForward(this, {{ $start }}, {{ $limit }});"><a class="page-link">Вперед</a></li>
            @else
                    <li class="page-item disabled"><a class="page-link">Вперед</a></li>
            @endif
        </ul>
    </div>
@endif