@extends('main_template')

@section('main_content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Выберите аккаунт</h3>
            </div>
        </div>

        @foreach ($accounts as $account)
            <section id="account" class="account-link account @if($account->is_active) active @else deactivated @endif"
                     data-account-id="{{ $account->id }}">
                <div class="row">
                    <div class="col-lg-11">
                        <h4>{{ $account->nickname }}</h4>
                    </div>
                    <div class="col-lg-1">
                        <i class="fa fa-angle-right"></i>
                    </div>
                </div>

            </section>
        @endforeach
    </div>
@stop