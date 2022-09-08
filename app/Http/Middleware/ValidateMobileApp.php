<?php

namespace App\Http\Middleware;

use App\Enums\ResponseStatus;
use Closure;
use Illuminate\Http\Request;

class ValidateMobileApp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        abort_if($request->headers->get('origin') == 'http://localhost' && $request->headers->get('x-requested-with') != 'org.madewithheart.receipt.app', ResponseStatus::BAD_REQUEST, "CORS");
        return $next($request);
    }
}
