<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Mail\Transport\ResendTransport;

class CommonLifeTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('pages.commonLife.task.index', compact('tasks'));
    }

    public  function store(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
        ]);
        Task::create($request->only('task_title', 'task_description'));
        return redirect()->route('tasks.index')->with('success', 'Tâche crée avec succès !');
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
        ]);
        Task::update($request->only('task_title', 'task_description'));
        return redirect()->route('tasks.index')->with('success', 'Tâche modifiée avec succès !');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès !');
    }
}
