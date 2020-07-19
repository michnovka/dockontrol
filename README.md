# DOCKontrol

Control panel to open gates, entrances and garage doors at DOCK residence in Prague

## Requirements

PHP 7.3+, michnovka/openwebnet-php submodule to communicate with Bticino

## Hardware

This CP uses relays to send inputs to garage gates. I use Raspberry Pi4 together with [WaveShare relay board](https://www.waveshare.com/wiki/RPi_Relay_Board_(B)). Commands are sent using Relay.sh script (must be added to sudoers file since it requires root privileges)

CP also communicates with Bticino door entry system using the Openwebnet protocol and using this gateway: https://catalogue.bticino.com/BTI-F454-EN

Some peripherals are needed in other buildings which is accomplished using a network of other Raspberry PIs running the same Relay from Waveshare. Repo for software of these nodes can be found here: https://github.com/michnovka/dockontrol-node

## Logging and permissions

All actions are logged in DB with the user id, time of action and originating IP address. All loads of camera shots are logged as well, there is a rate limiter preventing constant monitoring of cameras inside the object. The logs are kept indefinitely.

## API specification

A simple API control is possible using the most basic form of authentication - plaintext username + password sent over HTTPS connection. Since passwords flying around in plaintext is, ehm, frowned upon, there are plans to change this for API keys and HMAC in the future. For now, be grateful this does not fly over HTTP to honor Bticino security standard.

The API call looks like
```http request
https://HOSTNAME/api.php?username=XXXXXX&password=YYYYYYY&action=ZZZZZZ
```

Where action can be any of the supported functions from [process_action.php](./libs/process_action.php)

Permissions are checked and API calls are logged (as well as unsuccessful ones). There is a simple IP-time based brute-force protection.