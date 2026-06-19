<?php

namespace App\Services;

use App\Enums\BlocklistCategory;
use App\Models\BlocklistDomain;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlocklistService
{
    /**
     * Get paginated domains for a specific category
     */
    public function getByCategory(string $category, int $perPage = 15, ?string $search = null)
    {
        $query = BlocklistDomain::where('category', $category);

        if ($search) {
            $query->where('domain', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get paginated domains across every category
     */
    public function getAll(int $perPage = 15, ?string $search = null)
    {
        $query = BlocklistDomain::query();

        if ($search) {
            $query->where('domain', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Return all ad-category domains as a flat array of strings.
     * Used by the mobile DNS ad-blocker which consumes the entire list at once.
     */
    public function getAdBlockDomains(): array
    {
        return $this->getDomainsByCategory(BlocklistCategory::Ads);
    }

    /**
     * Return all family-safety domains (adult content, gambling, etc.) as a
     * flat array of strings. The parental-control filter is toggled in-app, but
     * the list itself always comes from the server. Kept separate from the
     * ad-block list so the two filters toggle independently.
     */
    public function getFamilySafetyDomains(): array
    {
        return $this->getDomainsByCategory(BlocklistCategory::Family);
    }

    /**
     * Return every domain in a category as a sorted, flat array of strings.
     * Used by the mobile filters that download a whole category at once.
     */
    private function getDomainsByCategory(BlocklistCategory $category): array
    {
        return BlocklistDomain::where('category', $category->value)
            ->orderBy('domain')
            ->pluck('domain')
            ->all();
    }

    /**
     * Identify valid domains from a bulk upload txt or csv file and insert them
     */
    public function bulkImport(UploadedFile $file, string $category): array
    {
        $contents = file_get_contents($file->getRealPath());
        $lines = explode("\n", str_replace("\r", "", $contents));

        $validDomains = [];
        $invalidLinesCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Simple CSV handling: take first column
            if (str_contains($line, ',')) {
                $parts = str_getcsv($line);
                $line = trim($parts[0] ?? '');
            }

            // Basic domain validation regex
            if (preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $line)) {
                $validDomains[strtolower($line)] = true; // Use array keys to deduplicate
            } else {
                $invalidLinesCount++;
            }
        }

        if (empty($validDomains)) {
            return [
                'status' => 'error',
                'message' => 'No valid domains found in the uploaded file.',
                'invalid_lines_count' => $invalidLinesCount,
            ];
        }

        $now = now();
        $insertData = [];
        foreach (array_keys($validDomains) as $domain) {
            $insertData[] = [
                'domain' => $domain,
                'category' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $insertedCount = 0;

        // Insert in chunks using insertOrIgnore to skip existing duplicates safely
        DB::transaction(function () use ($insertData, &$insertedCount) {
            foreach (array_chunk($insertData, 1000) as $chunk) {
                $insertedCount += DB::table('blocklist_domains')->insertOrIgnore($chunk);
            }
        });

        return [
            'status' => 'success',
            'message' => "Successfully inserted {$insertedCount} domains.",
            'inserted_count' => $insertedCount,
            'duplicate_count' => count($insertData) - $insertedCount,
            'invalid_lines_count' => $invalidLinesCount,
            'total_processed' => count($insertData),
        ];
    }
}
