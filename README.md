# DOCKontrol

Control panel to open gates, entrances and garage doors at DOCK residence in Prague

## Requirements

PHP 7.3+, michnovka/openwebnet-php submodule to communicate with Bticino

## Hardware

This CP uses relays to send inputs to garage gates. I use Raspberry Pi4 together with https://www.waveshare.com/wiki/RPi_Relay_Board_(B) relay board. Commands are sent using Relay.sh script (must be added to sudoers file since it requires root privileges)

CP also communicates with Bticino door entry system using the Openwebnet protocol and using this gateway: https://catalogue.bticino.com/BTI-F454-EN

Some peripherals are needed in other buildings which is accomplished using a network of other Raspberry PIs running the same Relay from Waveshare. Repo for software of these nodes will be published later.