#!/bin/sh
iptables -t nat -I POSTROUTING -o tun0 -j MASQUERADE
