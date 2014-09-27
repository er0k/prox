#!/bin/bash
# dynamic dns updater jawn

log=/var/log/lan/ddns.log
cert=/etc/ssl/certs/dyndns-registrar.pem
url=https://dyndns-registrar.tld/update
host=mydomain.tld
user=username
pass=password
old=/tmp/lan/ip.bak
new=/tmp/lan/ip
notify=er0k@deb
ip=`curl -s er0k.net/ip/`
rx='([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])'

if ! [[ "$ip" =~ ^$rx\.$rx\.$rx\.$rx$ ]]; then
	ip=error
fi

if [ ! -d /tmp/lan ]; then
	mkdir /tmp/lan
fi

if [ -f $new ]; then
	if [ -f $old ]; then
		rm $old
	fi
	mv $new $old
else
	touch $old
fi

echo $ip > $new

diff=`diff $old $new`
if [ "$diff" != "" ]; then
	echo -e \
	"`date`\t \
	`curl -s $url \
	-d hostname=$host \
	-d username=$user \
	-d password=$pass \
	--cacert $cert \
	--retry 3`" \
	>> $log
	echo "$diff" | mailx -s 'WAN IP changed' $notify
fi

