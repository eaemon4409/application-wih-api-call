<?php

use App\Services\WebContentExtractor;

test('it parses html and extracts title and clean content', function () {
    $extractor = new WebContentExtractor;

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
        <head>
            <title>Test Page Title</title>
            <meta name="description" content="This is a test description." />
            <script>console.log('strip me');</script>
            <style>body { color: red; }</style>
        </head>
        <body>
            <header>Header content should be ignored</header>
            <nav>Navigation links</nav>
            <article>
                <h1>Main Heading</h1>
                <p>Artificial Intelligence is transforming how we process information and summarize content across the web.</p>
                <p>This is a second paragraph with detailed explanations and substantive information.</p>
            </article>
            <footer>Footer copyright 2026</footer>
        </body>
    </html>
    HTML;

    $result = $extractor->parseHtml($html, 'https://example.com/test', 'example.com');

    expect($result['title'])->toBe('Test Page Title')
        ->and($result['domain'])->toBe('example.com')
        ->and($result['description'])->toBe('This is a test description.')
        ->and($result['content'])->toContain('Artificial Intelligence is transforming')
        ->and($result['content'])->not->toContain('strip me')
        ->and($result['content'])->not->toContain('Header content should be ignored')
        ->and($result['word_count'])->toBeGreaterThan(10);
});

test('it throws exception on invalid url', function () {
    $extractor = new WebContentExtractor;
    $extractor->extract('not-a-valid-url');
})->throws(Exception::class);
