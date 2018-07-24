<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

define('ISTAT_DATA_FILE', __DIR__ . '/data/cities.csv'); // "CODICI STATISTICI DELLE UNITÀ AMMINISTRATIVE TERRITORIALI: COMUNI, CITTÀ METROPOLITANE, PROVINCE E REGIONI" from Istat, see https://www.istat.it/it/archivio/6789
define('COMUNIJSON_DATA_FILE', __DIR__ . '/data/comuni-json-2018-03-31/comuni.json'); // https://github.com/matteocontrini/comuni-json/
define('VALID_TOKEN', 'BN78FGH'); // this is not for security, just so we can more easily disable the API if there is ever abused

try {
    if (!isset($_GET['access_token'])) {
        throw new \Exception('access token missing');
    }
    if ($_GET['access_token'] != VALID_TOKEN) {
        throw new \Exception('invalid access token');
    }

    $cities_csv = file_get_contents(ISTAT_DATA_FILE);

    /* 
    Fields we're currently interested in:
    ---
    5 Denominazione in italiano
    6 Denominazione in tedesco
    12 Flag Comune capoluogo di provincia    
    13 Sigla automobilistica
    18 Codice Catastale del comune
    19 Popolazione legale 2011 (09/10/2011)
    22 Codice NUTS3 2010

    Everything:
    ---
    0 Codice Regione
    1 Codice Città Metropolitana
    2 Codice Provincia (1)
    3 Progressivo del Comune (2)
    4 Codice Comune formato alfanumerico
    5 Denominazione in italiano
    6 Denominazione in tedesco
    7 Codice Ripartizione Geografica
    8 Ripartizione geografica
    9 Denominazione regione
    10 Denominazione Città metropolitana
    11 Denominazione provincia
    12 Flag Comune capoluogo di provincia
    13 Sigla automobilistica
    14 Codice Comune formato numerico
    15 Codice Comune numerico con 110 province (dal 2010 al 2016)
    16 Codice Comune numerico con 107 province (dal 2006 al 2009)
    17 Codice Comune numerico con 103 province (dal 1995 al 2005)
    18 Codice Catastale del comune
    19 Popolazione legale 2011 (09/10/2011)
    20 Codice NUTS1 2010
    21 Codice NUTS2 2010 (3) 
    22 Codice NUTS3 2010
    23 Codice NUTS1 2006
    24 Codice NUTS2 2006 (3)
    25 Codice NUTS3 2006
    */

    $_CACHE = [];

    $lines = explode("\n", trim($cities_csv, "\n"));

    for ($i=0; $i<count($lines); $i++) {
        if ($i == 0) {
            continue;
        }

        $line = $lines[$i];

        $fields = explode(';', $line);

        $is_province = (bool) $fields[12];
        $population = (int) str_replace(',', '', $fields[19]);
        $location_id = $is_province ? $fields[22] : $fields[22] . '@' . $fields[18];
        $_CACHE[$location_id] = [
            'id' => $location_id,
            'name' => $fields[5] . ($fields[6] ? '/' . $fields[6] : ''),

            'nuts_2010_code' => $fields[22],
            'cad_code' => $fields[18],
            'license_plate_code' =>$fields[13],

            'population' => $population,
            'is_province' => $is_province
        ];
    }

    function get_data($_CACHE, $data_type, $options=[]) {
        $data = [];
    
        switch ($data_type) {
            // case 'zones':
            //     // ITC NORD-OVEST
            //     // ITH NORD-EST
            //     // ITI CENTRO            
            //     // ITF SUD
            //     // ITG ISOLE
            //     // ITZ EXTRA-REGIO            
            //     break;
            case 'locations':
                $regions = get_data($_CACHE, 'regions', $options);
                $provinces = get_data($_CACHE, 'provinces', $options);
                $cities = get_data($_CACHE, 'cities', $options);

                $data = array_merge($regions, $provinces, $cities);
                break;
            case 'regions':
                $data = [
                    [
                        'id' => 'ITC1',
                        'type' => 'region',
                        'name' => 'Piemonte',
                        'nuts_2010_code' => ''
                    ],
                    [
                        'id' => 'ITC2',
                        'type' => 'region',
                        'name' => 'Valle d’Aosta/Vallée d’Aoste',
                        'nuts_2010_code' => 'ITC2'
                    ],
                    [
                        'id' => 'ITC3',
                        'type' => 'region',
                        'name' => 'Liguria',
                        'nuts_2010_code' => 'ITC3'
                    ],                                        
                    [
                        'id' => 'ITC4',
                        'type' => 'region',
                        'name' => 'Lombardia',
                        'nuts_2010_code' => 'ITC4'
                    ],
                    [
                        'id' => 'ITF1',
                        'type' => 'region',
                        'name' => 'Abruzzo',
                        'nuts_2010_code' => 'ITF1'
                    ],
                    [
                        'id' => 'ITF2',
                        'type' => 'region',
                        'name' => 'Molise',
                        'nuts_2010_code' => 'ITF2'
                    ],                                        
                    [
                        'id' => 'ITF3',
                        'type' => 'region',
                        'name' => 'Campania',
                        'nuts_2010_code' => 'ITF3'
                    ],
                    [
                        'id' => 'ITF4',
                        'type' => 'region',
                        'name' => 'Puglia',
                        'nuts_2010_code' => 'ITF4'
                    ],
                    [
                        'id' => 'ITF5',
                        'type' => 'region',
                        'name' => 'Basilicata',
                        'nuts_2010_code' => 'ITF5'
                    ],                                        
                    [
                        'id' => 'ITF6',
                        'type' => 'region',
                        'name' => 'Calabria',
                        'nuts_2010_code' => 'ITF6'
                    ],
                    [
                        'id' => 'ITG1',
                        'type' => 'region',
                        'name' => 'Sicilia',
                        'nuts_2010_code' => 'ITG1'
                    ],
                    [
                        'id' => 'ITG2',
                        'type' => 'region',
                        'name' => 'Sardegna',
                        'nuts_2010_code' => 'ITG2'
                    ],                                        
                    [
                        'id' => 'ITH1',
                        'type' => 'region',
                        'name' => 'Provincia Autonoma di Bolzano/Bozen',
                        'nuts_2010_code' => 'ITH1'
                    ],
                    [
                        'id' => 'ITH2',
                        'type' => 'region',
                        'name' => 'Provincia Autonoma di Trento',
                        'nuts_2010_code' => 'ITH2'
                    ],
                    [
                        'id' => 'ITH3',
                        'type' => 'region',
                        'name' => 'Veneto',
                        'nuts_2010_code' => 'ITH3'
                    ],                                        
                    [
                        'id' => 'ITH4',
                        'type' => 'region',
                        'name' => 'Friuli-Venezia Giulia',
                        'nuts_2010_code' => 'ITH4'
                    ],
                    [
                        'id' => 'ITH5',
                        'type' => 'region',
                        'name' => 'Emilia-Romagna',
                        'nuts_2010_code' => 'ITH5'
                    ],
                    [
                        'id' => 'ITI1',
                        'type' => 'region',
                        'name' => 'Toscana',
                        'nuts_2010_code' => 'ITI1'
                    ],                                        
                    [
                        'id' => 'ITI2',
                        'type' => 'region',
                        'name' => 'Umbria',
                        'nuts_2010_code' => 'ITI2'
                    ],
                    [
                        'id' => 'ITI3',
                        'type' => 'region',
                        'name' => 'Marche',
                        'nuts_2010_code' => 'ITI3'
                    ],
                    [
                        'id' => 'ITI4',
                        'type' => 'region',
                        'name' => 'Lazio',
                        'nuts_2010_code' => 'ITI4'
                    ]
                ];
                break;
            case 'provinces':
                foreach ($_CACHE as $city_data) {
                    if ($city_data['is_province']) {
                        $city_data['type'] = 'province';
                        $data[] = $city_data;
                    }
                }            
                break;                
            case 'cities':
                foreach ($_CACHE as $city_data) {
                    $city_data['type'] = 'city';
                    $data[] = $city_data;
                }            
                break;            
            default:   
                throw new \Exception('cannot handle route');
        }

        // transform in ID-based array
        $data_tmp = [];
        foreach ($data as $k=>$v) {
            if (!$data[$k]['id']) {
                throw new Exception('location does not have ID');
            }

            $data_tmp[$data[$k]['id']] = $v;
        }
        $data = $data_tmp;

        // filter data
        if (is_array($options) && count($options)) {
            foreach ($data as $location_id=>$location) {
                foreach (['country', 'region', 'province'] as $option_name) {
                    if (isset($options[$option_name]) && $options[$option_name]) {
                        if (strpos(strtoupper($location['nuts_2010_code']), strtoupper($options[$option_name])) !== 0) {
                            unset($data[$location_id]);
                        }
                    }
                }
            }
        }

        // sort data
        usort($data, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $data;
    }

    // set routes
    route('GET', '/api/v1/locations', function($_CACHE) {
        switch ($_GET['type']) {
            case 'region':
                $data = get_data($_CACHE, 'regions', [
                    'country' => 'IT',
                    'region' => isset($_GET['region']) ? $_GET['region'] : '',
                    'province' => isset($_GET['province']) ? $_GET['province'] : ''
                ]);
                break;
            case 'province':
                $data = get_data($_CACHE, 'provinces', [
                    'country' => 'IT',
                    'region' => isset($_GET['region']) ? $_GET['region'] : '',
                    'province' => isset($_GET['province']) ? $_GET['province'] : ''
                ]);
                break;
            case 'city':
                $data = get_data($_CACHE, 'cities', [
                    'country' => 'IT',
                    'region' => isset($_GET['region']) ? $_GET['region'] : '',
                    'province' => isset($_GET['province']) ? $_GET['province'] : ''
                ]);
                break;
            default:
                $data = get_data($_CACHE, 'locations', [
                    'country' => 'IT',
                    'region' => isset($_GET['region']) ? $_GET['region'] : '',
                    'province' => isset($_GET['province']) ? $_GET['province'] : ''
                ]);
        }
    });

    route('GET', '/api/v1/postcodes/{postcode}', function($_CACHE) {
        throw new \Exception('not implemented');

        return response(json_encode([
            'count' => count($data),
            'locations' => $data
        ]), 200, ['content-type' => 'application/json']);
    });        

    dispatch($_CACHE, null);
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
?>