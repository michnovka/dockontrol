<?php

/**
 * @param string $stream_url
 * @param string $stream_login
 * @return bool|string
 */
function fetchCameraPicture($stream_url, $stream_login){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $stream_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if($stream_login) {
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_USERPWD, $stream_login);
	}

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	return curl_exec($ch);
}