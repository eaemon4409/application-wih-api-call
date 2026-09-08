<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class AISummarizerService
{
    /**
     * Supported providers and their popular models.
     */
    public const PROVIDERS = [
        'groq' => [
            'name' => 'Groq (Ultra-Fast)',
            'icon' => 'bolt',
            'badge' => '⚡ Blazing Fast',
            'default_model' => 'groq/compound',
            'models' => [
                'groq/compound' => 'Groq Compound (Fast & Intelligent - Recommended)',
                'groq/compound-mini' => 'Groq Compound Mini (Instant)',
                'qwen/qwen3.8-27b' => 'Qwen 3.8 27B',
                'openai/gpt-oss-120b' => 'OpenAI GPT OSS 120B',
                'openai/gpt-oss-20b' => 'OpenAI GPT OSS 20B',
            ],
        ],
        'openrouter' => [
            'name' => 'OpenRouter (Multi-Model)',
            'icon' => 'globe-alt',
            'badge' => '🌐 100+ Models',
            'default_model' => 'meta-llama/llama-3.3-70b-instruct',
            'models' => [
                'meta-llama/llama-3.3-70b-instruct' => 'Meta Llama 3.3 70B Instruct',
                'google/gemini-2.0-flash-001' => 'Google Gemini 2.0 Flash',
                'deepseek/deepseek-chat' => 'DeepSeek V3 / Chat',
                'openai/gpt-4o-mini' => 'OpenAI GPT-4o Mini',
                'anthropic/claude-3.5-sonnet' => 'Anthropic Claude 3.5 Sonnet',
            ],
        ],
    ];

    /**
     * Supported summary formats.
     */
    public const FORMATS = [
        'tldr' => [
            'label' => 'Executive TL;DR',
            'desc' => 'High-level bullet points covering the core takeaway',
        ],
        'comprehensive' => [
            'label' => 'Comprehensive Breakdown',
            'desc' => 'Structured sections with headings and key details',
        ],
        'bullets' => [
            'label' => 'Key Takeaways & Insights',
            'desc' => 'Actionable highlights, main findings, and lessons',
        ],
        'eli5' => [
            'label' => 'Explain Like I\'m 5',
            'desc' => 'Simple analogies and jargon-free explanation',
        ],
    ];

    /**
     * Supported summary lengths.
     */
    public const LENGTHS = [
        'short' => ['label' => 'Short', 'words' => '~150 words'],
        'medium' => ['label' => 'Medium', 'words' => '~300 words'],
        'long' => ['label' => 'Detailed', 'words' => '~600 words'],
    ];

    /**
     * Generate summary using Groq or OpenRouter.
     *
     * @param  array  $extractedContent  Extracted page data from WebContentExtractor
     * @param  string  $provider  'groq' or 'openrouter'
     * @param  string  $model  Model identifier
     * @param  string  $format  'tldr', 'comprehensive', 'bullets', 'eli5'
     * @param  string  $length  'short', 'medium', 'long'
     * @param  string  $language  Target language
     * @param  string|null  $customApiKey  Optional override from user session/UI
     * @return array{markdown: string, summary_word_count: int, reading_time_saved: float}
     *
     * @throws Exception
     */
    public function summarize(
        array $extractedContent,
        string $provider = 'groq',
        ?string $model = null,
        string $format = 'tldr',
        string $length = 'medium',
        string $language = 'English',
        ?string $customApiKey = null
    ): array {
        $provider = strtolower($provider);
        if (! in_array($provider, ['groq', 'openrouter'])) {
            throw new Exception("Unsupported AI provider: {$provider}");
        }

        $apiKey = $customApiKey ?: $this->getApiKey($provider);
        if (empty($apiKey)) {
            throw new Exception("Missing API Key for {$provider}. Please add it in the API settings or your .env file.");
        }

        $model = $model ?: ($provider === 'groq'
            ? config('services.groq.default_model', 'llama-3.3-70b-versatile')
            : config('services.openrouter.default_model', 'meta-llama/llama-3.3-70b-instruct'));

        $systemPrompt = $this->buildSystemPrompt($format, $length, $language);
        $userPrompt = $this->buildUserPrompt($extractedContent, $format, $length, $language);

        $endpoint = $provider === 'groq'
            ? rtrim(config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/').'/chat/completions'
            : rtrim(config('services.openrouter.base_url', 'https://openrouter.ai/v1'), '/').'/chat/completions';

        $headers = [
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ];

        if ($provider === 'openrouter') {
            $headers['HTTP-Referer'] = config('app.url', 'http://localhost');
            $headers['X-Title'] = 'Website Summarizer';
        }

        $maxTokens = match ($length) {
            'short' => 800,
            'long' => 2500,
            default => 1500,
        };

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->withOptions([
                    'verify' => false,
                ])
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => $maxTokens,
                ]);
        } catch (Exception $e) {
            throw new Exception("Failed to connect to {$provider} API: ".$e->getMessage());
        }

        if (! $response->successful()) {
            $statusCode = $response->status();
            $errorJson = $response->json();
            $errorMessage = $errorJson['error']['message'] ?? $errorJson['message'] ?? $response->body();

            if ($statusCode === 401) {
                throw new Exception("Invalid API Key for {$provider}. Please check your key.");
            }

            if ($statusCode === 429) {
                throw new Exception("Rate limit reached for {$provider}. Please wait a moment or try another provider/model.");
            }

            throw new Exception("AI Provider Error ({$statusCode}): {$errorMessage}");
        }

        $responseData = $response->json();
        $summaryMarkdown = $responseData['choices'][0]['message']['content'] ?? null;

        if (empty($summaryMarkdown)) {
            throw new Exception("No summary returned by {$provider}. Please try again.");
        }

        $summaryWordCount = str_word_count($summaryMarkdown);
        $originalWordCount = $extractedContent['word_count'] ?? 0;

        // Reading speed assumed ~200 words/min
        $originalReadingMinutes = $originalWordCount / 200;
        $summaryReadingMinutes = $summaryWordCount / 200;
        $readingTimeSaved = max(0, round($originalReadingMinutes - $summaryReadingMinutes, 1));

        return [
            'markdown' => trim($summaryMarkdown),
            'summary_word_count' => $summaryWordCount,
            'reading_time_saved' => $readingTimeSaved,
        ];
    }

    /**
     * Resolve API key from config or environment.
     */
    public function getApiKey(string $provider): ?string
    {
        return match ($provider) {
            'groq' => config('services.groq.api_key'),
            'openrouter' => config('services.openrouter.api_key'),
            default => null,
        };
    }

    /**
     * Check if a provider has an API key configured in env/config.
     */
    public function hasConfiguredKey(string $provider): bool
    {
        return ! empty($this->getApiKey($provider));
    }

    /**
     * Construct system prompt.
     */
    protected function buildSystemPrompt(string $format, string $length, string $language): string
    {
        $prompt = "You are an expert content analyst and summarizer. Your mission is to provide an accurate, clear, engaging, and highly informative summary of the web page provided.\n\n";

        $prompt .= "Guidelines:\n";
        $prompt .= "1. Respond in fluent, natural {$language}.\n";
        $prompt .= "2. Use clean, beautiful Markdown formatting with clear hierarchy (headings, bullet points, bold keywords).\n";
        $prompt .= "3. Capture all critical facts, insights, and context while removing fluff, repetitive phrases, and marketing filler.\n";
        $prompt .= "4. Do NOT hallucinate or extrapolate beyond the provided text.\n";
        $prompt .= "5. Start directly with the summary content. Do NOT include meta-commentary like 'Sure, here is your summary' or 'In this article'.\n";

        return $prompt;
    }

    /**
     * Construct user prompt based on format and length.
     */
    protected function buildUserPrompt(array $extracted, string $format, string $length, string $language): string
    {
        $targetWordCount = match ($length) {
            'short' => 'around 100 to 150 words',
            'long' => 'around 500 to 700 words with thorough detail',
            default => 'around 250 to 350 words',
        };

        $formatInstruction = match ($format) {
            'tldr' => "Format: Executive TL;DR.\n- Provide a one-paragraph overarching summary.\n- Follow with 3 to 5 high-impact bullet points highlighting the most vital takeaways and conclusions.\n- Conclude with 'Core Impact / Bottom Line'.",
            'comprehensive' => "Format: Comprehensive Structured Summary.\n- Break down the content into 2 to 4 thematic sections with clear Markdown headings (`###`).\n- In each section, explain key arguments, statistics, background, and nuances.\n- End with a 'Key Conclusion' section.",
            'bullets' => "Format: Key Takeaways & Actionable Insights.\n- Provide a numbered or bulleted list of 5 to 8 key lessons, facts, or actionable takeaways.\n- Bold the key takeaway topic before each explanation.\n- Include who this matters to or what actions readers should take.",
            'eli5' => "Format: Explain Like I'm 5 (ELI5).\n- Explain what this website/topic is about in plain, simple, crystal-clear terms without jargon.\n- Use a friendly real-world analogy to illustrate the main concept.\n- Keep it fun, accessible, and easily understandable.",
            default => 'Format: Concise and well-structured summary with key bullet points.',
        };

        return <<<PROMPT
Please summarize the following web page content.

WEBSITE METADATA:
- URL: {$extracted['url']}
- Title: {$extracted['title']}
- Description: {$extracted['description']}

SUMMARY REQUIREMENTS:
- Format: {$formatInstruction}
- Target Length: {$targetWordCount}
- Language: {$language}

SOURCE CONTENT:
\"\"\"
{$extracted['content']}
\"\"\"
PROMPT;
    }
}
