<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify mahasiswa identity before allowing access to their documents.
 * Checks session for verified mahasiswa ID.
 * Prevents IDOR attacks on public document routes.
 */
class VerifyMahasiswaIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        $mahasiswa = $request->route('mahasiswa');

        if (!$mahasiswa instanceof Mahasiswa) {
            $mahasiswa = Mahasiswa::findOrFail($mahasiswa);
        }

        // Check if already verified in session
        $verifiedId = $request->session()->get('verified_mahasiswa_id');
        if ($verifiedId === $mahasiswa->id) {
            return $next($request);
        }

        // Not verified — redirect to verification page
        return redirect()->route('landing.verify', $mahasiswa->id);
    }
}
