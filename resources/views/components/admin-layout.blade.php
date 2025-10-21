<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Admin Navigation -->
            <nav class="bg-gray-800 border-b border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="text-white font-bold text-lg">
                                    Admin - Agglobet
                                </a>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.users.*') ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    Utilisateurs
                                </a>
                                <a href="{{ route('admin.seasons.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.seasons.*') ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    Saisons
                                </a>
                                <a href="{{ route('admin.games.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.games.*') ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    Matchs
                                </a>
                                <a href="{{ route('admin.results.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.results.*') ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    Résultats
                                </a>
                                 <a href="{{ route('admin.sync.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.sync.*')
   ? 'border-indigo-400 text-white' : 'border-transparent text-gray-100 hover:text-white hover:border-gray-400' }} text-sm font-medium
   leading-5 focus:outline-none transition duration-150 ease-in-out">
      Synchronisation
  </a>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <a href="{{ route('home') }}" class="text-gray-100 hover:text-white text-sm mr-4">
                                ← Retour au site
                            </a>
                            <span class="text-gray-100 text-sm">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @if(session('success'))
            <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('error') }}
            </div>
        @endif
    </body>
</html>
