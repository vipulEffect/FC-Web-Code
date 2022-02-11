<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use App\Appuser;
use Illuminate\Http\Request;

class TrialNotifyTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:notifyTest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron For notify for users which trail period end before 6 minute.';

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
		#echo "<pre>deviceToken=";print_r($deviceToken);#die;
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
	 * @param $http2ch          the curl connection
	 * @param $http2_server     the Apple server url
	 * @param $apple_cert       the path to the certificate
	 * @param $app_bundle_id    the app bundle id
	 * @param $message          the payload to send (JSON)
	 * @param $token            the token of the device
	 * @return mixed            the status code (see https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/APNsProviderAPI.html#//apple_ref/doc/uid/TP40008194-CH101-SW18)
	*/
	
	public function sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token) {
		$milliseconds = round(microtime(true) * 1000);

		//url (endpoint)
		$url = "{$http2_server}/3/device/{$token}";

		// certificate
		$cert = realpath($apple_cert);

		// headers
		$headers = array(
			"apns-topic: {$app_bundle_id}",
			"User-Agent: My Sender"
		);

		// other curl options
		curl_setopt_array($http2ch, array(
			CURLOPT_URL => "{$url}",
			CURLOPT_PORT => 443,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $message,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSLCERT => $cert,
			CURLOPT_HEADER => 1
		));

		// go...
		$result = curl_exec($http2ch);
		if ($result === FALSE) {
			throw new Exception('Curl failed with error: ' . curl_error($http2ch));
		}

		// get respnse
		$status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);
		$duration = round(microtime(true) * 1000) - $milliseconds;
		#echo "duration=".$duration;die;
		return $status;
	}
	
	
	//push notification for ios mobile devices
	public function push_notification_ios_mobile($deviceToken){
		// open connection
		if (!defined('CURL_HTTP_VERSION_2_0')) {
			define('CURL_HTTP_VERSION_2_0', 3);
		}
		$http2ch = curl_init();
		curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		
		$cron_folder_path = dirname(__FILE__);
		$root_path_arr = explode("/app/Console/Commands",$cron_folder_path);
		#echo "<pre>root_path_arr=";print_r($root_path_arr);die;
		if(!empty($root_path_arr)){
			//$root_path = $root_path_arr[0];
			$apple_cert = $root_path_arr[0].'/Fractal-APN-Prod.pem';
		} else {
			// send push
			$apple_cert = '/var/www/html/Fractal-APN-Prod.pem';
		}
		
		$message = '{"aps":{"alert":"Trial Period Expire","sound":"default"}}';
		$token = $deviceToken;
		$http2_server = 'https://api.push.apple.com';   // or 'api.push.apple.com' if production
		$app_bundle_id = 'com.effectualtech.fractchaostest';

		// close connection
		//for ($i = 0; $i < 1; $i++) {
		$status = $this->sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token);
		//echo "Response from apple -> {$status}\n";
			
		//}
		curl_close($http2ch);
		return $status;
	}
	

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    { 	//die('trial notify Test');
		//Get users list which trial period is going to end in next 6 min
		$subsUserInfo = "SELECT au.id,au.userEmail,au.subscriptionStartDate,au.userSubscriptionType,
		au.trial_notification_sent,ausw.pnToken,au.pnTokenLatest
		FROM appusers as au
		inner join appuserselwallpapers as ausw on au.id = ausw.appUserId
		where au.userSubscriptionType=0 and au.trial_notification_sent=0 and date_format(DATE_ADD(au.subscriptionStartDate, INTERVAL 6 MINUTE ),'%Y-%m-%d') = CURDATE() order by au.id asc";
		$subsUserResult = DB::select($subsUserInfo);
		#echo "<pre>subsUserResult";print_R($subsUserResult);die;
		if (count($subsUserResult)> 0) {
			foreach($subsUserResult as $kk=>$row) {
				$subscriptionDate = $row->subscriptionStartDate;
				//after 4 minutes
				$next_month_subs_valid_upto1 = date('Y-m-d H:i:s', strtotime("+4 minutes", strtotime($subscriptionDate )));
				$next_month_subs_valid_uptoStr1 = strtotime($next_month_subs_valid_upto1);
				
				//after 6 minutes
				$next_month_subs_valid_upto2 = date('Y-m-d H:i:s', strtotime("+6 minutes", strtotime($subscriptionDate )));
				$next_month_subs_valid_uptoStr2 = strtotime($next_month_subs_valid_upto2);
				
				$currentTime = date('Y-m-d H:i:s');
				$currentTimeStr = strtotime($currentTime);
				
				/*echo $next_month_subs_valid_upto1."====".$next_month_subs_valid_uptoStr1."<br>";
				echo $next_month_subs_valid_upto2."====".$next_month_subs_valid_uptoStr2."<br>";
				echo $currentTime."====".$currentTimeStr."<br>";*/
				//die;*/
				if( ($currentTimeStr >=$next_month_subs_valid_uptoStr1) && ($currentTimeStr <=$next_month_subs_valid_uptoStr2) ){ #echo "ifff";die;
					//send push notification to android user
					$pushed = $this->push_notification_mobile($row->pnTokenLatest);
					$pushed_decode = json_decode($pushed);
					//echo "<pre>pushed_decode=";print_r($pushed_decode);
					//echo $pushed_decode->success;die;
					if(!empty($pushed_decode) && $pushed_decode->success == 1){ //success
						echo "User ".$row->userEmail." is successfully sent notification of trial End.'<br/>'";
						DB::table('appusers')
						->where('id',$row->id)
						->update(['trial_notification_sent' => 1]);
					} else {
						echo $pushed_decode->success." Not sent PNTOKEN";
					}
					
					//send push notification to ios user
					$pushed_ios_status = $this->push_notification_ios_mobile($row->pnTokenLatest);
					#echo "<pre>pushed_ios_status=";print_r($pushed_ios_status);die;
					if(!empty($pushed_ios_status) && $pushed_ios_status == 200){ //success
						echo "User ".$row->userEmail." is successfully sent ios notification of trial End.'<br/>'";
						DB::table('appusers')
						->where('id',$row->id)
						->update(['trial_notification_sent' => 1]);
					} else {
						echo $pushed_ios_status." Not sent";
					}
				} else { #echo "elseee";die;
					echo "@@No user is exist in db for trail period end by tomorrow";#die;
				}
			} //end foreach
		} else {
			echo "No user is exist in db for trail period end by tomorrow";
		}
    }
}