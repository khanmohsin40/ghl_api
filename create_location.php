<?php

global $wpdb;

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://services.leadconnectorhq.com/locations/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'name' => $_POST["first_name"] . " " . $_POST["last_name"],
        'phone' => $_POST["phone"],
        'companyId' => $_POST["companyId"],
        'address' => $_POST["address"],
        'city' => $_POST["city"],
        'state' => $_POST["state"],
        'country' => $_POST["country"],
        'postalCode' => $_POST["postalCode"],
        'timezone' => 'US/Central',
        'prospectInfo' => [
            'firstName' => $_POST["first_name"],
            'lastName' => $_POST["last_name"],
            'email' => str_replace(" ","+", $_POST["email"])
        ],
        'settings' => [
            'allowDuplicateContact' => null,
            'allowDuplicateOpportunity' => null,
            'allowFacebookNameMerge' => null,
            'disableContactTimezone' => null
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Authorization: Bearer " . $_POST["accessToken"],
        "Content-Type: application/json",
        "Version: 2021-07-28"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);


if ($err) {
    echo "cURL Error #:" . $err;
     $wpdb->insert('wp_usermeta', array('user_id' => $_POST["user_id"], "meta_key" => 'plp_create_loc_err', 'meta_value' => "cURL Error #:" . $err, "date" => $date));
} else {
    include_once("../../../wp-load.php");
    $date = date("Y-m-d H:i:s");
    $data_json_decode = json_decode($response);
    $resp = json_decode($response, true);
    if(!(isset($resp['status']))){
        // file_put_contents("/var/www/members/plp_activate_license/Logs/Activation_Log.txt", "---- Request from Create Location / @ " . date("Y-m-d H:i:s") . " / \n" . var_export($data_json_decode, true) . "\n---- End request\n\n", FILE_APPEND);
        // file_put_contents("/home/hqepu4dokzax/public_html/plp_activate_license/Logs/Activation_Log.txt", "---- Request from Create Location / @ " . date("Y-m-d H:i:s") . " / \n" . var_export($data_json_decode, true) . "\n---- End request\n\n", FILE_APPEND);
        $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_loc_form_submit', 'meta_value' => json_encode($_POST), 'date' => $date));
        $wpdb->insert('wp_usermeta', array('user_id' => $_POST["user_id"], "meta_key" => 'plp_create_loc_curl_resp', 'meta_value' => $response, "date" => $date));
    }
    echo $response;
}