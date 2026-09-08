<?php

use App\Services\AISummarizerService;
use Illuminate\Support\Facades\Http;

test('it successfully calls groq api and parses summary', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => "## Executive Summary\n- Key Point 1: Fast inference\n- Key Point 2: High accuracy",
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new AISummarizerService;

    $extracted = [
        'url' => 'https://example.com/article',
        'domain' => 'example.com',
        'title' => 'Example Article',
        'description' => 'A test article',
        'content' => 'This is a long article about modern technology and advancements in software engineering.',
        'word_count' => 150,
    ];

    $result = $service->summarize(
        extractedContent: $extracted,
        provider: 'groq',
        model: 'llama-3.3-70b-versatile',
        format: 'tldr',
        length: 'short',
        language: 'English',
        customApiKey: 'gsk_test_key_12345'
    );

    expect($result['markdown'])->toContain('## Executive Summary')
        ->and($result['summary_word_count'])->toBeGreaterThan(5)
        ->and($result['reading_time_saved'])->toBeGreaterThanOrEqual(0);
});

test('it successfully calls openrouter api and parses summary', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => "### Overview\nOpenRouter provides access to various models including Claude and Gemini.",
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new AISummarizerService;

    $extracted = [
        'url' => 'https://openrouter.ai/docs',
        'domain' => 'openrouter.ai',
        'title' => 'OpenRouter Docs',
        'description' => 'Documentation',
        'content' => 'Learn how to use OpenRouter API with unified completions.',
        'word_count' => 100,
    ];

    $result = $service->summarize(
        extractedContent: $extracted,
        provider: 'openrouter',
        model: 'meta-llama/llama-3.3-70b-instruct',
        format: 'comprehensive',
        length: 'medium',
        language: 'English',
        customApiKey: 'sk-or-test_key_67890'
    );

    expect($result['markdown'])->toContain('OpenRouter provides access')
        ->and($result['summary_word_count'])->toBeGreaterThan(5);
});

test('it throws exception when api key is missing', function () {
    config(['services.groq.api_key' => null]);

    $service = new AISummarizerService;
    $extracted = [
        'url' => 'https://example.com',
        'domain' => 'example.com',
        'title' => 'Test',
        'description' => '',
        'content' => 'Some content to summarize',
        'word_count' => 50,
    ];

    $service->summarize($extracted, provider: 'groq', customApiKey: null);
})->throws(Exception::class, 'Missing API Key for groq');
