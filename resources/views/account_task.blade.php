@extends('main_template')

@section('main_content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>{{ $account->nickname }}</h3>
            </div>
        </div>
<br/>
        @foreach ($tasks as $task)
            <section class="account-tasks @if($task->is_active) active @else deactivated @endif">
                <div class="row">
                    <div class="col-lg-8">
                        <h4>{{ $task->message }}</h4>
                    </div>
                    <div class="col-lg-4 ml-auto">
                        @if($task->is_active == 1)
                            <button class="task-deactivate"
                                    data-task-type="{{ $task->taskType }}"
                                    data-task-id="{{ $task->id }}">Деактивировать</button>
                        @else
                            <button class="task-activate"
                                    data-task-type="{{ $task->taskType }}"
                                    data-task-id="{{ $task->id }}">Активировать</button>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex flex-row">
                        <div class="p-2">{{ $task->taskType }}</div>
                        <div class="p-2">{{ $task->taskList->title }}</div>
                        <div class="p-2">{{ $task->delay_time_min }}</div>
                        <div class="p-2">{{ $task->is_active }}</div>
                        <div class="p-2">{{ $task->total_messages }}</div>
                        <div class="p-2">{{ $task->success_count }}</div>
                        <div class="p-2">{{ $task->failure_count }}</div>
                    </div>
                </div>

            </section>
        @endforeach


        <div class="row">
            <div class="col-lg-12">
                <button id="add-task-btn">Добавить задание</button>
            </div>
        </div>

        <div id="add-task-form">
            <form method="POST" action="create_task">
                {{ csrf_field() }}

                <input type="hidden" name="account_id" value="{{ $account->id }}" />

                <div class="row">
                    <div class="col-lg-12">
                        <label for="add-task-task-type">Тип задания</label>
                        <select id="add-task-task-type" name="task_list_id">
                            @foreach($taskList as $i => $taskListItem)
                                @if($i == 0)
                                    <option selected value="{{ $taskListItem->id }}"
                                            data-task-type="{{ $taskListItem->type }}">
                                        {{ $taskListItem->title }}</option>
                                @else
                                    <option value="{{ $taskListItem->id }}"
                                            data-task-type="{{ $taskListItem->type }}">
                                        {{ $taskListItem->title }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <selection id="add-direct-task">
                    <h3>Директ ответ подписавшимся</h3>

                    <div class="row">
                        <div class="col-lg-12">
                            <label for="add-direct-task-text">Текст сообщения</label>
                            <textarea id="add-direct-task-text" name="direct_text" style="width: 100%; height: 100px;"></textarea>
                        </div>
                        <div class="col-lg-12">
                            <label for="add-direct-task-delay">Отправлять с задержкой 10 миинут</label>
                            <input id="add-direct-task-delay" type="checkbox" name="is_use_delay" />
                        </div>
                        <div class="col-lg-12">
                            <label for="add-direct-task-work-only-in-night">Работать только ночью</label>
                            <input id="add-direct-task-work-only-in-night" type="checkbox" name="work_only_in_night" />
                        </div>
                    </div>

                </selection>

                <selection id="add-unfollowing-task">
                    <div class="row">
                        <div class="col-lg-12">
                            <h3>unfollowing task</h3>
                        </div>
                    </div>
                </selection>


                <div class="row">
                    <div class="col-lg-12">
                        <input type="submit" value="Сохранить">
                    </div>
                </div>


            </form>
        </div>
    </div>
@stop