<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Appuser,App\Image,App\User;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class DashboardController extends Controller
{
	use AuthenticatesUsers;
	
	public function index(Request $request){
		return view('remember_me.dashboard');
    }
}
