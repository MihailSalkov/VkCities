<?php

class Save {
    const SAVE_PATH = '../dist/with_regions';

    static function saveCountries($countries) {
        self::saveFile('countries.json', $countries);
    }

    static function saveCountry($country_id, $data) {
        self::saveFile("countries/{$country_id}/country.json", $data, true);
    }

    static function saveRegion($country_id, $region_id, $data) {
        self::saveFile("countries/{$country_id}/regions/{$region_id}.json", $data);
    }

    static private function prepareData($data) {
        $result = [];

        foreach ($data as $name => $value) {
            if (is_array($value)) {
                $value = [
                    'count' => count($value),
                    'items' => $value,
                ];
            }

            $result[$name] = $value;
        }

        return $result;
    }

    static private function saveFile($path, $data, $prepare = false) {
        $path = self::SAVE_PATH . '/' . $path;
        self::createFolder(dirname($path));

        if ($prepare)
            $data = self::prepareData($data);

        return file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    static private function createFolder($path) {
        if (!file_exists($path))
            mkdir($path, 0777, true);
    }
}