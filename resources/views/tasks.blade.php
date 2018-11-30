@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <div class="row">
            <div class="col-lg-12">
                <h3>Выберите аккаунт</h3>
            </div>
        </div>

        @foreach ($accounts as $account)
            @if($account->is_active)
                <section class="account-link account active"
                         data-account-id="{{ $account->id }}">
                    <div class="row">
                        <div class="col-lg-12 d-flex">
                            <div class="p-2">
                                <span>@</span>{{ $account->nickname }}
                            </div>
                            <div class="ml-auto p-2">
                                <i class="fa fa-angle-right"></i>
                            </div>
                        </div>
                    </div>

                </section>
            @endif
        @endforeach
    </div>
@stop