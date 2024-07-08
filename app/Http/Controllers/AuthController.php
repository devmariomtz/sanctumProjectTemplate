<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        // Usar POST para operaciones de login
        abort_if($request->isMethod('get'), 401, 'Unauthorized');

        $credentials = $request->only('email', 'password');

        // Validar credenciales antes de intentar autenticación
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('loginToken')->plainTextToken;
            $token_id = explode('|', $token)[0];

            // Asegurarse de que el token existe antes de intentar actualizarlo
            $tokenDb = SanctumPersonalAccessToken::find($token_id);
            if ($tokenDb) {
                $tokenDb->forceFill([
                    'expires_at' => now()->addMinutes(60)
                ])->save();
            }

            return response()->json([
                'user' => $user,
                'access_token' => $token,
            ]);
        }

        // Respuesta directa en caso de fallo de autenticación
        return response()->json(['message' => 'Invalid login details'], 401);
    }


    public function logout(Request $request)
    {
        $tmp_user = Auth::user();

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'user' => $tmp_user,
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json(auth()->user());
    }

    public function hi(){
        return response()->json([
            'message' => 'Hi, ' . Auth::user()->name,
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json($user);
    }
}
