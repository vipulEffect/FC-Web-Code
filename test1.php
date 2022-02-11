<?php
#die('!!');
error_reporting(E_ALL);
ini_set('display_errors', '1');

$clientID = "588171352860-7q3uuh1sfhob79q3vu8cb39noi7kf753.apps.googleusercontent.com";
$redirectUri = "http://ec2-3-7-135-212.ap-south-1.compute.amazonaws.com/login";
$response_type = "code";
$scope = "https://www.googleapis.com/auth/androidpublisher";
$access_type = "offline";
//$state = "";

//$clientSecret = "hjX8uscIp32J-D3vCgme6BFj";

/*https://accounts.google.com/o/oauth2/v2/auth?client_id=588171352860-7q3uuh1sfhob79q3vu8cb39noi7kf753.apps.googleusercontent.com&scope=https://www.googleapis.com/auth/androidpublisher&redirect_uri=http://ec2-3-7-135-212.ap-south-1.compute.amazonaws.com/login&response_type=code*/


$ch = curl_init();

$TOKEN_URL = "https://accounts.google.com/o/oauth2/auth";
$input_fields = 'client_id='.$clientID.
    '&redirectUri='.$redirectUri.
    '&response_type='.$response_type.
    '&scope='.$scope.
	'&access_type='.$access_type;
    //'&state='.$state;
	
//Request to google oauth for authentication
curl_setopt($ch, CURLOPT_URL, $TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input_fields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$result = json_decode($result, true);
echo "<pre>result=";print_r($result);die;

/*if (!$result || !$result["access_token"]) {
 //error   
 return;
}


$VALIDATE_URL = "https://www.googleapis.com/androidpublisher/v3/applications/".
    $appid."/purchases/subscriptions/".
    $productID."/tokens/".$purchaseToken;
	
	$input_fields = 'refresh_token='.$refreshToken.
    '&client_secret='.$clientSecret.
    '&client_id='.$clientID.
    '&redirect_uri='.$redirectUri.
    '&grant_type=refresh_token';
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


echo "<pre>notify=";print_r($notify);die;*/
?>