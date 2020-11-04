<?php

require "lib/Load.php";
require "lib/Save.php";

new VkCities();

class VkCities {
    function __construct() {
        set_time_limit(0);

        echo 'Languages: ' . implode(', ', Load::LANGS) . "\n";

        $this->run();

        echo 'Queries count: ' . Load::$queries_count . "\n";

        echo "Done.";
    }

    function run() {
        $countries = Load::getCountries();
        $countries_count = count($countries);
        $regions_count = 0;
        $regions_count_all = 0;
        $cities_count_all = 0;
        $default_lang = Load::LANGS[Load::DEFAULT_LANG];

        $i = 0;

        echo "Countries: {$countries_count}\n";

        Save::saveCountries(array_values($countries));

        foreach ($countries as $country) {
            $i++;

            if ($country['id'] != 1) continue;

            echo "({$i}/{$countries_count} countries) ";
            echo "Loading country #{$country['id']}'" . $country['title_' . $default_lang] . "'...\n";

            $country['cities_count'] = 0;
            $country['regions'] = Load::getRegions($country['id']);
            $regions_count = count($country['regions']);
            $regions_count_all += $regions_count;

            $j = 0;
            foreach ($country['regions'] as $region) {
                $j++;

                echo "({$i}/{$countries_count} countries) ({$j}/{$regions_count} regions) ";
                echo "Loading region #{$region['id']} " . $region['title_' . $default_lang] . "... ";

                $region['cities'] = Load::getCities($country['id'], $region['id']);
                $country['cities_count'] += count($region['cities']);

                Save::saveRegion($country['id'], $region['id'], $region['cities']);

                echo "(Saved " . count($region['cities']) . " cities)\n";
            }

            Save::saveCountry($country['id'], $country);

            $cities_count_all += $country['cities_count'];

            echo "Saved " . count($country['regions']) . " regions\n";
        }

        echo "\nCountries: {$countries_count}. Regions: {$regions_count_all}. Cities: {$cities_count_all}.\n";

        return $countries;
    }


}