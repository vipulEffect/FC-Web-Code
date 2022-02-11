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
	//public $add_hr_min = "+5 hour +30 minutes"; //for time
	//public $add_hr_min_Sql = "330 MINUTE"; //for sql get current time
	
	public $add_hr_min = "+0 hour +00 minutes"; //for time
	public $add_hr_min_Sql = "0 MINUTE"; //for sql get current time
	
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
	
	////////////////////////////////////////////////////
	// android start /////////////////////////////////////
	////////////////////////////////////////////////////
	////////////////////////////////////////////////////
	
	//For get Wallpapers At Home wallpaper display fr andrid users
	public function getWallpapersAtHomeForAndroidUsers(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'deviceType' => 'required|in:1,2', //1=>mobile,2=>tablet,
				'pnToken' => 'sometimes|required', //(push notification) Fld not req but can't be empty
				'deviceName' => 'sometimes|required', //(Android/Iphone)Fld not req but can't be empty
				'subscriptionId' => 'required',
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
				$now = date("Y-m-d H:i:s");
				$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
				
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
				
				//check user email exist
				$isUsrExist = DB::table('appusers as ap')
				->select('ap.id','ap.subscriptionStartDate','ap.userSubscriptionType','ap.userSubscriptionDays','ap.subscriptionEndDate','ap.admin_unlock_access','ap.user_weekly_wallpaper_change_access','ap.isWallpaperSelected')
				->where('ap.userEmail', '=', $input["userEmail"])
				->first();
				#echo "<pre>isUsrExist==";print_r($isUsrExist);die;
				if(!empty($isUsrExist)){
					$finalArr['subscriptionDate'] = $isUsrExist->subscriptionStartDate;
				
					if($isUsrExist->userSubscriptionType == 0){//free
						$finalArr['userSubscriptionType'] = "free-Trial";
					} 
					else if($isUsrExist->userSubscriptionType == 1){//paid weekly
						$finalArr['userSubscriptionType'] = "1 week";
					} 
					else if($isUsrExist->userSubscriptionType == 2){ //paid monthly
						$finalArr['userSubscriptionType'] = "1 month";
					} 
					else if($isUsrExist->userSubscriptionType == 3){ //paid monthly
						$finalArr['userSubscriptionType'] = "3 month";
					} 
					else if($isUsrExist->userSubscriptionType == 4){ //paid monthly
						$finalArr['userSubscriptionType'] = "6 month";
					} 
					else if($isUsrExist->userSubscriptionType == 5){ //paid monthly
						$finalArr['userSubscriptionType'] = "1 year";
					}
					
					//////////////////////////////////////
					//added by vipul at 4 jan 2022 
					// in case if subscribed email used in new device then add new entry with device info
					/////////////////////////////////////
					
					//Check this device token is exist for this user or not
					$isdeviceTokenExist = DB::table('appuserselwallpapers as wall')
					->select('wall.id')
					->where('wall.appUserId', '=', $isUsrExist->id)
					->where('wall.deviceToken', '=', $input["deviceToken"])
					->get();
					#echo "<pre>isdeviceTokenExist==";print_r($isdeviceTokenExist);die;
					if(count($isdeviceTokenExist)<1){ //if no matching record
						//insert new device token 'appuserselwallpapers' table
						DB::table('appuserselwallpapers')->insert(
						[[
						'appUserId' => $isUsrExist->id,
						'deviceToken' => $input["deviceToken"],
						'created_at' => now(),
						'updated_at' => now()
						]]);
					}
					
					
					//////////////////////////////////////
					//end code by vipul at 4 jan 2022 
					/////////////////////////////////////
					
					
					//check user selected wallpaper for this device or not (current wallpaper)
					$wallpaperSelected = DB::table('appuserselwallpapers as ausw')
					->select('ausw.id','ausw.appUserId','ausw.deviceToken','ausw.selWallpaperId','ausw.selWallpaperName','ausw.selWallpaperDate','ausw.selFileName','ausw.selFileUrl')
					->where('ausw.deviceToken', '=', $input["deviceToken"])
					->where('ausw.appUserId', '=', $isUsrExist->id)
					->where('ausw.selWallpaperType', '=', $input["deviceType"])
					->first();
					#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
					if(!empty($wallpaperSelected)){
						$curKey =0;
						$finalArr['current'][$curKey]['wallpaperId'] = $wallpaperSelected->selWallpaperId;
						$finalArr['current'][$curKey]['wallpaperName'] = $wallpaperSelected->selWallpaperName;
						$finalArr['current'][$curKey]['wallpaperDate'] = $wallpaperSelected->selWallpaperDate;
						$finalArr['current'][$curKey]['fileName'] = $wallpaperSelected->selFileName;
						$finalArr['current'][$curKey]['url'] = $wallpaperSelected->selFileUrl;
						
						$finalArr['selectedWallpaperId'] = $wallpaperSelected->selWallpaperId;
						
						//Function is used to check any user can change wallpaper or not
						$renewDateStr = strtotime($isUsrExist->subscriptionEndDate);
						$currentDateStr = strtotime($currentDate);
						#echo $isUsrExist->subscriptionEndDate."====".$currentDate."<br/>";
						#echo $renewDateStr."===".$renewDateStr;die;
						$finalArr['userCanChangeWallpaper'] = $this->userCanChangeWallpaperOrNot($renewDateStr,$currentDateStr,$isUsrExist->isWallpaperSelected,$isUsrExist->admin_unlock_access,$isUsrExist->user_weekly_wallpaper_change_access);
						
					} else {
						$finalArr['current'] = array();
						$finalArr['selectedWallpaperId'] = NULL;
						
						//Function is used to check any user can change wallpaper or not
						$renewDateStr = strtotime($isUsrExist->subscriptionEndDate);
						$currentDateStr = strtotime($currentDate);
						#echo $isUsrExist->subscriptionEndDate."====".$currentDate."<br/>";
						#echo $renewDateStr."===".$renewDateStr;die;
						$finalArr['userCanChangeWallpaper'] = $this->userCanChangeWallpaperOrNot($renewDateStr,$currentDateStr,$isUsrExist->isWallpaperSelected,$isUsrExist->admin_unlock_access,$isUsrExist->user_weekly_wallpaper_change_access);
					}
					
					//update push notification key 
					if(!empty($input['pnToken'])){
						//update in main table
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update(['pnTokenLatest' =>$input['pnToken'],'updated_at'=> now()]);
						
						//check user with same device record exist or not
						$isDeviceTokenExist = DB::table('appuserselwallpapers as ausw')
						->select('ausw.id')
						->where('ausw.deviceToken', '=', $input["deviceToken"])
						->where('ausw.appUserId', '=', $isUsrExist->id)
						->first();
						if(!empty($isDeviceTokenExist)){
							DB::table('appuserselwallpapers')
							->where('id',$isDeviceTokenExist->id)
							->update(['pnToken' =>$input['pnToken'],'updated_at'=> now()]);
						}
						$finalArr['pnToken'] = $input['pnToken'];
					} else {
						$finalArr['pnToken'] = NULL;
					}
					
					//update device name
					if(!empty($input['deviceName'])){
						//update in main table
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update(['deviceNameLatest' =>$input['deviceName'],'updated_at'=> now()]);
						
						//check user with same device record exist or not
						$isDeviceNameExist = DB::table('appuserselwallpapers as ausw')
						->select('ausw.id')
						->where('ausw.deviceToken', '=', $input["deviceToken"])
						->where('ausw.appUserId', '=', $isUsrExist->id)
						->first();
						if(!empty($isDeviceNameExist)){
							DB::table('appuserselwallpapers')
							->where('id',$isDeviceNameExist->id)
							->update(['deviceName' =>$input['deviceName'],'updated_at'=> now()]);
						}
						
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
	
	//For selected Wallpaper from Home wallpaper for android user
	public function selectedWallpaperForAndroidUsers(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required',
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
				$now = date("Y-m-d H:i:s");
				$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
				
				if($input["selWallpaperType"] == 1){ //1=>phone,2=>tablet
					$selFileName = $wallpaperExist->phoneFilename;
					$selFileUrl =  $wallpaperExist->phoneUrl;
				} else {
					$selFileName = $wallpaperExist->tabletFilename;
					$selFileUrl =  $wallpaperExist->tabletUrl;
				}
				
				//check user email exist
				$isUsrExist = DB::table('appusers as ap')
				->select('ap.id','ap.subscriptionStartDate','ap.admin_unlock_access','ap.user_weekly_wallpaper_change_access','ap.isWallpaperSelected')
				->where('ap.userEmail', '=', $input["userEmail"])
				->first();
				#echo "<pre>isUsrExist==";print_r($isUsrExist);#die;
				if(!empty($isUsrExist)){
					//check user selected wallpaper for this device or not (current wallpaper)
					$wallpaperSelected = DB::table('appuserselwallpapers as ausw')
					->select('ausw.id')
					->where('ausw.deviceToken', '=', $input["deviceToken"])
					->where('ausw.appUserId', '=', $isUsrExist->id)
					->first();
					#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
					if(!empty($wallpaperSelected)){ //update entry
						DB::table('appuserselwallpapers')
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
						if($isUsrExist->admin_unlock_access == 1){
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['admin_unlock_access' => 0,'updated_at'=> now()]);
						}
						
						if($isUsrExist->user_weekly_wallpaper_change_access == 1){
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['user_weekly_wallpaper_change_access' => 0,'updated_at'=> now()]);
						}
						
						if($isUsrExist->isWallpaperSelected == 0) {//if not selected till now
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['isWallpaperSelected' => 1,'updated_at'=> now()]);
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
	
	//get user subscription type
	public function getUsrSubscriptionType($userSubscriptionDays){
		$subsArr = array();
		if($userSubscriptionDays == 7){ //weekly
			$subsArr['userSubscriptionType'] = 1; 
			$subsArr['userSubscriptionText'] = "weekly";
		}
		else if($userSubscriptionDays == 30){ //1 month
			$subsArr['userSubscriptionType'] = 2;
			$subsArr['userSubscriptionText'] = "1 month";
		}
		else if($userSubscriptionDays == 90){ //3 months
			$subsArr['userSubscriptionType'] = 3;
			$subsArr['userSubscriptionText'] = "3 months";
		}
		else if($userSubscriptionDays == 180){ //6 months
			$subsArr['userSubscriptionType'] = 4;
			$subsArr['userSubscriptionText'] = "6 months";
		}
		else if($userSubscriptionDays == 365){ //1 year
			$subsArr['userSubscriptionType'] = 5;
			$subsArr['userSubscriptionText'] = "1 year";
		}
		return $subsArr;
	}
	
	//For add edit subscription for android users 
	public function addUpdSubscriptionForAndroidUsers(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required',
				'subscriptionStartDate' => 'required',
				'userSubscriptionDays' => 'required',
				'subscriptionEndDate' => 'required'
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
		
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.subscriptionId','au.userSubscriptionType','au.deviceTokenLatest')
		->where('au.userEmail', '=', $input["userEmail"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			
			if($input["userSubscriptionDays"] !=""){
				$subsArr = $this->getUsrSubscriptionType($input["userSubscriptionDays"]);
				if(!empty($subsArr)){
					$userSubscriptionType = $subsArr['userSubscriptionType'];
					$userSubscriptionText = $subsArr['userSubscriptionText'];
				} else {
					$userSubscriptionType = "";
					$userSubscriptionText = "";
				}
			} else {
				$userSubscriptionType = "";
				$userSubscriptionText = "";
			}
			if($isUsrSubscribed->subscriptionId != $input["subscriptionId"]){ //subscription id not match
				DB::table('appusers')
				->where('id',$isUsrSubscribed->id)
				->update([
				'deviceTokenLatest' => $input["deviceToken"],
				'subscriptionId' => $input["subscriptionId"],
				'subscriptionStartDate' => $input["subscriptionStartDate"],
				'userSubscriptionType' => $userSubscriptionType , //Monthly
				'userSubscriptionDays' => $input["userSubscriptionDays"],
				'subscriptionEndDate' => $input["subscriptionEndDate"],
				'updated_at' => now()
				]);
			} 
			
			
			//Check this device token is exist for this user or not
			$isdeviceTokenExist = DB::table('appuserselwallpapers as wall')
			->select('wall.id')
			->where('wall.appUserId', '=', $isUsrSubscribed->id)
			->where('wall.deviceToken', '=', $input["deviceToken"])
			->get();
			#echo "<pre>isdeviceTokenExist==";print_r($isdeviceTokenExist);die;
			if(count($isdeviceTokenExist)<1){ //if no matching record
				//insert new device token 'appuserselwallpapers' table
				DB::table('appuserselwallpapers')->insert(
				[[
				'appUserId' => $isUsrSubscribed->id,
				'deviceToken' => $input["deviceToken"],
				'created_at' => now(),
				'updated_at' => now()
				]]);
			}
			
			//If device token not matched from last one
			if($isUsrSubscribed->deviceTokenLatest != $input["deviceToken"]){
				//update 'appusers' table
				DB::table('appusers')
				->where('id',$isUsrSubscribed->id)
				->update(['deviceTokenLatest' => $input["deviceToken"],'updated_at' => now()]);
			}
			
			$input["userSubscriptionType"] = $userSubscriptionText;
		} else {//insert user subscription as free user
			$subscriptionStartDate = strtotime($input["subscriptionStartDate"]);
			$subscriptionEndDateStr = strtotime("+7 days", $subscriptionStartDate);
			//$subscriptionEndDateStr = strtotime("+7 minutes", $subscriptionStartDate); //For PN Testing purpose
			$subscriptionEndDate =  date('Y-m-d H:i:s', $subscriptionEndDateStr);
			
			//insert in  appusers table
			$lastInsertedId = DB::table('appusers')->insertGetId(array
			(
			'deviceTokenLatest' => $input["deviceToken"],
			'userEmail' => $input["userEmail"],
			'subscriptionId' => $input["subscriptionId"],
			'subscriptionStartDate' => $input["subscriptionStartDate"],
			'userSubscriptionType' => 0, //Free trial
			
			'userSubscriptionDays' => 7, //no of days
			//'userSubscriptionDays' => 0, //For PN Testing purpose
			
			'subscriptionEndDate' => $subscriptionEndDate,
			'created_at' => now(),
			'updated_at' => now()
			));
			
			
			#echo "lastInsertedId=".$lastInsertedId;die;
			if($lastInsertedId != ""){
				//insert in  appuserselwallpapers table
				DB::table('appuserselwallpapers')->insert(
				[[
				'appUserId' => $lastInsertedId,
				'deviceToken' => $input["deviceToken"],
				'created_at' => now(),
				'updated_at' => now()
				]]);
			}
			$input["userSubscriptionType"] = "Trial-Weekly";
		}
		return response()->json([
			"isSuccess" => "true",
			"data" => $input,
			"errorCode" => "0",
			"errorMessage" => "",
			"cause" => ""
		], 200);
	}
	
	//For get user subscription info for android users
	public function getSubscriptionInfoForAndroidUsers(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId'=> 'required'
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
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.userEmail','au.subscriptionId','au.subscriptionStartDate','au.userSubscriptionType','au.userSubscriptionDays','au.subscriptionEndDate')
		->where('au.userEmail', '=', $input["userEmail"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			$input["deviceToken"] = $input["deviceToken"];
			$input["userEmail"] = $isUsrSubscribed->userEmail;
			$input["subscriptionId"] = $isUsrSubscribed->subscriptionId;
			$input["subscriptionStartDate"] = $isUsrSubscribed->subscriptionStartDate;
			$input["userSubscriptionDays"] = $isUsrSubscribed->userSubscriptionDays;
			$input["subscriptionEndDate"] = $isUsrSubscribed->subscriptionEndDate;
			
			//check is user subscription is active or not
			$subscriptionEndDateStr = strtotime($isUsrSubscribed->subscriptionEndDate);
			
			$now = date("Y-m-d H:i:s");
			$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
			$currentDateStr = strtotime($currentDate);
			if($currentDateStr <= $subscriptionEndDateStr){
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
	
	////////////////////////////////////////////////////
	// android end/////////////////////////////////////
	////////////////////////////////////////////////////
	////////////////////////////////////////////////////
	
	
	////////////////////////////////////////////////////
	// IoS start/////////////////////////////////////////
	////////////////////////////////////////////////////
	////////////////////////////////////////////////////
	
	
	//check if user selected wallpaper in any device under same subscription
	/*public function isSelWallInAnyDeviceInSameSubs($appUserId){ 
		$isSelWallInAnyDeviceInSameSubs = DB::table('appuserselwallpapers as ausw')
		->select('ausw.id','ausw.appUserId','ausw.selWallpaperId')
		->where('ausw.appUserId', '=', $appUserId)
		->whereNotNull('ausw.selWallpaperId')
		->get();
		#echo "<pre>isSelWallInAnyDeviceInSameSubs==";print_r($isSelWallInAnyDeviceInSameSubs);#die;
		#echo count($isSelWallInAnyDeviceInSameSubs);
		if(count($isSelWallInAnyDeviceInSameSubs)>=1){ 
			#die('already-selected-wallpaper-in-any-device');
			return true;
		} else { 
			#die('not-selected-wallpaper-in-any-device');
			return false;
		}
	}*/
	
	//Function is used to check any user can change wallpaper or not
	public function userCanChangeWallpaperOrNot($renewDateStr,$currentDateStr,$isWallpaperSelected,$admin_unlock_access,$user_weekly_wallpaper_change_access){
		#echo $renewDateStr.",".$currentDateStr.",".$isWallpaperSelected.",".$admin_unlock_access.",".$user_weekly_wallpaper_change_access;die;
		if($currentDateStr <= $renewDateStr){ #die('active');//subscription active case
			if($isWallpaperSelected == 1){ //If user already selected wallpaper in any single device
				if($admin_unlock_access == 1){ //1=>If admin gave unlock access
					$userCanChangeWallpaper = "Yes";
				} 
				else if($admin_unlock_access == 0){ //0=>bydefault
					if($user_weekly_wallpaper_change_access == 0){ //0=>bydefault
						$userCanChangeWallpaper = "No";
					}
					else if($user_weekly_wallpaper_change_access == 1){ //1=>User_can_change_wallpaper
						$userCanChangeWallpaper = "Yes";
					}
				}
			}
			else if($isWallpaperSelected == 0){ //If user not selected wallpaper in any single device
				/*if($admin_unlock_access == 1){ //1=>If admin gave unlock access
					$userCanChangeWallpaper = "Yes";
				} else if($admin_unlock_access == 0){ //0=>bydefault
					if($user_weekly_wallpaper_change_access == 0){ //0=>bydefault
						$userCanChangeWallpaper = "Yes";
					}
					else if($user_weekly_wallpaper_change_access == 1){ //1=>User_can_change_wallpaper
						$userCanChangeWallpaper = "Yes";
					}
				}*/
				$userCanChangeWallpaper = "Yes";
			}
		} else { #die('expire'); //subscription expired case
			$userCanChangeWallpaper = "No";
		}
		return $userCanChangeWallpaper;
	}
	
					
	//For get Wallpapers At Home wallpaper display fr IOS users
	public function getWallpapersAtHome(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'deviceType' => 'required|in:1,2', //1=>mobile,2=>tablet,
				'pnToken' => 'sometimes|required', //(push notification) Fld not req but can't be empty
				'deviceName' => 'sometimes|required', //(Android/Iphone)Fld not req but can't be empty
				'subscriptionId' => 'required',
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
				$now = date("Y-m-d H:i:s");
				$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
				
				#echo date_default_timezone_get()."<br>";
				#echo $now."===".$currentDate;die;
				//Check wallpaper is exist or not
				$latestWeekInfo = $this->getLatestWeekInfo();
				#echo "<pre>latestWeekInfo==";print_r($latestWeekInfo);die;
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
				
				//check user subscription exist
				$isUsrExist = DB::table('appusers as ap')
				->select('ap.id','ap.subscriptionStartDate','ap.userSubscriptionType','ap.userSubscriptionDays','ap.subscriptionEndDate','ap.admin_unlock_access','ap.user_weekly_wallpaper_change_access','ap.isWallpaperSelected')
				->where('ap.subscriptionId', '=', $input["subscriptionId"])
				->first();
				#echo "<pre>isUsrExist==";print_r($isUsrExist);die;
				if(!empty($isUsrExist)){
					$finalArr['subscriptionDate'] = $isUsrExist->subscriptionStartDate;
				
					if($isUsrExist->userSubscriptionType == 0){//free
						$finalArr['userSubscriptionType'] = "free-Trial";
					} 
					else if($isUsrExist->userSubscriptionType == 1){//paid weekly
						$finalArr['userSubscriptionType'] = "1 week";
					} 
					else if($isUsrExist->userSubscriptionType == 2){ //paid monthly
						$finalArr['userSubscriptionType'] = "1 month";
					} 
					else if($isUsrExist->userSubscriptionType == 3){ //paid monthly
						$finalArr['userSubscriptionType'] = "3 month";
					} 
					else if($isUsrExist->userSubscriptionType == 4){ //paid monthly
						$finalArr['userSubscriptionType'] = "6 month";
					} 
					else if($isUsrExist->userSubscriptionType == 5){ //paid monthly
						$finalArr['userSubscriptionType'] = "1 year";
					}
					
					
					//check user selected wallpaper for this device or not (current wallpaper)
					$wallpaperSelected = DB::table('appuserselwallpapers as ausw')
					->select('ausw.id','ausw.appUserId','ausw.deviceToken','ausw.selWallpaperId','ausw.selWallpaperName','ausw.selWallpaperDate','ausw.selFileName','ausw.selFileUrl')
					->where('ausw.deviceToken', '=', $input["deviceToken"])
					->where('ausw.appUserId', '=', $isUsrExist->id)
					->where('ausw.selWallpaperType', '=', $input["deviceType"])
					->first();
					#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
					if(!empty($wallpaperSelected)){ #die('iff');
						$curKey =0;
						$finalArr['current'][$curKey]['wallpaperId'] = $wallpaperSelected->selWallpaperId;
						$finalArr['current'][$curKey]['wallpaperName'] = $wallpaperSelected->selWallpaperName;
						$finalArr['current'][$curKey]['wallpaperDate'] = $wallpaperSelected->selWallpaperDate;
						$finalArr['current'][$curKey]['fileName'] = $wallpaperSelected->selFileName;
						$finalArr['current'][$curKey]['url'] = $wallpaperSelected->selFileUrl;
						
						$finalArr['selectedWallpaperId'] = $wallpaperSelected->selWallpaperId;
						
						//Function is used to check any user can change wallpaper or not
						$renewDateStr = strtotime($isUsrExist->subscriptionEndDate);
						$currentDateStr = strtotime($currentDate);
						#echo $isUsrExist->subscriptionEndDate."====".$currentDate."<br/>";
						#echo $renewDateStr."===".$renewDateStr;die;
						$finalArr['userCanChangeWallpaper'] = $this->userCanChangeWallpaperOrNot($renewDateStr,$currentDateStr,$isUsrExist->isWallpaperSelected,$isUsrExist->admin_unlock_access,$isUsrExist->user_weekly_wallpaper_change_access);
					} else { #die('elsee');
						$finalArr['current'] = array();
						$finalArr['selectedWallpaperId'] = NULL;
						
						//Function is used to check any user can change wallpaper or not
						$renewDateStr = strtotime($isUsrExist->subscriptionEndDate);
						$currentDateStr = strtotime($currentDate);
						#echo $isUsrExist->subscriptionEndDate."====".$currentDate."<br/>";
						#echo $renewDateStr."===".$renewDateStr;die;
						$finalArr['userCanChangeWallpaper'] = $this->userCanChangeWallpaperOrNot($renewDateStr,$currentDateStr,$isUsrExist->isWallpaperSelected,$isUsrExist->admin_unlock_access,$isUsrExist->user_weekly_wallpaper_change_access);
					}
					
					//update push notification key 
					if(!empty($input['pnToken'])){ 
						//update in main table
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update(['pnTokenLatest' =>$input['pnToken'],'updated_at'=> now()]);
						
						//check user with same device record exist or not
						$isDeviceTokenExist = DB::table('appuserselwallpapers as ausw')
						->select('ausw.id')
						->where('ausw.deviceToken', '=', $input["deviceToken"])
						->where('ausw.appUserId', '=', $isUsrExist->id)
						->first();
						if(!empty($isDeviceTokenExist)){
							DB::table('appuserselwallpapers')
							->where('id',$isDeviceTokenExist->id)
							->update(['pnToken' =>$input['pnToken'],'updated_at'=> now()]);
						}
						$finalArr['pnToken'] = $input['pnToken'];
					} else { 
						$finalArr['pnToken'] = NULL;
					}
					
					//update device name
					if(!empty($input['deviceName'])){
						//update in main table
						DB::table('appusers')
						->where('id',$isUsrExist->id)
						->update(['deviceNameLatest' =>$input['deviceName'],'updated_at'=> now()]);
						
						//check user with same device record exist or not
						$isDeviceNameExist = DB::table('appuserselwallpapers as ausw')
						->select('ausw.id')
						->where('ausw.deviceToken', '=', $input["deviceToken"])
						->where('ausw.appUserId', '=', $isUsrExist->id)
						->first();
						if(!empty($isDeviceNameExist)){
							DB::table('appuserselwallpapers')
							->where('id',$isDeviceNameExist->id)
							->update(['deviceName' =>$input['deviceName'],'updated_at'=> now()]);
						}
						
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
	
	//For selected Wallpaper from Home wallpaper for IOS user
	public function selectedWallpaper(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required',
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
				$now = date("Y-m-d H:i:s");
				$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
				
				if($input["selWallpaperType"] == 1){ //1=>phone,2=>tablet
					$selFileName = $wallpaperExist->phoneFilename;
					$selFileUrl =  $wallpaperExist->phoneUrl;
				} else {
					$selFileName = $wallpaperExist->tabletFilename;
					$selFileUrl =  $wallpaperExist->tabletUrl;
				}
				
				//check user email exist
				$isUsrExist = DB::table('appusers as ap')
				->select('ap.id','ap.subscriptionStartDate','ap.admin_unlock_access','ap.user_weekly_wallpaper_change_access','ap.isWallpaperSelected')
				->where('ap.subscriptionId', '=', $input["subscriptionId"])
				->first();
				#echo "<pre>isUsrExist==";print_r($isUsrExist);#die;
				if(!empty($isUsrExist)){
					//check user selected wallpaper for this device or not (current wallpaper)
					$wallpaperSelected = DB::table('appuserselwallpapers as ausw')
					->select('ausw.id')
					->where('ausw.deviceToken', '=', $input["deviceToken"])
					->where('ausw.appUserId', '=', $isUsrExist->id)
					->first();
					#echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
					if(!empty($wallpaperSelected)){ //update entry
						DB::table('appuserselwallpapers')
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
						if($isUsrExist->admin_unlock_access == 1){
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['admin_unlock_access' => 0,'updated_at'=> now()]);
						}
						
						if($isUsrExist->user_weekly_wallpaper_change_access == 1){
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['user_weekly_wallpaper_change_access' => 0,'updated_at'=> now()]);
						}
						
						if($isUsrExist->isWallpaperSelected == 0) {//if not selected till now
							DB::table('appusers')
							->where('id',$isUsrExist->id)
							->update(['isWallpaperSelected' => 1,'updated_at'=> now()]);
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
	
	//For add edit subscription for ios users (For Live users)
	public function addUpdSubscription(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required',
				'subscriptionStartDate' => 'required',
				'userSubscriptionDays' => 'required',
				'subscriptionEndDate' => 'required'
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
		
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.subscriptionId','au.userSubscriptionType','au.deviceTokenLatest')
		->where('au.subscriptionId', '=', $input["subscriptionId"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		
		if(!empty($isUsrSubscribed) ){ //update user subscription as monthly user
			
			//Check user email and subscription id is exist or not
			$isSameUsrEmailAndSameSubscriptionIdExist = DB::table('appusers as au')
			->select('au.id','au.subscriptionId','au.userSubscriptionType','au.deviceTokenLatest')
			->where('au.userEmail', '=', $input["userEmail"])
			->where('au.subscriptionId', '=', $input["subscriptionId"])
			->first();
			#echo "<pre>isSameUsrEmailAndSameSubscriptionIdExist==";print_r($isSameUsrEmailAndSameSubscriptionIdExist);die;
			if(empty($isSameUsrEmailAndSameSubscriptionIdExist))
			{
				//Case 1=> When email is different and subscription id is same
				//then insert with a new email id
				if($input["userSubscriptionDays"] !=""){
					$subsArr = $this->getUsrSubscriptionType($input["userSubscriptionDays"]);
					if(!empty($subsArr)){
						$userSubscriptionType = $subsArr['userSubscriptionType'];
						$userSubscriptionText = $subsArr['userSubscriptionText'];
					} else {
						$userSubscriptionType = "";
						$userSubscriptionText = "";
					}
				} else {
					$userSubscriptionType = "";
					$userSubscriptionText = "";
				}
				
				//insert user subscription as free user
				$subscriptionStartDate = strtotime($input["subscriptionStartDate"]);
				$subscriptionEndDateStr = strtotime("+7 days", $subscriptionStartDate);
				$subscriptionEndDate =  date('Y-m-d H:i:s', $subscriptionEndDateStr);

				//insert in  appusers table
				$lastInsertedId = DB::table('appusers')->insertGetId(array
				(
					'deviceTokenLatest' => $input["deviceToken"],
					'userEmail' => $input["userEmail"],
					'subscriptionId' => $input["subscriptionId"],
					'subscriptionStartDate' => $input["subscriptionStartDate"],
					'userSubscriptionType' => 0, //Free trial
					'userSubscriptionDays' => $input["userSubscriptionDays"], //7 //no of days
					'subscriptionEndDate' => $input["subscriptionEndDate"],//$subscriptionEndDate,
					'created_at' => now(),
					'updated_at' => now()
				));

				#echo "lastInsertedId=".$lastInsertedId;die;
				if($lastInsertedId != ""){
					//insert in  appuserselwallpapers table
					DB::table('appuserselwallpapers')->insert(
					[[
						'appUserId' => $lastInsertedId,
						'deviceToken' => $input["deviceToken"],
						'created_at' => now(),
						'updated_at' => now()
					]]);
				}
				$input["userSubscriptionType"] = $userSubscriptionText;
				
			} else { 
				//Case 2=> When email is same and subscription id is same
				//update same record
				if($input["userSubscriptionDays"] !=""){
					$subsArr = $this->getUsrSubscriptionType($input["userSubscriptionDays"]);
					if(!empty($subsArr)){
						$userSubscriptionType = $subsArr['userSubscriptionType'];
						$userSubscriptionText = $subsArr['userSubscriptionText'];
					} else {
						$userSubscriptionType = "";
						$userSubscriptionText = "";
					}
				} else {
					$userSubscriptionType = "";
					$userSubscriptionText = "";
				}
				
				DB::table('appusers')
				->where('id',$isUsrSubscribed->id)
				->update([
				'deviceTokenLatest' => $input["deviceToken"],
				'subscriptionId' => $input["subscriptionId"],
				'subscriptionStartDate' => $input["subscriptionStartDate"],
				'userSubscriptionType' => $userSubscriptionType , //Monthly
				'userSubscriptionDays' => $input["userSubscriptionDays"],
				'subscriptionEndDate' => $input["subscriptionEndDate"],
				'updated_at' => now()
				]);
				
				//Check this device token is exist for this user or not
				$isdeviceTokenExist = DB::table('appuserselwallpapers as wall')
				->select('wall.id')
				->where('wall.appUserId', '=', $isUsrSubscribed->id)
				->where('wall.deviceToken', '=', $input["deviceToken"])
				->get();
				#echo "<pre>isdeviceTokenExist==";print_r($isdeviceTokenExist);die;
				if(count($isdeviceTokenExist)<1){ //if no matching record
					//insert new device token 'appuserselwallpapers' table
					DB::table('appuserselwallpapers')->insert(
					[[
					'appUserId' => $isUsrSubscribed->id,
					'deviceToken' => $input["deviceToken"],
					'created_at' => now(),
					'updated_at' => now()
					]]);
				}
				
				//If device token not matched from last one
				if($isUsrSubscribed->deviceTokenLatest != $input["deviceToken"]){
					//update 'appusers' table
					DB::table('appusers')
					->where('id',$isUsrSubscribed->id)
					->update(['deviceTokenLatest' => $input["deviceToken"],'updated_at' => now()]);
				}
				
				$input["userSubscriptionType"] = $userSubscriptionText;
				
			}
		} else {//insert user subscription as free user
			$subscriptionStartDate = strtotime($input["subscriptionStartDate"]);
			$subscriptionEndDateStr = strtotime("+7 days", $subscriptionStartDate);
			$subscriptionEndDate =  date('Y-m-d H:i:s', $subscriptionEndDateStr);
			
			//insert in  appusers table
			$lastInsertedId = DB::table('appusers')->insertGetId(array
			(
				'deviceTokenLatest' => $input["deviceToken"],
				'userEmail' => $input["userEmail"],
				'subscriptionId' => $input["subscriptionId"],
				'subscriptionStartDate' => $input["subscriptionStartDate"],
				'userSubscriptionType' => 0, //Free trial
				'userSubscriptionDays' => $input["userSubscriptionDays"],//7, //no of days
				'subscriptionEndDate' => $input["subscriptionEndDate"], //$subscriptionEndDate
				'created_at' => now(),
				'updated_at' => now()
			));
			
			#echo "lastInsertedId=".$lastInsertedId;die;
			if($lastInsertedId != ""){
				//insert in  appuserselwallpapers table
				DB::table('appuserselwallpapers')->insert(
				[[
					'appUserId' => $lastInsertedId,
					'deviceToken' => $input["deviceToken"],
					'created_at' => now(),
					'updated_at' => now()
				]]);
			}
			$input["userSubscriptionType"] = "Trial-Weekly";
		}
		return response()->json([
			"isSuccess" => "true",
			"data" => $input,
			"errorCode" => "0",
			"errorMessage" => "",
			"cause" => ""
		], 200);
	}
	
	//For add edit subscription for ios users (For Sandbox users)
	/*public function addUpdSubscription(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required',
				'subscriptionStartDate' => 'required',
				'userSubscriptionDays' => 'required',
				'subscriptionEndDate' => 'required'
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
		
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.subscriptionId','au.userSubscriptionType','au.deviceTokenLatest')
		//->where('au.subscriptionId', '=', $input["subscriptionId"])
		->where('au.userEmail', '=', $input["userEmail"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			
			if($input["userSubscriptionDays"] !=""){
				$subsArr = $this->getUsrSubscriptionType($input["userSubscriptionDays"]);
				if(!empty($subsArr)){
					$userSubscriptionType = $subsArr['userSubscriptionType'];
					$userSubscriptionText = $subsArr['userSubscriptionText'];
				} else {
					$userSubscriptionType = "";
					$userSubscriptionText = "";
				}
			} else {
				$userSubscriptionType = "";
				$userSubscriptionText = "";
			}
			
			DB::table('appusers')
			->where('id',$isUsrSubscribed->id)
			->update([
			'deviceTokenLatest' => $input["deviceToken"],
			'subscriptionId' => $input["subscriptionId"],
			'subscriptionStartDate' => $input["subscriptionStartDate"],
			'userSubscriptionType' => $userSubscriptionType , //Monthly
			'userSubscriptionDays' => $input["userSubscriptionDays"],
			'subscriptionEndDate' => $input["subscriptionEndDate"],
			'updated_at' => now()
			]);
			
			//Check this device token is exist for this user or not
			$isdeviceTokenExist = DB::table('appuserselwallpapers as wall')
			->select('wall.id')
			->where('wall.appUserId', '=', $isUsrSubscribed->id)
			->where('wall.deviceToken', '=', $input["deviceToken"])
			->get();
			#echo "<pre>isdeviceTokenExist==";print_r($isdeviceTokenExist);die;
			if(count($isdeviceTokenExist)<1){ //if no matching record
				//insert new device token 'appuserselwallpapers' table
				DB::table('appuserselwallpapers')->insert(
				[[
				'appUserId' => $isUsrSubscribed->id,
				'deviceToken' => $input["deviceToken"],
				'created_at' => now(),
				'updated_at' => now()
				]]);
			}
			
			//If device token not matched from last one
			if($isUsrSubscribed->deviceTokenLatest != $input["deviceToken"]){
				//update 'appusers' table
				DB::table('appusers')
				->where('id',$isUsrSubscribed->id)
				->update(['deviceTokenLatest' => $input["deviceToken"],'updated_at' => now()]);
			}
			
			$input["userSubscriptionType"] = $userSubscriptionText;
		} else {//insert user subscription as free user
			$subscriptionStartDate = strtotime($input["subscriptionStartDate"]);
			$subscriptionEndDateStr = strtotime("+7 days", $subscriptionStartDate);
			$subscriptionEndDate =  date('Y-m-d H:i:s', $subscriptionEndDateStr);
			
			//insert in  appusers table
			$lastInsertedId = DB::table('appusers')->insertGetId(array
			(
			'deviceTokenLatest' => $input["deviceToken"],
			'userEmail' => $input["userEmail"],
			'subscriptionId' => $input["subscriptionId"],
			'subscriptionStartDate' => $input["subscriptionStartDate"],
			'userSubscriptionType' => 0, //Free trial
			'userSubscriptionDays' => 7, //no of days
			'subscriptionEndDate' => $subscriptionEndDate,
			'created_at' => now(),
			'updated_at' => now()
			));
			
			#echo "lastInsertedId=".$lastInsertedId;die;
			if($lastInsertedId != ""){
				//insert in  appuserselwallpapers table
				DB::table('appuserselwallpapers')->insert(
				[[
				'appUserId' => $lastInsertedId,
				'deviceToken' => $input["deviceToken"],
				'created_at' => now(),
				'updated_at' => now()
				]]);
			}
			$input["userSubscriptionType"] = "Trial-Weekly";
		}
		return response()->json([
			"isSuccess" => "true",
			"data" => $input,
			"errorCode" => "0",
			"errorMessage" => "",
			"cause" => ""
		], 200);
	}*/
	
	//For get user subscription info for ios users
	public function getSubscriptionInfo(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required|email',
				'subscriptionId' => 'required'
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
		//Check this user is already subscribed or not
		$isUsrSubscribed = DB::table('appusers as au')
		->select('au.id','au.userEmail','au.subscriptionId','au.subscriptionStartDate','au.userSubscriptionType','au.userSubscriptionDays','au.subscriptionEndDate')
		->where('au.subscriptionId', '=', $input["subscriptionId"])
		->first();
		#echo "<pre>isUsrSubscribed==";print_r($isUsrSubscribed);die;
		if(!empty($isUsrSubscribed)){ //update user subscription as monthly user
			$input["deviceToken"] = $input["deviceToken"];
			$input["userEmail"] = $isUsrSubscribed->userEmail;
			$input["subscriptionId"] = $isUsrSubscribed->subscriptionId;
			$input["subscriptionStartDate"] = $isUsrSubscribed->subscriptionStartDate;
			$input["userSubscriptionDays"] = $isUsrSubscribed->userSubscriptionDays;
			$input["subscriptionEndDate"] = $isUsrSubscribed->subscriptionEndDate;
			
			//check is user subscription is active or not
			$subscriptionEndDateStr = strtotime($isUsrSubscribed->subscriptionEndDate);
			
			$now = date("Y-m-d H:i:s");
			$currentDate = date('Y-m-d H:i:s',strtotime($this->add_hr_min,strtotime($now)));
			$currentDateStr = strtotime($currentDate);
			if($currentDateStr <= $subscriptionEndDateStr){
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
	////////////////////////////////////////////////////
	// IoS end ////////////////////////////////////////
	////////////////////////////////////////////////////
	////////////////////////////////////////////////////
} 