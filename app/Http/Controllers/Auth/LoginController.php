<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        return $this->respondWithToken($token);
    }
    
    protected function respondWithToken($token)
    {
        $minutes = 60;
        $cookie = cookie('jwt', $token, $minutes, null, null, config('app.env') !== 'local', true);
    
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 60 * 60
        ])->withCookie($cookie);
    }
}