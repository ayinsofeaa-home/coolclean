<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = $request->routeIs('admin.password.form', 'admin.password.change', 'logout');

        if ($request->user()?->PASSWORD_CHANGE_REQUIRED && ! $allowed) {
            return redirect()->route('admin.password.form')
                ->with('success', 'Please replace your temporary password before continuing.');
        }

        return $next($request);
    }
}
