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
     * check if it is indexed in Elsevier Scopus, and cross-reference
     * its author list against assigned thesis supervisors.
     */
    public function verify(string $doiInput, iterable $supervisors = []): array
    {
        $cleanDoi = $this->extractDoi($doiInput);

        if (!$cleanDoi) {
            return [
                'valid' => false,
                'doi' => $doiInput,
                'error' => 'Invalid DOI format. A standard DOI starts with 10.xxxx/...',
                'is_scopus_indexed' => false,
                'has_supervisor_coauthor' => false,
                'matched_supervisor_name' => null,
                'matched_author' => null,
                'supervisors_checked' => [],
                'title' => null,
                'journal' => null,
                'authors' => [],
                'status_label' => 'Invalid DOI Format',
            ];
        }

        // Cache the raw bibliographic and Scopus lookup for 7 days
        $cacheKey = 'scopus_doi_verify_v2_' . md5(strtolower($cleanDoi));
        $doiDetails = Cache::remember($cacheKey, now()->addDays(7), function () use ($cleanDoi) {
            return $this->fetchDoiDetails($cleanDoi);
        });

        // Cross-reference authors with assigned supervisors
        $authors = $doiDetails['authors'] ?? [];
        $supervisorCheck = $this->matchSupervisorCoauthor($authors, $supervisors);

        $doiDetails['has_supervisor_coauthor'] = $supervisorCheck['matched'];
        $doiDetails['matched_supervisor_name'] = $supervisorCheck['supervisor'];
        $doiDetails['matched_author'] = $supervisorCheck['matched_author'];
        $doiDetails['supervisors_checked'] = $supervisorCheck['supervisors_checked'];
        $doiDetails['supervisor_match_message'] = $supervisorCheck['message'];

        // Determine comprehensive status label
        if (!$doiDetails['valid']) {
            $doiDetails['status_label'] = 'DOI Not Found';
        } elseif (!$doiDetails['is_scopus_indexed']) {
            $doiDetails['status_label'] = 'Not Indexed in Scopus';
        } elseif (empty($supervisorCheck['supervisors_checked'])) {
            $doiDetails['status_label'] = 'Indexed in Scopus (No supervisors assigned to check)';
        } elseif ($supervisorCheck['matched']) {
            $doiDetails['status_label'] = "Indexed in Scopus & Co-authored with {$supervisorCheck['supervisor']}";
        } else {
            $doiDetails['status_label'] = 'Indexed in Scopus (Supervisor Co-authorship Missing)';
        }

        return $doiDetails;
    }

    /**
     * Perform the actual HTTP checks against Elsevier Scopus API, Crossref API, and DataCite API.
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

                if (!empty($coreData['dc:creator'])) {
                    $creator = $coreData['dc:creator'];
                    if (is_array($creator)) {
                        foreach ($creator as $c) {
                            if (is_string($c)) $authors[] = trim($c);
                        }
                    } elseif (is_string($creator)) {
                        $authors[] = trim($creator);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Scopus API verification failed for DOI {$doi}: " . $e->getMessage());
        }

        // 2. Query Crossref API for authoritative bibliographic details & full author roster
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
                    $crossrefAuthors = [];
                    foreach ($message['author'] as $author) {
                        $authorName = trim(($author['given'] ?? '') . ' ' . ($author['family'] ?? ''));
                        if (empty($authorName) && !empty($author['name'])) {
                            $authorName = trim($author['name']);
                        }
                        if ($authorName && !in_array($authorName, $crossrefAuthors)) {
                            $crossrefAuthors[] = $authorName;
                        }
                    }
                    if (!empty($crossrefAuthors)) {
                        $authors = $crossrefAuthors;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Crossref API verification failed for DOI {$doi}: " . $e->getMessage());
        }

        // 3. Fallback to DataCite API if title or authors are still incomplete
        if (empty($title) || empty($authors)) {
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

                    if (empty($title) && !empty($attrs['titles'][0]['title'])) {
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
                        foreach ($attrs['creators'] as $creator) {
                            $creatorName = $creator['name'] ?? trim(($creator['givenName'] ?? '') . ' ' . ($creator['familyName'] ?? ''));
                            if ($creatorName && !in_array($creatorName, $authors)) {
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
            'authors' => array_values(array_unique(array_filter($authors))),
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

    /**
     * Cross-reference publication authors with assigned supervisors.
     */
    public function matchSupervisorCoauthor(array $publicationAuthors, iterable $supervisors): array
    {
        $supervisorNames = [];
        foreach ($supervisors as $s) {
            $name = is_string($s) ? $s : ($s->user?->name ?? $s->name ?? null);
            if ($name && !in_array($name, $supervisorNames)) {
                $supervisorNames[] = trim($name);
            }
        }

        if (empty($supervisorNames)) {
            return [
                'matched' => false,
                'supervisor' => null,
                'matched_author' => null,
                'supervisors_checked' => [],
                'message' => 'No supervisors are assigned to this candidate for cross-referencing.',
            ];
        }

        if (empty($publicationAuthors)) {
            return [
                'matched' => false,
                'supervisor' => null,
                'matched_author' => null,
                'supervisors_checked' => $supervisorNames,
                'message' => 'No authors could be retrieved from the DOI registry to cross-reference.',
            ];
        }

        // Tokenize publication authors
        $normalizedAuthors = [];
        foreach ($publicationAuthors as $auth) {
            $tokens = $this->tokenizeName($auth);
            if (!empty($tokens)) {
                $normalizedAuthors[] = [
                    'raw' => $auth,
                    'tokens' => $tokens,
                ];
            }
        }

        foreach ($supervisorNames as $rawSupName) {
            $cleanSupName = $this->stripHonorifics($rawSupName);
            $supTokens = $this->tokenizeName($cleanSupName);
            if (empty($supTokens)) continue;

            $supSurnameLast = end($supTokens);
            $supSurnameFirst = $supTokens[0];
            $allSupInitials = array_map(fn($t) => substr($t, 0, 1), $supTokens);

            foreach ($normalizedAuthors as $auth) {
                $authTokens = $auth['tokens'];

                foreach ([$supSurnameLast, $supSurnameFirst] as $candidateSurname) {
                    if (strlen($candidateSurname) < 2) continue;

                    $surnameFound = false;
                    foreach ($authTokens as $aToken) {
                        if ($aToken === $candidateSurname || (strlen($candidateSurname) >= 4 && levenshtein($aToken, $candidateSurname) <= 1)) {
                            $surnameFound = true;
                            break;
                        }
                    }

                    if ($surnameFound) {
                        $otherSupTokens = array_filter($supTokens, fn($t) => $t !== $candidateSurname);
                        if (empty($otherSupTokens)) {
                            return [
                                'matched' => true,
                                'supervisor' => $rawSupName,
                                'matched_author' => $auth['raw'],
                                'supervisors_checked' => $supervisorNames,
                                'message' => "Supervisor {$rawSupName} verified as author ({$auth['raw']}).",
                            ];
                        }

                        // Check for another full name token match
                        foreach ($otherSupTokens as $otherToken) {
                            if (strlen($otherToken) < 2) continue;
                            foreach ($authTokens as $aToken) {
                                if ($aToken === $otherToken || (strlen($otherToken) >= 4 && levenshtein($aToken, $otherToken) <= 1)) {
                                    return [
                                        'matched' => true,
                                        'supervisor' => $rawSupName,
                                        'matched_author' => $auth['raw'],
                                        'supervisors_checked' => $supervisorNames,
                                        'message' => "Supervisor {$rawSupName} verified as author ({$auth['raw']}).",
                                    ];
                                }
                            }
                        }

                        // Check for initials match (e.g. "O." for "Olufemi")
                        foreach ($otherSupTokens as $otherToken) {
                            $initial = substr($otherToken, 0, 1);
                            foreach ($authTokens as $aToken) {
                                if (strlen($aToken) === 1 && $aToken === $initial) {
                                    return [
                                        'matched' => true,
                                        'supervisor' => $rawSupName,
                                        'matched_author' => $auth['raw'],
                                        'supervisors_checked' => $supervisorNames,
                                        'message' => "Supervisor {$rawSupName} verified as author ({$auth['raw']}).",
                                    ];
                                }
                            }
                        }

                        // Check for multiple initials (e.g. "I. O." for "Isaac Oludayo")
                        foreach ($authTokens as $aToken) {
                            if (strlen($aToken) <= 3 && in_array(substr($aToken, 0, 1), $allSupInitials)) {
                                return [
                                    'matched' => true,
                                    'supervisor' => $rawSupName,
                                    'matched_author' => $auth['raw'],
                                    'supervisors_checked' => $supervisorNames,
                                    'message' => "Supervisor {$rawSupName} verified as author ({$auth['raw']}).",
                                ];
                            }
                        }
                    }
                }
            }
        }

        $supListStr = implode(', ', $supervisorNames);
        return [
            'matched' => false,
            'supervisor' => null,
            'matched_author' => null,
            'supervisors_checked' => $supervisorNames,
            'message' => "None of the assigned supervisors ({$supListStr}) were found in the publication author list.",
        ];
    }

    /**
     * Remove academic honorifics and titles from person name.
     */
    protected function stripHonorifics(string $name): string
    {
        $pattern = '/\b(prof(essor|\.)?|assoc(\.|\s+prof(\.|\s+essor)?)?|dr\.?|doctor|engr?\.?|engineer|mr\.?|mrs\.?|ms\.?|rev\.?|mal(lam)?\.?|sheikh|alhaji)\b/i';
        $cleaned = preg_replace($pattern, '', $name);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }

    /**
     * Tokenize and normalize a name string into alphabetical tokens.
     */
    protected function tokenizeName(string $name): array
    {
        $cleaned = strtolower(preg_replace('/[^a-zA-Z\s]/', ' ', $name));
        return array_values(array_filter(explode(' ', $cleaned), fn($t) => strlen($t) > 0));
    }
}
