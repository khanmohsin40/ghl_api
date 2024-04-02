<?php

include_once("../../wp-load.php");
global $wpdb;
    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET["code"]) && !empty($_GET["code"])) {
    echo '<h1> CODE : <span style="color:red">' . $_GET["code"] . '</span></h1><hr />';
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://services.leadconnectorhq.com/oauth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "client_id=64cce807cc1d8f541b86b53d-lkwjb3w7&client_secret=9d8d56ca-8f47-46f4-a8f9-1bdb84ca9667&grant_type=authorization_code&code=".$_GET["code"],
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Content-Type: application/x-www-form-urlencoded"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $atInfo = json_decode($response, true);
    
    if ($err) {
        echo "Please go back or refresh the page and try authentication once agian. , a cURL error occurred with # : " . $err;
        die();
    } else {
        if(isset($atInfo['error'])){
            echo '<h3 style="color:#FF9900">Failed to generate new Auth Token, Please try again.</h3>Error Title : <span style="color:red"><strong>'.$atInfo['error'].'</strong></span><br />Error Description : <span style="color:red"><strong>'.$atInfo['error_description'].'</strong></span>';
            $accessTokenInfo = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s AND user_id = %d", "ghl_api_token_agency", 1));
            $atInfo = json_decode($accessTokenInfo, true);
            
            
            echo 'Failed...';
            echo '<hr />Auth Token Info : ';
            echo '<pre>';
            print_r($atInfo);
            echo '</pre>';
            
            
        }else{
            $meta_key = "ghl_api_token_location";
            if($atInfo['userType'] == "Company"){ $meta_key = "ghl_api_token_agency"; }
            $wpdb->update(
                $wpdb->usermeta,
                array('meta_value' => $response),
                array('user_id' => 1, 'meta_key' => $meta_key)
            );
            if ($wpdb->last_error) {
                echo '<hr /><h1>Please go back or refresh the page and try authentication once agian. The system failed to update auth key info in the database : <span style="color:red">' . $wpdb->last_error."</span></h1>";
            }
            
            
            echo 'Success...';
            echo '<hr />Auth Token Info : ';
            echo '<pre>';
            print_r($atInfo);
            echo '</pre>';
        }
    }
    die(); 
}

$accessTokenInfoLocation = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s AND user_id = %d", "ghl_api_token_agency", 1));
if ($accessTokenInfoLocation) { getRefreshAuthToken($wpdb, "ghl_api_token_agency", $accessTokenInfoLocation); }
$accessTokenInfoAgency = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s AND user_id = %d", "ghl_api_token_location", 1));
if ($accessTokenInfoAgency) { getRefreshAuthToken($wpdb, "ghl_api_token_location", $accessTokenInfoAgency); }

function getRefreshAuthToken($wpdb, $meta_key, $accessTokenInfo){
    $atInfo = json_decode($accessTokenInfo, true);
    
    $accessTokenCreateDate = $wpdb->get_var($wpdb->prepare("SELECT `date` FROM $wpdb->usermeta WHERE meta_key = %s AND user_id = %d", $meta_key, 1));
    $createdDate = new DateTime($accessTokenCreateDate);
    $dateTodayStr = date("Y-m-d H:i:s");
    $dateToday = new DateTime($dateTodayStr);
    $timeInterval = $dateToday->getTimestamp() - $createdDate->getTimestamp();
    
    if($timeInterval > $atInfo["expires_in"]){
        // echo '<h1> Refreshing Token </h1><hr />';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://services.leadconnectorhq.com/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "client_id=64cce807cc1d8f541b86b53d-lkwjb3w7&client_secret=9d8d56ca-8f47-46f4-a8f9-1bdb84ca9667&grant_type=refresh_token&refresh_token=".$atInfo["refresh_token"]."&user_type=Location&redirect_uri=",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Content-Type: application/x-www-form-urlencoded"
            ],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);            
        $atInfo = json_decode($response, true);
        
        if ($err) {
            echo "Please go back or refresh the page and try once agian. , a cURL error occurred with # : " . $err;
            die();
        } else {
            if(isset($atInfo['error'])){
                echo '<h3 style="color:#FF9900">Failed to refreshed the Auth Token, Please try again.</h3><br />Error Title : <span style="color:red"><strong>'.$atInfo['error'].'</strong></span><br />Error Description : <span style="color:red"><strong>'.$atInfo['error_description'].'</strong></span>';
                die();
            }else{
                $wpdb->update(
                    $wpdb->usermeta,
                    array('meta_value' => $response),
                    array('user_id' => 1, 'meta_key' => $meta_key)
                );
                if ($wpdb->last_error) {
                    echo '<hr /><h1>Please go back and try once agian. The system failed to update auth key info in the database : <span style="color:red">' . $wpdb->last_error."</span></h1>";
                }
            }
        }
    }
    echo 'All good :+1 ...<hr />';
    /* echo '<br />Auth Token Info ('.$meta_key.') : ';
    echo '<pre>';
    print_r($atInfo);
    echo '</pre>'; */
}