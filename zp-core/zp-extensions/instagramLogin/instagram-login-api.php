<?php

class InstagramApi 
{
	public function GetAccessToken($client_id, $redirect_uri, $client_secret, $code) {		
		$url = 'https://api.instagram.com/oauth/access_token';
		
		$curlPost = 'client_id='. $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);			
		$data = json_decode(curl_exec($ch), true);	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
		curl_close($ch); 		
		
		
		var_dump($curlPost,$http_code,$data);
		
		if($http_code != '200')			
			throw new Exception('Error : Failed to receieve access token');
		
		return $data['access_token'];	
	}

	public function GetUserProfileInfo($access_token) { 
		$url = 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token;	

		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
		curl_close($ch); 
		if($data['meta']['code'] != 200 || $http_code != 200)
			throw new Exception('Error : Failed to get user information');

		return $data['data'];
	}
}

?>