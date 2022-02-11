<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Appuser;
use Illuminate\Http\Request;

class TrialNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron For notify for users which trail period end before 24 hrs.';

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
				"title" => "Trial Period Expire",
				"body" => "Your trail period is going to end by tomorrow.Please buy.",
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
    { 	#die('trial notify Live');
		///////////////////////////////////////
		/////////////////////////////////////////
		//For PN Live purpose
		////////////////////////////////////////
		/////////////////////////////////////////
		
		//Get users list which trial period is going to end in next 24 hrs
		$subsUserInfo = "SELECT au.id,au.userEmail,au.subscriptionStartDate,au.userSubscriptionType,
		au.trial_notification_sent,ausw.pnToken,au.pnTokenLatest
		FROM appusers as au
		inner join appuserselwallpapers as ausw on au.id = ausw.appUserId
		where au.userSubscriptionType=0 and au.trial_notification_sent=0 and date_format(DATE_ADD(au.subscriptionStartDate, INTERVAL 6 DAY),'%Y-%m-%d') = CURDATE() order by au.id asc";
		$subsUserResult = DB::select($subsUserInfo);
		#echo "<pre>subsUserResult";print_R($subsUserResult);#die;
		if (count($subsUserResult)> 0) {
			foreach($subsUserResult as $kk=>$row) {
				//send push notification to user
				$pushed = $this->push_notification_mobile($row->pnTokenLatest);
				$pushed_decode = json_decode($pushed);
				//echo "<pre>pushed_decode=";print_r($pushed_decode);
				//echo $pushed_decode->success;die;
				if(!empty($pushed_decode) && $pushed_decode->success == 1){ //success
					echo "User ".$row->userEmail." is successfully sent notification of trial End.'<br/>'";
					DB::table('appusers')
					->where('id',$row->id)
					->update(['trial_notification_sent' => 1]);
				}
			} //end foreach
		} else {
			echo "No user is exist in db for trail period end by tomorrow";
		}
    }
}