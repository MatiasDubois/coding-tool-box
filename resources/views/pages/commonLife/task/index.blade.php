<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">

        </h1>
    </x-slot>

    <div>
        <div class="float-start">
            <h4 class="pb-3">My Tasks</h4>
        </div>
        <div class="float-end">
            <a href="{{route('tasks.index')}}" class="btn btn-info">
                Create Task
            </a>
        </div>
        <div class="clearfix"></div>
    </div>

    @foreach($tasks as $task)
        <div class="card mt-3">
            <h5 class="card-header">
                {{ $task->task_title }}
                <span class="badge rounded-pill bg-warning text-dark">
                    {{$task->created_at->diffForHumans() }}
                </span>
            </h5>
            <div class="card-body">
                <div class="card-text">
                    <div class="float-start">
                        {{ $task->task_description }}
                        <br>

{{--                        @if ($tast->status === "Todo")--}}
{{--                            <span class="badge rounded-pill bg-info text-dark">--}}
{{--                        Todo--}}
{{--                        </span>--}}
{{--                        @else--}}
                        <span class="badge rounded-pill bg-info text-white">
                        Done
                        </span>
{{--                        @endif--}}

                        <small>Last Updated - {{$task->updated_at->diffForHumans() }}
                        </small>
                    </div>
                    <div class="float-end">
                        <a href="{{route('tasks.index', $task->id)}}" class="btn btn-success">
                            Edit
                        </a>
                        <a href="{{route('tasks.index', $task->id)}}" class="btn btn-danger">
                            Delete
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

    @endforeach

</x-app-layout>
