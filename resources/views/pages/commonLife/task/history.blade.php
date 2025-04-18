<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700"></span>
        </h1>
    </x-slot>

    @if($role === 'student')
        <p class="text-sm text-gray-600 mb-4">Voici vos tâches validées dans votre promotion.</p>
    @endif

    <!-- begin: grid -->
    <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
        <div class="lg:col-span-3">
            <div class="grid">
                <div class="card card-grid h-full min-w-full">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="{{ route('tasks.index') }}" class="card-title">
                            Liste des tâches
                        </a>

                        <h3 class="card-title">Historique</h3>

                        <div class="flex items-center gap-3">
                            <!-- Search bar -->
                            <form method="GET" action="{{ route('tasks.history') }}" class="flex items-center gap-2">
                                <div class="input input-sm max-w-48">
                                    <i class="ki-filled ki-magnifier"></i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une tâche" />
                                </div>
                                <button type="submit" class="btn btn-sm bg-blue-100 text-blue-700 rounded px-3">Rechercher</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="scrollable-x-auto">
                            <table class="table table-border">
                                <thead>
                                <tr>
                                    <th>
                                        <a href="{{ route('tasks.history', ['sort' => 'task_title', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}"
                                           style="display: flex; align-items: center; gap: 4px;">
                                            Titre
                                            @if(request('sort') === 'task_title')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Description</th>
                                    <th>
                                        <a href="{{ route('tasks.history', ['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}">
                                            Créé le
                                            @if(request('sort') === 'created_at')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('tasks.history', ['sort' => 'updated_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}">
                                            Dernière modification
                                            @if(request('sort') === 'updated_at')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Date de validation</th>
                                    <th>
                                        <a href="{{ route('tasks.history', ['sort' => 'promotions', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}"
                                           style="display: flex; align-items: center; gap: 4px;">
                                            Promotions
                                            @if(request('sort') === 'promotions')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    @if($role !== 'student')
                                        <th>Actions</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $task)
                                        @php
                                            $validatedUser = $task->users->firstWhere('pivot.validated_at', '!=', null);
                                        @endphp

                                        <tr>
                                            <td>{{ $task->task_title }}</td>
                                            <td>{{ $task->task_description }}</td>
                                            <td>{{ $task->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $task->updated_at->format('d/m/Y') }}</td>
                                            <td>{{ optional($validatedUser?->pivot->validated_at)->format('d/m/Y') }}</td>
                                            <td>{{ $task->cohorts?->pluck('description')->join(', ') ?? '—' }}</td>

                                            @if($role !== 'student')
                                                <td>
                                                    @if($validatedUser)
                                                        <form method="POST" action="{{ route('tasks.unvalidate', [$task->id, $validatedUser->id]) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm bg-red-100 text-red-700 hover:bg-red-200">Annuler</button>
                                                        </form>
                                                    @else
                                                        <span class="text-gray-400 italic">Non validée</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                @if($role === 'student')
                                                    Aucune tâche validée pour votre promo.
                                                @else
                                                    Aucune tâche trouvée.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div style="text-align: center; margin-top: 2rem;">
                            {{ $items->withQueryString()->links() }}
                        </div>

                        <!-- Footer de pagination personnalisé -->
                        <div class="card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-gray-600 text-2sm font-medium flex mt-4">
                            <div class="flex items-center gap-2 order-2 md:order-1">
                                Afficher
                                <form method="GET" action="{{ route('tasks.history') }}">
                                    <select class="select select-sm w-16" name="perpage" onchange="this.form.submit()">
                                        @foreach([10, 25, 50, 100] as $size)
                                            <option value="{{ $size }}" {{ request('perpage', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                        @endforeach
                                    </select>
                                    par page
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                                </form>
                            </div>

                            <div class="flex items-center gap-4 order-1 md:order-2">
                                <span>
                                    Page {{ $items->currentPage() }} sur {{ $items->lastPage() }}
                                </span>
                                <div class="pagination">
                                    {{ $items->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
