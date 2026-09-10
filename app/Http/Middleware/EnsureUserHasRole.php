<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pembatas akses berdasarkan role.
 *
 * Contoh pemakaian di routes/web.php:
 *   Route::middleware('role:admin,tu')->group(fn () => ...);   // admin & TU boleh
 *   Route::middleware('role:admin')->group(fn () => ...);      // khusus admin
 *
 * Role 'kepsek' (Kepala Sekolah) didesain view-only, sehingga rute create/
 * update/delete pada tiap modul cukup dibungkus 'role:admin,tu' agar kepsek
 * otomatis tidak bisa mengakses (akan mendapat 403).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || (! empty($roles) && ! in_array($user->role, $roles, true))) {
            abort(403, 'Anda tidak memiliki hak akses untuk melakukan aksi ini.');
        }

        return $next($request);
    }
}
