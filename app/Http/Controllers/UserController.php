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

class UserController extends Controller
{
	use AuthenticatesUsers;
	//User login
	public function webLoginPost(Request $request)
    { 	
		#return $request->all();
		$this->validate($request, [
			'email' => 'required|email',
			'password' => 'required',
		]);

		$remember_me = $request->has('remember') ? true : false; 
		if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')], $remember_me))
		{ //die('ifff');
			$user = auth()->user();
			#Auth::login($user,true);
			return redirect('/list-wallpaper');
		}else{ //die('elsee');
			return back()->withInput()->with('message', 'Please enter the valid username and password');
		}
	}
	
	//User subscription listing
	public function sublist(Request $request)
    { #die('@@@@');
		if ($request->ajax()) { 
			$data = Appuser::select('id','userEmail','userSubscriptionType','subscriptionStartDate','subscriptionEndDate','admin_unlock_access','user_weekly_wallpaper_change_access');
			return Datatables::of($data)
			->addIndexColumn()
			->addColumn('action', function($row){ 
				if($row->userSubscriptionType == 0 || $row->userSubscriptionType ==1){ //Weekly-trail or Weekly Plan
					$subsExpDateFormated = strtotime($row->subscriptionEndDate);
					$todayDate = date("Y-m-d H:i:s");
					$todayDateFormated = strtotime($todayDate);
					
					if($subsExpDateFormated >= $todayDateFormated){//trial period
						//if($row->selWallpaperId != ""){ //if already selected wallpaper
							if($row->admin_unlock_access == 1){ //If admin gave unlock access
								$btn = '<a href="javascript:void(0)"><img src="/public/images/unlock.png"></a>';
							} else if($row->admin_unlock_access == 0){ //0=>bydefault
								$btn = '<a href="javascript:void(0)" onClick="unlockAccess('.$row->id.')"><img src="/public/images/lock.png"></a>';
							}
						/*} else { //if not selected wallpaper
							$btn = '<a href="javascript:void(0)"><img src="/public/images/unlock.png"></a>';
						}*/
					} else { //Expired
						$btn = '<a href="javascript:void(0)"><img src="/public/images/lock.png"></a>';
					}
				} 
				else if($row->userSubscriptionType == 2) { //Monthly
					$subsExpDateFormated = strtotime($row->subscriptionEndDate);
					
					$todayDate = date("Y-m-d");
					$todayDateFormated = strtotime($todayDate);
					
					if($subsExpDateFormated >= $todayDateFormated){//Subscribed
						//if($row->selWallpaperId != ""){ //if already selected wallpaper
							if($row->admin_unlock_access == 1){ //If admin gave unlock access (already)
								$btn = '<a href="javascript:void(0)"><img src="/public/images/unlock.png"></a>';
							} else if($row->admin_unlock_access == 0){ //0=>bydefault
								if($row->user_weekly_wallpaper_change_access == 0){ //0=>bydefault
									$btn = '<a href="javascript:void(0)" onClick="unlockAccess('.$row->id.')"><img src="/public/images/lock.png"></a>';
								}
								else if($row->user_weekly_wallpaper_change_access == 1){ //1=>User_can_change_wallpaper
									$btn = '<a href="javascript:void(0)"><img src="/public/images/lock.png"></a>';
								}
							}
						/*} else { //if not selected wallpaper
							$btn = '<a href="javascript:void(0)"><img src="/public/images/unlock.png"></a>';
						}*/
					} else { //Expired
						$btn = '<a href="javascript:void(0)"><img src="/public/images/lock.png"></a>';
					}
				}
				return $btn;
			})
			->rawColumns(['action'])
			->make(true);
		}
        return view('users.usersubscriptionlist');
	}
	
