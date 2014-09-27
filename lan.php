#!/usr/bin/env php
<?php
/**
scrape DD-WRT router page for LAN clients and log them 
put this in a cron job or w/e
**/

$router = '192.168.1.1';
$user = 'root';
$pass = 'password';
$file = '/tmp/lan/pub';
$logfile = '/var/log/lan/pub';

// get lan status from router
$ch = curl_init("http://$router/Status_Lan.live.asp");

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");

$out = curl_exec($ch);
curl_close($ch);

// put status results into array
$array = explode("\n", $out);
array_pop($array);

// build the lan array
// with setting name as key
// and setting value as value
foreach($array as $key => $value) {
	// strip out curly braces
	$rem = array('{','}');
	$value = str_replace($rem,'',$value);
	
	#echo "$value\n";

	$array[$key] = explode('::', $value);

	$a = $array[$key][0];
	$b = $array[$key][1];
	$lan[$a] = $b;
}

$l = explode(',',$lan['dhcp_leases']);
$a = explode(',',$lan['arp_table']);

$leases = $arps = array();
$li = $ai = 0;

// create array for dhcp leases
foreach ($l as $lkey => $lval) {
	if ($lkey % 5 == 0) {
		$li++;
		$lj = 0;
	}
	switch ($lj) {
		case 0: $key = 'host'; break;
		case 1: $key = 'ip'; break;
		case 2: $key = 'mac'; break;
		case 3: $key = 'time'; break;
		case 4: $key = 'num'; break;
	}
	$leases[$li][$key] = str_replace("'", '', $lval);
	$lj++;
}

// create array for arp table
foreach ($a as $akey => $aval) {
	if ($akey % 4 == 0) {
		$ai++;
		$aj = 0;
	}
	switch ($aj) {
		case 0: $key = 'host'; break;
		case 1: $key = 'ip'; break;
		case 2: $key = 'mac'; break;
		case 3: $key = 'connects'; break;
	}
	$arps[$ai][$key] = str_replace("'", '', $aval);
	$aj++;
}

// merge leases and aprs back to lan array
$lan['dhcp_leases'] = $leases;
$lan['arp_table'] = $arps;

/**
 * lan_mac		=> mac address of lan adapter
 * lan_ip		=> ip of lan adapter
 * lan_ip_prefix	=>
 * lan_netmast		=>
 * lan_gateway		=>
 * lan_dns		=>
 * lan_proto		=>
 * dhcp_daemon		=>
 * dhcp_start		=>
 * dhcp_num		=>
 * dhcp_lease_time	=> lease time in seconds
 * dhcp_leases
 * 	n
 * 		host		=> hostname
 * 		ip		=> ip address
 * 		mac		=> mac address
 * 		time		=> lease time
 * 		num		=> ip suffix
 * arp_table
 * 	n
 * 		host		=> hostname
 * 		ip		=> ip address
 * 		mac		=> mac address
 * 		connects	=> number of IP connections
 */

$msg = $msg2 = '';

// if there are active clients, put info in msg
foreach($lan['arp_table'] as $client) {
	if (array_key_exists('host', $client) &&
		array_key_exists('ip', $client) &&
		array_key_exists('mac', $client) &&
		array_key_exists('connects', $client)
	) {
		$hasClients = true;
		$msg .= trim($client['ip'])."\t";
		$msg .= trim($client['mac'])."\t";
		$msg .= trim($client['host'])."\t";
	} else {
		$hasClients = false;
		$msg .= 'no clients';
	}
	$msg .= "\n";
}

// set up files for diff
$old = $file . '.bak';
$new = $file;

// move new file to old
if(file_exists($new)) {
	if(file_exists($old)) {
		unlink($old);
	}
	rename($new,$old);
}

// write new file
if(!$fp = fopen($new, 'w')) {
	echo "cannot open $new\n";
} else {
	if (fwrite($fp, $msg) === FALSE) {
		echo "cannot write to $new";
	}
	fclose($fp);
}

// diff old to new
$cmd = "diff $old $new";
$output = shell_exec($cmd);

// if there is a difference
if ($output) {
	// if ($hasClients) {
	// 	// nmap each client
	// 	foreach ($wifi as $n => $client) {
	// 		$cmd2 = 'nmap '.$wifi[$n][1];
	// 		$msg2 .= shell_exec($cmd2);
	// 	}
	// }

	// write to log
	$tmplog = explode("\n", $msg);
	foreach ($tmplog as $key => $line) {
		if (!empty($line)) $tmplog[$key] = date('r')."\t".$line;
	}
	$log = implode("\n", $tmplog);

	if(!$fp = fopen($logfile, 'a')) {
		echo "cannot open logfile\n";
	} else {
		if (fwrite($fp, $log) === FALSE) {
			echo "cannot write to $new";
		}
		fclose($fp);
	}
}

?>
