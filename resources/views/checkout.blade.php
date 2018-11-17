@extends('main_template')

@section('main_content')
    <div class="container">
        {{--"id" => 3--}}
        {{--"name" => "Продвинутый"--}}
        {{--"description" => "Direct to subscribers<br/>And other stuff<br/>Mass following<br/>Mass unfollowing"--}}
        {{--"price_one_uah" => "500.00"--}}
        {{--"price_three_uah" => "1000.00"--}}
        {{--"price_five_uah" => "1700.00"--}}
        {{--"price_ten_uah" => "2200.00"--}}
        {{--"length_in_days" => 31--}}
        {{--"is_active" => 1--}}
        {{--"is_trial" => 0--}}
        {{--"created_at" => null--}}
        {{--"updated_at" => null--}}
        <h2>Тариф № {{ $selectedTariff->name }}</h2>
        <h5>Возможности:</h5>
        <div style="margin-left: 150px;"> {!! $selectedTariff->description  !!} </div>
        <h2>Кол-во аккаунтов: {{ $accountsCount }}</h2>

        @if($accountsCount == 1)
            <h2>Стоимость: {{ $selectedTariff->price_one_uah }} грн.</h2>
        @elseif($accountsCount == 3)
            <h2>Стоимость: {{ $selectedTariff->price_three_uah }} грн.</h2>
        @elseif($accountsCount == 5)
            <h2>Стоимость: {{ $selectedTariff->price_five_uah }} грн.</h2>
        @elseif($accountsCount == 10)
            <h2>Стоимость: {{ $selectedTariff->price_ten_uah }} грн.</h2>
        @endif

        {!! $payButton !!}
    </div>
@stop