<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use JWTAuth;
use App\Http\Requests;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth',['except'=>['authenticate']]);
    }

    public function index()
    {
        return "Auth index";
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->only('email','password');

        try{

            if( !$token = JWTAuth::attempt($credentials) ){
                return response()->json(
                    [
                        'error'=>'invalid_credentials'
                    ],401
                );
            }

        }catch(JWTException $e){
            return response()->json(['error'=>'could_not_create_token',500]);
        }

        return response()->json(compact('token'));

    }

    public function getAuthenticatedUser()
    {

        try{
            if(! $user = JWTAuth::parseToken()->authenticate()){
                return response()->json(['user_not_found'],404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));

    }

}
