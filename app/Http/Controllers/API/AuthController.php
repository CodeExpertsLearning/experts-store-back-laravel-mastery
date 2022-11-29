<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if(!auth()->attempt($credentials)) abort(401, 'Invalid Credentials');

        $expires = (Carbon::now())->addHours(2);

        return response()->json([
            'data' => [
                'token' => auth()->user()->createToken('default', ['*'], $expires)->plainTextToken
            ]
        ]);

        /**
         * Roles: Admin, Customer, Anonymous
         * Admin: store, update, destroy
         * Customer: list_orders
         * Anonymous: somente rotas que não verificam permissões de tokens...
         */
    }

    public function logout()
    {
        auth()->user()->tokens()->delete(); //remove todos os tokens existentes...

        return response()->json([], 204);
    }
}
