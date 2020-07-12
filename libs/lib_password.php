<?php

/**
 * Class PasswordTools
 *
 * Takes care of general password related things like random pwd generation,  hashing, checking and testing strength
 */
class PasswordTools
{
	//
	//

	/**
	 * generates random string from given alphabet
	 *
	 * @param int $length
	 * @param string|int $alphabet string which is used for random hash generation,numerical values of alphabet: 2 means binary, 10 - digits only, 16 - hexdec
	 * @return string
	 */
	static function generateRandomHash($length = 0, $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%^&*()-_+=;[]<>?,.')
	{

		if(intval($length) <= 0)
			$length = 8;

		if (is_numeric($alphabet)) {
			if ($alphabet == 10) {
				$alphabet = '0123456789';
			} elseif ($alphabet == 2) {
				$alphabet = '01';
			} else {
				$alphabet = '0123456789abcdef';
			}
		}

		$alphabet_length = strlen($alphabet);

		$hash = '';

		for ($i = 0; $i < $length; $i++) {
			$hash .= $alphabet[rand(0, $alphabet_length - 1)];
		}

		return $hash;
	}

	/**
	 * @param $password
	 * @param string $hash_algorithm
	 * @param string|null $salt if salt is string, then we take it as salt. otherwise, we take it as size of new salt to be created
	 * @param string|int|null $salt_alphabet
	 * @param string|null $salt_separator if salt separator is null, then salt is not appended
	 * @param null $salt_output
	 * @return string
	 */
	static function getHashedPassword($password, $hash_algorithm = 'md5', $salt = null, $salt_alphabet = null, $salt_separator = ':', &$salt_output = null)
	{
		if(!$salt || is_numeric($salt))
			$salt = $salt_alphabet ? self::generateRandomHash(intval($salt), $salt_alphabet) : self::generateRandomHash(intval($salt));

		if(!in_array($hash_algorithm, array('md5', 'sha256')))
			$hash_algorithm = 'md5';

		$salt_output = $salt;

		return hash($hash_algorithm, $salt.$password).$salt_separator.$salt;

	}


	/**
	 * checks if plain pwd and hashed match
	 *
	 * @param $password
	 * @param string $hashed_password_with_salt
	 * @param string $hash_algorithm
	 * @param string $salt_separator
	 * @return bool|null
	 */
	static function checkPassword($password, $hashed_password_with_salt, $hash_algorithm = 'md5', $salt_separator = ':'){
		if(!in_array($hash_algorithm, array('md5', 'sha256')))
			return null;

		list($hashed_password, $salt) = explode($salt_separator, $hashed_password_with_salt, 2);

		return hash($hash_algorithm, $salt.$password) == $hashed_password;
	}


	//
	/**
	 * checks strength of password
	 *
	 * @param $password
	 * @return float returns 0-10, where 0 is unsafe, 5 is acceptable and 10 is strong
	 */
	static function checkPasswordStrengthLevel($password){
		$score = 0;

		// check if contains lowercase
		if(preg_match('/^\S*(?=\S*[a-z])\S*$/', $password))
			$score++;

		// check if contains upper
		if(preg_match('/^\S*(?=\S*[A-Z])\S*$/', $password))
			$score++;

		//numbers
		if(preg_match('/^\S*(?=\S*[\d])\S*$/', $password))
			$score++;

		//special chars
		if(preg_match('/^\S*(?=\S*[\W])\S*$/', $password))
			$score++;

		$score += floor(strlen($password) / 3);

		return $score > 10 ? 10 : $score;
	}

	/**
	 * boolean function to check if pwd is strong enough
	 *
	 * @param $password
	 * @return bool
	 */
	static function checkPasswordStrength($password){
		return self::checkPasswordStrengthLevel($password) >= 5;
	}
}