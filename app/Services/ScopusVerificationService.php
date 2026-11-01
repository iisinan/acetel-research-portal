<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScopusVerificationService
{
    /**
     * Clean and extract a DOI from user input.
     */
    public function extractDoi(string $input): ?string
    {
        $input = trim($input);
        if (preg_match('/(10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+)/i', $input, $matches)) {
            $doi = rtrim($matches[1], '.,;');
            return $doi;
        }

        return null;
    }

    /**
     * Verify whether a DOI is valid, fetch its publication metadata,
     * and check if it is indexed in Elsevier Scopus.
     */
    public function verify(string $doiInput): array
    {
        $cleanDoi = $this->extractDoi($doiInput);

        if (!$cleanDoi) {
            return [
                'valid' => false,
                'doi' => $doiInput,
                'error' => 'Invalid DOI format. A standard DOI starts with 10.xxxx/...',
                'is_scopus_indexed' => false,
                'title' => null,
                'journal' => null,
            ];
        }

        $cacheKey = 'scopus_doi_verify_' . md5(strtolower($cleanDoi));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($cleanDoi) {
            return $this->fetchDoiDetails($cleanDoi);
        });
    }

    /**
     * Perform the actual HTTP checks against Elsevier Scopus API and Crossref API.
     */
    protected function fetchDoiDetails(string $doi): array
    {
        $isScopusIndexed = false;
        $scopusEid = null;
        $title = null;
        $journal = null;
        $publisher = null;
        $year = null;
        $authors = [];
        $scopusRaw = null;

        // 1. Query Elsevier Scopus Abstract Retrieval API
        try {
            $headers = ['Accept' => 'application/json'];
            $apiKey = config('services.scopus.api_key') ?: env('SCOPUS_API_KEY');
            if ($apiKey) {
                $headers['X-ELS-APIKey'] = $apiKey;
            }

            $encodedDoi = urlencode($doi);
            $scopusResponse = Http::withoutVerifying()
                ->timeout(8)
                ->withHeaders($headers)
                ->get("https://api.elsevier.com/content/abstract/doi/{$encodedDoi}");

            if ($scopusResponse->successful()) {
                $scopusJson = $scopusResponse->json();
                $coreData = $scopusJson['abstracts-retrieval-response']['coredata'] ?? [];
                
                $scopusEid = $coreData['eid'] ?? null;
                $isScopusIndexed = !empty($scopusEid) || isset($scopusJson['abstracts-retrieval-response']);
                $scopusRaw = $coreData;

                if (!empty($coreData['dc:title'])) {
                    $title = is_array($coreData['dc:title']) ? ($coreData['dc:title'][0] ?? null) : $coreData['dc:title'];
                }
                if (!empty($coreData['prism:publicationName'])) {
                    $journal = $coreData['prism:publicationName'];
                }
                if (!empty($coreData['prism:coverDate'])) {
                    $year = substr($coreData['prism:coverDate'], 0, 4);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Scopus API verification failed for DOI {$doi}: " . $e->getMessage());
        }

        // 2. Query Crossref API for authoritative bibliographic details
        try {
            $encodedDoi = urlencode($doi);
            $crossrefResponse = Http::withoutVerifying()
                ->timeout(8)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'AcetelThesisMonitoring/1.0 (mailto:acetel@noun.edu.ng)',
                ])
                ->get("https://api.crossref.org/works/{$encodedDoi}");

            if ($crossrefResponse->successful()) {
                $crossrefJson = $crossrefResponse->json();
                $message = $crossrefJson['message'] ?? [];

                if (empty($title) && !empty($message['title'])) {
                    $title = is_array($message['title']) ? ($message['title'][0] ?? null) : $message['title'];
                }
                if (empty($journal) && !empty($message['container-title'])) {
                    $journal = is_array($message['container-title']) ? ($message['container-title'][0] ?? null) : $message['container-title'];
                }
                if (empty($publisher) && !empty($message['publisher'])) {
                    $publisher = $message['publisher'];
                }
                if (empty($year)) {
                    $year = $message['published']['date-parts'][0][0] ?? ($message['created']['date-parts'][0][0] ?? null);
                }

                if (!empty($message['author']) && is_array($message['author'])) {
                    foreach (array_slice($message['author'], 0, 5) as $author) {
                        $authorName = trim(($author['given'] ?? '') . ' ' . ($author['family'] ?? ''));
                        if ($authorName) {
                            $authors[] = $authorName;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Crossref API verification failed for DOI {$doi}: " . $e->getMessage());
        }

        // 3. Fallback to DataCite API if title is still not resolved
        if (empty($title)) {
            try {
                $encodedDoi = urlencode($doi);
                $dataciteResponse = Http::withoutVerifying()
                    ->timeout(8)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'AcetelThesisMonitoring/1.0 (mailto:acetel@noun.edu.ng)',
                    ])
                    ->get("https://api.datacite.org/dois/{$encodedDoi}");

                if ($dataciteResponse->successful()) {
                    $dataciteJson = $dataciteResponse->json();
                    $attrs = $dataciteJson['data']['attributes'] ?? [];

                    if (!empty($attrs['titles'][0]['title'])) {
                        $title = $attrs['titles'][0]['title'];
                    }
                    if (empty($publisher) && !empty($attrs['publisher'])) {
                        $publisher = is_string($attrs['publisher']) ? $attrs['publisher'] : ($attrs['publisher']['name'] ?? null);
                    }
                    if (empty($journal) && !empty($attrs['container']['title'])) {
                        $journal = $attrs['container']['title'];
                    }
                    if (empty($year) && !empty($attrs['publicationYear'])) {
                        $year = $attrs['publicationYear'];
                    }
                    if (empty($authors) && !empty($attrs['creators']) && is_array($attrs['creators'])) {
                        foreach (array_slice($attrs['creators'], 0, 5) as $creator) {
                            $creatorName = $creator['name'] ?? trim(($creator['givenName'] ?? '') . ' ' . ($creator['familyName'] ?? ''));
                            if ($creatorName) {
                                $authors[] = $creatorName;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("DataCite API verification failed for DOI {$doi}: " . $e->getMessage());
            }
        }

        // Clean up title (remove HTML tags or linebreaks)
        if ($title) {
            $title = html_entity_decode(strip_tags($title));
            $title = preg_replace('/\s+/', ' ', trim($title));
        }

        $isValid = !empty($title) || !empty($scopusEid) || $isScopusIndexed;

        return [
            'valid' => $isValid,
            'doi' => $doi,
            'doi_url' => "https://doi.org/{$doi}",
            'title' => $title,
            'journal' => $journal,
            'publisher' => $publisher,
            'year' => $year,
            'authors' => $authors,
            'is_scopus_indexed' => $isScopusIndexed,
            'scopus_eid' => $scopusEid,
            'status_label' => $isScopusIndexed ? 'Indexed in Scopus' : ($isValid ? 'Verified DOI (Not indexed in Scopus)' : 'DOI Not Found'),
            'scopus_data' => [
                'eid' => $scopusEid,
                'verified_at' => now()->toIso8601String(),
                'journal' => $journal,
                'publisher' => $publisher,
                'year' => $year,
                'raw' => $scopusRaw,
            ],
        ];
    }
}
