<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['admin', 'super_admin'], true), 403);
        return $next($request);
    }
}