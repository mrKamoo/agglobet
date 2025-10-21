<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Test Vue.js Integration
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Composants Vue.js</h3>

                    <!-- Zone pour monter l'application Vue -->
                    <div id="app">
                        <example-component></example-component>
                    </div>

                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">Test Alpine.js (toujours fonctionnel)</h4>
                        <div x-data="{ count: 0 }">
                            <button @click="count++" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                                Alpine Counter: <span x-text="count"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
