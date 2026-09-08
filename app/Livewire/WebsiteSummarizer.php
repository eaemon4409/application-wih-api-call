<?php

namespace App\Livewire;

use App\Models\Summary;
use App\Services\AISummarizerService;
use App\Services\WebContentExtractor;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('AI Website Summarizer - OpenRouter & Groq')]
class WebsiteSummarizer extends Component
{
    public string $url = '';

    public string $provider = 'groq';

    public string $model = 'groq/compound';

    public string $customModel = '';

    public string $format = 'tldr';

    public string $length = 'medium';

    public string $language = 'English';

    // API Key inputs (stored in session for convenience)
    public string $groqApiKey = '';

    public string $openrouterApiKey = '';

    public bool $showApiKeyModal = false;

    // Execution state
    public bool $isLoading = false;

    public string $loadingStep = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    // Active summary view
    public ?Summary $currentSummary = null;

    // Available sample URLs for fast testing
    public array $sampleUrls = [
        [
            'title' => 'Wikipedia: Artificial Intelligence',
            'url' => 'https://en.wikipedia.org/wiki/Artificial_intelligence',
            'badge' => 'Encyclopedia',
        ],
        [
            'title' => 'Laravel 11 Announcement',
            'url' => 'https://laravel-news.com/laravel-11',
            'badge' => 'Tech News',
        ],
        [
            'title' => 'MIT Technology Review: AI Breakthroughs',
            'url' => 'https://www.technologyreview.com/topic/artificial-intelligence/',
            'badge' => 'Technology',
        ],
    ];

    public function mount(): void
    {
        // Load API keys from session if saved previously
        $this->groqApiKey = session('groq_api_key', config('services.groq.api_key', ''));
        $this->openrouterApiKey = session('openrouter_api_key', config('services.openrouter.api_key', ''));

        // Load most recent summary if available
        $this->currentSummary = Summary::latest()->first();
    }

    public function updatedProvider($value): void
    {
        $providers = AISummarizerService::PROVIDERS;
        if (isset($providers[$value])) {
            $this->model = $providers[$value]['default_model'];
            $this->customModel = '';
        }
    }

    public function setSampleUrl(string $sampleUrl): void
    {
        $this->url = $sampleUrl;
        $this->errorMessage = null;
    }

    public function openApiKeyModal(): void
    {
        $this->showApiKeyModal = true;
    }

    public function closeApiKeyModal(): void
    {
        $this->showApiKeyModal = false;
    }

    public function saveApiKeys(): void
    {
        $this->groqApiKey = trim($this->groqApiKey);
        $this->openrouterApiKey = trim($this->openrouterApiKey);

        session([
            'groq_api_key' => $this->groqApiKey,
            'openrouter_api_key' => $this->openrouterApiKey,
        ]);

        $this->showApiKeyModal = false;
        $this->successMessage = 'API keys updated successfully!';
    }

    public function summarize(WebContentExtractor $extractor, AISummarizerService $aiService): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $this->validate([
            'url' => 'required|url:http,https',
            'provider' => 'required|in:groq,openrouter',
            'format' => 'required|string',
            'length' => 'required|string',
            'language' => 'required|string',
        ], [
            'url.required' => 'Please enter a website URL to summarize.',
            'url.url' => 'Please enter a valid URL including http:// or https://',
        ]);

        // Resolve active API key
        $activeKey = $this->provider === 'groq'
            ? ($this->groqApiKey ?: config('services.groq.api_key'))
            : ($this->openrouterApiKey ?: config('services.openrouter.api_key'));

        if (empty($activeKey)) {
            $providerName = AISummarizerService::PROVIDERS[$this->provider]['name'] ?? $this->provider;
            $this->errorMessage = "No API key found for {$providerName}. Please click 'Configure API Keys' above to enter your key.";
            $this->showApiKeyModal = true;

            return;
        }

        $this->isLoading = true;
        $this->loadingStep = 'Connecting to website and fetching content...';

        try {
            // Step 1: Extract Web Content
            $extracted = $extractor->extract($this->url);

            // Step 2: AI Summarization
            $this->loadingStep = "Analyzing content and generating summary with {$this->provider}...";

            $chosenModel = ! empty($this->customModel) ? $this->customModel : $this->model;

            $aiResult = $aiService->summarize(
                extractedContent: $extracted,
                provider: $this->provider,
                model: $chosenModel,
                format: $this->format,
                length: $this->length,
                language: $this->language,
                customApiKey: $activeKey
            );

            // Step 3: Save to Database
            $summary = Summary::create([
                'user_id' => Auth::id(),
                'url' => $extracted['url'],
                'domain' => $extracted['domain'],
                'title' => $extracted['title'] ?: $extracted['domain'],
                'provider' => $this->provider,
                'model' => $chosenModel,
                'format' => $this->format,
                'length' => $this->length,
                'language' => $this->language,
                'original_word_count' => $extracted['word_count'],
                'summary_word_count' => $aiResult['summary_word_count'],
                'reading_time_saved_minutes' => $aiResult['reading_time_saved'],
                'summary_markdown' => $aiResult['markdown'],
            ]);

            $this->currentSummary = $summary;
            $this->successMessage = 'Summary generated successfully!';

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
            $this->loadingStep = '';
        }
    }

    public function loadSummary(int $id): void
    {
        $this->currentSummary = Summary::find($id);
        if ($this->currentSummary) {
            $this->url = $this->currentSummary->url;
            $this->errorMessage = null;
        }
    }

    public function deleteSummary(int $id): void
    {
        Summary::where('id', $id)->delete();

        if ($this->currentSummary && $this->currentSummary->id === $id) {
            $this->currentSummary = Summary::latest()->first();
        }

        $this->successMessage = 'Summary removed from history.';
    }

    public function clearAllHistory(): void
    {
        Summary::truncate();
        $this->currentSummary = null;
        $this->successMessage = 'History cleared.';
    }

    public function render()
    {
        $history = Summary::latest()->take(10)->get();

        $hasGroqKey = ! empty($this->groqApiKey) || ! empty(config('services.groq.api_key'));
        $hasOpenRouterKey = ! empty($this->openrouterApiKey) || ! empty(config('services.openrouter.api_key'));

        return view('livewire.website-summarizer', [
            'history' => $history,
            'providers' => AISummarizerService::PROVIDERS,
            'formats' => AISummarizerService::FORMATS,
            'lengths' => AISummarizerService::LENGTHS,
            'hasGroqKey' => $hasGroqKey,
            'hasOpenRouterKey' => $hasOpenRouterKey,
        ]);
    }
}
