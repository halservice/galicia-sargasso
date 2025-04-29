<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('images/webicon.png') }}" type="image/png">
</head>
<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

{{-- NAVBAR mobile only --}}
<x-nav sticky class="lg:hidden">
    <x-slot:brand>
        <x-app-brand />
    </x-slot:brand>
    <x-slot:actions>
        <label for="main-drawer" class="lg:hidden me-3">
            <x-icon name="o-bars-3" class="cursor-pointer" />
        </label>
    </x-slot:actions>
</x-nav>

{{-- MAIN --}}
<x-main full-width>
    @if($user = auth()->user())

    {{-- SIDEBAR --}}
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100">

        {{-- BRAND --}}
        <x-app-brand class="p-5 pt-3" />

        {{-- MENU --}}
        <x-menu activate-by-route>

            {{-- User --}}

{{--                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">--}}
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                    <x-slot:actions>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-circle btn-ghost btn-xs" title="logoff">
                                <x-icon name="o-power" />
                            </button>
                        </form>
                    </x-slot:actions>

                </x-list-item>

                <x-menu-separator />

            <x-menu-item title="Source Code Generator" icon="o-code-bracket" link="/source-code-generator" />
            <x-menu-item title="Formal Model Generator"  icon="o-document" link="/formal-model-generator" />
            <x-menu-item title="Code Validation"  icon="o-check-badge" link="/code-validation" />
            <x-menu-item title="Feedback" icon="o-chart-bar-square" link="/feedback" />
            <x-menu-item title="Settings" icon="o-cog-6-tooth" link="/settings" />
            <x-menu-item title="Statistics"  icon="o-chart-pie" link="/statistics" />
            <x-menu-item title="Logs" icon="o-circle-stack" link="/logs" />

        </x-menu>
    </x-slot:sidebar>
    @endif

    {{-- The `$slot` goes here --}}
    <x-slot:content>
        {{ $slot }}
    </x-slot:content>
</x-main>

{{--  TOAST area --}}
<x-toast />
</body>
</html>
