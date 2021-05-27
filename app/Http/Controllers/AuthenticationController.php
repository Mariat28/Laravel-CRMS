<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['guest']);
    }

    public function index()
    {
        return view('auth.login');
    }

    public function loginUser(Request $request)
    {
        $this->validate($request,[
            'email'=> 'required|email',
            'password'=> 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        if(!Auth::attempt($credentials,$request->remember)){
            return back()->with('status', 'Invalid Login Details');
        }
        else{
            return redirect()->route('home');
        }
    }

    public function logoutUser()
    {
        Auth::logout();

        return redirect()->route('login');
    }
}
