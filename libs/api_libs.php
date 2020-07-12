<?php

/**
 * @param int $channel
 * @param string $action
 * @param int $duration in microseconds
 * @param int $pause in microseconds
 * @return string
 */
function DoAction($channel, $action, $duration = 0, $pause = 0){
    switch($action){
        case 'ON':
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                break;
        case 'OFF':
                $output = `sudo /var/www/html/Relay.sh CH$channel OFF`;
                break;

        case 'PULSE':
                $duration = intval($duration);
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;

                break;

        case 'DOUBLECLICK':
                $duration = intval($duration);
                $pause = intval($pause);
                $output = `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;
                $output .= "\nSleeping $pause microseconds...\n";
                usleep($pause);
                $output .= `sudo /var/www/html/Relay.sh CH$channel ON`;
                $output .= "\nSleeping $duration microseconds...\n";
                usleep($duration);
                $output .= `sudo /var/www/html/Relay.sh CH$channel OFF`;

                break;

        default:   
                $output = "Unknown action";
    }

    return $output;
}

/**
 * @param string $remote_host
 * @param int $channel
 * @param string $action
 * @param int $duration in microseconds
 * @param int $pause in microseconds
 * @return string
 */
function DoActionRemote($remote_host, $channel, $action, $duration = 0, $pause = 0){
    $url = 'https://'.$remote_host.'/api.php?secret=PASSWORD736dbdyUDBN__d&channel='.intval($channel).'&action='.urlencode($action).'&duration='.intval($duration).'&pause='.intval($pause);

    $output = file_get_contents($url, false, stream_context_create(
    	array(
    		'ssl' => array(
    			'verify_peer' => false,
    			'verify_peer_name' => false,
				'allow_self_signed' => true
			),
			'http' => array(
				'timeout' => 10
			)
		)
	));

    return $output;
}







