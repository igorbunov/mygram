<?php

namespace App\Http\Controllers;

use App\account;
use App\Task;
use App\TaskList;
use App\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function getTasks(int $accountId) {
        $res = account::find($accountId);

        $activeDirectTasks = [];

        foreach($res->directTasks as $i => $task) {
            $res->directTasks[$i]->taskList = $task->taskList;
//            $res->directTasks[$i]['taskList'] = $task->taskList->toArray();
//            $activeDirectTasks[$task->taskList->id] = $task->taskList->toArray();
        }

//        dd($res->directTasks[0]->taskList);
//        dd($activeDirectTasks);

//        $taskTypeList = TaskList::all()->toArray();
//
//        dd($taskTypeList);

        return view('account_task', [
            'title' => 'Задачи',
            'activePage' => 'tasks',
            'tasks' => $res->directTasks,
            'account' => $res
        ]);


        dd($res->directTasks->toArray());
//        dd($activeDirectTasks);
        dd($res->toArray(), $res->user->toArray(),  $res->user->tariffs->toArray(), $res->directTasks->toArray());
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($error = '')
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::find($userId)->accounts;

        $res = [
            'title' => 'Задачи'
            , 'activePage' => 'tasks'
            , 'accounts' => $accounts
        ];

        if ($error != '') {
            $res['error'] = $error;
        }

        return view('tasks', $res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        //
    }
}
