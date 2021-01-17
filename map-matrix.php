<?php
/**
 * Plugin Name: JebStores Map Distance Matrix 0.1.0
 */
const               __THIS_PLUGIN_NAME__  = 'JebStores Geolocation Map & Distance Matrix'        ;
const                        __VERSION__  = '0.1.0'                                              ;
const    __JEBSTORES_DEFAULT_REFERENCE__  = 'jebstores_default_reference_1'                      ;
const       __JEBSTORES_MAPBOX_API_URL__  = 'jebstores_mapbox_api_url'                           ;
const     __JEBSTORES_MAPBOX_API_LOGIN__  = 'jebstores_mapbox_api_login'                         ;
const     __JEBSTORES_MAPBOX_API_TOKEN__  = 'jebstores_mapbox_api_token'                         ;
const        __JEBSTORES_ACTIVE_CITIES__  = 'jeb_active_cities'                                  ;
const                __MAPBOX_BASE_URL__  = 'https://api.mapbox.com'                             ;
const          __MAPBOX_MATRIX_PROFILE__  = 'mapbox/driving-traffic'                             ;
const      __JEBSTORES_MAP_PANEL_TITLE__  = 'Set JebStores default point of reference:'          ;
const        __JEBSTORES_MAP_API_TITLE__  = 'Set MapBox API crendentials:'                       ;
const         __JEBSTORES_CITIES_PANEL__  = 'Set JebStores active cities:'                       ;
const __JEBSTORES_INITIAL_LONDON_POINT__  = '[0.1278, 51.5074]'                                  ;
const             __JEBSTORES_MAP_ZOOM__  = 7                                                    ;
const       __MAPBOX_GEOCODING_API_URL__  = '/geocoding/v5/mapbox.places/%s.json?access_token=%s';   
const         __MAPBOX_DRIVING_API_URL__  = '/directions-matrix/v1/mapbox/driving-traffic/'      ;
const __MAPBOX_DRIVING_API_QUERY_PARAMS__ = '%f,%f;%f,%f?sources=0&annotations=duration&destinations=1&fallback_speed=20&access_token=%s';   
const      __POSTCODES_IO_POSTCODE_URL__  = 'https://postcodes.io/postcodes/%s'                  ;
const       __POSTCODES_IO_LAT_LNG_URL__  = 'https://postcodes.io/postcodes?lon=%f&lat=%f'       ;
const                        __ENGLAND__  = 'england'                                             ;                    
const                         __LONDON__  = 'london'                                             ;                    


$__MAPBOX_API_TOKEN__  = get_option('jebstores_mapbox_api_token')             ;

require_once ABSPATH . 'wp-content/plugins/map-matrix/map-matrix-templates.php';
require_once ABSPATH . 'wp-content/plugins/map-matrix/map-matrix-scripts.php';
require_once ABSPATH . 'wp-content/plugins/map-matrix/map-matrix-functions.php';

?>

