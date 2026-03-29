<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify mahasiswa identity before allowing access to their documents.
 * Checks session for verified mahasiswa ID with time-based expiry.
 * Prevents IDOR attacks on public document routes.
 */
class VerifyMahasiswaIdentity
{
    /**
     * Session verification expires after this many minutes.
     */
    private const VERIFICATION_TTL_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $mahasiswa = $request->route('mahasiswa');

        if (!$mahasiswa instanceof Mahasiswa) {
            $mahasiswa = Mahasiswa::findOrFail($mahasiswa);
        }

        // Check if already verified in session (with expiry)
        $verifiedId = $request->session()->get('verified_mahasiswa_id');
        $verifiedAt = $request->session()->get('verified_mahasiswa_at');

        if (
            $verifiedId === $mahasiswa->id &&
            $verifiedAt &&
            now()->diffInMinutes($verifiedAt) < self::VERIFICATION_TTL_MINUTES
        ) {
            return $next($request);
        }

        // Expired or not verified — clear and redirect to verification page
        $request->session()->forget(['verified_mahasiswa_id', 'verified_mahasiswa_at']);

        return redirect()->route('landing.verify', $mahasiswa->id);
    }
}
