<?php

require 'vendor/autoload.php';

setlocale(LC_ALL, 'en_US.UTF8');

$countryDb = '/opt/geoip/country/GeoLite2-Country.mmdb';
$cityDb = '/opt/geoip/city/GeoLite2-City.mmdb';

$ip = $_GET['ip'] ?? $_GET['ip'] ?: $_SERVER['REMOTE_ADDR'];
$getLocation = $_GET['loc'] ?? false;
$plainText = $_GET['txt'] ?? false;
$ssid = $_GET['ssid'] ?? false;
$prefix = $_GET['pre'] ?? '';

$host = gethostbyaddr($ip);

if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    $ip = gethostbyname($ip);
}

if ($getLocation) {
    $reader = new MaxMind\Db\Reader($cityDb);
    $record = $reader->get($ip);

    $continent = $record['continent']['names']['en'];
    $country = $record['country']['names']['en'];
    $city = $record['city']['names']['en'];
    $region = $record['subdivisions'][0]['names']['en'];
    $zip = $record['postal']['code'];
    $lat = $record['location']['latitude'];
    $lon = $record['location']['longitude'];
    $tz = $record['location']['time_zone'];

    if ($plainText) {
        $output = "$ip ($host)\n";
        $output .= "$country\n";
        $output .= "$city $region $zip\n";
    } else if ($ssid) {
        $country = strtok(str_replace(' ', '', $country), "\n");
        $city = str_replace(' ', '', $city);
        if ($country === 'UnitedStates') {
            $country = 'US';
        } else if ($country === 'UnitedKingdom') {
            $country = 'UK';
        }
        $region = $country === 'US' ? $region : '';
        $output = substr(str_replace(',', '', $prefix . $city . $region . $country), 0, 32);
    } else {
        $output = "<p>$ip ($host)</p>\n";
        $output .= "<p>$country</p>\n";
        $output .= "<p>$city $region $zip</p>\n";
        $output .= "<p><a href='http://maps.google.com/maps?q=$lat+$lon'>$lat $lon</a></p>";
    }
    echo iconv('ISO-8859-1', 'ASCII//TRANSLIT//IGNORE', $output);
} else {
    echo $ip;
}

?>
