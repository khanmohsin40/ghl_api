<?php

global $wpdb;

$printResponse = array(
    "searchContactCurlErr" => "",
    "searchContactCurlResponse" => "",
    "searchContactContactId" => "",
    "updateContactTagCurlErr" => "",
    "updateContactTagCurlResponse" => "",
    "updateContactCustFieldsCurlErr" => "",
    "updateContactCustFieldsCurlResponse" => "",
    "createTaskCurlErr" => "",
    "createTaskCurlResponse" => "",
    "accessToken" => $_POST["access_token"],
    "searchContzctCURL" => "https://services.leadconnectorhq.com/contacts/search/duplicate?locationId=8E7gyNsI19TGtqmSnzNm&email=".urlencode($_POST["email"])
);

$searchContactCurl = curl_init();
curl_setopt_array($searchContactCurl, [
  CURLOPT_URL => "https://services.leadconnectorhq.com/contacts/search/duplicate?locationId=8E7gyNsI19TGtqmSnzNm&email=".urlencode($_POST["email"]),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "Accept: application/json",
    "Authorization: Bearer ".$_POST["access_token"],
    "Version: 2021-07-28"
  ],
]);
$searchContactCurlResponse = curl_exec($searchContactCurl);
$searchContactCurlErr = curl_error($searchContactCurl);
curl_close($searchContactCurl);
if ($searchContactCurlErr) { $printResponse["searchContactCurlErr"] =  "cURL Error #:" . $searchContactCurlErr; }
else { $printResponse["searchContactCurlResponse"] = json_decode($searchContactCurlResponse, true); }

if($printResponse["searchContactCurlErr"] == "" || $printResponse["searchContactCurlErr"] == NULL){
  $printResponse["searchContactContactId"] = $printResponse["searchContactCurlResponse"]["contact"]["id"];
  $updateContactCustFieldsCurl = curl_init();
  curl_setopt_array($updateContactCustFieldsCurl, [
    CURLOPT_URL => "https://services.leadconnectorhq.com/contacts/".$printResponse["searchContactCurlResponse"]["contact"]["id"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "PUT",
    CURLOPT_POSTFIELDS => json_encode([
      'customFields' => [
          [
            'id' => 'uyqPQlhHF3pLfojiCM7A',
            'key' => 'contact.password',
            'field_value' => $_POST["password"]
          ]
      ]
    ]),
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "Authorization: Bearer ".$_POST["access_token"],
      "Content-Type: application/json",
      "Version: 2021-07-28"
    ],
  ]);
  $updateContactCustFieldsCurlResponse = curl_exec($updateContactCustFieldsCurl);
  $updateContactCustFieldsCurlErr = curl_error($updateContactCustFieldsCurl);
  curl_close($updateContactCustFieldsCurl);
  if ($updateContactCustFieldsCurlErr) { 
      $printResponse["updateContactCustFieldsCurlErr"] =  "cURL Error #:" . $updateContactCustFieldsCurlErr;
      $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_update_contact_custom_field_err', 'meta_value' =>  "cURL Error #:" . $updateContactCustFieldsCurlErr, "date" => $date));
  }
  else { $printResponse["updateContactCustFieldsCurlResponse"] = json_encode($updateContactCustFieldsCurlResponse, true); }

  $updateContactTagCurl = curl_init();
  curl_setopt_array($updateContactTagCurl, [
    CURLOPT_URL => "https://services.leadconnectorhq.com/contacts/".$printResponse["searchContactCurlResponse"]["contact"]["id"]."/tags",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
      'tags' => [
          'lifetime license member: activated license'
      ]
    ]),
    CURLOPT_HTTPHEADER => [
      "Accept: application/json",
      "Authorization: Bearer ".$_POST["access_token"],
      "Content-Type: application/json",
      "Version: 2021-07-28"
    ],
  ]);
  $updateContactTagCurlResponse = curl_exec($updateContactTagCurl);
  $updateContactTagCurlErr = curl_error($updateContactTagCurl);
  curl_close($updateContactTagCurl);
  if ($updateContactTagCurlErr) {
      $printResponse["updateContactTagCurlErr"] =  "cURL Error #:" . $updateContactTagCurlErr;
      $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_update_tag_err', 'meta_value' =>  "cURL Error #:" . $updateContactTagCurlErr, "date" => $date));
  }
  else { $printResponse["updateContactTagCurlResponse"] = json_encode($updateContactTagCurlResponse, true); }
}

//$task_description = 'A new customer has requested their Dashboard login details => Name : '.$_POST["name"].' | email : '.$_POST["email"].' | phone : '.$_POST["phone"];
$task_description = 'A new customer has requested their Dashboard login details => Name : '.$_POST["name"].' | email : '.$_POST["email"];
$createTaskCurl = curl_init();
curl_setopt_array($createTaskCurl, [
  CURLOPT_URL => "https://services.leadconnectorhq.com/contacts/".$printResponse["searchContactCurlResponse"]["contact"]["id"]."/tasks",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'title' => 'NEW Lifetime License Activation Request!',
    'body' => $task_description,
    'dueDate' => '2020-10-25T11:00:00Z',
    'completed' => false,
    'assignedTo' => 'b1MDWD5EEGxZniwX1idz'
  ]),
  CURLOPT_HTTPHEADER => [
    "Accept: application/json",
    "Authorization: Bearer ".$_POST["access_token"],
    "Content-Type: application/json",
    "Version: 2021-07-28"
  ],
]);
$createTaskCurlResponse = curl_exec($createTaskCurl);
$createTaskCurlErr = curl_error($createTaskCurl);
curl_close($createTaskCurl);

if ($createTaskCurlErr) {
  $printResponse["createTaskCurlErr"] = "cURL Error #:" . $createTaskCurlErr;
  $wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_task_err', 'meta_value' =>  "cURL Error #:" . $createTaskCurlErr, "date" => $date));
} else {
  $printResponse["createTaskCurlResponse"] = json_encode($createTaskCurlResponse, true);
}

include_once("../../../wp-load.php");
$wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_search_cont_curl_resp', 'meta_value' => json_encode($searchContactCurlResponse), "date" => $date));
$wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_updt_cont_cust_fields_curl_resp', 'meta_value' => json_encode($updateContactCustFieldsCurlResponse), "date" => $date));
$wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_updt_cont_tags_curl_resp', 'meta_value' => json_encode($updateContactTagCurlResponse), "date" => $date));
$wpdb->insert('wp_usermeta', array( 'user_id' => $_POST["user_id"], "meta_key" => 'plp_create_task_curl_resp', 'meta_value' => json_encode($createTaskCurlResponse), "date" => $date));
$wpdb->insert('wp_user_exist', array(
  'first_name' => $_POST["firstName"],
  "last_name" => $_POST["lastName"],
  'email_id' => $_POST["email"],
  "location_id" => $_POST["location_id"],
  "business_name" => $_POST["name"],
  "flag_exist" => 1,
  "date" => date("Y-m-d H:i:s"),
  "from_ip" => $_SERVER['REMOTE_ADDR'],
  "logged_in_user" => $_POST["user_id"]
));
echo json_encode($printResponse);