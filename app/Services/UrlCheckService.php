<?php

namespace App\Services;

use App\Models\BlocklistDomain;
use Illuminate\Support\Facades\Log;

class UrlCheckService
{
    /**
     * Extract domain from URL and check if it's blocked in any category.
     */
    public function checkUrl(string $url): array
    {
        $domain = $this->extractDomain($url);

        if (!$domain) {
            return [
                'status' => 'error',
                'message' => 'Invalid URL format provided.',
                'code' => 400,
            ];
        }
        Log::info('Domain: ' . $domain);
        // Search for the domain in blocklists exactly
        $blocklistEntry = BlocklistDomain::where('domain', $domain)->first();

        // Also check if any base domain is blocked (e.g. if 'example.com' is blocked, 'sub.example.com' should be blocked)
        if (!$blocklistEntry) {
            $parts = explode('.', $domain);
            while (count($parts) > 2) {
                array_shift($parts);
                $baseDomain = implode('.', $parts);
                $blocklistEntry = BlocklistDomain::where('domain', $baseDomain)->first();
                if ($blocklistEntry) break;
            }
        }
        Log::info('Blocklist Entry: ' . $blocklistEntry);
        if ($blocklistEntry) {
            return [
                'status' => 'blocked',
                'category' => $blocklistEntry->category->value,
                'category_label' => $blocklistEntry->category->label(),
                'domain_matched' => $blocklistEntry->domain,
                'code' => 200,
            ];
        }

        return [
            'status' => 'clean',
            'message' => 'URL is not on any blocklist.',
            'code' => 200,
        ];
    }

    /**
     * Helper to extract domain from full URL
     */
    public function extractDomain(string $url): ?string
    {
        // Add http protocol if missing to allow parse_url to work correctly
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'http://' . $url;
        }

        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['host'])) {
            return null;
        }

        $host = strtolower($parsedUrl['host']);

        // Remove www.
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }
}
