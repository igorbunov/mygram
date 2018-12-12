@extends('main_template')

@section('main_content')
    <div class="container container-nopadding">
        @foreach ($directTasks as $task)
            <section class="account-tasks {{ $task->status }}">
                <div class="row">
                    <div class="col-lg-12 d-flex">
                        <div class="p-2 account-tasks-type-title">{{ $task->taskList->title }}</div>

                        <div class="p-2 ml-auto">
                            @if($currentTariff != null)
                                @if($task->status == \App\DirectTask::STATUS_ACTIVE)
                                    <div class="btn-dark my-btn pause-task"
                                         data-task-type="{{ $task->taskType }}"
                                         data-account-id="{{ $account->id }}"
                                         data-task-id="{{ $task->id }}">
                                        <i class="fas fa-pause"></i>
                                    </div>
                                @elseif($task->status == \App\DirectTask::STATUS_PAUSED)
                                    <div class="btn-dark my-btn unpause-task"
                                         data-task-type="{{ $task->taskType }}"
                                         data-account-id="{{ $account->id }}"
                                         data-task-id="{{ $task->id }}">
                                        <i class="fas fa-play"></i>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="p-2">
                            @if($currentTariff != null)
                                @if($task->status != \App\DirectTask::STATUS_DEACTIVATED)
                                    <div class="btn-dark my-btn task-deactivate"
                                         data-task-type="{{ $task->taskType }}"
                                         data-account-id="{{ $account->id }}"
                                         data-task-id="{{ $task->id }}">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                @elseif($task->status == \App\DirectTask::STATUS_DEACTIVATED)
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
                <div class="row">
                    <div class="col-lg-12 d-flex flex-row" style="align-items: center;">
                        <div style="margin: 0 5px 5px 8px;">В очереди на отправку: {{ $task->inQueue }}</div>
                        <div class="btn-dark my-btn-small clear-direct-queue"
                             data-task-type="{{ $task->taskType }}"
                             data-account-id="{{ $account->id }}"
                             data-task-id="{{ $task->id }}">
                            <i class="fas fa-trash"></i>
                        </div>
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