<?php
// geolocate ip address
setlocale(LC_ALL, 'en_US.UTF8');
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

if ($loc) {
    $geoipdb = "/opt/geoip/GeoLiteCity.dat";
    require_once('geoipcity.inc');
    $gi = geoip_open($geoipdb, GEOIP_STANDARD);
    $record = geoip_record_by_addr($gi, $ip);
    geoip_close($gi);

    if ($txt) {
        $output = "$ip ($host)\n";
        $output .= "{$record->country_name}\n";
        $output .= "{$record->city} {$record->region} {$record->postal_code}\n";
    } else if ($ssid) {
        $country = strtok(str_replace(' ', '', $record->country_name), "\n");
        $city = str_replace(' ', '', $record->city);
        if ($country === 'UnitedStates') {
            $country = 'US';
        } else if ($country === 'UnitedKingdom') {
            $country = 'UK';
        }
        $region = $country === 'US' ? $record->region : '';
        $output = substr(str_replace(',', '', $prefix . $city . $region . $country), 0, 32);
    } else {
        $output = "<p>$ip ($host)</p>\n";
        $output .= "<p>{$record->country_name}</p>\n";
        $output .= "<p>{$record->city} {$record->region} {$record->postal_code}</p>\n";
        $output .= "<p><a href='http://maps.google.com/maps?q={$record->latitude}+{$record->longitude}'>{$record->latitude} {$record->longitude}</a></p>";
    }
    echo iconv('ISO-8859-1', 'ASCII//TRANSLIT//IGNORE', $output);
} else {
    echo $ip;
}

?>
