<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use DB;
use App\Image,App\Wallpapertype;
use Illuminate\Http\Request;

class WeeklyWallpaper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallpaper:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User will get notification at each month 1st and 15th date when new wallpapers will used';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    //push notification for mobile devices
	public function push_notification_mobile($deviceToken){
		//echo "<pre>deviceToken=";print_r($deviceToken);die;
		//API URL of FCM
		$url = 'https://fcm.googleapis.com/fcm/send';
		/*api_key available in:
		Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key*/    
		$api_key = 'AAAAykPsSx4:APA91bFuELaOFyakfge0sXO4zx6EZ5S5SvXl2VRMSnfxeJZocaIuaWQdZPYGbUYzUYIC_OKy_ybMFm1OzE4mXAB6wzFYAZXxzy1abOfUK3Hx32Ww_ugRlUYy2UgeA-xmuoA25kRsbTOL';
		
		$fields = array(
			"to" => $deviceToken,
			"data" => array(
				"title" => "Bi-weekly wallpaper update",
				"body" => "Wallpapers has been updated.Please check at your home screen.",
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
	
	
	//check wallpaper list is updated from previous weeks from wallpapertypes table
	public function isWallpapersListUpdatedFrmLastWeek(){
		//last week
		$lastWeekInfo = DB::table('wallpapertypes')
		->orderBy('week_num', 'desc')
		->first();
		#echo "<pre>lastWeekInfo=";print_r($lastWeekInfo);die;
		if (!empty($lastWeekInfo)) {
			if($lastWeekInfo->upcoming_wallpaper_ids != ""){
				$upcoming_wallpaper_ids = explode(",",$lastWeekInfo->upcoming_wallpaper_ids);
				sort($upcoming_wallpaper_ids);
			}
			if($lastWeekInfo->past_wallpaper_ids != ""){
				$past_wallpaper_ids = explode(",",$lastWeekInfo->past_wallpaper_ids);
				sort($past_wallpaper_ids);
			}
		} else {
			$upcoming_wallpaper_ids = array();
			$past_wallpaper_ids = array();
			return 2; 
		}
		#echo "<pre>upcoming_wallpaper_ids=";print_r($upcoming_wallpaper_ids);
		#echo "<pre>past_wallpaper_ids=";print_r($past_wallpaper_ids);#die;
		
		//last to last week
		$lasttolastWeekInfo = DB::table('wallpapertypes')
		->orderBy('week_num', 'desc')
		->skip(1)
		->first();
		#echo "<pre>lasttolastWeekInfo=";print_r($lasttolastWeekInfo);die;
		if (!empty($lasttolastWeekInfo)) {
			if($lasttolastWeekInfo->upcoming_wallpaper_ids != ""){
				$upcoming_wallpaper_ids1 = explode(",",$lasttolastWeekInfo->upcoming_wallpaper_ids);
				sort($upcoming_wallpaper_ids1);
			} else {
				$upcoming_wallpaper_ids1 = array();
			}
			
			if($lasttolastWeekInfo->past_wallpaper_ids != ""){
				$past_wallpaper_ids1 = explode(",",$lasttolastWeekInfo->past_wallpaper_ids);
				sort($past_wallpaper_ids1);
			} else {
				$past_wallpaper_ids1 = array();
			}
		} else {
			$upcoming_wallpaper_ids1 = array();
			$past_wallpaper_ids1 = array();
			return 2; 
		}
		
		/*echo "<pre>upcoming_wallpaper_ids=";print_r($upcoming_wallpaper_ids);
		echo "<pre>upcoming_wallpaper_ids1=";print_r($upcoming_wallpaper_ids1);
		
		echo "<pre>past_wallpaper_ids=";print_r($past_wallpaper_ids);
		echo "<pre>past_wallpaper_ids1=";print_r($past_wallpaper_ids1);
		die;*/
		
		if(!empty($upcoming_wallpaper_ids) && !empty($upcoming_wallpaper_ids1)){
			if ($upcoming_wallpaper_ids===$upcoming_wallpaper_ids1) { //equal
				$upcoming_last_2_week = "same"; //if same
			} else {
				$upcoming_last_2_week = "not_same"; //if not same
			}
		} else {
			if ($upcoming_wallpaper_ids===$upcoming_wallpaper_ids1) { //equal
				$upcoming_last_2_week = "same"; //if same
			} else {
				$upcoming_last_2_week = "not_same"; //if not same
			}
		}
		
		if(!empty($past_wallpaper_ids) && !empty($past_wallpaper_ids1)){
			if ($past_wallpaper_ids===$past_wallpaper_ids1) { //equal
				$past_last_2_week = "same"; //if same
			} else {
				$past_last_2_week = "not_same"; //if not same
			}
		} else {
			if ($past_wallpaper_ids===$past_wallpaper_ids1) { //equal
				$past_last_2_week = "same"; //if same
			} else {
				$past_last_2_week = "not_same"; //if not same
			}
		}
		#echo $upcoming_last_2_week."====".$past_last_2_week;
		if ($upcoming_last_2_week == "not_same" || $past_last_2_week == "not_same" ){
			return 1; //means change from previous week
		} else {
			return 2; //means same from previous week
		}
	}

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    { 	#die('Test weekly wallpaper notify at 15 min');
		$isUpdated = $this->isWallpapersListUpdatedFrmLastWeek();
		#echo "<pre>isUpdated=";print_r($isUpdated);#die;
		if($isUpdated == 1){ //if updated then sent to notification
			//Get all users pnToken
			#$subsUserInfo = "SELECT id,appUserId,pnToken from appuserselwallpapers order by id asc";
			$subsUserInfo = "SELECT id,userEmail,pnTokenLatest from appusers order by id asc";
			$subsUserResult = DB::select($subsUserInfo);
			#echo "<pre>subsUserResult=";print_R($subsUserResult);
			#echo count($subsUserResult);
			#die;
			if (count($subsUserResult)> 0) {
				foreach($subsUserResult as $kk=>$row) {
					//send push notification to user
					$pushed = $this->push_notification_mobile($row->pnTokenLatest);
					$pushed_decode = json_decode($pushed);
					if(!empty($pushed_decode) && $pushed_decode->success == 1){ //success
						echo "User ".$row->userEmail." is successfully sent notification of update wallpapers.'<br/>'";
					}
				}
			} else {
				echo "No user is exist in db for send";
			}
		}
	}
}
