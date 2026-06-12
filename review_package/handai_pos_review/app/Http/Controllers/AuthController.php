<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $firebase;
    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function showLoginPage()
    {
        return view("login");
    }
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            'password' => 'required',
        ]);
        $auth = $this->firebase->getAuth();
        try {
            $user = $auth->verifyPassword($request->email, $request->password);
            Session::put('user', $user);
            return redirect('dashboard')->with('success', 'Login successful');

        }catch (\Exception $e){
            return $e;
        }

    }
    public function logout()
    {
        Session::forget('user');
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}