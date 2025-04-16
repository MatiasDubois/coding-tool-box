<x-app-layout>
    <x-slot name="header">
        <h1 class="flex items-center gap-1 text-sm font-normal">
            <span class="text-gray-700">
                {{ __('Vie Commune') }}
            </span>
        </h1>
    </x-slot>
    <div class="max-w-4xl mx-auto py-10">
        <h3 class="text-lg font-medium mb-4">Bienvenue dans la section Vie Commune</h3>

        <a href="{{ route('tasks.index') }}"
           class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            Gérer les Tâches
        </a>
    </div>
</x-app-layout>
