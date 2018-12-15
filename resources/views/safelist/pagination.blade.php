@if($safelistTotal > $limit)
    <div class="safelist-pagination">
        <ul class="pagination pagination-lg">
            @if ($start > 0)
                <li class="page-item" onclick="safelistPaginateBack(this, {{ $start }}, {{ $limit }});"><a class="page-link">Назад</a></li>
            @else
                <li class="page-item disabled"><a class="page-link">Назад</a></li>
            @endif

            @if (($safelistTotal - $start) > $limit)
                <li class="page-item" onclick="safelistPaginateForward(this, {{ $start }}, {{ $limit }});"><a class="page-link">Вперед</a></li>
            @else
                    <li class="page-item disabled"><a class="page-link">Вперед</a></li>
            @endif
        </ul>
    </div>
@endif