<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->guest(route('admin.login'));
        }

        if (! auth()->user()->hasPermission('admin.access')) {
            abort(403);
        }

        return $next($request);
    }
}
