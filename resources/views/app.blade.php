@php
    $dietaryTags = ['vegan', 'no_sugar', 'no_cholesterol', 'gluten_free', 'no_lactose'];
    $dietaryLabels = [
        'vegan' => 'Vegan',
        'no_sugar' => 'No sugar',
        'no_cholesterol' => 'No cholesterol',
        'gluten_free' => 'Gluten free',
        'no_lactose' => 'No lactose',
    ];
@endphp

@guest
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RestaurantAI-Brigade') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <div class="relative isolate min-h-screen">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 left-1/2 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] translate-x-1/3 rounded-full bg-emerald-500/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-12">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                            <svg viewBox="0 0 24 24" class="h-6 w-6 text-indigo-200" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V3m0 3a9 9 0 100 18 9 9 0 000-18zm0 0c2.21 0 4 1.79 4 4 0 3-4 7-4 7s-4-4-4-7c0-2.21 1.79-4 4-4z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-slate-300">RestaurantAI-Brigade</div>
                            <div class="text-xl font-semibold tracking-tight">Smart plate recommendations</div>
                        </div>
                    </div>
                    <a href="/docs" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm hover:bg-white/10">API Docs</a>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <h1 class="text-3xl font-semibold tracking-tight">Welcome back</h1>
                        <p class="mt-2 text-sm text-slate-300">
                            Sign in or create an account to manage your dietary profile and see personalized recommendations.
                        </p>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <div class="text-sm font-semibold">Fast demo</div>
                                <div class="mt-1 text-xs text-slate-300">Use seeded accounts (after migrate --seed).</div>
                                <div class="mt-3 rounded-xl bg-black/30 p-3 text-xs">
                                    <div><span class="text-slate-400">Admin:</span> admin@demo.test</div>
                                    <div><span class="text-slate-400">User:</span> user@demo.test</div>
                                    <div class="mt-1"><span class="text-slate-400">Password:</span> password123</div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <div class="text-sm font-semibold">Async analysis</div>
                                <div class="mt-1 text-xs text-slate-300">Run the queue worker for AI jobs.</div>
                                <div class="mt-3 rounded-xl bg-black/30 p-3 text-xs font-mono text-slate-100">php artisan queue:work</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-6"
                         x-data="{ tab: '{{ (old('name') || $errors->has('name') || $errors->has('password_confirmation')) ? 'register' : 'login' }}' }">
                        <div class="flex gap-2 rounded-2xl border border-white/10 bg-black/20 p-1">
                            <button type="button"
                                    class="flex-1 rounded-xl px-3 py-2 text-sm"
                                    :class="tab === 'login' ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white'"
                                    @click="tab='login'">
                                Login
                            </button>
                            <button type="button"
                                    class="flex-1 rounded-xl px-3 py-2 text-sm"
                                    :class="tab === 'register' ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white'"
                                    @click="tab='register'">
                                Register
                            </button>
                        </div>

                        <div class="mt-4">
                            <x-auth-session-status class="mb-4" :status="session('status')" />
                        </div>

                        <div x-show="tab === 'login'" x-cloak>
                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="email" :value="__('Email')" class="text-slate-200" />
                                    <x-text-input id="email" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="password" :value="__('Password')" class="text-slate-200" />
                                    <x-text-input id="password" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="password" name="password" required autocomplete="current-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <label class="inline-flex items-center text-sm text-slate-300">
                                        <input type="checkbox" class="rounded border-white/10 bg-black/20 text-indigo-400 shadow-sm focus:ring-white/10" name="remember">
                                        <span class="ms-2">Remember me</span>
                                    </label>

                                    <a class="text-sm text-slate-300 hover:text-white" href="{{ route('password.request') }}">
                                        Forgot password?
                                    </a>
                                </div>

                                <x-primary-button class="w-full justify-center bg-indigo-500 hover:bg-indigo-400 focus:bg-indigo-400 active:bg-indigo-500">
                                    {{ __('Login') }}
                                </x-primary-button>
                            </form>
                        </div>

                        <div x-show="tab === 'register'" x-cloak>
                            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="name" :value="__('Name')" class="text-slate-200" />
                                    <x-text-input id="name" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="text" name="name" :value="old('name')" required autocomplete="name" />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="email_register" :value="__('Email')" class="text-slate-200" />
                                    <x-text-input id="email_register" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="email" name="email" :value="old('email')" required autocomplete="username" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="password_register" :value="__('Password')" class="text-slate-200" />
                                    <x-text-input id="password_register" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="password" name="password" required autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-200" />
                                    <x-text-input id="password_confirmation" class="mt-1 block w-full bg-black/20 text-slate-100 border-white/10 focus:border-white/20 focus:ring-white/10"
                                                  type="password" name="password_confirmation" required autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>

                                <x-primary-button class="w-full justify-center bg-emerald-500 hover:bg-emerald-400 focus:bg-emerald-400 active:bg-emerald-500">
                                    {{ __('Create account') }}
                                </x-primary-button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="mt-10 text-center text-xs text-slate-400">
                    Built with Laravel Breeze • Sanctum • Queue Jobs
                </div>
            </div>
        </div>
    </body>
