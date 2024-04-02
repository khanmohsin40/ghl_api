<?php
    
    include_once("includes/loader.php");
    include_once("../../wp-load.php");
    global $wpdb;
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header("Access-Control-Allow-Origin: https://services.leadconnectorhq.com/locations/");
    header("Access-Control-Allow-Credentials: true");
        
    $plp_user_email = (isset($_GET["uid"]))?$_GET["uid"]:"no@mail.com";
    $plp_user_email = str_replace(" ","+", $plp_user_email);
    // $plp_user_id = get_current_user_id();
    
    $plp_user_details = get_user_by_email($plp_user_email);
    $plp_user_id = "";
    if($plp_user_details){
        $plp_user_id = $plp_user_details->data->ID;
    }
    
    if(!$plp_user_id){
        include_once("includes/no_purchase_found.php");
        die();
    } 

    include_once("includes/form_css.php");

    $plp_create_user_curl_resp = $wpdb->get_row( $wpdb->prepare( 'SELECT `meta_value` FROM `wp_usermeta` WHERE `user_id` = '.$plp_user_id.' AND `meta_key` = "plp_create_user_curl_resp" ORDER BY umeta_id DESC;' ) );
    $plp_create_user_curl_resp_arr = NULL;
    if($plp_create_user_curl_resp){ $plp_create_user_curl_resp_arr = json_decode($plp_create_user_curl_resp->meta_value, true); }
    $plp_create_loc_form_submit = $wpdb->get_row( $wpdb->prepare( 'SELECT `meta_value` FROM `wp_usermeta` WHERE `user_id` = '.$plp_user_id.' AND `meta_key` = "plp_create_loc_form_submit" ORDER BY umeta_id DESC;' ) );
    $plp_create_loc_form_submit_arr = NULL;
    if($plp_create_loc_form_submit){ $plp_create_loc_form_submit_arr = json_decode($plp_create_loc_form_submit->meta_value, true); }
    
    if($plp_create_user_curl_resp == NULL || !isset($plp_create_user_curl_resp_arr["id"])){

        $atInfo = getRefreshAuthToken($wpdb, "ghl_api_token_agency");

        $plp_create_loc_curl_resp = $wpdb->get_row( $wpdb->prepare( 'SELECT `meta_value` FROM `wp_usermeta` WHERE `user_id` = '.$plp_user_id.' AND `meta_key` = "plp_create_loc_curl_resp" ORDER BY umeta_id DESC;' ) );
        
        $is_plp_lic_active = $wpdb->get_row( $wpdb->prepare( 'SELECT `meta_value` FROM `wp_usermeta` WHERE `user_id` = '.$plp_user_id.' AND `meta_key` = "is_plp_lic_active";' ) );
        
        if($is_plp_lic_active == NULL || ($is_plp_lic_active->meta_value == 0 || $is_plp_lic_active->meta_value == "0")){
            $plp_create_loc_curl_resp_arr = NULL;
            if($plp_create_loc_curl_resp){
                $plp_create_loc_curl_resp_arr = json_decode($plp_create_loc_curl_resp->meta_value, true);
            }
            if($plp_create_loc_curl_resp == NULL || !isset($plp_create_loc_curl_resp_arr["id"])){
                
                include_once("includes/form_location_details.php");
    
            } else {
                $user_location = json_decode($plp_create_loc_curl_resp->meta_value, true);
                $atInfoLoc = getRefreshAuthToken($wpdb, "ghl_api_token_location");
                include_once("includes/form_set_user_password.php");
            } 
        } else {
            include_once("includes/license_already_active.php");
        }
    } else {
        include_once("includes/license_already_active.php");        
    }


    function getRefreshAuthToken($wpdb, $meta_key ){
        $accessTokenInfo = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s AND user_id = %d", $meta_key, 1));
        $atInfo = json_decode($accessTokenInfo, true);

        if(isset($atInfo["expires_in"])){
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
        }else{
            echo '<h3 style="color:#FF9900; text-align: center;">Could not find the Auth Token and details, Please contact support.</h3>';
            die();
        }

        return $atInfo;

    }



?>