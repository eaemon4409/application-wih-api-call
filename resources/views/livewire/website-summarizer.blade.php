<div class="w-full max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <!-- Header / Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-900/90 to-zinc-950 border border-zinc-800 p-8 sm:p-12 mb-8 shadow-2xl">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-zinc-800/80 border border-zinc-700/70 text-zinc-300">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Powered by Dual AI Engine
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Summarize <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-indigo-400">Any Website</span> in Seconds
                </h1>
                <p class="text-zinc-400 text-sm sm:text-base leading-relaxed">
                    Instantly extract clean, readable text from any URL and generate structured summaries with ultra-fast Groq or multi-model OpenRouter.
                </p>
            </div>

            <!-- API Keys Status / Trigger Button -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button
                    wire:click="openApiKeyModal"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 border bg-zinc-800/80 hover:bg-zinc-700/80 border-zinc-700 text-zinc-200 shadow-sm hover:border-zinc-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                >
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>API Keys</span>
                    <div class="flex items-center gap-1.5 ml-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $hasGroqKey ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            ⚡ Groq: {{ $hasGroqKey ? 'Active' : 'Setup' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $hasOpenRouterKey ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            🌐 OpenRouter: {{ $hasOpenRouterKey ? 'Active' : 'Setup' }}
                        </span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if ($errorMessage)
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-3 animate-fade-in">
            <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <p class="font-medium text-rose-200">Execution Error</p>
                <p class="mt-0.5">{{ $errorMessage }}</p>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-rose-400 hover:text-rose-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if ($successMessage)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', null)" class="text-emerald-400 hover:text-emerald-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <!-- Main Generator Form Card -->
    <div class="bg-zinc-900/90 border border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-xl backdrop-blur-md mb-8">
        <form wire:submit.prevent="summarize" class="space-y-6">
            <!-- URL Input Field -->
            <div>
                <label for="url-input" class="block text-sm font-semibold text-zinc-200 mb-2">
                    Website URL to Summarize
                </label>
                <div class="relative rounded-2xl shadow-inner">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <input
                        id="url-input"
                        type="url"
                        wire:model="url"
                        placeholder="https://en.wikipedia.org/wiki/Artificial_intelligence"
                        class="block w-full pl-11 pr-24 py-3.5 bg-zinc-950 border border-zinc-800 rounded-2xl text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm sm:text-base transition-all duration-200"
                        required
                    />
                    @if ($url)
                        <button
                            type="button"
                            wire:click="$set('url', '')"
                            class="absolute inset-y-0 right-28 flex items-center px-2 text-zinc-500 hover:text-zinc-300"
                            title="Clear URL"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                    <button
                        type="button"
                        onclick="navigator.clipboard.readText().then(text => @this.set('url', text))"
                        class="absolute inset-y-1.5 right-1.5 px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-medium rounded-xl transition-colors duration-150 flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Paste
                    </button>
                </div>
                @error('url')
                    <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Quick Sample Pills -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-zinc-500 font-medium">Try a sample:</span>
                @foreach ($sampleUrls as $sample)
                    <button
                        type="button"
                        wire:click="setSampleUrl('{{ $sample['url'] }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-zinc-800/60 hover:bg-zinc-800 border border-zinc-700/60 text-zinc-300 hover:text-white transition-all duration-150 hover:border-zinc-600"
                    >
                        <span class="text-zinc-500 font-mono text-[10px]">[{{ $sample['badge'] }}]</span>
                        <span>{{ $sample['title'] }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Options Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 pt-4 border-t border-zinc-800/80">
                <!-- 1. AI Provider -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                        1. AI Engine
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            wire:click="$set('provider', 'groq')"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all duration-200 {{ $provider === 'groq' ? 'bg-emerald-500/10 border-emerald-500 text-emerald-300 shadow-md shadow-emerald-500/5' : 'bg-zinc-950 border-zinc-800 text-zinc-400 hover:border-zinc-700' }}"
                        >
                            <span class="text-base font-bold flex items-center gap-1">
                                ⚡ Groq
                            </span>
                            <span class="text-[10px] text-zinc-400 mt-0.5">Ultra-Fast LPU</span>
                        </button>
                        <button
                            type="button"
                            wire:click="$set('provider', 'openrouter')"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all duration-200 {{ $provider === 'openrouter' ? 'bg-indigo-500/10 border-indigo-500 text-indigo-300 shadow-md shadow-indigo-500/5' : 'bg-zinc-950 border-zinc-800 text-zinc-400 hover:border-zinc-700' }}"
                        >
                            <span class="text-base font-bold flex items-center gap-1">
                                🌐 OpenRouter
                            </span>
                            <span class="text-[10px] text-zinc-400 mt-0.5">100+ Models</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Model Selection -->
                <div>
                    <label for="model-select" class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                        2. Model
                    </label>
                    <div class="space-y-2">
                        <select
                            id="model-select"
                            wire:model="model"
                            class="block w-full px-3 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        >
                            @foreach ($providers[$provider]['models'] as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                            <option value="custom">-- Custom Model Name --</option>
                        </select>

                        @if ($model === 'custom')
                            <input
                                type="text"
                                wire:model="customModel"
                                placeholder="Enter model identifier (e.g. meta-llama/llama-3.1-8b-instruct)"
                                class="block w-full px-3 py-2 bg-zinc-950 border border-zinc-800 rounded-xl text-white text-xs placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        @endif
                    </div>
                </div>

                <!-- 3. Summary Format -->
                <div>
                    <label for="format-select" class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                        3. Summary Format
                    </label>
                    <select
                        id="format-select"
                        wire:model="format"
                        class="block w-full px-3 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    >
                        @foreach ($formats as $key => $data)
                            <option value="{{ $key }}">{{ $data['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-zinc-500 mt-1 truncate">
                        {{ $formats[$format]['desc'] ?? '' }}
                    </p>
                </div>

                <!-- 4. Length & Language -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                        4. Length & Language
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <select
                            wire:model="length"
                            class="block w-full px-2 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            @foreach ($lengths as $key => $len)
                                <option value="{{ $key }}">{{ $len['label'] }} ({{ $len['words'] }})</option>
                            @endforeach
                        </select>
                        <select
                            wire:model="language"
                            class="block w-full px-2 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        >
                            <option value="English">English</option>
                            <option value="Bengali">Bengali (বাংলা)</option>
                            <option value="Spanish">Spanish (Español)</option>
                            <option value="French">French (Français)</option>
                            <option value="German">German (Deutsch)</option>
                            <option value="Japanese">Japanese (日本語)</option>
                            <option value="Chinese">Chinese (中文)</option>
                            <option value="Arabic">Arabic (العربية)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Button & Loader -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-zinc-400 flex items-center gap-2">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Ready to parse HTML, strip noise & summarize using {{ strtoupper($provider) }}.</span>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    @disabled($isLoading)
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-2xl font-bold text-sm text-zinc-950 bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-300 hover:from-emerald-300 hover:to-teal-200 shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all duration-200 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="summarize" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Summarize Website
                    </span>
                    <span wire:loading wire:target="summarize" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-zinc-950" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Analyzing & Generating...
                    </span>
                </button>
            </div>
        </form>

        <!-- Loading Step Card -->
        @if ($isLoading)
            <div class="mt-6 p-4 rounded-2xl bg-zinc-950/80 border border-zinc-800 flex items-center gap-3 animate-pulse">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="animate-spin w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">AI Pipeline Active</p>
                    <p class="text-sm text-zinc-300">{{ $loadingStep ?: 'Processing request...' }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Active Summary Result Display -->
    @if ($currentSummary)
        <div class="bg-zinc-900/90 border border-zinc-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-md mb-8">
            <!-- Header with Title & Metadata -->
            <div class="border-b border-zinc-800/80 pb-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="space-y-1 max-w-3xl">
                        <div class="flex items-center gap-2 flex-wrap text-xs text-zinc-400">
                            <span class="inline-flex items-center gap-1 font-semibold text-zinc-300 bg-zinc-800 px-2.5 py-1 rounded-md border border-zinc-700/60">
                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                {{ $currentSummary->domain }}
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium bg-zinc-800/50 px-2 py-0.5 rounded text-zinc-400">
                                {{ strtoupper($currentSummary->provider) }} &bull; {{ $currentSummary->model }}
                            </span>
                            <span class="inline-flex items-center font-medium bg-zinc-800/50 px-2 py-0.5 rounded text-zinc-400">
                                {{ ucfirst($currentSummary->format) }}
                            </span>
                            <span class="text-zinc-500">
                                {{ $currentSummary->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <h2 class="text-2xl font-bold text-white pt-1">
                            {{ $currentSummary->title }}
                        </h2>

                        <a
                            href="{{ $currentSummary->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 text-xs text-emerald-400 hover:text-emerald-300 hover:underline pt-0.5 truncate max-w-xl"
                        >
                            <span>{{ $currentSummary->url }}</span>
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>

                    <!-- Actions Bar (Copy / Export) -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            onclick="navigator.clipboard.writeText(`{{ addslashes($currentSummary->summary_markdown) }}`).then(() => alert('Summary copied to clipboard!'))"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 transition-colors"
                        >
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy Summary
                        </button>

                        <a
                            href="data:text/markdown;charset=utf-8,{{ rawurlencode("# {$currentSummary->title}\nSource: {$currentSummary->url}\n\n{$currentSummary->summary_markdown}") }}"
                            download="summary-{{ Str::slug($currentSummary->title ?: 'website') }}.md"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 transition-colors"
                        >
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download .md
                        </a>
                    </div>
                </div>

                <!-- Stats Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-4 border-t border-zinc-800/60">
                    <div class="p-3 rounded-2xl bg-zinc-950/60 border border-zinc-800/80">
                        <p class="text-[11px] text-zinc-500 font-medium">Original Length</p>
                        <p class="text-base font-bold text-zinc-200 mt-0.5">
                            {{ number_format($currentSummary->original_word_count) }} <span class="text-xs font-normal text-zinc-500">words</span>
                        </p>
                    </div>
                    <div class="p-3 rounded-2xl bg-zinc-950/60 border border-zinc-800/80">
                        <p class="text-[11px] text-zinc-500 font-medium">Summary Length</p>
                        <p class="text-base font-bold text-zinc-200 mt-0.5">
                            {{ number_format($currentSummary->summary_word_count) }} <span class="text-xs font-normal text-zinc-500">words</span>
                        </p>
                    </div>
                    <div class="p-3 rounded-2xl bg-emerald-500/5 border border-emerald-500/20">
                        <p class="text-[11px] text-emerald-400/90 font-medium">Content Reduction</p>
                        <p class="text-base font-bold text-emerald-300 mt-0.5">
                            {{ $currentSummary->reduction_percentage }}% <span class="text-xs font-normal text-emerald-400/70">shorter</span>
                        </p>
                    </div>
                    <div class="p-3 rounded-2xl bg-indigo-500/5 border border-indigo-500/20">
                        <p class="text-[11px] text-indigo-400/90 font-medium">Reading Time Saved</p>
                        <p class="text-base font-bold text-indigo-300 mt-0.5">
                            {{ $currentSummary->reading_time_saved_formatted }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formatted Markdown Content -->
            <div class="prose prose-invert prose-emerald max-w-none text-zinc-200 leading-relaxed text-sm sm:text-base space-y-4">
                {!! $currentSummary->summary_html !!}
            </div>
        </div>
    @endif

    <!-- Recent Summaries History Section -->
    <div class="bg-zinc-900/60 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 backdrop-blur-md">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-bold text-white">Recent Summaries History</h3>
                <span class="px-2 py-0.5 text-xs bg-zinc-800 text-zinc-400 rounded-full font-mono">
                    {{ $history->count() }}
                </span>
            </div>

            @if ($history->isNotEmpty())
                <button
                    wire:click="clearAllHistory"
                    type="button"
                    wire:confirm="Are you sure you want to clear all summary history?"
                    class="text-xs text-zinc-500 hover:text-rose-400 transition-colors"
                >
                    Clear All
                </button>
            @endif
        </div>

        @if ($history->isEmpty())
            <div class="text-center py-10 border border-dashed border-zinc-800 rounded-2xl">
                <p class="text-zinc-500 text-sm">No summaries generated yet.</p>
                <p class="text-zinc-600 text-xs mt-1">Paste any URL above or click a sample to get your first AI summary!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($history as $item)
                    <div
                        wire:key="summary-{{ $item->id }}"
                        class="p-4 rounded-2xl bg-zinc-950 border transition-all duration-200 flex flex-col justify-between gap-3 {{ $currentSummary && $currentSummary->id === $item->id ? 'border-emerald-500/50 bg-emerald-500/[0.02]' : 'border-zinc-800/70 hover:border-zinc-700' }}"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-2 text-[11px] text-zinc-500">
                                <span class="font-semibold text-zinc-400 truncate">{{ $item->domain }}</span>
                                <span>{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <h4
                                wire:click="loadSummary({{ $item->id }})"
                                class="font-medium text-sm text-zinc-200 hover:text-emerald-400 cursor-pointer line-clamp-2 transition-colors"
                            >
                                {{ $item->title ?: $item->url }}
                            </h4>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-zinc-800/50 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-zinc-800 text-zinc-400">
                                    {{ $item->provider }}
                                </span>
                                <span class="text-zinc-500 text-[11px]">
                                    Saved {{ $item->reading_time_saved_formatted }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="loadSummary({{ $item->id }})"
                                    class="text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                                >
                                    View
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteSummary({{ $item->id }})"
                                    class="text-xs text-zinc-500 hover:text-rose-400 transition-colors"
                                    title="Delete"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- API Keys Modal -->
    @if ($showApiKeyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in">
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 relative">
                <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">AI API Configuration</h3>
                    </div>
                    <button wire:click="closeApiKeyModal" class="text-zinc-500 hover:text-zinc-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Groq API Key -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="groq-key-input" class="text-xs font-semibold text-zinc-300 flex items-center gap-1.5">
                                <span>⚡ Groq API Key</span>
                                <span class="text-[10px] text-emerald-400 font-normal">(Free & Ultra-Fast)</span>
                            </label>
                            <a href="https://console.groq.com/keys" target="_blank" class="text-[11px] text-emerald-400 hover:underline">
                                Get Groq Key &rarr;
                            </a>
                        </div>
                        <input
                            id="groq-key-input"
                            type="password"
                            wire:model="groqApiKey"
                            placeholder="gsk_..."
                            class="block w-full px-3.5 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white placeholder-zinc-600 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                        />
                        <p class="text-[11px] text-zinc-500 mt-1">
                            Groq provides blazing-fast inference with Llama 3.3 and Mixtral.
                        </p>
                    </div>

                    <!-- OpenRouter API Key -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="openrouter-key-input" class="text-xs font-semibold text-zinc-300 flex items-center gap-1.5">
                                <span>🌐 OpenRouter API Key</span>
                                <span class="text-[10px] text-indigo-400 font-normal">(100+ Models)</span>
                            </label>
                            <a href="https://openrouter.ai/keys" target="_blank" class="text-[11px] text-indigo-400 hover:underline">
                                Get OpenRouter Key &rarr;
                            </a>
                        </div>
                        <input
                            id="openrouter-key-input"
                            type="password"
                            wire:model="openrouterApiKey"
                            placeholder="sk-or-..."
                            class="block w-full px-3.5 py-2.5 bg-zinc-950 border border-zinc-800 rounded-xl text-white placeholder-zinc-600 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        />
                        <p class="text-[11px] text-zinc-500 mt-1">
                            OpenRouter gives access to Claude, DeepSeek, Gemini, and open-source models.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-zinc-950/80 border border-zinc-800 text-xs text-zinc-400 space-y-1">
                        <p class="font-medium text-zinc-300">💡 Security Notice:</p>
                        <p>Keys entered here are securely saved in your current active browser session. Alternatively, you can save them in your <code class="text-emerald-400 font-mono">.env</code> file under <code class="text-emerald-400 font-mono">GROQ_API_KEY</code> and <code class="text-emerald-400 font-mono">OPENROUTER_API_KEY</code>.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-800">
                    <button
                        type="button"
                        wire:click="closeApiKeyModal"
                        class="px-4 py-2 rounded-xl text-xs font-medium text-zinc-400 hover:text-white"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="saveApiKeys"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-zinc-950 bg-emerald-400 hover:bg-emerald-300 transition-colors shadow-md shadow-emerald-500/20"
                    >
                        Save API Keys
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
