<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            $user = User::where('email', $request->email)->first();
    
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->respondInvalid('Invalid Credentials');
            }
    
            $token = $user->createToken($user->id.'Auth-Token');
            return $this->respondSuccess('Success Retrieve Token', ['token' => $token->plainTextToken]);
        } catch (ValidationException $e) {
            return $this->respondInvalid('Invalid Input', $e->errors());
        } catch (\Throwable $th) {
            $this->respondInternalError('Error');
        }
        // return $this->respondSuccess('Login Success', new AuthResource($user));
    }
}
