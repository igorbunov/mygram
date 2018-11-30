@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        <div class="row">
            <div class="col-lg-12 account-link  active ">
                <span>@</span>{{ $account->nickname }}
            </div>
        </div>
<br/>
        @foreach ($directTasks as $task)
            <section class="account-tasks @if($task->is_active) active @else deactivated @endif">
                <div class="row">
                    <div class="col-lg-8">
                        <h4>{{ $task->message }}</h4>
                    </div>
                    <div class="col-lg-4 ml-auto">
                        @if($currentTariff != null)
                            @if($task->is_active == 1)
                                <button type="button" class="btn btn-basic task-deactivate"
                                        data-task-type="{{ $task->taskType }}"
                                        data-account-id="{{ $account->id }}"
                                        data-task-id="{{ $task->id }}">Деактивировать</button>
                            @else
                                <button type="button" class="btn btn-basic task-activate"
                                        data-task-type="{{ $task->taskType }}"
                                        data-account-id="{{ $account->id }}"
                                        data-task-id="{{ $task->id }}">Активировать</button>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex flex-row">
                        <div class="p-2">Тип: {{ $task->taskList->title }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 d-flex flex-row">
                        <div class="p-2">Всего: {{ $task->total_messages }}</div>
                        <div class="p-2">Удачно: {{ $task->success_count }}</div>
                        <div class="p-2">Ошибок: {{ $task->failure_count }}</div>
                        <div class="p-2">Сегодня: {{ $task->sendedToday }}</div>
                    </div>
                </div>

            </section>
        @endforeach

        @if($currentTariff != null)
            <div class="row">
                <div class="col-lg-12">
                    <button type="button" class="btn btn-dark" id="add-task-btn">Добавить задание</button>
                </div>
            </div>

            <div id="add-task-form">
                <form method="POST" action="create_task">
                    {{ csrf_field() }}

                    <input type="hidden" name="account_id" value="{{ $account->id }}" />

                    <div class="row">
                        <div class="col-lg-12">
                            <label for="add-task-task-type">Тип задания</label>
                            <select class="custom-select mb-2 mr-sm-2 mb-sm-0" id="add-task-task-type" name="task_list_id">
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
                                <textarea class="form-control" id="add-direct-task-text" name="direct_text" style="width: 100%; height: 100px;"></textarea>
                            </div>
                            <div class="col-lg-12">
                                <label for="add-direct-task-work-only-in-night">Работать только ночью</label>
                                <input class="form-check-input" id="add-direct-task-work-only-in-night" type="checkbox" name="work_only_in_night" />
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
                            <input type="button" class="btn btn-success" type="submit" value="Сохранить">
                        </div>
                    </div>


                </form>
            </div>
        @endif
    </div>
@stop