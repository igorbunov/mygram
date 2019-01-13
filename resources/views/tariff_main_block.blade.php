<selection id="tariffs-container">
    <div class="row">
        @foreach ($tariffList as $tariff)
            <div class="col-lg-6">
                <form method="POST" action="checkout">
                    {{ @csrf_field() }}

                    <input type="hidden" name="tariff_list_id" value="{{ $tariff->id }}" />

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h4 class="my-0 font-weight-normal">{{ $tariff->name }}</h4>
                        </div>
                        <div class="card-body">
                            @if ($tariff->is_trial == 1)
                                <input type="hidden" name="accounts_count" value="1" />

                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>кол-во аккаунтов: 1</li>
                                    <li><b>{!! $tariff->descriptionRus  !!}</b></li>
                                </ul>
                            @else
                                <h2 class="card-title pricing-card-title">
                                    <span id="tariff-list-{{ $tariff->id }}-beginer-price">{{ $tariff->price_one_uah }}</span> грн<small class="text-muted"> / мес.</small>
                                </h2>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>
                                        кол-во аккаунтов:
                                        <select class="tariff-account-count-selection" name="accounts_count"
                                                data-list-id="{{ $tariff->id }}"
                                                data-tariff-price1="{{ $tariff->price_one_uah }}"
                                                data-tariff-price3="{{ $tariff->price_three_uah }}"
                                                data-tariff-price5="{{ $tariff->price_five_uah }}"
                                                data-tariff-price10="{{ $tariff->price_ten_uah }}">
                                            <option selected>1</option>
                                            <option>3</option>
                                            <option>5</option>
                                            <option>10</option>
                                        </select>
                                    </li>
                                    <li><b>{!! $tariff->descriptionRus  !!}</b></li>
                                </ul>
                            @endif

                            <button type="submit" class="btn btn-lg btn-block btn-outline-primary">Подключить</button>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</selection>