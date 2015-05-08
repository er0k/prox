#!/bin/sh
iptables -t nat -D POSTROUTING -o tun0 -j MASQUERADE
