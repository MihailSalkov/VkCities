<?php

class VkCities {
    const ACCESS_TOKEN = '';
    const LANG = 0; // 0 - ru
    const VERSION = '5.102';
    const LIMIT = 1000;

    function __construct() {
        set_time_limit(0);

        $countries = $this->run();

        echo "Done.";
    }

    function run() {
        $vk_countries = self::query('database.getCountries');
        $all_countries = count($vk_countries);

        $countries = [];

        echo "Countries: {$all_countries}\n";

        foreach ($vk_countries as $country) {
            $i++;
            $cities = self::getCities($country['id']);

            $countries[] = [
                'id'    => $country['id'],
                'title' => $country['title'],
            ];

            self::saveCountry($country['id'], [
                'title'  => $country['title'],
                'cities' => $cities,
            ]);

            echo "({$i}/{$all_countries}) ";
            echo "Saved country #" . $country['id'] . "'" . $country['title'] . "' (" . count($cities) . " cities)\n";
        }

        self::saveCountries($countries);

        return $countries;
    }

    static function saveCountries($countries) {
        return file_put_contents(
            '../dist/countries.json',
            json_encode($countries, JSON_UNESCAPED_UNICODE)
        );
    }

    static function saveCountry($id, $country) {
        return file_put_contents(
            '../dist/countries/' . $id . '.json',
            json_encode($country, JSON_UNESCAPED_UNICODE)
        );
    }

    static function getCities($country_id) {
        $cities = [];

        $offset = 0;
        do {
            $cities_append = self::getCitiesPart($country_id, $offset);

            $cities = array_merge($cities, $cities_append);

            $offset += self::LIMIT;
        } while (count($cities) == $offset);

        return $cities;
    }

    static function getCitiesPart($country_id, $offset = 0) {
        $cities = self::query('database.getCities', [
            'country_id' => $country_id,
            'offset'     => $offset,
        ]);

        return array_map(function ($city) {
            return $city['title'];
        }, $cities);
    }

    static function query($method, $params = []) {
        $common_params = [
            'v'            => self::VERSION,
            'lang'         => self::LANG,
            'access_token' => self::ACCESS_TOKEN,
            'count'        => self::LIMIT,
            'need_all'     => 1,
        ];

        $params = array_merge($params, $common_params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/' . $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = json_decode(curl_exec($ch), 1);

        curl_close($ch);

        if (!isset($response['response'])) {
            exit(json_encode($response));
        }

        return $response['response']['items'];
    }
}

new VkCities();