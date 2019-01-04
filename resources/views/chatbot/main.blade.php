@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <section id="chatbot-instruction" class="chatbot-container">
            <div class="row">
                <div class="col-lg-12">
                    <p>Чат бот работает по списку пользователей, которые вы должны загрузить по хештегам (Польватели для бота).
                    Он не пишет сообщения тем людям которые у вас в "Белом списке".
                    Бот суммирует белые списки всех ваших аккаунтов и не будет им писать.
                    Задание у бота одно на все ваши аккаунты, тоесть все ваши аккаунты будут начинать чат
                    с пользователями которых загрузите в список "Польватели для бота".
                    Чем больше аккаунтов вы подключите, тем больше бот сможет вести одновременных переписок со всех аккаунтов.</p>

                    <p>Итак шаги:</p>
                    <p>1. Загрузить белый список (списки) и отметить людей которые в них входят.</p>
                    <p>2. Указать хештеги по которым будет загружаться список "Польватели для бота" и загрузить его.</p>
                    <p>3. Указать логику чат переписки (ответы на вопросы и логику окончания диалога).</p>
                    <p>4. Запустить задание.</p>
                    <p>5. Получать на почту сообщения о том что диалог с каким-то пользователем был закончен.</p>
                </div>
            </div>
            <div class="row">
                <div style="display: flex; padding: 5px 15px; justify-content: space-between;">

                    <div style="padding: 0 5px;height: 360px;">
                        {{--<label>Сколько аккаунтов по хештегу загрузить? (от 1 до 100):</label>--}}

                        {{--<input type="number" style="width: 100%;" step="10" id="chatbot-max-accounts" min="1" max="100" value="{{ $chatbot->max_accounts }}"--}}
                            {{--@if($chatbot->status == \App\Chatbot::STATUS_UPDATING || $chatbot->status == \App\Chatbot::STATUS_IN_PROGRESS)--}}
                                {{--disabled--}}
                            {{--@endif--}}
                        {{--/>--}}



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
                <div style="display: flex; padding: 5px 15px; justify-content: space-between;  width: 450px;">
                    <div>Статус: {{ $chatbot->statusRus }}</div>

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
                <div style="display: flex; padding: 5px 15px; justify-content: space-between;  width: 450px;">
                    <div>В очереди: {{ $chatbot->total_chats }}</div>
                    <div>Чатов начато: {{ $chatbot->chats_in_progress }}</div>
                    <div>Чатов окончено: {{ $chatbot->chats_finished }}</div>
                </div>
            </div>
        </section>
    </div>
@stop