<?php

header('Content-Type: text/html; charset=utf-8');
require_once('./classes/GoogleMapsApi.php');

$map = new MapsApi();
$message = 'улица Марата 8';
echo $map->getCoordinates($message)['latitude'];
// print_r($map->getCoordinates('СПб, ул. Политехническая, 21'));
