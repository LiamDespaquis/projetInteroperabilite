<?php

namespace Gaetan\AppVoiture;

require_once __DIR__ . "/../vendor/autoload.php";
$urlAPILoc = "http://ip-api.com/xml/";

$ipClient = $_SERVER['REMOTE_ADDR'];
$ipClient = "193.50.135.206";
$res = file_get_contents($urlAPILoc . $ipClient);
$status = explode(' ', $http_response_header[0])[1];
if ($status != "200") {
    echo "Status pas ok $status";
    return 1;
}
$xml = simplexml_load_string($res);
/*var_dump($xml);*/
$pays = $xml->country;
if ($pays != "France") {
    echo "Pays pas français, $pays";
    return 1;
}
"https://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
$loc = $xml->lat. ",". $xml->lon;
echo $xml->city. " " .$loc;
