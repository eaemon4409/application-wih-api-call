<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;

class WebContentExtractor
{
    /**
     * Fetch and extract clean, readable text from a URL.
     *
     * @return array{url: string, domain: string, title: string, description: string, content: string, word_count: int}
     *
     * @throws Exception
     */
    public function extract(string $url): array
    {
        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('The provided URL is invalid. Please make sure it starts with http:// or https://');
        }

        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? 'unknown';

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->timeout(20)
                ->connectTimeout(10)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'strict' => true,
                        'referer' => true,
                        'protocols' => ['http', 'https'],
                    ],
                    'verify' => false, // Prevents local cURL SSL cert issues on Windows
                ])
                ->get($url);

        } catch (Exception $e) {
            throw new Exception("Could not connect to {$domain}. Reason: ".$e->getMessage());
        }

        if (! $response->successful()) {
            $status = $response->status();
            if ($status === 403 || $status === 401) {
                throw new Exception("Access denied ({$status}) by {$domain}. The website is blocking automated requests.");
            }
            if ($status === 404) {
                throw new Exception("The page was not found (404) at {$url}.");
            }
            throw new Exception("Failed to fetch website. HTTP Status: {$status}.");
        }

        $html = $response->body();

        if (empty(trim($html))) {
            throw new Exception('The website returned an empty response.');
        }

        return $this->parseHtml($html, $url, $domain);
    }

    /**
     * Parse HTML and extract clean text content and metadata.
     */
    public function parseHtml(string $html, string $url, string $domain): array
    {
        // Suppress HTML5 parsing warnings in DOMDocument
        libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        // Convert to UTF-8 entities to preserve characters
        $encodedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        @$dom->loadHTML($encodedHtml, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // 1. Extract Title
        $title = '';
        $ogTitle = $xpath->query('//meta[@property="og:title"]/@content');
        if ($ogTitle && $ogTitle->length > 0) {
            $title = trim($ogTitle->item(0)->nodeValue);
        }
        if (empty($title)) {
            $titleNodes = $dom->getElementsByTagName('title');
            if ($titleNodes->length > 0) {
                $title = trim($titleNodes->item(0)->nodeValue);
            }
        }
        if (empty($title)) {
            $h1Nodes = $dom->getElementsByTagName('h1');
            if ($h1Nodes->length > 0) {
                $title = trim($h1Nodes->item(0)->nodeValue);
            }
        }
        if (empty($title)) {
            $title = $domain;
        }

        // 2. Extract Description
        $description = '';
        $metaDesc = $xpath->query('//meta[@name="description"]/@content | //meta[@property="og:description"]/@content');
        if ($metaDesc && $metaDesc->length > 0) {
            $description = trim($metaDesc->item(0)->nodeValue);
        }

        // 3. Remove unwanted noise tags
        $noiseTags = ['script', 'style', 'noscript', 'svg', 'iframe', 'canvas', 'footer', 'nav', 'header', 'aside', 'form', 'button', 'select'];
        foreach ($noiseTags as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            // Iterate backwards because DOMNodeList is live
            for ($i = $elements->length - 1; $i >= 0; $i--) {
                $node = $elements->item($i);
                $node->parentNode->removeChild($node);
            }
        }

        // Remove typical ad/widget containers by class or id
        $noiseXPaths = [
            '//*[contains(@class, "ad-")]',
            '//*[contains(@class, "ads-")]',
            '//*[contains(@class, "sidebar")]',
            '//*[contains(@class, "cookie-banner")]',
            '//*[contains(@class, "social-share")]',
            '//*[contains(@class, "comment")]',
        ];
        foreach ($noiseXPaths as $expression) {
            $noiseNodes = $xpath->query($expression);
            if ($noiseNodes) {
                for ($i = $noiseNodes->length - 1; $i >= 0; $i--) {
                    $node = $noiseNodes->item($i);
                    if ($node && $node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }

        // 4. Target main content containers if present
        $targetNodes = $xpath->query('//article | //main | //*[@role="main"]');
        $rawText = '';

        if ($targetNodes && $targetNodes->length > 0) {
            foreach ($targetNodes as $node) {
                $rawText .= ' '.$node->textContent;
            }
        } else {
            $body = $dom->getElementsByTagName('body');
            if ($body->length > 0) {
                $rawText = $body->item(0)->textContent;
            } else {
                $rawText = $dom->textContent;
            }
        }

        // 5. Clean whitespace & normalize
        $cleanText = preg_replace('/\s+/', ' ', $rawText);
        $cleanText = trim($cleanText);

        if (empty($cleanText) || strlen($cleanText) < 50) {
            // Fallback: strip tags from original HTML if DOM returned minimal text
            $stripped = strip_tags($html);
            $cleanText = trim(preg_replace('/\s+/', ' ', $stripped));
        }

        if (empty($cleanText) || strlen($cleanText) < 30) {
            throw new Exception('Unable to extract sufficient readable text from this page. It might be heavily JavaScript-rendered or protected.');
        }

        // Truncate to ~25,000 characters to safely fit within LLM prompt limits
        if (strlen($cleanText) > 25000) {
            $cleanText = mb_substr($cleanText, 0, 25000).'... [Content truncated for summary]';
        }

        $wordCount = str_word_count($cleanText);

        return [
            'url' => $url,
            'domain' => $domain,
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'description' => html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'content' => $cleanText,
            'word_count' => $wordCount,
        ];
    }
}
