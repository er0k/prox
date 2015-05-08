# prox
These are some scripts to make a public wifi hotspot using a dd-wrt router
that automatically connects to a random proxy

We will assume you main (private) subnet is at 10.11.11.0/24
and your dd-wrt (public) subnet is 192.168.0.0/24


first, set up your dd-wrt with some basic settings:

under Services > Secure Shell > Authorized Keys, add your public ssh key of the 
machine that will run these scripts

you may want to enable remote syslog (Services > System Log) to help debug any
openvpn problems

under NAT / QoS > QoS you may want to limit the bandwidth of the dd-wrt
I like 2048 kbps for up and down

under Administration > Management > Remote Access enable Web GUI and SSH
so you can administer the dd-wrt from your main network
you should also Disable the Allow Any Remote IP and add your network range

under Administration > Commands > Firewall add the contents of iptables

hopefully you have a server outside of your LAN you can use to check external IP
put ip.php there

prox is the script that will check if the proxy connection on the dd-wrt is active
if not it will pick a random IP from the list and try to connect

pubClients will check for any client connections to the public wifi network and
log them to a remote syslog server

if you need a good proxy service try https://proxy.sh :)
