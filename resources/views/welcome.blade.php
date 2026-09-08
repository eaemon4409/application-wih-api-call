<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>AI Website Summarizer - Groq & OpenRouter</title>
        <meta name="description" content="Instantly extract, analyze and summarize any website URL with Groq and OpenRouter AI models." />
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased selection:bg-emerald-500 selection:text-zinc-950 font-sans">
        <!-- Top Navigation -->
        <header class="border-b border-zinc-800/80 bg-zinc-900/60 backdrop-blur-md sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-zinc-950 shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform duration-200">
                        <svg class="w-5 h-5 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-base tracking-tight text-white group-hover:text-emerald-400 transition-colors">
                            Summarix<span class="text-emerald-400">AI</span>
                        </span>
                        <span class="text-[10px] text-zinc-400 uppercase tracking-widest -mt-1 font-mono">
                            Groq &bull; OpenRouter
                        </span>
                    </div>
                </a>

                <nav class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700 transition-all duration-150"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="px-3.5 py-1.5 text-xs font-medium text-zinc-400 hover:text-white transition-colors"
                            >
                                Log in
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-white border border-zinc-700 hover:border-zinc-600 transition-all duration-150"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="py-6">
            <livewire:website-summarizer />
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-800/60 py-8 mt-12 text-center text-xs text-zinc-500">
            <div class="max-w-7xl mx-auto px-4 space-y-2">
                <p class="font-medium text-zinc-400">
                    SummarixAI &bull; High-Performance Website Summarizer
                </p>
                <p>
                    Powered by <span class="text-emerald-400 font-medium">Groq LPU</span> & <span class="text-indigo-400 font-medium">OpenRouter AI</span> &bull; Built with Laravel 13 & Livewire
                </p>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
