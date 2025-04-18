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
        $allowedSorts = ['task_title', 'created_at', 'updated_at', 'promotions'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'updated_at';
        $order = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $search = $request->get('search');

        $query = Task::with(['cohorts.school', 'users']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('task_title', 'like', '%' . $search . '%')
                    ->orWhere('task_description', 'like', '%' . $search . '%');
            });
        }

        if ($sort === 'promotions') {
            $query = $query->withCount('cohorts')->orderBy('cohorts_count', $order);
        } else {
            $query = $query->orderBy($sort, $order);
        }

        $perPage = request('perpage', 10);
        $tasks = $query->paginate($perPage)->withQueryString();
        $tasks->load('users');
        $cohorts = Cohort::all();

        return view('pages.commonLife.task.index', compact('tasks', 'cohorts', 'sort', 'order'));
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

    public function toggleComplete(Task $task)
    {
        $user = auth()->user();
        $pivot = $task->users()->where('user_id', $user->id)->first()?->pivot;

        if ($pivot && $pivot->validated_at !== null) {
            $task->users()->updateExistingPivot($user->id, [
                'validated_at' => null,
            ]);

            return back()->with('status', 'Validation annulée.');
        }

        $task->users()->syncWithoutDetaching([
            $user->id => ['validated_at' => now()]
        ]);

        return back()->with('status', 'Tâche marquée comme terminée.');
    }

    public function comment(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $userId = auth()->id();

        $task->users()->syncWithoutDetaching([
            $userId => ['comment' => $request->comment]
        ]);

        return redirect()->back()->with('success', 'Commentaire ajouté avec succès.');
    }
}
