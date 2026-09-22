@if(auth()->user()->hasRole('Super Admin'))
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{{ config('app.name', 'Admin Panel') }}</title>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            @livewireStyles
        </head>
        <body class="font-sans antialiased text-slate-800 bg-green-50 overflow-hidden">
            <livewire:admin-dashboard />
            @livewireScripts
        </body>
    </html>
@else
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @role('Coordinator')
                    <livewire:coordinator-dashboard />
                @endrole
                
                @role('Supervisor')
                    <livewire:supervisor-dashboard />
                @endrole

                @role('Student')
                    <livewire:student-dashboard />
                @endrole
            </div>
        </div>
    </x-app-layout>
@endif
