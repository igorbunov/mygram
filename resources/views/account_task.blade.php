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
            <section>
                <div class="row">
                    <div class="col-lg-12">
                        <h4>{{ $task->message }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex flex-row">
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

        <section id="task-add-selection">
            <div class="row">
                <div class="col-lg-12">
                    <button id="add-task-btn">Добавить задание</button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h4 class="my-0 font-weight-normal">Free</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title pricing-card-title">$0 <small class="text-muted">/ mo</small></h1>
                            <ul class="list-unstyled mt-3 mb-4">
                                <li>10 users included</li>
                                <li>2 GB of storage</li>
                                <li>Email support</li>
                                <li>Help center access</li>
                            </ul>
                            <button type="button" class="btn btn-lg btn-block btn-outline-primary">Sign up for free</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h4 class="my-0 font-weight-normal">Free</h4>
                        </div>
                        <div class="card-body">
                            <h1 class="card-title pricing-card-title">$0 <small class="text-muted">/ mo</small></h1>
                            <ul class="list-unstyled mt-3 mb-4">
                                <li>10 users included</li>
                                <li>2 GB of storage</li>
                                <li>Email support</li>
                                <li>Help center access</li>
                            </ul>
                            <button type="button" class="btn btn-lg btn-block btn-outline-primary">Sign up for free</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@stop