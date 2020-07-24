<?php


function convBase($numberInput, $fromBaseInput, $toBaseInput)
{
	if ($fromBaseInput==$toBaseInput) return $numberInput;
	$fromBase = str_split($fromBaseInput,1);
	$toBase = str_split($toBaseInput,1);
	$number = str_split($numberInput,1);
	$fromLen=strlen($fromBaseInput);
	$toLen=strlen($toBaseInput);
	$numberLen=strlen($numberInput);
	$retval='';
	if ($toBaseInput == '0123456789')
	{
		$retval=0;
		for ($i = 1;$i <= $numberLen; $i++)
			$retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
		return $retval;
	}
	if ($fromBaseInput != '0123456789')
		$base10=convBase($numberInput, $fromBaseInput, '0123456789');
	else
		$base10 = $numberInput;
	if ($base10<strlen($toBaseInput))
		return $toBase[$base10];
	while($base10 != '0')
	{
		$retval = $toBase[bcmod($base10,$toLen)].$retval;
		$base10 = bcdiv($base10,$toLen,0);
	}

	return $retval;
}

class GoogleAuthenticator{

	public static function hex_to_base32($hex){
		return convBase($hex, '0123456789abcdef', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
	}

	public static function base32_decode($b32) {
		$lut = array(
			"A" => 0,       "B" => 1,
			"C" => 2,       "D" => 3,
			"E" => 4,       "F" => 5,
			"G" => 6,       "H" => 7,
			"I" => 8,       "J" => 9,
			"K" => 10,      "L" => 11,
			"M" => 12,      "N" => 13,
			"O" => 14,      "P" => 15,
			"Q" => 16,      "R" => 17,
			"S" => 18,      "T" => 19,
			"U" => 20,      "V" => 21,
			"W" => 22,      "X" => 23,
			"Y" => 24,      "Z" => 25,
			"2" => 26,      "3" => 27,
			"4" => 28,      "5" => 29,
			"6" => 30,      "7" => 31
		);

		$b32    = strtoupper($b32);
		$l      = strlen($b32);
		$n      = 0;
		$j      = 0;
		$binary = "";

		for ($i = 0; $i < $l; $i++) {

			$n = $n << 5;
			$n = $n + $lut[$b32[$i]];
			$j = $j + 5;

			if ($j >= 8) {
				$j = $j - 8;
				$binary .= chr(($n & (0xFF << $j)) >> $j);
			}
		}

		return $binary;
	}

	public static function get_timestamp($microtime_float_now = null) {

		if(!$microtime_float_now)
			$microtime_float_now = microtime(true);

		return floor($microtime_float_now/30);
	}

	public static function get_totp($key, $timestamp = null){


		$binary_timestamp = pack('N*', 0) . pack('N*', self::get_timestamp($timestamp));

		//Once you have the binary seed and the binary timestamp you have to pass them into the "hash_hmac" function. This gives you a 20 byte SHA1 string.

		$binary_key = self::base32_decode($key);

		$hash = hash_hmac ('sha1', $binary_timestamp, $binary_key, true);

		//This hash is then processed in accordance with RFC 4226 to obtain the one time password.

		$offset = ord($hash[19]) & 0xf;

		$OTP = (
				((ord($hash[$offset+0]) & 0x7f) << 24 ) |
				((ord($hash[$offset+1]) & 0xff) << 16 ) |
				((ord($hash[$offset+2]) & 0xff) << 8 ) |
				(ord($hash[$offset+3]) & 0xff)
			) % pow(10, 6);

		return str_pad($OTP, 6, '0', STR_PAD_LEFT);

	}

	public static function check_totp($key, $code){
		$timestamp = microtime(true) - 120;

		for($i = 0; $i < 7; $i++){
			$timestamp += 30;

			if(intval(self::get_totp($key, $timestamp)) == intval($code)){
				return true;
			}
		}

		return false;

	}

	public static function get_random_base32_string($length){

		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

		$alphabet_length = strlen($alphabet);

		$hash = '';

		for ($i = 0; $i < $length; $i++) {
			$hash .= $alphabet[rand(0, $alphabet_length - 1)];
		}


		return $hash;
	}


}
