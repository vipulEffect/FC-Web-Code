<html xmlns="http://www.w3.org/1999/xhtml">
<body>
<a href="intent://123#Intent;scheme=https;package=com.fractal.chaoss;end">Click me</a>
</body>
</html>

<!--?php

/*session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

$code = $_REQUEST['code'];
//$code = "4/0AX4XfWipmlrQQ10KptGZ1_WjYfcdn2KYQT9ECRkQTwBO96fgutrXdbtQk4mHlVfcJpo1xw";

$client_id = "868722952990-28c1j8p1m833jrn8j02lse5lnvjrdujn.apps.googleusercontent.com";
$client_secret = "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";
$redirect_uri = "https://effectualtech.net/redirectGoogleoath2Result.php";

$url = 'https://accounts.google.com/o/oauth2/token';
$headers = array('Content-Type:application/json');
$fields = array(
	"grant_type" => "authorization_code",
	"client_id" => $client_id,
	"client_secret" => $client_secret,
	"redirect_uri" => $redirect_uri,
	"code" => $code
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
$resultF = curl_exec($ch);
$result = json_decode($resultF);
echo "<pre>result@@@@=";print_r($result);
#echo "refresh_token==".$result->refresh_token;


if ($result === FALSE) {
	die('FCM Send Error: ' . curl_error($ch));
} 
else { 
	//Get refresh token
	$refresh_token = $result->refresh_token;
	$_SESSION['refresh_token'] = $refresh_token;
	$fields1 = array(
		"grant_type" => "refresh_token",
		"client_id" => $client_id,
		"client_secret" => $client_secret,
		"refresh_token" => $refresh_token
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields1));
	$resultS = curl_exec($ch);
	$result1 = json_decode($resultS);
	echo "<pre>result1=";print_r($result1);
	echo "access_tokenFinal=".$access_tokenFinal = $result1->access_token;
	$_SESSION['access_tokenFinal'] = $access_tokenFinal;
	#die;
	curl_close($ch);
}*/
?>