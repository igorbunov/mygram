@extends('main_template')

@section('main_content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if ($isError)
                    <h3>Активация не удалась: {{ $message }}</h3>
                @else
                    <h3>Активация выполнена!</h3>
                @endif
            </div>
        </div>
    </div>
@stop
