<?php

global $wpdb;

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://services.leadconnectorhq.com/users/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'companyId' => $_POST["companyId"],
        'firstName' => $_POST["firstName"],
        'lastName' => $_POST["lastName"],
        'email' => str_replace(" ","+", $_POST["email"]),
        'password' => $_POST["password"],
        'type' => 'account',
        'role' => 'user',
        'locationIds' => [
            $_POST["locationId"]
        ],
        'permissions' => [
            'campaignsEnabled' => false,
            'campaignsReadOnly' => false,
            'contactsEnabled' => true,
            'workflowsEnabled' => false,
            'workflowsReadOnly' => false,
            'triggersEnabled' => true,
            'funnelsEnabled' => true,
            'websitesEnabled' => true,
            'opportunitiesEnabled' => true,
            'dashboardStatsEnabled' => true,
            'bulkRequestsEnabled' => false,
            'appointmentsEnabled' => true,
            'reviewsEnabled' => true,
            'onlineListingsEnabled' => true,
            'phoneCallEnabled' => false,
            'conversationsEnabled' => false,
            'assignedDataOnly' => false,
            'adwordsReportingEnabled' => false,
            'membershipEnabled' => false,
            'facebookAdsReportingEnabled' => false,
            'attributionsReportingEnabled' => false,
            'settingsEnabled' => true,
            'tagsEnabled' => true,
            'leadValueEnabled' => true,
            'marketingEnabled' => true,
            'agentReportingEnabled' => false,
            'botService' => false,
            'socialPlanner' => true,
            'bloggingEnabled' => false,
            'invoiceEnabled' => false,
            'affiliateManagerEnabled' => false,
            'contentAiEnabled' => false,
            'refundsEnabled' => false,
            'recordPaymentEnabled' => false,
            'cancelSubscriptionEnabled' => false,
            'paymentsEnabled' => false
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "Accept: application/json",
        "Authorization: Bearer ".$_POST["access_token"],
        "Content-Type: application/json",
        "Version: 2021-07-28"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    $date = date("Y-m-d H:i:s");
    $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_user_form_submit_err', 'meta_value' => "cURL Error #:" . $err, "date" => $date ));
    echo "cURL Error #:" . $err;
} else {
    include_once("../../../wp-load.php");
    $resp = json_decode($response, true);
    if((isset($resp['id']))){
        $date = date("Y-m-d H:i:s");
        $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_user_form_submit', 'meta_value' => json_encode($_POST), "date" => $date));
        $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_user_curl_resp', 'meta_value' => $response, "date" => $date ));
        $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'is_plp_lic_active', 'meta_value' => "1", "date" => $date ));
    }else{
        $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_user_form_err', 'meta_value' => $response, "date" => $date ));
    }
    echo $response;
}