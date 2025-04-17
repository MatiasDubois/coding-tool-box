<x-app-layout>
    <x-slot name="header">
        <!-- Tu peux ajouter du contenu ici si besoin -->
    </x-slot>

    <div style="
        max-width: 720px;
        margin: 3rem auto;
        background-color: white;
        padding: 2rem;
        border-radius: 0.75rem;
        border: 2px solid #d1d5db;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
        font-size: 1.1rem;
    ">
        <h2 style="
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2rem;
        ">
            Créer une tâche
        </h2>

        <form method="POST" action="{{ route('tasks.store') }}">
            @csrf

            <!-- Titre -->
            <div style="margin-bottom: 1.5rem;">
                <label style="
                    display: block;
                    font-weight: 600;
                    margin-bottom: 0.75rem;
                    color: #111827;
                ">
                    Titre
                </label>
                <input
                    type="text"
                    name="task_title"
                    required
                    style="
                        width: 100%;
                        padding: 0.75rem 1rem;
                        font-size: 1.05rem;
                        border: 1px solid #d1d5db;
                        border-radius: 0.5rem;
                        outline: none;
                    "
                >
            </div>

            <!-- Description -->
            <div style="margin-bottom: 1.5rem;">
                <label style="
                    display: block;
                    font-weight: 600;
                    margin-bottom: 0.75rem;
                    color: #111827;
                ">
                    Description
                </label>
                <textarea
                    name="task_description"
                    rows="5"
                    style="
                        width: 100%;
                        padding: 0.75rem 1rem;
                        font-size: 1.05rem;
                        border: 1px solid #d1d5db;
                        border-radius: 0.5rem;
                        outline: none;
                    "
                ></textarea>
            </div>

            <!-- Boutons -->
            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('tasks.index') }}"
                   style="
                       padding: 0.75rem 1.25rem;
                       background-color: #fee2e2;
                       color: #b91c1c;
                       font-weight: 500;
                       font-size: 1rem;
                       border-radius: 0.5rem;
                       text-decoration: none;
                       transition: background-color 0.2s;
                   "
                   onmouseover="this.style.backgroundColor='#fecaca'"
                   onmouseout="this.style.backgroundColor='#fee2e2'">
                    Annuler
                </a>
                <button
                    type="submit"
                    style="
                        padding: 0.75rem 1.25rem;
                        background-color: #dbeafe;
                        color: #1d4ed8;
                        font-weight: 500;
                        font-size: 1rem;
                        border: none;
                        border-radius: 0.5rem;
                        cursor: pointer;
                        transition: background-color 0.2s;
                    "
                    onmouseover="this.style.backgroundColor='#bfdbfe'"
                    onmouseout="this.style.backgroundColor='#dbeafe'">
                    Créer
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
