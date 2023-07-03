<?php

namespace App\Http\Middleware;

use Closure;

class Administrator {
    public function handle($request, Closure $next, $guard = null) {
        // если это не администратор — показываем 404 Not Found
        if ( ! auth()->user()->admin) {
            abort(404);
        }
        return $next($request);
    }
}
