<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScopusVerificationService;

class PublicationVerificationController extends Controller
{
    /**
     * Live AJAX endpoint to verify a publication DOI against Scopus & Crossref/DataCite.
     */
    public function verifyDoi(Request $request, ScopusVerificationService $service)
    {
        $doi = $request->query('doi') ?? $request->input('doi');

        if (empty($doi)) {
            return response()->json([
                'valid' => false,
                'message' => 'Please provide a valid DOI string.',
            ], 422);
        }

        $result = $service->verify($doi);

        return response()->json($result);
    }
}
