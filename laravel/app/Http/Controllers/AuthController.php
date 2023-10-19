<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //register function
    public function register(Request $request){

        $data = $request->validate([
              'name' => 'required|string',
              'email'=> 'required|email|string|unique:users,email',
              'password'=>[
                'required',
                'confirmed'
              ]
            ]);

        
        /** @var \App\Models\User $user **/    
        $user = User::create([
                'name'=>$data['name'],
                'email'=>$data['email'],
                'password'=>bcrypt($data['password'])
        ]);
        $token = $user->createToken('main')->plainTextToken;

        return response([
            'user'=>$user,
            'token'=>$token,
        ]);
    }

    // login function
    public function login(Request $request){

        // validate credentials
        $credentials = $request->validate([
            'email'=> 'required|email|string|exists:users,email',
            'password'=>[
                'required',
              ]
            ]);

            // try to login
            if (!Auth::attempt($credentials)) {
                // return a validation error in case informations are incorrect
                return response([
                    'error'=>"The Provided informations seems to be incorrect",
                ],422);
            }

            // prepare user data
            $user = Auth::user();
            $token = $user->createToken('main')->plainTextToken;

            // send back user data
            return response([
                    'user'=>$user,
                    'token'=>$token,
                ]);
    }
    public function logout(){
        /** @var User $user **/
        $user = Auth::user();
        // delete the current authenticated token  
        $user->currentAccessToken()->delete();

        return response([
            'success'=>true
        ]);
    }
}