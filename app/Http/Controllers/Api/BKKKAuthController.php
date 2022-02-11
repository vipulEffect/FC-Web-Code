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
	public $record_per_page = 20;
	public $add_hr_min = "+5 hour +30 minutes"; //for time
	public $add_hr_min_Sql = "330 MINUTE"; //for sql get current time
	
	
	//For get Wallpapers At Home wallpaper display
	public function getWallpapersAtHome(Request $request){ #die('---');
		$validator = Validator::make($request->all(), 
			[ 
				'deviceToken' => 'required',
				'userEmail' => 'required',
				'deviceType' => 'required|in:1,2' //1=>mobile,2=>tablet
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => "{}",
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
			->select('img.*')
			->get();
			//echo "<pre>wallpaperExist==";print_r($wallpaperExist);#die;
			$finalArr = array();
			if(!empty($wallpaperExist)){
				$currentDate = date("Y-m-d"); //2021-05-27
				//past wallpaper
				$pastWallpaper = DB::table('images as img')
				->select('img.*')
				->where('img.wallpaperDate', '<', $currentDate)
				->limit(2)
				->get();
				#echo "<pre>pastWallpaper==";print_r($pastWallpaper);#die;
				if(!empty($pastWallpaper) && count($pastWallpaper)>0){ #die('ifff');
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
				} else { #die('elsee');
					$finalArr['past'] = array();
				}
				
				//current wallpaper
				$currentWallpaper = DB::table('images as img')
				->select('img.*')
				->where('img.wallpaperDate', '=', $currentDate)
				->limit(1)
				->get();
				#echo "<pre>currentWallpaper==";print_r($currentWallpaper);#die;
				if(!empty($currentWallpaper) && count($currentWallpaper)>0){ #die('ifff');
					foreach($currentWallpaper as $curKey=>$curVal){
						$finalArr['current'][$curKey]['wallpaperName'] = $curVal->wallpaperName;
						$finalArr['current'][$curKey]['wallpaperDate'] = $curVal->wallpaperDate;
						if($deviceType == 1){ //phone
							$finalArr['current'][$curKey]['fileName'] = $curVal->phoneFilename;
							$finalArr['current'][$curKey]['url'] = $curVal->phoneUrl;
						} else if($deviceType == 2){ //tablet
							$finalArr['current'][$curKey]['fileName'] = $curVal->tabletFilename;
							$finalArr['current'][$curKey]['url'] = $curVal->tabletUrl;
						}
					}
				} else { #die('elsee');
					$finalArr['current'] = array();
				}
				
				//upcoming wallpaper
				/*$upcomingWallpaper = DB::table('images as img')
				->select('img.*')
				->where('img.wallpaperDate', '>', $currentDate)
				->limit(3)
				->get();*/
				
				$upcomingWallpaper = DB::table('images as img')
                ->orderBy('img.updated_at', 'desc')
				->limit(3)
                ->get();
				//echo "<pre>upcomingWallpaper==";print_r($upcomingWallpaper);die;
				if(!empty($upcomingWallpaper) && count($upcomingWallpaper)>0){ #die('ifff');
					foreach($upcomingWallpaper as $upcomingKey=>$upcomingVal){
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
				} else { #die('elsee');
					$finalArr['upcoming'] = array();
				}
				
				//Check this user is already selected wallpaper or not
				$wallpaperSelected = DB::table('appusers')
				->select('appusers.*')
				->where('appusers.deviceToken', '=', $input["deviceToken"])
				->where('appusers.userEmail', '=', $input["userEmail"])
				->where('appusers.selWallpaperType', '=', $input["deviceType"])
				->first();
				echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
				if(!empty($wallpaperSelected)){
					$finalArr['isWallPaperSelected'] = $wallpaperSelected->id;
					$finalArr['subscriptionDate'] = $wallpaperSelected->subscriptionDate;
					$finalArr['userSubscriptionType'] = $wallpaperSelected->userSubscriptionType;
					
					//check is user is eligible for chnage wallpaper or not
					if($wallpaperSelected->userSubscriptionType == 0){//free
						$renewDays = "+7 day";
					} else if($wallpaperSelected->userSubscriptionType == 1){//paid weekly
						$renewDays = "+7 day";
					} else if($wallpaperSelected->userSubscriptionType == 2){//paid monthly
						$renewDays = "+30 day";
					}
					$renewDateStr = strtotime($renewDays, strtotime($wallpaperSelected->subscriptionDate));
					$renewDate = date("Y-m-d", $renewDateStr);
					
					$currentDate = date("Y-m-d");
					$currentDateStr = strtotime($currentDate);
					if($currentDateStr <= $renewDateStr){
						$finalArr['userCanChangeWallpaper'] = "No";
					} else {
						$finalArr['userCanChangeWallpaper'] = "Yes";
					}
				} else {
					$finalArr['isWallPaperSelected'] = "";
					$finalArr['subscriptionDate'] = "";
					$finalArr['userSubscriptionType'] = "";
					$finalArr['userCanChangeWallpaper'] = "Yes";
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
					"data" => "{}",
					"errorCode" => "9001",
					"errorMessage" => "Wallpaper is not exist",
					"cause" => "Wallpaper is not exist"
				], 200);
			}
		} else { 
			return response()->json([
				"isSuccess" => "false",
				"data" => "{}",
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
				'userSubscriptionType' => 'required|in:0,1,2', //0=>free,1=>weekly,2=>monthly
				'selWallpaperId' => 'required',
				'selWallpaperType' => 'required|in:1,2' //1=>mobile,2=>tablet
			]);   
		
		if ($validator->fails()) {  
			return response()->json([
				"isSuccess" => "false",
				"data" => "{}",
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
			->select('img.id')
			->where('img.id', '=', $input["selWallpaperId"])
			->first();
			#echo "<pre>wallpaperExist==";print_r($wallpaperExist);#die;
			$finalArr = array();
			if(!empty($wallpaperExist)){
				$currentDate = date("Y-m-d"); //2021-05-27
				
				//Check this user is already selected wallpaper or not
				$wallpaperSelected = DB::table('appusers')
				->select('appusers.*')
				->where('appusers.deviceToken', '=', $input["deviceToken"])
				->where('appusers.userEmail', '=', $input["userEmail"])
				->where('appusers.selWallpaperType', '=', $input["selWallpaperType"])
				->first();
				//echo "<pre>wallpaperSelected==";print_r($wallpaperSelected);die;
				if(!empty($wallpaperSelected)){ //update entry
					DB::table('appusers')
					->where('id',$wallpaperSelected->id)
					->update([
					'subscriptionDate' =>$currentDate,
					'userSubscriptionType' =>$input["userSubscriptionType"],
					'selWallpaperId' => $input["selWallpaperId"],
					'selWallpaperType' => $input["selWallpaperType"],
					'selWallpaperDate' => $currentDate,
					'updated_at'=> now()
					]);
				} else {//insert
					DB::table('appusers')->insert(
					[[
					'deviceToken' => $input["deviceToken"],
					'userEmail' => $input["userEmail"],
					'subscriptionDate' => $currentDate,
					'userSubscriptionType' => $input["userSubscriptionType"], 
					'selWallpaperId' => $input["selWallpaperId"],
					'selWallpaperType' => $input["selWallpaperType"],
					'selWallpaperDate' => $currentDate,
					'created_at' => now(),
					'updated_at' => now()
					]]);
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
					"data" => "{}",
					"errorCode" => "9001",
					"errorMessage" => "Wallpaper is not exist in db",
					"cause" => "Wallpaper is not exist in db"
				], 200);
			}
		} else { 
			return response()->json([
				"isSuccess" => "false",
				"data" => "{}",
				"errorCode" => "9001",
				"errorMessage" => "Device Token is not exist",
				"cause" => "Device Token is not exist"
			], 401);
		}
	}
	
	
} 