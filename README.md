# prox
some scripts to make a public wifi hotspot that automatically connects to a random proxy every 24 hours

You will need to install [dd-wrt](https://dd-wrt.com/) on your router

You will also need some kind of *nix machine(s) to run these scripts

Set up dd-wrt with some basic settings:

* under **Services > Secure Shell > Authorized Keys**, add your public ssh key of the machine that will run these scripts
* you may want to enable remote syslog (**Services > System Log**) to help debug any openvpn problems
* under **Administration > Management > Remote Access** enable Web GUI and SSH so you can administer the dd-wrt from your main network. You should also Disable the Allow Any Remote IP and add your network range
* under **Administration > Commands > Firewall** add the contents of `iptables`

`ip.php` should live outside your local network in order to check your external IP address. It uses [MaxMind's geolocation database](http://dev.maxmind.com/geoip/geoip2/geolite2/). If you don't have a server I guess you can use [this one](https://er0k.net/ip.php)

`prox` is a bash script that will check if the proxy connection on the router is active. If not it will pick a random IP from the list and try to connect. You can run it in a cronjob every few minutes. If a socket is already open to the router the script will terminate. 

Create a text file with all the IP addresses from your VPN provider. Each IP should be on a new line. Update `proxyList` in `prox` with the location of this file.

`pubClients` is a php script that will check for any client connections to the public wifi network and log them to a remote syslog server. Put the dd-wrt credentials in `config.php`

`pubclients.conf` is a simple way to daemonize it with upstart.

You should get a username, password and certificate from your VPN provider. Put them in `pub/`. You should also put an actual IP in `pub/openvpn.conf` for the first run. After that `prox` will pattern match on the IP address to replace it with a random one. 
