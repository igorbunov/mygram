@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        @foreach ($directTasks as $task)
            <section class="account-tasks @if($task->is_active) active @else deactivated @endif">
                <div class="row">
                    <div class="col-lg-12 d-flex">
                        <div class="p-2 account-tasks-type-title @if($task->is_active) active @else deactivated @endif">{{ $task->taskList->title }}</div>

                        <div class="p-2 ml-auto">
                            @if($currentTariff != null)
                                @if($task->is_active == 1)
                                    <button type="button" class="btn btn-info task-deactivate"
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
                </div>
                <div class="row">
                    <div class="col-lg-12 ml-auto direct-message-text">
                        {{ $task->message }}
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
                <div class="col-lg-12 d-flex justify-content-around">
                    <div class="p-2">
                        <button type="button" class="btn btn-dark" id="add-task-btn">Добавить</button>
                    </div>
                    <div class="p-2 ml-auto">
                        @if($onlyActiveTasks == true)
                            <button type="button" class="btn btn-dark" data-all="true" data-account-id="{{ $account->id }}" id="all-tasks-btn">Все задания</button>
                        @else
                            <button type="button" class="btn btn-dark" data-all="false" data-account-id="{{ $account->id }}" id="all-tasks-btn">Активные задания</button>
                        @endif
                    </div>
                </div>
            </div>

            <div id="add-task-form">
                <form>
                    <input type="hidden" id="add-task-account-id" value="{{ $account->id }}" />

                    <div class="row">
                        <div class="col-lg-12">
                            <label for="add-task-task-type" class="my-label">Тип задания</label>
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
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="add-direct-task-text" class="my-label">Текст сообщения</label>
                                <textarea class="form-control" id="add-direct-task-text" name="direct_text" style="width: 100%; height: 100px;"></textarea>
                            </div>
                            <div class="col-lg-12">
                                <label for="add-direct-task-work-only-in-night" class="my-label">Работать только ночью</label>
                                <input class="form-check-input" id="add-direct-task-work-only-in-night" type="checkbox"/>
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
                            <button type="button" class="btn btn-success" id="add-task-submit">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
@stop