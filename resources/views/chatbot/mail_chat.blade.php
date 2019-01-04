<!doctype html>
<html>
    <meta charset="utf-8">

    <body>
        <h2>Диалог с {{ $threadTitle }}</h2>
        <h3>На аккаунте: {{ $account }}, получен телефон: {{ $phone }}</h3>
        <br/><br/>
        <div style="width: 400px; background-color: lightcyan;">
            @foreach($dialog as $message)
                @if (!$message['isMy'])
                    <div style="padding: 5px;border: 2px solid green;margin: 5px 100px 0 0;font-size: 22px;color: green;">
                        {{ $message['text'] }}
                    </div>
                @else
                    <div style="padding: 5px;border: 2px solid blue;margin: 5px 0 0 100px;font-size: 22px;color: blue;">
                        {{ $message['text'] }}
                    </div>
                @endif
            @endforeach
        </div>
    </body>
</html>