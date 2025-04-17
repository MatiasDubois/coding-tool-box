<x-app-layout>
    <x-slot name="header"></x-slot>

    @php
        $sort = request('sort', 'updated_at');
        $order = request('order', 'desc');

        $createdOrder = ($sort === 'created_at' && $order === 'asc') ? 'desc' : 'asc';
        $updatedOrder = ($sort === 'updated_at' && $order === 'asc') ? 'desc' : 'asc';

        $arrow = fn($field, $currentOrder) =>
            ($sort === $field)
                ? ($currentOrder === 'asc' ? '↑' : '↓')
                : '';
    @endphp

        <!-- Conteneur principal -->
    <div style="max-width: 800px; margin: 2rem auto; background-color: white; padding: 1.5rem; border-radius: 0.5rem; border: 2px solid #d1d5db; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

        <!-- Titre -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: #111827;">Liste des tâches</h2>
        </div>

        <!-- Barre d'actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <!-- Bouton de création -->
            <a href="{{ route('tasks.create') }}"
               style="padding: 0.5rem 1rem;
              background-color: #dbeafe;
              color: #1d4ed8;
              border-radius: 0.375rem;
              text-decoration: none;
              white-space: nowrap;">
                Créer une tâche
            </a>

            <!-- Boutons de tri -->
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span style="font-size: 0.875rem; color: #6b7280;">Trier par :</span>

                <!-- Tri par date de création -->
                <form method="GET" action="{{ route('tasks.index') }}">
                    <input type="hidden" name="sort" value="created_at">
                    <input type="hidden" name="order" value="{{ $createdOrder }}">
                    <button type="submit"
                            style="padding: 0.4rem 0.75rem;
                           background-color: #f3f4f6;
                           color: #374151;
                           border: 1px solid #d1d5db;
                           border-radius: 0.375rem;
                           font-size: 0.875rem;
                           cursor: pointer;">
                        Création {{ $arrow('created_at', $order) }}
                    </button>
                </form>

                <!-- Tri par dernière modification -->
                <form method="GET" action="{{ route('tasks.index') }}">
                    <input type="hidden" name="sort" value="updated_at">
                    <input type="hidden" name="order" value="{{ $updatedOrder }}">
                    <button type="submit"
                            style="padding: 0.4rem 0.75rem;
                           background-color: #f3f4f6;
                           color: #374151;
                           border: 1px solid #d1d5db;
                           border-radius: 0.375rem;
                           font-size: 0.875rem;
                           cursor: pointer;">
                        Modification {{ $arrow('updated_at', $order) }}
                    </button>
                </form>
            </div>
        </div>


        <!-- Liste des tâches -->
        @foreach($tasks as $task)
            <div style="border: 1px solid #d1d5db;
                        border-radius: 0.5rem;
                        padding: 1rem;
                        margin-bottom: 1rem;
                        background-color: white;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

                <!-- Titre + date de création -->
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <h3 style="font-size: 1.25rem; font-weight: bold; color: #111827;">{{ $task->task_title }}</h3>
                    <div style="text-align: right;">
                        <div style="font-size: 0.875rem; color: #6b7280;">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <!-- Description + durée depuis modif -->
                <div style="display: flex; justify-content: space-between; align-items: end; margin-top: 0.5rem;">
                    <p style="color: #374151;">{{ $task->task_description }}</p>
                    <small style="color: #6b7280;">Modifié {{ $task->updated_at->diffForHumans() }}</small>
                </div>

                <!-- Promotions associées -->
                @if($task->cohorts->isNotEmpty())
                    <div style="margin-top: 0.5rem;">
                        <strong style="font-size: 0.875rem; color: #4b5563;">Promotions :</strong>
                        <ul style="list-style: disc; margin-left: 1.5rem; color: #374151; font-size: 0.875rem;">
                            @foreach($task->cohorts as $cohort)
                                <li>{{ $cohort->school->name }} – {{ $cohort->description }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <!-- Actions -->
                <div style="text-align: right; margin-top: 1rem;">
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
                </div>

                <!-- Formulaire de modification (caché) -->
                <div id="edit-form-{{ $task->id }}" class="hidden" style="margin-top: 1rem;">
                    <form method="POST" action="{{ route('tasks.update', $task->id) }}">
                        @csrf
                        @method('PUT')

                        <input type="text" name="task_title" value="{{ $task->task_title }}"
                               style="width: 100%; margin-bottom: 0.5rem;
                                      padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" required>

                        <textarea name="task_description" rows="3"
                                  style="width: 100%; margin-bottom: 0.5rem;
                                         padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">{{ $task->task_description }}</textarea>

                        <label for="cohorts" class="block text-sm font-medium text-gray-700">Promotions associées</label>
                        <select name="cohorts[]" multiple>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}"
                                    {{ $task->cohorts->contains($cohort->id) ? 'selected' : '' }}>
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
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div style="text-align: center; margin-top: 2rem;">
            {{ $tasks->withQueryString()->links() }}
        </div>

    </div>

    <!-- Script pour afficher/masquer le formulaire de modification -->
    <script>
        function toggleEdit(id) {
            const el = document.getElementById('edit-form-' + id);
            el.classList.toggle('hidden');
        }
    </script>
</x-app-layout>
