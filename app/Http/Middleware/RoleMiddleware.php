<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Sesuai aturan: Owner dan Admin memiliki wewenang setara
        $userRole = $user->role;
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $subRole) {
                $subRole = trim($subRole);
                $allowedRoles[] = $subRole;
                if ($subRole === 'admin' || $subRole === 'owner') {
                    $allowedRoles[] = 'admin';
                    $allowedRoles[] = 'owner';
                }
            }
        }
        $allowedRoles = array_unique($allowedRoles);

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
