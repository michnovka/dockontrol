# DOCKontrol

Control panel to open gates, entrances and garage doors at DOCK residence in Prague

## Features
- Opening gates and garages
- Opening building entrances
- Camera snapshots
- API control and camera streams
- Guest passes
- User control with permission management

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

Where `action` can be any of the supported functions from [process_action.php](./libs/process_action.php)

The API call to fetch camera JPG looks like
```http request
https://HOSTNAME/camera.php?username=XXXXXX&password=YYYYYYY&camera=ZZZZZZ
```

Where `camera` is a key value of the `$cameras` array in [camera.php](./camera.php)

Permissions are checked and API calls are logged (as well as unsuccessful ones). There is a simple IP-time based brute-force protection.

## NUKI integration
 
This feature enables you to lock / unlock any doors with NUKI using [dockontrol-nuki-api](https://github.com/michnovka/dockontrol-nuki-api) server on your LAN.

Make sure that *dockontrol-nuki-api* is accessible over HTTPS from DOCKontrol CP. Configure it with NUKI Bridge parameters and add this connection in DOCKontrol settings page.

### NUKI security

Special care was put into securing NUKI locks, as they can serve as doors to your home. For that reason, every DOCKontrol NUKI lock is configured with 2 passwords. One password is stored securely inside the DOCKontrol DB, another password never leaves your browser. Both passwords are used to generate TOTP codes and DOCKontrol NUKI API server issues lock / unlock commands only if both match.

This means that even if somebody hacks DOCKontrol CP, they have no way to unlock your home. Even if somebody intercepts traffic on DOCKontrol server (even though its HTTPS), they cannot issue any commands, as TOTP codes are one-way hashed numbers, so they reveal no information about original passwords. Every TOTP code expires in 30 seconds and the use of nonces ensures that replay of commands is not possible.

Not even the developers / DOCKontrol server providers can issue commands to your NUKI, as one piece of the necessary codes is always saved only on your devices.

To avoid unauthorized door opening if your phone is stolen, a PIN code can be configured that is required for every NUKI unlock / lock operation. 

If your device supports fingerprints, then these can be used instead of the PIN code to protect unlocking without the owner's permission.

## CRON

In order to function, a CRON must be set to process queue. A sample CRON is below:

```crontab
* * * * * php /var/www/html/cron/action_queue.php gate
* * * * * php /var/www/html/cron/action_queue.php entrances
* * * * * php /var/www/html/cron/action_queue.php z7
* * * * * php /var/www/html/cron/action_queue.php z8
* * * * * php /var/www/html/cron/action_queue.php z9

0 2 * * * php /var/www/html/cron/db_cleanup.php
```

### Disclaimer

I honestly believe the security is sound, but I encourage everybody to properly review the code before using it with their NUKI devices. I am not responsible for any bugs that might be present, the software is provided in good faith as is, with no guarantees.

## Credits

Special thanks to the contributors of these repos:

- https://github.com/lbuchs/WebAuthn
- https://github.com/jiangts/JS-OTP
- https://github.com/emn178/hi-base32

### TODO

Want to help? The following things are currently on my TODO and I will be grateful for any pull requests:

- CSRF protection
- Allow/disallow guests to control NUKI
- Camera cache
- Guest codes overview and management
- show NUKI logs
- Dark mode CSS