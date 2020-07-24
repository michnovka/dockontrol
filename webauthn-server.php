<?php

require_once 'WebAuthn/WebAuthn.php';
require_once dirname(__FILE__).'/libs/config.php';

_require_login();
$user = _get_user_array();


try {

	session_start();

	// read get argument and post body
	$fn = $_GET['fn'];

	$post = trim(file_get_contents('php://input'));
	if ($post) {
		$post = json_decode($post);
	}


	$requireResidentKey = false;

	$rpId = 'cp.libenskedoky.cz';

	$formats = array();

	$formats[] = 'android-key';
	$formats[] = 'android-safetynet';
	$formats[] = 'fido-u2f';
	$formats[] = 'none';
	$formats[] = 'packed';

	// new Instance of the server library.
	// make sure that $rpId is the domain name.
	$WebAuthn = new \WebAuthn\WebAuthn('WebAuthn Library', $rpId, $formats);

	$WebAuthn->addRootCertificates('rootCertificates/solo.pem');
	$WebAuthn->addRootCertificates('rootCertificates/yubico.pem');
	$WebAuthn->addRootCertificates('rootCertificates/hypersecu.pem');
	$WebAuthn->addRootCertificates('rootCertificates/globalSign.pem');
	$WebAuthn->addRootCertificates('rootCertificates/googleHardware.pem');

	// ------------------------------------
	// request for create arguments
	// ------------------------------------

	if ($fn === 'getCreateArgs') {
		// check NUKI pin
		$nuki = $db->queryfirst('SELECT * FROM nuki WHERE user_id=# AND id=# LIMIT 1', $user['id'], $_GET['nuki_id']);

		if(empty($nuki)){
			throw new Exception('Unauthorized');
		}elseif(intval($db->fetch('SELECT COUNT(*) FROM nuki_logs WHERE status=\'incorrect_pin\' AND time > DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND nuki_id=#', $nuki['id'])) > 5){
			throw new Exception('Too many PIN attempts. Try in 5 mins');
		}elseif(!$nuki['pin'] || $nuki['pin'] != $_GET['pin']){
			$db->query('INSERT INTO nuki_logs SET time=NOW(), nuki_id=#, status=\'incorrect_pin\', action=?', $nuki['id'], 'pin_check');
			throw new Exception('Incorrect PIN'.$_GET['pin']."-");
		}

		$createArgs = $WebAuthn->getCreateArgs($user['id'], $user['username'], $user['name'], 20);

		print(json_encode($createArgs));

		// save challange to session. you have to deliver it to processGet later.
		$_SESSION['challenge'] = $WebAuthn->getChallenge();

		// ------------------------------------
		// request for get arguments
		// ------------------------------------

	} else if ($fn === 'getGetArgs') {
		$ids = array();

		$credentialIds = null;
		$db->queryall('SELECT credentialId FROM webauthn_registrations WHERE user_id=#', $credentialIds, 'credentialId', $user['id']);

		foreach ($credentialIds as $credentialId){
			$ids[] = hex2bin($credentialId);
		}

		if (count($ids) === 0) {
			throw new Exception('no registrations in session.');
		}

		$getArgs = $WebAuthn->getGetArgs($ids);

		print(json_encode($getArgs));

		// save challange to session. you have to deliver it to processGet later.
		$_SESSION['challenge'] = $WebAuthn->getChallenge();

		// ------------------------------------
		// process create
		// ------------------------------------

	} else if ($fn === 'processCreate') {
		$clientDataJSON = base64_decode($post->clientDataJSON);
		$attestationObject = base64_decode($post->attestationObject);
		$challenge = $_SESSION['challenge'];

		// processCreate returns data to be stored for future logins.
		// in this example we store it in the php session.
		// Normaly you have to store the data in a database connected
		// with the user name.

		$data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge);

		$db->query('INSERT INTO webauthn_registrations SET user_id=#, created_time=now(), last_used_time=now(), data=??, credentialId = ?', $user['id'], serialize($data), bin2hex($data->credentialId));

		$return = new stdClass();
		$return->success = true;
		$return->msg = 'Registration Success.';
		print(json_encode($return));

		// ------------------------------------
		// proccess get
		// ------------------------------------

	} else if ($fn === 'processGet') {
		$clientDataJSON = base64_decode($post->clientDataJSON);
		$authenticatorData = base64_decode($post->authenticatorData);
		$signature = base64_decode($post->signature);
		$id = base64_decode($post->id);
		$nuki_id = $post->nuki_id;
		$challenge = $_SESSION['challenge'];
		$credentialPublicKey = null;

		$reg = $db->fetch('SELECT data FROM webauthn_registrations WHERE user_id=# AND credentialId=? LIMIT 1', $user['id'], bin2hex($id));

		if($reg) {
			$reg = unserialize($reg);
			$credentialPublicKey = $reg->credentialPublicKey;
		}

		if ($credentialPublicKey === null) {
			throw new Exception('Public Key for credential ID not found!');
		}

		// process the get request. throws WebAuthnException if it fails
		$WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge);

		$db->fetch('UPDATE webauthn_registrations SET last_used_time=NOW() WHERE user_id=# AND credentialId=? LIMIT 1', $user['id'], bin2hex($id));

		$pin = $db->fetch('SELECT pin FROM nuki WHERE id=# AND user_id=# LIMIT 1', $nuki_id, $user['id']);

		if(empty($pin)){
			throw new Exception("Unknown error");
		}

		$return = new stdClass();

		$return->pin = $pin;

		$return->success = true;


		print(json_encode($return));

		// ------------------------------------
		// proccess clear registrations
		// ------------------------------------

	}

} catch (Throwable $ex) {
	$return = new stdClass();
	$return->success = false;
	$return->msg = $ex->getMessage();
	print(json_encode($return));
}