<?php
// geolocate ip address

$ip = isset($_GET['ip']) ? $_GET['ip'] : $_SERVER['REMOTE_ADDR'];
$loc = isset($_GET['loc']) ? $_GET['loc'] : '';
$txt = isset($_GET['txt']) ? true : false;
$ssid = isset($_GET['ssid']) ? true : false;
$prefix = isset($_GET['pre']) ? $_GET['pre'] : '';

if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $host = gethostbyaddr($ip);
} else {
        $ip = gethostbyname($ip);
        $host = gethostbyaddr($ip);
}

if($loc=="y") {
        require_once('geo.php');
        $gi = geoip_open($geoipdb,GEOIP_STANDARD);
        $record = geoip_record_by_addr($gi,$ip);

        if ($txt) {
                $output = "$ip ($host)\n";
                $output .= $record->country_name . "\n";
                $output .= $record->city . ' ' . $record->region . ' ' . $record->postal_code . "\n";
        } else if ($ssid) {
                $country = strtok(str_replace(' ', '', $record->country_name), "\n");
                $city = str_replace(' ', '', $record->city);
                $output = substr(str_replace(',', '', $prefix . $city . $country), 0, 32);
        } else {
                $output = "<p>".$ip." (".$host.")</p>\n";
                $output .= "<p>".$record->country_name . "</p>\n";
                $output .= "<p>".$record->city." ".$record->region." ".$record->postal_code."</p>\n";
                $output .= '<p><a href="http://maps.google.com/maps?q='.$record->latitude.'+'.$record->longitude.'">'.$record->latitude.' '.$record->longitude.'</a></p>';
        }
        geoip_close($gi);

        print $output;

} else {
    print $ip;
}

?>
