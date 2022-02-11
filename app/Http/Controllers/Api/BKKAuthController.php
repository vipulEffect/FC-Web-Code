<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller; 

use App\User,App\Image,Hash,Mail;
use Illuminate\Support\Facades\Auth; 
use Validator;

class AuthController extends Controller 
{
	public $successStatus = 200;
	
	//Get Latest Week info from wallpapertypes table
	public function getLatestWeekInfo(){
		$latestWeekInfo = "SELECT * FROM wallpapertypes order by id desc limit 1"; #echo $latestWeekInfo;
		$result = DB::select($latestWeekInfo);
		if (count($result)> 0) {
			$latestWeekArr = array();
			foreach($result as $kk=>$row) {
				$latestWeekArr['week_num'] = $row->week_num;
				if(!empty($row->upcoming_wallpaper_ids)){
					$latestWeekArr['upcoming_wallpaper_ids'] = explode(",",$row->upcoming_wallpaper_ids);
				} else {
					$latestWeekArr['upcoming_wallpaper_ids'] = array();
				}
				if(!empty($row->past_wallpaper_ids)){
					$latestWeekArr['past_wallpaper_ids'] = explode(",",$row->past_wallpaper_ids);
				} else {
					$latestWeekArr['past_wallpaper_ids'] = array();
				}
				$latestWeekArr['creation_date'] = $row->created_at;
			}
			return $latestWeekArr;
		} else {
			return array();
		}
	}
	
