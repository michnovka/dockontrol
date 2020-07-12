#!/bin/bash

if [ $1 == 'CH8' ]
then
 ch=26
elif [ $1 == 'CH7' ]
then
 ch=21
elif [ $1 == 'CH6' ]
then
 ch=20
elif [ $1 == 'CH5' ]
then
 ch=19
elif [ $1 == 'CH4' ]
then
 ch=16
elif [ $1 == 'CH3' ]
then
 ch=13
elif [ $1 == 'CH2' ]
then
 ch=6
elif [ $1 == 'CH1' ]
then
 ch=5
else
 echo "Parameter error"
 exit
fi

if [ $2 == 'ON' ]
then
 state=0
elif [ $2 == 'OFF' ]
then
 state=1
else
 echo "Parameter error"
 exit
fi


# Check if gpio is already exported
if [ ! -d /sys/class/gpio/gpio$ch ]
then
	echo $ch > /sys/class/gpio/export
  	sleep 1 ;# Short delay while GPIO permissions are set up
fi

echo out > /sys/class/gpio/gpio$ch/direction
echo $state > /sys/class/gpio/gpio$ch/value

echo Relay $1 $2

