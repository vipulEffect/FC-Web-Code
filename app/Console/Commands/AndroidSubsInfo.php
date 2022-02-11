<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use DB;
use App\Image,App\Wallpapertype;
use Illuminate\Http\Request;

class AndroidSubsInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'android:subsInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'android subscription info';

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
		$api_key = 'AAAATF7r64o:APA91bGu1ki_xNTMSNhGl3xmlHgFhb1-913_Rvr8Fm0vP09bGudFu9f10YKaZbM9NumXdMjKQvsEdMOsjeDTMUF4rD6EI66HMUUCpnEwZwZuZ7N3ceket1taPEyoVCJnSg-ok2LPV8M6';
		
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
	
	

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    { 	die('android subs-info');
		echo file_get_contents("https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/androidpublisher&response_type=code&access_type=offline&redirect_uri=https://effectualtech.net/redirectGoogleoath2Result.php&client_id=868722952990-28c1j8p1m833jrn8j02lse5lnvjrdujn.apps.googleusercontent.com");
		
		die;
		//Get all android user info
		$androidUserInfo = "SELECT id,deviceNameLatest,subscriptionId,userEmail 
		from appusers 
		where `deviceNameLatest` LIKE  '%Android%' and userEmail='nishagupta@effectualtech.com' ";
		$androidUserInfoResult = DB::select($androidUserInfo);
		echo $androidUserInfo;
		echo "<pre>androidUserInfoResult=";print_R($androidUserInfoResult);
		if (count($androidUserInfoResult)> 0) {
			foreach($androidUserInfoResult as $kk=>$row) {
				
				//send push notification to user
				/*$pushed = $this->push_notification_mobile($row->pnToken);
				$pushed_decode = json_decode($pushed);
				if(!empty($pushed_decode) && $pushed_decode->success == 1){ //success
					echo "User ".$row->appUserId." is successfully sent notification of update wallpers.'<br/>'";
				}*/
			}
		} else {
			echo "No user is exist in db for send";
		}
		
	}
}
