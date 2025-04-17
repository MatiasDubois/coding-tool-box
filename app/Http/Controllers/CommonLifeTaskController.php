<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DeepCopy\Filter\ChainableFilter;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Cohort;
use Illuminate\Mail\Transport\ResendTransport;

class CommonLifeTaskController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'updated_at');
        $order = $request->get('order', 'desc');

        $tasks = Task::orderBy($sort, $order)->paginate(10);
        $cohorts = Cohort::all();

        $tasks = Task::with('cohorts.school')
            ->orderBy($sort, $order)
            ->paginate(10);

        return view('pages.commonLife.task.index', compact('tasks', 'cohorts'));
    }

    public function create()
    {
        $cohorts = Cohort::all();
        return view('pages.commonLife.task.create', compact('cohorts'));
    }

    public  function store(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'cohorts' => 'array|nullable',
            'cohorts.*' => 'exists:cohorts,id',
        ]);

        $task = Task::create($request->only('task_title', 'task_description'));

        if ($request->filled('cohorts')){
            $task->cohorts()->sync($request->cohorts);
        }

        return redirect()->route('tasks.index')->with('success', 'Tâche crée avec succès !');
    }

    public function edit (Task $task, $id)
    {
        $task = Task::findOrFail($id);
        $cohorts = Cohort::all();

        return view('pages.commonLife.task.edit', compact('task', 'cohorts'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'cohorts' => 'array',
            'cohorts.*' => 'exists:cohorts,id'
        ]);

        $task->update($request->only('task_title', 'task_description'));

        if ($request->has('cohorts')) {
            $task->cohorts()->sync($request->input('cohorts'));
            $task->touch();
        }

        return redirect()->route('tasks.index')->with('success', 'Tâche modifiée avec succès !');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès !');
    }
}
