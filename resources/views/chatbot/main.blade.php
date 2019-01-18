@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <section id="chatbot-instruction" class="chatbot-container">
            <div class="btn-dark show-chatbot-instruction">
                <span style="margin-right: 22px;">Показать инструкцию</span>
                <i class="fas fa-angle-down"></i>
            </div>
            <div class="row chatbot-instruction">
                <div class="col-lg-12">
                    <p>Чат бот работает по списку пользователей, которые он загрузит по лайкам их первых 3х постов
                        пользователей которых вы укажете (Польватели для бота).
                    Он не пишет сообщения тем людям которые у вас в "Белом списке" или тем с кем у вас уже есть переписка.
                        Бот также прекращает вести переписку если видит от вас сообщение не из его скрипта.
                    Бот суммирует белые списки всех ваших аккаунтов и не будет им писать.
                    Задание у бота одно на все ваши аккаунты, тоесть все ваши аккаунты будут начинать чат
                    с пользователями которых загрузите в список "Польватели для бота".</p>

                    <p>Итак шаги:<br/>
                    1. Загрузить белый список (списки) и отметить людей которые в них входят.<br/>
                    2. Указать аккаунты по которым будет загружаться список "Польватели для бота" и загрузить его.<br/>
                    3. Почистить список (снять галочки с ненужных аккаунтов) чтоб бот им не писал.<br/>
                    4. Запустить задание.<br/>
                    5. Получать на почту сообщения о том что диалог с каким-то пользователем был закончен.</p>
                </div>
            </div>
            <div class="row">
                <div style="display: flex; padding: 5px 15px; justify-content: space-between;">

                    <div style="padding: 0 5px;">

                        <label>Аккаунты (каждый с новой строки):</label>

                        <textarea type="textarea" id="chatbot-hashtags" maxlength="450" rows="5" style="width: 100%;height: 245px;"
                          @if($chatbot->status == \App\Chatbot::STATUS_UPDATING || $chatbot->status == \App\Chatbot::STATUS_IN_PROGRESS)
                                  disabled
                            @endif
                        >{{ $chatbot->hashtags }}</textarea>
                    </div>

                    @if($chatbot->status != \App\Chatbot::STATUS_UPDATING && $chatbot->status != \App\Chatbot::STATUS_IN_PROGRESS)
                        <div class="btn-dark refresh-bot-list">
                            <i class="fas fa-sync"></i>
                            <span style="margin-left: 20px;">Загрузить пользователей</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="row">
                <h4 style="padding: 5px 15px;" >Диалогов сегодня:</h4>
                <div class="chatbot-dialog-statuses">
                    @foreach($statsByAccount as $stByAccount)
                        @if($stByAccount->delay > 15)
                            <div style="font-style:italic;">{{ $stByAccount->nickname }}</div>
                            <div>{{ $stByAccount->cnt }}</div>
                            <div>(ждет {{ $stByAccount->delay }} мин.)</div>
                        @else
                            <div style="font-weight: bold;">{{ $stByAccount->nickname }}</div>
                            <div>{{ $stByAccount->cnt }}</div>
                            <div>(ждет {{ $stByAccount->delay }} мин.)</div>
                        @endif
                    @endforeach
                </div>

            </div>
            <div class="row">
                <div style="display: flex; padding: 5px 15px; justify-content: space-between;  width: 450px;">
                    <div style="font-weight: bold;">Статус: {{ $chatbot->statusRus }}</div>

                    @if($chatbot->status == \App\Chatbot::STATUS_SYNCHRONIZED or $chatbot->status == \App\Chatbot::STATUS_EMPTY)
                        <div class="btn-dark start-chatbot-task" data-status="{{ $chatbot->status }}">
                            <i class="fas fa-play"></i>
                            <span style="margin-left: 20px;">Начать задание</span>
                        </div>
                    @elseif($chatbot->status == \App\Chatbot::STATUS_IN_PROGRESS)
                        <div class="btn-dark start-chatbot-task" data-status="{{ $chatbot->status }}">
                            <i class="fas fa-pause"></i>
                            <span style="margin-left: 20px;">Остановить</span>
                        </div>
                    @endif
                </div>
                <div class="chatbot-stats-block">
                    <div>Очередь: {{ $chatbotStats->in_queue }}</div>
                    <div>Сегодня: {{ $chatbotStats->sended_today }}</div>
                    <div>Вчера: {{ $chatbotStats->sended_yesterday }}</div>
                    <div>Неделя: {{ $chatbotStats->sended_weekly }}</div>
                    <div>Всего: {{ $chatbotStats->total }}</div>
                </div>
            </div>
        </section>

        <div id="chatbot-accounts-container">
            @include('chatbot.chatbot_account_item')
        </div>
    </div>
@stop