<?php

function call($endpoint, $type = NULL, $data_string = NULL)
{
  $url = 'https://api.github.com/'.$endpoint;
  
  $timeout = 10;
  $ch = curl_init($url);
        
  if( $type == CURLOPT_PUT){
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  }
  else if( $type == CURLOPT_POST){
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  }
  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout-1);
  // Authenticate via OAuth Tokens
  // http://developer.github.com/v3/auth/
  // https://github.com/blog/1509-personal-api-tokens
  $secret_token = '';
  curl_setopt($ch, CURLOPT_USERPWD, $secret_token.':x-oauth-basic');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $contents = curl_exec($ch);
  curl_close($ch);

  return json_decode($contents);
}


//Open a new Pull Request
$pulls = call('repos/beenwa/GitHub-API/pulls?state=open&base=qa');
if( !count($pulls) ){
  call('repos/beenwa/GitHub-API/pulls', CURLOPT_POST, json_encode(Array('title'=>'Merge','head'=>'beenwa:dev','base'=>'beenwa:qa')));    
}

//Merge time!
if( date('H') == 9 ){
  $pulls = call('repos/beenwa/GitHub-API/pulls?state=open&base=qa');
  foreach($pulls as $pull)
  {
    if($pull->head->ref != 'dev'){
      continue;
    }
    if( empty($pull->mergeable) && empty($pull->merge_commit_sha) ){
      continue;
    }
    call('repos/beenwa/GitHub-API/pulls/'.$pull->number.'/merge', CURLOPT_PUT, json_encode(Array('commit_message' => 'Done!')));
  }
}