	//For get Wallpapers At Home wallpaper display
	public function getWallpapersAtHome(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required',
				'deviceType' => 'required|in:1,2', //1=>mobile,2=>tablet,
				'pnToken' => 'sometimes|required', //(push notification) Fld not req but can't be empty
				'deviceName' => 'sometimes|required' //(Android/Iphone)Fld not req but can't be empty
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => implode(",",$validator->errors()->all()),
				"cause" => implode(",",$validator->errors()->all())
			], 401);
		} 
		$input = $request->all();
		$deviceType = $input["deviceType"];
		if($deviceType != ""){
			//Check wallpaper is exist or not
			$wallpaperExist = DB::table('images as img')
			->select('img.id')
			->first();
			#echo "<pre>wallpaperExist==";print_r($wallpaperExist);die;
			$finalArr = array();
			if(!empty($wallpaperExist)){
				$currentDate = date("Y-m-d"); //2021-05-27
				
				//Check wallpaper is exist or not
				$latestWeekInfo = $this->getLatestWeekInfo();
				//echo "<pre>latestWeekInfo==";print_r($latestWeekInfo);#die;
				if(!empty($latestWeekInfo)){ 
					//past wallpaper
					if(!empty($latestWeekInfo['past_wallpaper_ids'])){
						$pastWallpaper = DB::table('images')
								->whereIn('id', $latestWeekInfo['past_wallpaper_ids'])
								->get();
						//echo "<pre>pastWallpaper==";print_r($pastWallpaper);//die;
						if(!empty($pastWallpaper) && count($pastWallpaper)>0){
							foreach($pastWallpaper as $pastKey=>$pastVal){
								$finalArr['past'][$pastKey]['wallpaperId'] = $pastVal->id;
								$finalArr['past'][$pastKey]['wallpaperName'] = $pastVal->wallpaperName;
								$finalArr['past'][$pastKey]['wallpaperDate'] = $pastVal->wallpaperDate;
								if($deviceType == 1){ //phone
									$finalArr['past'][$pastKey]['fileName'] = $pastVal->phoneFilename;
									$finalArr['past'][$pastKey]['url'] = $pastVal->phoneUrl;
								} else if($deviceType == 2){ //tablet
									$finalArr['past'][$pastKey]['fileName'] = $pastVal->tabletFilename;
									$finalArr['past'][$pastKey]['url'] = $pastVal->tabletUrl;
								}
							}
						} else {
							$finalArr['past'] = array();
						}
					} else {
						$finalArr['past'] = array();
					}
					
					//upcoming wallpaper
					if(!empty($latestWeekInfo['upcoming_wallpaper_ids'])){
						$upcomingWallpaper = DB::table('images')
								->whereIn('id', $latestWeekInfo['upcoming_wallpaper_ids'])
								->get();
						//echo "<pre>upcomingWallpaper==";print_r($upcomingWallpaper);die;
						if(!empty($upcomingWallpaper) && count($upcomingWallpaper)>0){ 
							foreach($upcomingWallpaper as $upcomingKey=>$upcomingVal){
								$finalArr['upcoming'][$upcomingKey]['wallpaperId'] = $upcomingVal->id;
								$finalArr['upcoming'][$upcomingKey]['wallpaperName'] = $upcomingVal->wallpaperName;
								$finalArr['upcoming'][$upcomingKey]['wallpaperDate'] = $upcomingVal->wallpaperDate;
								if($deviceType == 1){ //phone
									$finalArr['upcoming'][$upcomingKey]['fileName'] = $upcomingVal->phoneFilename;
									$finalArr['upcoming'][$upcomingKey]['url'] = $upcomingVal->phoneUrl;
								} else if($deviceType == 2){ //tablet
									$finalArr['upcoming'][$upcomingKey]['fileName'] = $upcomingVal->tabletFilename;
									$finalArr['upcoming'][$upcomingKey]['url'] = $upcomingVal->tabletUrl;
								}
							}
						} else {
							$finalArr['upcoming'] = array();
						}
					} else {
						$finalArr['upcoming'] = array();
					}
				}
				
				//current wallpaper info (selected wallpaper)
				$wallpaperSelected = DB::table('appusers')
				->select('appusers.id','appusers.selWallpaperId','appusers.selWallpaperName','appusers.selWallpaperDate','appusers.selFileName','appusers.selFileUrl')
				->where('appusers.deviceToken', '=', $input["deviceToken"])
				->where('appusers.userEmail', '=', $input["userEmail"])
				->where('appusers.selWallpaperType', '=', $input["deviceType"])
				->first();
				#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
				if(!empty($wallpaperSelected)){
					$curKey =0;
					$finalArr['current'][$curKey]['wallpaperId'] = $wallpaperSelected->selWallpaperId;
					$finalArr['current'][$curKey]['wallpaperName'] = $wallpaperSelected->selWallpaperName;
					$finalArr['current'][$curKey]['wallpaperDate'] = $wallpaperSelected->selWallpaperDate;
					$finalArr['current'][$curKey]['fileName'] = $wallpaperSelected->selFileName;
					$finalArr['current'][$curKey]['url'] = $wallpaperSelected->selFileUrl;
				} else {
					$finalArr['current'] = array();
				}
				
				//Check this user is already exist in DB or not
				$isUsrExist = DB::table('appusers')
				->select('appusers.id','appusers.selWallpaperId','appusers.subscriptionDate','appusers.userSubscriptionType','appusers.admin_unlock_access','appusers.user_weekly_wallpaper_change_access')
				->where('appusers.deviceToken', '=', $input["deviceToken"])
				->where('appusers.userEmail', '=', $input["userEmail"])
				->first();
				#echo "<pre>isUsrExist==";print_r($isUsrExist);die;
				if(!empty($isUsrExist)){
					$finalArr['selectedWallpaperId'] = $isUsrExist->selWallpaperId;
					$finalArr['subscriptionDate'] = $isUsrExist->subscriptionDate;
					
					if($isUsrExist->userSubscriptionType == 0){//free
						$finalArr['userSubscriptionType'] = "free-Trial";
					} else if($isUsrExist->userSubscriptionType == 1){//paid weekly
						$finalArr['userSubscriptionType'] = "paid-weekly";
					} else if($isUsrExist->userSubscriptionType == 2){//paid monthly
						$finalArr['userSubscriptionType'] = "paid-monthly";
					}
					
					
					//check is user is eligible for chnage wallpaper or not
					if($isUsrExist->userSubscriptionType == 0){//free
						$renewDays = "+7 day";
					} else if($isUsrExist->userSubscriptionType == 1){//paid weekly
						$renewDays = "+7 day";
					} else if($isUsrExist->userSubscriptionType == 2){//paid monthly
						$renewDays = "+30 day";
					}
					$renewDateStr = strtotime($renewDays, strtotime($isUsrExist->subscriptionDate));
					$renewDate = date("Y-m-d", $renewDateStr);
					
					$currentDate = date("Y-m-d");
					$currentDateStr = strtotime($currentDate);
					
					
					if($currentDateStr <= $renewDateStr){ //true case
						if($isUsrExist->selWallpaperId != ""){
							if($isUsrExist->admin_unlock_access == 1){ //1=>If admin gave unlock access
								$finalArr['userCanChangeWallpaper'] = "Yes";
							} else if($isUsrExist->admin_unlock_access == 0){ //0=>bydefault
								if($isUsrExist->user_weekly_wallpaper_change_access == 0){ //0=>bydefault
									$finalArr['userCanChangeWallpaper'] = "No";
								}
								else if($isUsrExist->user_weekly_wallpaper_change_access == 1){ //1=>User_can_change_wallpaper
									$finalArr['userCanChangeWallpaper'] = "Yes";
								}
							}
						} else {
							$finalArr['userCanChangeWallpaper'] = "Yes";
						}
					} else { //expired case
						$finalArr['userCanChangeWallpaper'] = "No";
					}
					
					//update push notification key 
					if(!empty($input['pnToken'])){
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update([
						'pnToken' =>$input['pnToken'],
						'updated_at'=> now()
						]);
						$finalArr['pnToken'] = $input['pnToken'];
					} else {
						$finalArr['pnToken'] = NULL;
					}
					
					//update device name
					if(!empty($input['deviceName'])){
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update([
						'deviceName' =>$input['deviceName'],
						'updated_at'=> now()
						]);
						$finalArr['deviceName'] = $input['deviceName'];
					} else {
						$finalArr['deviceName'] = NULL;
					}
				} else {
					$finalArr['selectedWallpaperId'] = NULL;
					$finalArr['subscriptionDate'] = NULL;
					$finalArr['userSubscriptionType'] = NULL;
					$finalArr['userCanChangeWallpaper'] = "Yes";
					
					$finalArr['pnToken'] = NULL;
					$finalArr['deviceName'] = NULL;
				}
				return response()->json([
					"isSuccess" => "true",
					"data" => $finalArr,
					"errorCode" => "0",
					"errorMessage" => "",
					"cause" => ""
				], 200);
			} else {
				return response()->json([
					"isSuccess" => "false",
					"data" => NULL,
					"errorCode" => "9001",
					"errorMessage" => "Wallpaper is not exist",
					"cause" => "Wallpaper is not exist"
				], 200);
			}
		} else { 
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => "Device Type is not exist",
				"cause" => "Device Type is not exist"
			], 401);
		}
	}
	
	//For selected Wallpaper from Home wallpaper
	public function selectedWallpaper(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required',
				'userSubscriptionType' => 'sometimes|required|in:0,1,2', //0=>free,1=>weekly,2=>monthly
				'selWallpaperId' => 'required',
				'selWallpaperType' => 'required|in:1,2' //1=>mobile,2=>tablet
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => implode(",",$validator->errors()->all()),
				"cause" => implode(",",$validator->errors()->all())
			], 401);
		} 
		$input = $request->all();
		$deviceToken = $input["deviceToken"];
		if($deviceToken != ""){
			//Check selcted wallpaper is exist or not
			$wallpaperExist = DB::table('images as img')
			->select('img.id','img.wallpaperName','img.phoneFilename','img.phoneUrl','img.tabletFilename','img.tabletUrl')
			->where('img.id', '=', $input["selWallpaperId"])
			->first();
			#echo "<pre>wallpaperExist==";print_r($wallpaperExist);die;
			$finalArr = array();
			if(!empty($wallpaperExist)){
				$currentDate = date("Y-m-d"); //2021-05-27
				
				if($input["selWallpaperType"] == 1){ //1=>phone,2=>tablet
					$selFileName = $wallpaperExist->phoneFilename;
					$selFileUrl =  $wallpaperExist->phoneUrl;
				} else {
					$selFileName = $wallpaperExist->tabletFilename;
					$selFileUrl =  $wallpaperExist->tabletUrl;
				}
				
				//Check this user is already selected wallpaper or not
				$wallpaperSelected = DB::table('appusers')
				->select('appusers.id','appusers.admin_unlock_access')
				->where('appusers.deviceToken', '=', $input["deviceToken"])
				->where('appusers.userEmail', '=', $input["userEmail"])
				//->where('appusers.selWallpaperType', '=', $input["selWallpaperType"])
				->first();
				#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
				if(!empty($wallpaperSelected)){ //update entry
					DB::table('appusers')
					->where('id',$wallpaperSelected->id)
					->update([
					'selWallpaperId' => $input["selWallpaperId"],
					'selWallpaperName' => $wallpaperExist->wallpaperName,
					'selWallpaperType' => $input["selWallpaperType"],
					'selWallpaperDate' => $currentDate,
					'selFileName' => $selFileName,
					'selFileUrl' => $selFileUrl,
					'updated_at'=> now()
					]);
					
					//if user selected wallpaper again then admin_unlock_access bit set to 0 
					if($wallpaperSelected->admin_unlock_access == 1){
						DB::table('appusers')
						->where('id',$wallpaperSelected->id)
						->update([
						'admin_unlock_access' => 0,
						'updated_at'=> now()
						]);
					}
					return response()->json([
						"isSuccess" => "true",
						"data" => $input,
						"errorCode" => "0",
						"errorMessage" => "",
						"cause" => ""
					], 200);
				} else {
					return response()->json([
						"isSuccess" => "false",
						"data" => NULL,
						"errorCode" => "9001",
						"errorMessage" => "User is not exist in db",
						"cause" => "User is not exist in db"
					], 200);
				}
			} else {
				return response()->json([
					"isSuccess" => "false",
					"data" => NULL,
					"errorCode" => "9001",
					"errorMessage" => "Wallpaper is not exist in db",
					"cause" => "Wallpaper is not exist in db"
				], 200);
			}
		} else { 
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => "Device Token is not exist",
				"cause" => "Device Token is not exist"
			], 401);
		}
	}
	
	//For add edit subscription for user
	public function addUpdSubscription(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required'
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => implode(",",$validator->errors()->all()),
				"cause" => implode(",",$validator->errors()->all())
			], 401);
		} 
		$input = $request->all();
		
		$currentDate = date("Y-m-d"); //2021-05-27
				
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers')
		->select('appusers.id')
		->where('appusers.deviceToken', '=', $input["deviceToken"])
		->where('appusers.userEmail', '=', $input["userEmail"])
		->first();
		//echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			DB::table('appusers')
			->where('id',$isUsrSubscribed->id)
			->update([
			'subscriptionDate' =>$currentDate,
			'userSubscriptionType' =>2, //Monthly
			'updated_at'=> now()
			]);
			$input["userSubscriptionType"] = "Monthly";
			$input["subscriptionDate"] = $currentDate;
			//$input["userId"] = $isUsrSubscribed->id;
			
		} else {//insert user subscription as free user
			DB::table('appusers')->insert(
			[[
			'deviceToken' => $input["deviceToken"],
			'userEmail' => $input["userEmail"],
			'subscriptionDate' => $currentDate,
			'created_at' => now(),
			'updated_at' => now()
			]]);
			$input["userSubscriptionType"] = "Trial-Weekly";
			$input["subscriptionDate"] = $currentDate;
			//$input["userId"] = NULL;
		}
		return response()->json([
			"isSuccess" => "true",
			"data" => $input,
			"errorCode" => "0",
			"errorMessage" => "",
			"cause" => ""
		], 200);
	}
	
	//For get user subscription info
	public function getSubscriptionInfo(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required'
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => implode(",",$validator->errors()->all()),
				"cause" => implode(",",$validator->errors()->all())
			], 401);
		} 
		$input = $request->all();
		
		$currentDate = date("Y-m-d"); //2021-05-27
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.deviceToken','au.userEmail','au.subscriptionDate','au.userSubscriptionType')
		->where('au.deviceToken', '=', $input["deviceToken"])
		->where('au.userEmail', '=', $input["userEmail"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			$input["deviceToken"] = $isUsrSubscribed->deviceToken;
			$input["userEmail"] = $isUsrSubscribed->userEmail;
			$input["subscriptionBuyDate"] = $isUsrSubscribed->subscriptionDate;
			
			if($isUsrSubscribed->userSubscriptionType == 0){//free
				$input["userSubscriptionType"] = "Trial-Weekly";
			} else if($isUsrSubscribed->userSubscriptionType == 1){//paid weekly
				$input["userSubscriptionType"] = "Weekly";
			} else if($isUsrSubscribed->userSubscriptionType == 2){//paid monthly
				$input["userSubscriptionType"] = "Monthly";
			}
					
					
			//check is user is eligible for chnage wallpaper or not
			if($isUsrSubscribed->userSubscriptionType == 0){//free
				$renewDays = "+7 day";
			} else if($isUsrSubscribed->userSubscriptionType == 1){//paid weekly
				$renewDays = "+7 day";
			} else if($isUsrSubscribed->userSubscriptionType == 2){//paid monthly
				$renewDays = "+30 day";
			}
			$renewDateStr = strtotime($renewDays, strtotime($isUsrSubscribed->subscriptionDate));
			$renewDate = date("Y-m-d", $renewDateStr);
			
			$currentDate = date("Y-m-d");
			$currentDateStr = strtotime($currentDate);
			$input['subscriptionVaildDateUpto'] = $renewDate;
			if($currentDateStr <= $renewDateStr){
				$input['isUserSubscriptionActive'] = "Yes";
			} else {
				$input['isUserSubscriptionActive'] = "No";
			}
					
			return response()->json([
				"isSuccess" => "true",
				"data" => $input,
				"errorCode" => "0",
				"errorMessage" => "",
				"cause" => ""
			], 200);
		} else { 
			return response()->json([
				"isSuccess" => "false",
				"data" => NULL,
				"errorCode" => "9001",
				"errorMessage" => "User Info is not exist in db",
				"cause" => "User Info is not exist in db"
			], 200);
		}
	}
} 