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
    // Main Function
    public function index(Request $request)
    {
        $allowedSorts = ['task_title', 'created_at', 'updated_at', 'promotions'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'updated_at';
        $order = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $search = $request->get('search');
        $perPage = request('perpage', 10);

        $user = auth()->user();
        $school = $user->school();
        $role = $school?->pivot->role ?? 'student';

        $query = Task::with(['cohorts.school', 'users' => function ($q) {
            $q->wherePivotNull('validated_at');
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('task_title', 'like', '%' . $search . '%')
                    ->orWhere('task_description', 'like', '%' . $search . '%');
            });
        }

        if ($role === 'student') {
            $cohortIds = $user->cohorts_from_tasks->pluck('id');

            $query->whereHas('cohorts', function ($q) use ($cohortIds) {
                $q->whereIn('cohorts.id', $cohortIds);
            });

            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereNull('task_user.validated_at');
            });
        } else {
            $query->whereDoesntHave('users', function ($q) {
                $q->whereNotNull('task_user.validated_at');
            });
        }

        if ($sort === 'promotions') {
            $query = $query->withCount('cohorts')->orderBy('cohorts_count', $order);
        } else {
            $query = $query->orderBy($sort, $order);
        }

        $tasks = $query->paginate($perPage)->withQueryString();
        $cohorts = Cohort::all();

        return view('pages.commonLife.task.index', compact('tasks', 'cohorts', 'sort', 'order'));
    }

    // Function to show all task
    public function show(Task $task)
    {
        $user = auth()->user();
        $school = $user->school();
        $role = $school?->pivot->role ?? 'student';
    }

    // Function to create a task
    public function create()
    {
        $cohorts = Cohort::all();
        return view('pages.commonLife.task.create', compact('cohorts'));
    }

    // Function to store a task
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

    // Function to edit a task
    public function edit (Task $task)
    {
        $cohorts = Cohort::all();
        return view('pages.commonLife.task.edit', compact('task', 'cohorts'));
    }

    // Function to update a task
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

    // Function to delete a task
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès !');
    }

    //Function to toggle the status of the task (not working well)
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

    // Function to create a comment
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

    // Function for the history pages
    public function history(Request $request)
    {
        $user = auth()->user();
        $role = $user->school()?->pivot->role ?? 'student';

        $allowedSorts = ['task_title', 'created_at', 'updated_at', 'promotions'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'updated_at';
        $order = $request->get('direction') === 'asc' ? 'asc' : 'desc';
        $search = $request->get('search');
        $perPage = $request->get('perpage', 10);

        $cohorts = Cohort::all(); // utile pour les filtres futurs si besoin

        if ($role === 'student') {
            // Récupère les tâches validées liées aux promotions de l’étudiant
            $validatedTasks = $user->tasks()
                ->wherePivotNotNull('validated_at')
                ->whereHas('cohorts', function ($q) use ($user) {
                    $q->whereIn('cohorts.id', $user->cohorts_from_tasks->pluck('id'));
                })
                ->with(['cohorts.school'])
                ->orderByDesc('pivot_validated_at')
                ->paginate($perPage);

            $items = $validatedTasks;
        } else {
            // Pour enseignants/admins
            $query = Task::with(['cohorts.school', 'users']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('task_title', 'like', "%$search%")
                        ->orWhere('task_description', 'like', "%$search%");
                });
            }

            $query->whereHas('users', function ($q) {
                $q->whereNotNull('task_user.validated_at');
            });

            if ($sort === 'promotions') {
                $query->withCount('cohorts')->orderBy('cohorts_count', $order);
            } else {
                $query->orderBy($sort, $order);
            }

            $validatedTasks = $query->paginate($perPage);
            $items = $validatedTasks;
        }
        return view('pages.commonLife.task.history', compact('items', 'cohorts', 'sort', 'order', 'role'));
    }

    // Function to remove a task in the history page (also not working well)
    public function unvalidate(Task $task, $userId)
    {
        $task->users()->updateExistingPivot($userId, [
            'validated_at' => null,
            'comment' => null,
        ]);

        return back()->with('success', 'Validation annulée avec succès.');
    }
}
