<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Tâches') }}
            </span>
        </h1>
    </x-slot>

    <!-- begin: grid -->
    <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
        <div class="lg:col-span-3">
            <div class="grid">
                <div class="card card-grid h-full min-w-full">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Liste des tâches</h3>

                        <div class="flex items-center gap-3">
                            <!-- Search bar -->
                            <form method="GET" action="{{ route('tasks.index') }}" class="flex items-center gap-2">
                                <div class="input input-sm max-w-48">
                                    <i class="ki-filled ki-magnifier"></i>
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une tâche" />
                                </div>
                                <button type="submit" class="btn btn-sm bg-blue-100 text-blue-700 rounded px-3">Rechercher</button>
                            </form>

                            <a href="{{ route('tasks.create') }}"
                               style="padding: 0.5rem 1rem;
                               background-color: #dbeafe;
                               color: #1d4ed8;
                               border-radius: 0.375rem;
                               text-decoration: none;">
                                Créer une tâche
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="scrollable-x-auto">
                            <table class="table table-border">
                                <thead>
                                <tr>
                                    <th>
                                        <a href="{{ route('tasks.index', ['sort' => 'task_title', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}"
                                           style="display: flex; align-items: center; gap: 4px;">
                                            Titre
                                            @if(request('sort') === 'title')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Description</th>
                                    <th>
                                        <a href="{{ route('tasks.index', ['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}">
                                            Créé le
                                            @if(request('sort') === 'created_at')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('tasks.index', ['sort' => 'updated_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}">
                                            Dernière modification
                                            @if(request('sort') === 'updated_at')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>

                                    <th>
                                        <a href="{{ route('tasks.index', ['sort' => 'promotions', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc' , 'search' => request('search')]) }}"
                                           style="display: flex; align-items: center; gap: 4px;">
                                            Promotions
                                            @if(request('sort') === 'promotions')
                                                <span>{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ $task->task_title }}</td>
                                        <td>{{ $task->task_description }}</td>
                                        <td>{{ $task->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $task->updated_at->diffForHumans() }}</td>
                                        <td>
                                            @if($task->cohorts->isNotEmpty())
                                                <ul style="list-style: disc; margin-left: 1rem; font-size: 0.875rem;">
                                                    @foreach($task->cohorts as $cohort)
                                                        <li>{{ $cohort->school->name }} – {{ $cohort->description }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <em>Aucune</em>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            @php
                                                $user = auth()->user();
                                                $pivot = $task->users->find($user->id)?->pivot;
                                                $isValidated = $pivot?->validated_at !== null;
                                            @endphp

                                        @if(in_array(auth()->user()->last_name, ['Student', 'Admin', 'Teacher']))
                                                <form method="POST" action="{{ route('tasks.toggleComplete', $task->id) }}" style="display:inline-block;" onsubmit="return confirmToggle('{{ $task->task_title }}', {{ $isValidated ? 'true' : 'false' }});">
                                                    @csrf
                                                    <button type="submit"
                                                            style="padding: 0.4rem 0.75rem;
                                                            background-color: {{ $isValidated ? '#bbf7d0' : '#fef3c7'}};
                                                            color: {{ $isValidated ? '#065f46' : '#92400e'}};
                                                            border-radius: 0.375rem;
                                                            margin-right: 0.5rem;
                                                            cursor: pointer;">
                                                        {{ $isValidated ? 'Terminé' : 'En Cours' }}
                                                    </button>
                                                </form>
                                            @endif

                                            <button onclick="toggleComment('{{ $task->id }}')"
                                                    style="padding: 0.4rem 0.75rem;
                                                    background-color: #f0f9ff;
                                                    color: #0284c7;
                                                    border: none;
                                                    border-radius: 0.375rem;
                                                    cursor: pointer;
                                                    margin-right: 0.5rem;">
                                                Commenter
                                            </button>


                                            <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                                <button type="submit"
                                                        style="padding: 0.4rem 0.75rem;
                                                        background-color: #fecaca;
                                                        color: #991b1b;
                                                        border: none;
                                                        border-radius: 0.375rem;
                                                        margin-right: 0.5rem;
                                                        cursor: pointer;">
                                                    Supprimer
                                                </button>
                                            </form>
                                            <button onclick="toggleEdit('{{ $task->id }}')"
                                                    style="padding: 0.4rem 0.75rem;
                                                    background-color: #dbeafe;
                                                    color: #1d4ed8;
                                                    border: none;
                                                    border-radius: 0.375rem;
                                                    cursor: pointer;">
                                                Modifier
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Formulaire de commentaire caché -->
                                    <tr id="comment-form-{{ $task->id }}" class="hidden">
                                        <td colspan="6">
                                            <!-- Affichage des commentaires existants -->
                                            @if($task->users->whereNotNull('pivot.comment')->isNotEmpty())
                                                <div class="mb-4 border-t pt-3">
                                                    <h4 class="text-sm font-semibold mb-2">Commentaires existants :</h4>
                                                    <ul class="space-y-2">
                                                        @foreach($task->users as $user)
                                                            @if($user->pivot->comment)
                                                                <li class="bg-gray-50 p-2 rounded">
                                                                    <strong>{{ $user->first_name }} {{ $user->last_name }}:</strong>
                                                                    <p class="text-sm text-gray-700">{{ $user->pivot->comment }}</p>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif


                                            <form method="POST" action="{{ route('tasks.comment', $task->id) }}">
                                                @csrf
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire :</label>
                                                <textarea name="comment" rows="3"
                                                          class="w-full border border-gray-300 rounded p-2 mb-3"
                                                          placeholder="Écrivez votre commentaire..."></textarea>

                                                <div class="flex justify-end gap-2">
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded">
                                                        Enregistrer
                                                    </button>
                                                    <button type="button" onclick="toggleComment('{{ $task->id }}')"
                                                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded">
                                                        Annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Formulaire de modification caché -->
                                    <tr id="edit-form-{{ $task->id }}" class="hidden">
                                        <td colspan="6">
                                            <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <input type="text" name="task_title" value="{{ $task->task_title }}"
                                                       style="width: 100%; margin-bottom: 0.5rem;
                                                              padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" required>

                                                <textarea name="task_description" rows="3"
                                                          style="width: 100%; margin-bottom: 0.5rem;
                                                                 padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">{{ $task->task_description }}</textarea>

                                                <label style="font-weight: bold;">Promotions associées</label>
                                                <select id="cohort-select-{{ $task->id }}" name="cohorts[]" multiple
                                                        style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem; height: 200px; font-size: 0.875rem;">
                                                    @foreach($cohorts as $cohort)
                                                        <option value="{{ $cohort->id }}"
                                                                style="padding: 0.5rem; margin-bottom: 0.3rem;">
                                                            {{ $cohort->description }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                                    <button type="submit"
                                                            style="padding: 0.5rem 1rem;
                                                                   background-color: #bbf7d0;
                                                                   color: #065f46;
                                                                   border-radius: 0.375rem;
                                                                   border: none;">
                                                        Enregistrer
                                                    </button>
                                                    <button type="button" onclick="toggleEdit('{{ $task->id }}')"
                                                            style="padding: 0.5rem 1rem;
                                                                   background-color: #e5e7eb;
                                                                   color: #1f2937;
                                                                   border-radius: 0.375rem;
                                                                   border: none;">
                                                        Annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div style="text-align: center; margin-top: 2rem;">
                            {{ $tasks->withQueryString()->links() }}
                        </div>

                        <!-- Footer de pagination personnalisé -->
                        <div class="card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-gray-600 text-2sm font-medium flex mt-4">
                            <div class="flex items-center gap-2 order-2 md:order-1">
                                Afficher
                                <form method="GET" action="{{ route('tasks.index') }}">
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
                                    Page {{ $tasks->currentPage() }} sur {{ $tasks->lastPage() }}
                                </span>
                                <div class="pagination">
                                    {{ $tasks->withQueryString()->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleEdit(id) {
            const row = document.getElementById('edit-form-' + id);
            row.classList.toggle('hidden');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('select[multiple]').forEach(function(select) {
                select.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    const option = e.target;
                    if (option.tagName.toLowerCase() === 'option') {
                        option.selected = !option.selected;
                        updateOptionStyles(select);
                    }
                });

                // Appliquer les styles initiaux
                updateOptionStyles(select);
            });

            function updateOptionStyles(select) {
                Array.from(select.options).forEach(function (option) {
                    option.style.backgroundColor = option.selected ? '#e5e7eb' : 'white';
                    option.style.color = '#111827'; // texte noir (tailwind: text-gray-900)
                    option.style.fontWeight = '500';
                });
            }
        });
    </script>

    <script>
        function confirmToggle(taskTitle, alreadyValidated) {
            if (alreadyValidated) {
                return confirm("Souhaitez-vous vraiment annuler la validation de cette tâche : \"" + taskTitle + "\" ?");
            }
            return true; // pas de confirmation si c’est une première validation
        }
    </script>

    <script>
        function toggleComment(id) {
            const row = document.getElementById('comment-form-' + id);
            row.classList.toggle('hidden');
        }
    </script>

</x-app-layout>