	/**
     * Show the view subscription WRT device.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewSubscription(Request $request)
    {   #echo "@@@@".$request->id;#die;
		$wallpaperArr = DB::table('appuserselwallpapers as ausw')
		->join('appusers as au', 'ausw.appUserId', '=', 'au.id')
		->select('au.userEmail','ausw.deviceToken','ausw.deviceName','ausw.selWallpaperType','ausw.selFileName')
		->where('ausw.appUserId','=',$request->id)
		->get();
		#echo "<pre>wallpaperArr=";print_r($wallpaperArr);die;
		if(count($wallpaperArr)>0){
			$finalArr = array();
			foreach($wallpaperArr as $fKey=>$fVal){
				$finalArr[$fKey]['userEmail'] = $fVal->userEmail;
				if($fVal->deviceName == ""){
					$finalArr[$fKey]['device'] = $fVal->deviceToken;
				} 
				else if($fVal->deviceToken == ""){
					$finalArr[$fKey]['device'] = $fVal->deviceName;
				} 
				else {
					$finalArr[$fKey]['device'] = $fVal->deviceName."-".$fVal->deviceToken;
				}
				
				if($fVal->selWallpaperType == 1){ //phone
					$imgPath = "https://fractalchaos.s3.ap-south-1.amazonaws.com/phoneWallpaper/".$fVal->selFileName;
				} else if($fVal->selWallpaperType == 2){ //tablet
					$imgPath = "https://fractalchaos.s3.ap-south-1.amazonaws.com/tabletWallpaper/".$fVal->selFileName;
				} else {
					$imgPath = "";
				}
				$finalArr[$fKey]['selFileName'] = $imgPath;
			}
			#echo "<pre>finalArr=";print_r($finalArr);die;
		} else {
			$finalArr = array();
		}
		return Response()->json($finalArr);
    }
	
	//push notification for mobile devices
	public function push_notification_mobile($pnToken){
		//echo "<pre>pnToken=";print_r($pnToken);die;
		//API URL of FCM
		$url = 'https://fcm.googleapis.com/fcm/send';
		/*api_key available in:
		Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key*/    
		$api_key = 'AAAA_UJ6460:APA91bGbiUafw9u5gCVNOI5WRiQ998kJAC9mgWo_DP23ydh2SyLGUPWT_Hz9SOZD2RX1gxA749i_xJlV6xQIQCHDehoKElpRgCZpMdO2bkLN3WsQuZyiIFcE88G8sfdoXm5LTRzyCl8C';
		
		$fields = array(
			"to" => $pnToken,
			"notification" => array(
				"title" => "Wallpaper Selection Access",
				"body" => "Site Admin has permitted you to select wallpaper again within your period.",
				"mutable_content" => "",
				"vibrate" => 1,
				"sound" => 1,
				"priority" => "high",
				"content_available"=> true
			)
		);
			
		//header includes Content type and api key
		$headers = array(
			'Content-Type:application/json',
			'Authorization:key='.$api_key
		);
					
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		#echo "<pre>result=";print_r($result);die;
		if ($result === FALSE) {
			die('FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}
	
	/**
     * To unlock access for user to select a wallpaper
     *
     * @param  \App\Appuser  $image
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
		//echo "id===".$request->id;die;
		//updated admin_unlock_access to 1
		$Appuser = Appuser::where('id', $request->id)->update(['admin_unlock_access' => 1,'isWallpaperSelected' =>0]);
		//notification to those user for wallpaper unlock
		//get user PN token
		if(!empty($Appuser)){
			//get users device pntoken
			$userInfo = DB::table('appuserselwallpapers as ausw')
			->select('ausw.id','ausw.appUserId','ausw.pnToken')
			->where('ausw.appUserId', '=', $request->id)
			->get();
			#echo "<pre>userInfo==";print_r($userInfo);#die;
			if(!empty($userInfo)){
				foreach($userInfo as $kk=>$vv){
					//send push notification to user
					$pushed = $this->push_notification_mobile($vv->pnToken);
					$pushed_decode = json_decode($pushed);
					//echo "<pre>pushed_decode=";print_r($pushed_decode);
					//echo $pushed_decode->success;die;
					if(!empty($pushed_decode) && $pushed_decode->success == 1){ //success
						DB::table('appusers')
						->where('id',$request->id)
						->update(['unlock_notification_sent' => 1]);
					}
				}
			}
			return Response()->json($Appuser);
		}
	}
}
