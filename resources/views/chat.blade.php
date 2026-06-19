<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Discussion des participants') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="app">
                <dashboard-chat :current-user-id="{{ auth()->id() }}" :current-user-is-admin="{{ auth()->user()->is_admin ? 'true' : 'false' }}" height="h-[650px]"></dashboard-chat>
            </div>
        </div>
    </div>
</x-app-layout>
