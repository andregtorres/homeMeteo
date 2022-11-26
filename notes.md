
# homeMeteo
Distributed home meteorologic stations.

#Network testing
make sure that iptables are correct:
agtorres@andrePC:~/repos/homeMeteo$ sudo iptables -S
-P INPUT DROP
-P FORWARD DROP
-P OUTPUT ACCEPT

sudo iptables --policy INPUT ACCEPT

## Simple hhtp server
python:
python3 -m http.server 8080
php:
php -S 192.168.0.199:8080


For my old node MCU boards, I had to downgrade the Esp Board Driver to 2.3.0 as seen [here](
https://github.com/FirebaseExtended/firebase-arduino/issues/460).

## Wiring
orange D1
green D2
red 3V
yellow G
