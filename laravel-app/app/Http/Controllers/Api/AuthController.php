<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    
}


//login
//Valide email (required, email) e password (required) com $request->validate([...]).
//Busque o usuário: User::where('email', $request->email)->first().
//Se o usuário não existir ou Hash::check($request->password, $user->password) for falso, retorne response()->json(['message' => 'Credenciais inválidas'], 401). Use a mesma mensagem nos dois casos, para não revelar se o e-mail existe.
//Se passar, retorne ['token' => $user->createToken('api')->plainTextToken, 'user' => $user].

//logout

//$request->user()->currentAccessToken()->delete(); e retorne uma mensagem de sucesso.
//me
//return $request->user();