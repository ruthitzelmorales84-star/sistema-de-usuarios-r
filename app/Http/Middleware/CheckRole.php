<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login', ['intended' => $request->fullUrl()]);
        }

        $isVerifiedProfessor = in_array('profesor', $roles, true)
            && $request->session()->get('academic_access_role') === 'profesor'
            && $request->session()->get('academic_2fa_verified') === true;

        if (! $isVerifiedProfessor && ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}