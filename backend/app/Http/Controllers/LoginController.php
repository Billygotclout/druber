<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            //code...
            //Validate Phone Number

            $request->validate([
                'phone' => 'required|numeric|min:11'
            ]);

            //Find or create user
            $user = User::firstOrCreate([
                'phone' => $request->phone
            ]);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found with that phone number'
                ], 401);
            }
            //Send user one time code
            $user->notify(new LoginNeedsVerification());


            //rETURN MESSAGE
            return response()->json([
                'message' => 'Text message notification sent to user.'
            ]);
        } catch (\Exception $th) {
            dd($th->getMessage());
        }


    }
    public function verify(Request $request)
    {
        // validate
        $request->validate([
            'phone' => 'required|numeric|min:11',
            'login_code' => 'required|numeric|between:100000,999999'
        ]);

        // find user
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)->first();

        // check if code matches

        if ($user) {
            $user->update([
                'login_code' => null
            ]);
            return $user->createToken($request->login_code)->plainTextToken;
        }


        //if so return token



        //if not return error
        return response()->json([
            'message' => 'Invalid login code'
        ], 401);
    }
}