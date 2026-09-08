<?php

use App\Livewire\WebsiteSummarizer;
use App\Models\Summary;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

test('it renders the website summarizer livewire component on the home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeLivewire(WebsiteSummarizer::class)
        ->assertSee('SummarixAI');
});

test('it validates that url is required', function () {
    Livewire::test(WebsiteSummarizer::class)
        ->set('url', '')
        ->call('summarize')
        ->assertHasErrors(['url' => 'required']);
});

test('it validates url scheme', function () {
    Livewire::test(WebsiteSummarizer::class)
        ->set('url', 'invalid-url-string')
        ->call('summarize')
        ->assertHasErrors(['url']);
});

test('it can populate sample url', function () {
    Livewire::test(WebsiteSummarizer::class)
        ->call('setSampleUrl', 'https://en.wikipedia.org/wiki/Artificial_intelligence')
        ->assertSet('url', 'https://en.wikipedia.org/wiki/Artificial_intelligence');
});

test('it can save api keys in session', function () {
    Livewire::test(WebsiteSummarizer::class)
        ->set('groqApiKey', 'gsk_custom_session_key')
        ->set('openrouterApiKey', 'sk-or-custom_session_key')
        ->call('saveApiKeys')
        ->assertSessionHas('groq_api_key', 'gsk_custom_session_key')
        ->assertSessionHas('openrouter_api_key', 'sk-or-custom_session_key');
});

test('it summarizes a website and creates a summary record', function () {
    Http::fake([
        'https://example.com/test' => Http::response(
            '<html><head><title>Test Article</title></head><body><article><p>This is substantive article content about technology and machine learning algorithms.</p></article></body></html>',
            200
        ),
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => '## Summary of Test Article\n- Point 1: Machine learning is powerful.',
                    ],
                ],
            ],
        ], 200),
    ]);

    Livewire::test(WebsiteSummarizer::class)
        ->set('url', 'https://example.com/test')
        ->set('groqApiKey', 'gsk_dummy_key')
        ->set('provider', 'groq')
        ->call('summarize')
        ->assertHasNoErrors()
        ->assertSee('Summary of Test Article');

    expect(Summary::count())->toBe(1);
    $summary = Summary::first();
    expect($summary->url)->toBe('https://example.com/test')
        ->and($summary->domain)->toBe('example.com')
        ->and($summary->title)->toBe('Test Article')
        ->and($summary->summary_markdown)->toContain('Machine learning is powerful');
});
