<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Redirect;
class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function index(Request $request){
        $info = [];
        return view('remember_me.login', $info);
    }

    public function verify(Request $request){
		/*$this->validate($request, [
			'email' => 'required|email',
			'password' => 'required',
		]);

		$remember_me = $request->has('remember_me') ? true : false; 
		//echo $request->input('email');
		//echo $request->input('password');
		//die;
		if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')], $remember_me))
		{ die('ifff');
			$user = auth()->user();
			#Auth::login($user,true);
			//dd($user);
			//return redirect()->route('/list-wallpaper');
			return redirect('/list-wallpaper');
			//return redirect(route('/list'));
		}else{ die('elsee');
			return back()->withInput()->with('message', 'Please enter the valid username and password');
		}*/
		
		
        $credential = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember_me  = ( !empty( $request->remember_me ) )? TRUE : FALSE;

        if(Auth::attempt($credential)){ #die('ifff');
            $user = User::where(["email" => $credential['email']])->first();
            #echo "<pre>";print_r($user);die;
            Auth::login($user, $remember_me);
			return redirect(route('dashboard'));
          //  return redirect(route('list'));
        }else { die('elsee');
		}
    }

    public function logout(Request $request){
        Auth::logout();
        return redirect(route('remember-me.login'));
    }
}