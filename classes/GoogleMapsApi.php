<?php


class MapsApi {

    const MAPS_API_URL = 'https://maps.googleapis.com/maps/api/';
    const MAPS_API_KEY = 'GOOGLE-MAPS-API-KEY';

    private $apiKey;


    public function __construct() {
        $this->apiKey = self::MAPS_API_KEY;
    }


    private function getApiKey() {
        return $this->apiKey;
    }


    public function isAddressValid($address) {
        // get and decode json
        $requestParams = http_build_query([
            'key' => $this->getApiKey(),
            'address' => $address,
            'language' => 'ru-RU'
        ]);

        // specify service and output format
        $service = 'geocode';
        $format = 'json';

        // make request
        $response = json_decode(file_get_contents(self::MAPS_API_URL . "$service/$format?" . $requestParams));

        // validate and return bool
        if ($response->status == 'OK') {
            return $response->results[0]->formatted_address;
        } else {
            return false;
        }
    }


    public function getCoordinates($textAddress) {
        // get and decode json
        $requestParams = http_build_query([
            'key' => $this->getApiKey(),
            'address' => $textAddress,
            'language' => 'ru-RU'
        ]);

        // specify service and output format
        $service = 'geocode';
        $format = 'json';

        // make request
        $response = json_decode(file_get_contents(self::MAPS_API_URL . "$service/$format?" . $requestParams));
        
        // get latitude and longitude
        $loc = $response->results[0]->geometry->location;
        $lat = $loc->lat;
        $lng = $loc->lng;

        // return array of lat and lng
        return ['latitude' => $lat, 'longitude' => $lng];
    }


    public function getWalkingParams($coordsFrom, $coordsTo) {
        // specify request parameters
        // https://maps.googleapis.com/maps/api/
        $requestParams = http_build_query([
            'key' => self::MAPS_API_KEY,
            'origins' => implode(',', $coordsFrom),
            'destinations' => implode(',', $coordsTo),
            'mode' => 'walking',
            'language' => 'ru-RU',
            'sensor' => false
        ]);

        // specify service and format
        $service = 'distancematrix';
        $format = 'json';

        // make request
        $response = json_decode(file_get_contents(self::MAPS_API_URL . "$service/$format?" . $requestParams));

        // get kms and time in hrs:mins from response object
        $element = $response->rows[0]->elements[0];
        $distance = $element->distance->text;
        $time = $element->duration->text;

        // return distance and time
        return ['distance' => $distance, 'time' => $time];
    }

}

// $map = new MapsApi();
// echo var_dump($map->getCoordinates('Полюстровский проспект 84 спб'));