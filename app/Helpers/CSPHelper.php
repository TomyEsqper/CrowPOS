<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class CSPHelper
{
    /**
     * Get the CSP nonce for the current request
     */
    public static function nonce(): string
    {
        try {
            $request = app(Request::class);
            return $request->attributes->get('csp_nonce', '');
        } catch (\Exception $e) {
            // Fallback for testing environment or when request is not available
            return '';
        }
    }
}
