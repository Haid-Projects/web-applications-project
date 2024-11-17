<?php

namespace App\Http\Controllers\Auth;

use App\Aspects\Logger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserAuth extends Controller
{
    use GeneralTrait;
    /**
     * Register
     */

    public function index()
    {
        $users=User::all();
        return $this->returnSuccessData($users, 'all users', 200);
    }
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            // 'avatar' => 'required',
            'gender' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'birthdate' => 'required',
        ]);
        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }

        if($request->photo) {
            $photoName = rand() . '.' . $request->photo->getClientOriginalExtension();
            $request->photo->move(public_path('users_photos'), $photoName);
            $path='/users_photos' . '/' . $photoName;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
           // 'avatar' => isset($request->photo) ? $path : $request->avatar ,
            'gender' => $request->gender ,
            'phone_number' => $request->phone_number ,
            'address' => $request->address,
            'birthdate' => $request->birthdate
        ]);
        return $this->login($request);
    }

    /**
     * Login
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()) {
            return  $this->returnValidationError($validator->errors()->first(),400);
        }
        //  $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $request->email)->first();
        if(isset($user)){
            if(Hash::check($request->password, $user->password)){
                //create token
                $token = $user->createToken('user_token')->plainTextToken;

                //response
                return $this->returnSuccessData($token, 'user logged in successfully', 200);
            }else{
                return $this->returnError('password did not match', 400);
            }
        }
        return $this->returnError('user not found', 404);
    }

    /**
     * Get User Profile
     */
    public function profile()
    {
        $user = Auth::guard('user')->user();
        return $this->returnSuccessData($user, '', 200);
    }
    /**
     * Logout
     */
    public function logout(){
        Auth::guard('user')->user()->tokens()->delete();

        return $this->returnSuccessData(null, 'user logged out successfully', 200);
    }

    public function test(){
        $user = Auth::guard('user')->user();
        return $this->returnSuccessData($user,'message', 200);
    }

}
