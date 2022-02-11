<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


/**
 * @param $http2ch          the curl connection
 * @param $http2_server     the Apple server url
 * @param $apple_cert       the path to the certificate
 * @param $app_bundle_id    the app bundle id
 * @param $message          the payload to send (JSON)
 * @param $token            the token of the device
 * @return mixed            the status code (see https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/APNsProviderAPI.html#//apple_ref/doc/uid/TP40008194-CH101-SW18)
 */
function sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token) {
	$milliseconds = round(microtime(true) * 1000);

    // url (endpoint)
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
	//echo $duration;
	return $status;
}

// open connection
if (!defined('CURL_HTTP_VERSION_2_0')) {
    define('CURL_HTTP_VERSION_2_0', 3);
}
$http2ch = curl_init();
curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

// send push
$apple_cert = dirname(__FILE__).'/Fractal-APN-Prod.pem';
$message = '{"aps":{"alert":"Trial Period Expire","sound":"default"}}';
$token = '8043ab9cf969a7f8a650796478e851a0f0a0d04898a4b371d8c62f4f83b8e76e';
$http2_server = 'https://api.push.apple.com';   // or 'api.push.apple.com' if production
$app_bundle_id = 'com.effectualtech.fractchaostest';

// close connection
//for ($i = 0; $i < 1; $i++) {
    $status = sendHTTP2Push($http2ch, $http2_server, $apple_cert, $app_bundle_id, $message, $token);
    echo "Response from apple -> {$status}\n";
//}

curl_close($http2ch);


/*session_start();
echo "<pre>SESSION=";print_r($_SESSION);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$appid = "com.fractal.chaoss";
$productID = "product_002";
$purchaseToken ="aollpnccinppemegphejiejc.AO-J1OxiBNbu7c5NdX-_EjeuBabP4XUoNfSWMETqaFrSKVX859sNO0KvPaSuRv_pEpYHP1W9Riy-q2Zwhe0FKCahAD8vhN2kaw";
$access_token = "ya29.A0ARrdaM9Bd96txXdNnu3hFye6kxreIhJ8YvhJby3sjZ-jWHRDyBH7g6eyIIX94QqSYDjCyKkdSCha6jRz-p9Vsyc1luRCIo1ZEwuwodLNro5B2BJCWb9iAkV-ZmbuGZ3UzEup-gQSc4ahvipYAhr0GwNQJz6W";

$VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/".$appid."/purchases/subscriptions/".$productID."/tokens/".$purchaseToken;
//request to play store with the access token from the authentication request

//echo "<br/>";
//echo "VALIDATE_URL=".$VALIDATE_URL;
//echo "<br/>";
$nextUrl = $VALIDATE_URL."?access_token=".$access_token;
echo "nextUrl=".$nextUrl;
#die;

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$nextUrl);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result2F = curl_exec($ch);
$result2 = json_decode($result2F);
echo "<pre>result2@@@@@@=";print_r($result2);
if ($result2 === FALSE) {
	die('issue: ' . curl_error($ch));
} else {
	$startTimeMillis = $result2->startTimeMillis;
	$expiryTimeMillis = $result2->expiryTimeMillis;
	
	$add_hr_min = "+5 hour +30 minutes"; //for time
	echo "startTime==".$startTime = date('Y-m-d H:i:s', $startTimeMillis/1000); 
	echo "<br/>";
	echo "startTime@@@@@==".$startTime = date('Y-m-d H:i:s',strtotime($add_hr_min,strtotime($startTime)));

	echo "<br/>";
	echo "expireTime==".$expireTime = date('Y-m-d H:i:s', $expiryTimeMillis/1000); 
	echo "<br/>";
	echo "expireTime@@@@@==".$currentDate = date('Y-m-d H:i:s',strtotime($add_hr_min,strtotime($expireTime)));
}
curl_close($ch);
*/


//die('!!');
/*error_reporting(E_ALL);
ini_set('display_errors', '1');

$clientID = "868722952990-28c1j8p1m833jrn8j02lse5lnvjrdujn.apps.googleusercontent.com";
$clientSecret = "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";
$redirectUri = "https://ec2-3-7-135-212.ap-south-1.compute.amazonaws.com/redirectGoogleoath2Result.php";
$refreshToken = "4/0AX4XfWjhMzEWMcSLsroOZDDqUVgDp71CCW45G3UqXOUCxjA_QIRSHGdlx-kJxKSy1ZaVNA";
$TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

$ch = curl_init();

echo $input_fields = 'refresh_token='.$refreshToken.
    '&client_secret='.$clientSecret.
    '&client_id='.$clientID.
    '&redirect_uri='.$redirectUri.
    '&grant_type=refresh_token';

//Request to google oauth for authentication
curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$result = json_decode($result, true);
echo "<pre>result=";print_r($result);die;



if (!$result || !$result["access_token"]) {
 //error   
 return;
}
die;

$VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/".
    $appid."/purchases/subscriptions/".
    $productID."/tokens/".$purchaseToken;
//request to play store with the access token from the authentication request
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$VALIDATE_URL."?access_token=".$result["access_token"]);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$result = json_decode($result, true);

if (!$result || $result["error"] != null) {
    //error
    return;
}

$expireTime = date('Y-m-d H:i:s', $result["expiryTimeMillis"]/1000. - date("Z")); 
//You get the purchase expire time, for example 2017-02-22 09:16:42

*/
?>