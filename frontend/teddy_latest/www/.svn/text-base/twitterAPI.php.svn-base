<?php

// A simple function using Curl to post (GET) to Twitter
// Kosso : March 14 2007

function postToTwitter($username,$password,$message){

    $host = "http://twitter.com/statuses/update.xml?status=".urlencode(stripslashes(urldecode($message)));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $result = curl_exec($ch);
    // Look at the returned header
    $resultArray = curl_getinfo($ch);

    curl_close($ch);

    if($resultArray['http_code'] == "200"){
         $twitter_status='Your message has been sended! <a href="http://twitter.com/'.$username.'">See your profile</a>';
    } else {
         $twitter_status="Error posting to Twitter. Retry";
    }
	return $twitter_status;
}
?>