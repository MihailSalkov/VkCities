<?php

class CitiesCheck {
    function __construct() {
        $cities_count = 0;

        $countries = self::getCountries();

        foreach ($countries as $country) {
            $cities_count += $country['cities_count'];
        }

        echo 'Countries: ' . count($countries) . '. Cities: ' . $cities_count;
    }

    static function getCountries() {
        $countries = json_decode(file_get_contents('../dist/countries.json'), 1);

        foreach ($countries as &$country) {
            $country_info = json_decode(file_get_contents('../dist/countries/' . $country['id'] . '.json'), 1);

            $country['cities_count'] = count($country_info['cities']);
        }

        return $countries;
    }
}

new CitiesCheck();