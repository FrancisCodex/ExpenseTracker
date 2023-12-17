<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // if (!$request->expectsJson()) {
        //     return route('login'); // Change "login" to your actual login route name
        // }

        return response()->json([
            'message' => 'Authentication Required, Please Login',
            'status' => 401, // Optional to explicitly mention the status code
        ], 401);
    }
}
