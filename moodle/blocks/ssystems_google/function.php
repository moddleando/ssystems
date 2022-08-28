<?php

function googleSearch($sentence){


    $search = $sentence;
    if( $search != "")
    {
		
        $url = 'https://www.googleapis.com/customsearch/v1?key='.$API_GOOGLE.'&cx='.$ID_SEARCHENGINE.'&q='.$search;

        // sendRequest
        // note how referer is set manually
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $body = curl_exec($ch);
        curl_close($ch);
        
        // now, process the JSON string
        $json = json_decode($body);
        
        //return the results in json file
        return $json;
	
    }

}