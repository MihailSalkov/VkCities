<?php

class Load {
    const LANGS = [
        0 => 'ru',
        3 => 'en',
    ];
    const ACCESS_TOKEN = '';
    const VERSION = '5.124';
    const DEFAULT_LANG = 0;
    const LIMIT = 1000;
    static $queries_count = 0;

    static function getCountries() {
        $countries = [];

        foreach (self::LANGS as $lang_id => $lang_name) {
            $vk_countries = self::vk_method('database.getCountries', [
                'lang' => $lang_id,
            ]);

            foreach ($vk_countries as $country) {
                $country['id'] = intval($country['id']);
                $countries[$country['id']]['id'] = $country['id'];
                $countries[$country['id']]['title_' . $lang_name] = $country['title'];
            }
        }

        return $countries;
    }

    static function getRegions($country_id) {
        $regions = [];

        foreach (self::LANGS as $lang_id => $lang_name) {
            $regions_lang = self::vk_method('database.getRegions', [
                'country_id' => $country_id,
                'lang'       => $lang_id,
            ]);

            foreach ($regions_lang as $region) {
                $region['id'] = intval($region['id']);
                $regions[$region['id']]['id'] = $region['id'];
                $regions[$region['id']]['title_' . $lang_name] = $region['title'];
            }
        }

        return array_values($regions);
    }

    static function getCities($country_id, $region_id = 0) {
        $cities = [];

        $offset = 0;
        while (true) {
            $cities_append = self::getCitiesPart($country_id, $region_id, $offset);

            if (!count($cities_append))
                break;

            $cities = array_merge($cities, $cities_append);

            $offset += self::LIMIT;

            if ($offset % (self::LIMIT * 10) == 0) {
                echo "Loaded cities: {$offset}...\n";
            }
        }

        return $cities;
    }

    static private function getCitiesPart($country_id, $region_id = 0, $offset = 0) {
        $cities = self::vk_method('database.getCities', [
            'country_id' => $country_id,
            'region_id'  => $region_id,
            'offset'     => $offset,
        ]);

        $city_ids = [];
        foreach ($cities as $city) {
            $city_ids[] = $city['id'];
        }
        $city_ids = implode(',', $city_ids);

        $result = [];
        foreach (self::LANGS as $lang_id => $lang_name) {
            $cities_lang = self::vk_method('database.getCitiesById', [
                'city_ids' => $city_ids,
                'lang'     => $lang_id,
            ]);

            foreach ($cities_lang as $city) {
                $result[$city['id']]['id'] = $city['id'];
                $result[$city['id']]['title_' . $lang_name] = $city['title'];
            }
        }

        return $result;
    }

    static function vk_method($method, $params = []) {
        $common_params = [
            'v'            => self::VERSION,
            'lang'         => self::DEFAULT_LANG,
            'access_token' => self::ACCESS_TOKEN,
            'count'        => self::LIMIT,
            'need_all'     => 1,
        ];

        $params = array_merge($common_params, $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/' . $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = json_decode(curl_exec($ch), 1);

        curl_close($ch);

        self::$queries_count++;

        if (!isset($response['response'])) {
            exit(json_encode($response));
        }

        $response = $response['response'];

        return $response['items'] ?? $response;
    }
}