</html>
@endguest

@auth
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="text-sm text-gray-500">RestaurantAI-Brigade</div>
                <div class="text-xl font-semibold text-gray-900">Dashboard</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="/docs" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm hover:bg-gray-50">API Docs</a>
                @if(auth()->user()->is_admin)
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Admin</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="{ tab: 'plates' }">
            @if(session('status') === 'dietary-profile-updated')
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Dietary profile updated.
                </div>
            @elseif(session('status') === 'recommendation-queued')
                <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                    Recommendation analysis queued. Run <span class="font-mono">php artisan queue:work</span> to process.
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <button type="button" class="rounded-xl px-4 py-2 text-sm ring-1 ring-gray-200"
                        :class="tab==='plates' ? 'bg-gray-900 text-white ring-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        @click="tab='plates'">
                    Plates
                </button>
                <button type="button" class="rounded-xl px-4 py-2 text-sm ring-1 ring-gray-200"
                        :class="tab==='categories' ? 'bg-gray-900 text-white ring-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        @click="tab='categories'">
                    Categories
                </button>
                <button type="button" class="rounded-xl px-4 py-2 text-sm ring-1 ring-gray-200"
                        :class="tab==='profile' ? 'bg-gray-900 text-white ring-gray-900' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        @click="tab='profile'">
                    Dietary Profile
                </button>
            </div>

            <div class="mt-6" x-show="tab==='plates'">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach(($plates ?? collect()) as $plate)
                        @php
                            $rec = $plate->recommendations->first();
                            $score = $rec?->score;
                            $status = $rec?->status;
                            $badgeClasses = 'bg-gray-100 text-gray-700 ring-gray-200';
                            if ($status === 'processing') $badgeClasses = 'bg-indigo-50 text-indigo-700 ring-indigo-200';
                            if ($status === 'ready' && $score !== null) {
                                if ($score >= 80) $badgeClasses = 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                                elseif ($score >= 50) $badgeClasses = 'bg-amber-50 text-amber-700 ring-amber-200';
                                else $badgeClasses = 'bg-rose-50 text-rose-700 ring-rose-200';
                            }
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-base font-semibold text-gray-900">{{ $plate->name }}</div>
                                    <div class="mt-1 text-sm text-gray-600">
                                        {{ $plate->category?->name ?? '—' }} • ${{ number_format((float) $plate->price, 2) }}
                                    </div>
                                </div>
                                <div class="shrink-0 rounded-full px-3 py-1 text-xs font-medium ring-1 {{ $badgeClasses }}">
                                    @if(!$rec)
                                        Not analyzed
                                    @elseif($status === 'processing')
                                        processing
                                    @else
                                        {{ (int) $score }}%
                                    @endif
                                </div>
                            </div>

                            @if($plate->description)
                                <div class="mt-3 text-sm text-gray-700">{{ $plate->description }}</div>
                            @endif

                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($plate->ingredients as $ingredient)
                                    <span class="rounded-full bg-gray-50 px-3 py-1 text-xs text-gray-700 ring-1 ring-gray-200">
                                        {{ $ingredient->name }}
                                    </span>
                                @endforeach
                            </div>

                            @if($rec?->warning_message)
                                <div class="mt-4 rounded-xl bg-gray-50 p-3 text-xs text-gray-700 ring-1 ring-gray-200">
                                    {{ $rec->warning_message }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('app.recommendations.analyze', ['plate' => $plate->id]) }}" class="mt-4">
                                @csrf
                                <x-primary-button class="w-full justify-center">
                                    Analyze compatibility
                                </x-primary-button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6" x-show="tab==='categories'">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach(($categories ?? collect()) as $category)
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-base font-semibold text-gray-900">{{ $category->name }}</div>
                                    <div class="mt-1 text-sm text-gray-600">{{ $category->description ?? '—' }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($category->color)
                                        <span class="h-3 w-3 rounded-full ring-1 ring-gray-200" style="background: {{ $category->color }}"></span>
                                    @endif
                                    <span class="rounded-full px-3 py-1 text-xs font-medium ring-1 {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-700 ring-gray-200' }}">
                                        {{ $category->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6" x-show="tab==='profile'">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-base font-semibold text-gray-900">Dietary tags</div>
                            <div class="mt-1 text-sm text-gray-600">These restrictions affect recommendation scoring.</div>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-700 ring-1 ring-gray-200">
                            Queue: <span class="font-mono">php artisan queue:work</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('app.dietary-profile') }}" class="mt-6">
                        @csrf
                        @php $selected = collect($dietaryProfile->dietary_tags ?? [])->values()->all(); @endphp

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($dietaryTags as $tag)
                                <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800">
                                    <input type="checkbox"
                                           name="dietary_tags[]"
                                           value="{{ $tag }}"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           @checked(in_array($tag, $selected, true))>
                                    <span class="font-medium">{{ $dietaryLabels[$tag] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            <x-primary-button>
                                Save profile
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@endauth

