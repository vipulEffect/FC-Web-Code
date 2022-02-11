<?php
die('!!');
error_reporting(E_ALL);
ini_set('display_errors', '1');


require_once ('hhb_.inc.php');
//declare(strict_types = 1);

$banggood_username = 'eptraina@gmail.com';
$banggood_password = 'Rtem39tow?';

$deathbycaptcha_username = 'dev@effectualtech.com';
$deathbycaptcha_password = 'etech1234@A';

$hc = new hhb_curl ( '', true );
$html = $hc->exec ( 'https://kcdc-efiling.kingcounty.gov/ecourt/?q=Login' )->getStdOut ();
$domd = @DOMDocument::loadHTML ( $html );
//echo "<pre>domd=";print_R($domd);die;
$xp = new DOMXPath ( $domd );
//echo "<pre>xp=";print_R($xp);die;

//$csrf_token = $xp->query ( '//input[@name="at"]' )->item (0)->getAttribute("value");

//$captcha_image_url = 'https://kcdc-efiling.kingcounty.gov/ecourt/?q=Login' . $domd->getElementById ( "get_login_image" )->getAttribute ( "src" );

$captcha_image_url = $xp->query ( 'input[@title="Image CAPTCHA"]' )->item (0)->getAttribute("src");
$captcha_image = $hc->exec ( $captcha_image_url )->getStdOut ();
echo "@@@".$captcha_image;die;
//$captcha_image = "ecourt/?q=image_captcha&amp;sid=6257820&amp;ts=1623929413";

$captcha_answer = deathbycaptcha ( $captcha_image, $deathbycaptcha_username, $deathbycaptcha_password );

$html = $hc->setopt_array ( array (
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query ( array (
                'form_build_id' => 'form-tUhW2g4h2WzFITPSZQMlYIMKchV7qGpmnr7iSUA23pw',
                'form_id' => 'user_login',
                'name' => $banggood_username,
                'pass' => $banggood_password,
                //'at' => $csrf_token,
                'login_image_code' => $captcha_answer,
				'captcha_sid' => '6257820',
				'captcha_token' => '4fe8e2ea0287dceb883fa255a72f5f5d',
        ) ),
        CURLOPT_HTTPHEADER => array (
                'x-requested-with: XMLHttpRequest' 
        ) 
) )->exec ()->getStdOut ();
var_dump ( $hc->getStdErr (),$html );

function deathbycaptcha(string $imageBinary, string $apiUsername, string $apiPassword): string {
    $hc = new hhb_curl ( '', true );
    $response = $hc->setopt_array ( array (
            CURLOPT_URL => 'http://api.dbcapi.me/api/captcha',
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array (
                    'Accept: application/json' 
            ),
            CURLOPT_POSTFIELDS => array (
                    'username' => $apiUsername,
                    'password' => $apiPassword,
                    'captchafile' => 'base64:' . base64_encode ( $imageBinary )  // use base64 because CURLFile requires a file, and i cba with tmpfile() .. but it would save bandwidth.
            ),
            CURLOPT_FOLLOWLOCATION => 0 
    ) )->exec ()->getStdOut ();
    $response_code = $hc->getinfo ( CURLINFO_HTTP_CODE );
    if ($response_code !== 303) {
        // some error
        $err = "DeathByCaptcha api retuned \"$response_code\", expected 303, ";
        switch ($response_code) {
            case 403 :
                $err .= " the api username/password was rejected";
                break;
            case 400 :
                $err .= " we sent an invalid request to the api (maybe the API specs has been updated?)";
                break;
            case 500 :
                $err .= " the api had an internal server error";
                break;
            case 503 :
                $err .= " api is temorarily unreachable, try again later";
                break;
            default :
                {
                    $err .= " unknown error";
                    break;
                }
        }
        $err .= ' - ' . $response;
        throw new \RuntimeException ( $err );
    }
    $response = json_decode ( $response, true );
    if (! empty ( $response ['text'] ) && $response ['text'] !== '?') {
        return $response ['text']; // sometimes the answer might be available right away.
    }
    $id = $response ['captcha'];
    $url = 'http://api.dbcapi.me/api/captcha/' . urlencode ( $id );
    while ( true ) {
        sleep ( 10 ); // check every 10 seconds
        $response = $hc->setopt ( CURLOPT_HTTPHEADER, array (
                'Accept: application/json' 
        ) )->exec ( $url )->getStdOut ();
        $response = json_decode ( $response, true );
        if (! empty ( $response ['text'] ) && $response ['text'] !== '?') {
            return $response ['text'];
        }
    }
}