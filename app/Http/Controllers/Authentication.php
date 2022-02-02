<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class Authentication extends Controller
{
    //
    public function register(Request $r)
    {
      $r->validate(['username' => 'required|min:3|max:20|string|unique:users',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|between:10,25']);
      $password = bcrypt($r->password);
	    $api_token = Str::random(100);
      $user = User::create(['username'=>$r->username,
							'password' =>$password,
							'email' => $r->email,
							'api_token' =>$api_token]);

        //TODO: handle the avatar
        if($r->avatar)
        {
            $user->avatar = $r->avatar;
            $user->save();
        } 
	    return response()->json(['success' => true, 'message' => ['user data'=>$user, 'api_token'=>$api_token]],201);
    }

    public function login(Request $r){

        $r->validate([ 'email' => 'required',
                      'password' =>'required'
      ]);

        $user = User::where('email',$r->email)->orWhere('username',$r->email)->first();
		if($user)$password = Hash::check($r->password, $user->password);

        if($user && $password){
           $user->api_token = Str::random(100) ;
           $user->save();
           return response()->json(['success' => true, "api_token"=>$user->api_token],201);
        }
        else
           return response()->json(['success' => false, 'message' => 'invalid email or password'], 401);
    }

    public function logout(Request $r){

        $user = User::where('api_token',$r->bearerToken())->first();
        if($user){
            $user->api_token ="";
            $user->save();
            return response()->json(['success'=>true,'message' =>'logout successfuly'] , 200);
        }
		return response()->json(['message' => 'you are not logged in'],200);
    }
}
