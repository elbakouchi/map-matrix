<?php

use function PHPSTORM_META\type;

/**
 * Jebstores Extended Woocommerce Product
 * 
 */


function jebstores_wc_add_postcodes_to_wc_product()
{
  $args = array(
    'label' => __('Postcodes list', 'woocommerce'),
    'placeholder' => __('Enter a comma separated list of postcodes', 'woocommerce'),
    'id' => __JEBSTORES_PRODUCT_POSTCODES__,
    'desc_tip' => true,
    'description' => __('This product can only be delivered to these postcodes.', 'woocommerce'),
    'placeholder' => get_option(__JEBSTORES_DELIVERABLE_POSTCODES__)
  );
  woocommerce_wp_textarea_input($args);
}


function jebstores_wc_save_postcodes_to_wc_product($post_id)
{
  // grab the custom SKU from $_POST
  $postcodes = isset($_POST[__JEBSTORES_PRODUCT_POSTCODES__]) ? sanitize_textarea_field($_POST[__JEBSTORES_PRODUCT_POSTCODES__]) : '';

  // grab the product
  $product = wc_get_product($post_id);

  // save the custom SKU using WooCommerce built-in functions
  $product->update_meta_data(__JEBSTORES_PRODUCT_POSTCODES__, $postcodes);
  $product->save();
}


/**
 * Actions Callbacks
 */


$mapbox_api_credentials = array();

function css_import()
{
  echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">';
  echo '<link href="https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.css" rel="stylesheet" />';
}

function js_import()
{
  echo '<script src="https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.js"></script>';
}

function scripts_import()
{
  global $persistApiScript, $persistActiveCitiesScript, $persistDefaultReferenceScript, $persistPostCodes;
  echo   $persistApiScript, $persistActiveCitiesScript, $persistDefaultReferenceScript, $persistPostCodes;
}


function geo_map_distance_matrix_menu()
{
  $page_title = 'Geolocation Map & Distance Matrix';
  $menu_title = 'GeoMap Distance Matrix';
  $capability = 'manage_options';
  $menu_slug  = 'geo-map-distance-matrix';
  $function   = 'geo_map_distance_matrix_page';
  $icon_url   = 'dashicons-admin-site-alt';
  $position   = 39;
  add_menu_page(
    $page_title,
    $menu_title,
    $capability,
    $menu_slug,
    $function,
    $icon_url,
    $position
  );
}


function geo_map_distance_matrix_page($e)
{
  $login = get_option(__JEBSTORES_MAPBOX_API_LOGIN__);
  $token = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
  $url   = get_option(__JEBSTORES_MAPBOX_API_URL__);
  $postcodes   = get_option(__JEBSTORES_DELIVERABLE_POSTCODES__);

  global $mapBox, $mapPanel, $apiFormBox, $listOfDelieverablePostcodesForm; // , $activateCitiesForm; 

  $filledApiFormBox = sprintf($apiFormBox, $url, $login, $token);
  $filledPostcodesForm = sprintf($listOfDelieverablePostcodesForm, $postcodes);


  $map   = sprintf($mapPanel, 'is-primary', '', __JEBSTORES_MAP_PANEL_TITLE__, 'is-full', $mapBox);
  $form1 = sprintf($mapPanel, 'is-info',    '', __JEBSTORES_MAP_API_TITLE__,   'is-full', $filledApiFormBox);
  $form2 = null; //sprintf($mapPanel, 'is-warning', '', __JEBSTORES_POSTCODES_PANEL__, 'is-full' , $persistPostCodes );
  $form3 = sprintf($mapPanel, 'is-warning', '', __JEBSTORES_POSTCODES_PANEL__, 'is-full', $filledPostcodesForm);

  template($map, $form1, $form2, $form3);
}

function map_box_script()
{
  global $mapBoxScript;
  $token    = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
  $position = json_decode(get_option(__JEBSTORES_DEFAULT_REFERENCE__));
  $filledMapBoxScript = sprintf(
    $mapBoxScript,
    $token,
    __JEBSTORES_INITIAL_LONDON_POINT__,
    __JEBSTORES_MAP_ZOOM__,
    $position->lat,
    $position->lng
  );
  echo $filledMapBoxScript;
}

// function get_mapbox_api_credentialst(){
//   global $mapbox_api_credentials;
// }

/**
 * Helpers
 */

function jebstores_wc_register_widgets()
{
  register_widget('Jebstores_WC_Widget_Product_Postcode_Filter');
}

function template($map, $form1, $form2 = null, $form3)
{
  echo
    '<div class="wrap has-background-white">'
      . '<h1 class="wp-heading-inline">' . __THIS_PLUGIN_NAME__ . '</h1><h2><subtitle>' . __VERSION__ . '</subtitle></h2>'
      //. '<hr>' 
      . '<div class="section">'
      . '<div class="container">'
      . '<div class="columns">'
      . '<div class="column is-third">'
      . $map
      . '</div>'
      . '<div class="column is-third">'
      . $form1
      . '</div>'
      . '</div>'
      . '</div>'
      . '</div>'
      //  . '</div>'
      // . '<div class="clear"></div>'
      . '<div class="section">'
      . '<div class="container">'
      . '<div class="columns">'
      . '<div class="column is-third">'
      . $form3
      . '</div>'
      . '</div>'
      . '</div>'
      . '</div>'
      . '</div>'
    //  . '<div class="clear"></div>';
  ;
}

function get_bulk_driving_distance_and_duration($mapped_coords)
{
  global $__MAPBOX_API_TOKEN__;
  global $__JEBSTORES_LONDON_REFERENCE__;
  if (count($mapped_coords)) {
    $destinations = implode(';', $mapped_coords);
    $dump = implode(',', range(1, count($mapped_coords)));
    $mapbox_url = __MAPBOX_BASE_URL__
      . __MAPBOX_DRIVING_API_URL__
      . sprintf(
        __MAPBOX_DRIVING_API_BULK_QUERY__,
        $__JEBSTORES_LONDON_REFERENCE__->lng,
        $__JEBSTORES_LONDON_REFERENCE__->lat,
        $destinations,
        $dump,
        $__MAPBOX_API_TOKEN__
      );
    $mapbox_request = new WP_Http();
    $mapbox_response = $mapbox_request->request($mapbox_url); //echo $mapbox_url;//var_dump($mapbox_response);

    if ($mapbox_response['response']['code'] == 200) {
      return rest_ensure_response(json_decode($mapbox_response['body']));
    } elseif ($mapbox_response['response']['code'] == 'InvalidInput') {
      $error = new WP_Error(400, $mapbox_response['response']['message']);
      return rest_ensure_response($error);
    } else {
      $error = new WP_Error(400, 'Error getting postcode geocoding information from service.');
      return rest_ensure_response($error);
    }
  } else {
    $error = new WP_Error(400, 'No postcode provided');
    return rest_ensure_response($error);
  }
}

function get_driving_distance_and_duration($longitude, $latitude, $raw = false)
{
  global $__MAPBOX_API_TOKEN__;
  global $__JEBSTORES_LONDON_REFERENCE__;
  if (is_float($longitude) && is_float($latitude)) {
    $mapbox_url = __MAPBOX_BASE_URL__
      . __MAPBOX_DRIVING_API_URL__
      . sprintf(
        __MAPBOX_DRIVING_API_QUERY_PARAMS__,
        $__JEBSTORES_LONDON_REFERENCE__->lng,
        $__JEBSTORES_LONDON_REFERENCE__->lat,
        $longitude,
        $latitude,
        $__MAPBOX_API_TOKEN__
      );
    error_log($mapbox_url);
    $mapbox_request  = new WP_Http();
    $mapbox_response = $mapbox_request->request($mapbox_url);
    if (!is_array($mapbox_response))
      $mapbox_response = (object) json_decode($mapbox_response);
    // print_r($mapbox_response);
    if ($mapbox_response->response->code == 200) {
      if ($raw) return $mapbox_response->body;
      return rest_ensure_response(json_decode($mapbox_response['body']));
    } else {
      $error = new WP_Error(400, 'Error getting postcode geocoding information from service.');
      return rest_ensure_response($error);
    }
  } else {
    $error = new WP_Error(400, 'No postcode provided');
    return rest_ensure_response($error);
  }
}

function map_coords($a, $b)
{
  return $a . ',' . $b;
}

/**
 * Rest APIs & Controllers
 */

function fetch_postcodes_io_lat_lng_info($request)
{
  if ($request->has_param('lat') && $request->has_param('lng')) {
    $postcode_io_url = sprintf(__POSTCODES_IO_LAT_LNG_URL__, $request->get_param('lat'), $request->get_param('lng'));
    // echo $postcode_io_url;
    $postcode_io_request = new WP_Http();
    $postcode_io_response = $postcode_io_request->request($postcode_io_url);
    ////($postcode_io_response);
    if ($postcode_io_response['response']['code'] == 200) {
      $response = json_decode($postcode_io_response['body']);
      return rest_ensure_response($response->result);
      // return rest_ensure_response(json_decode($postcode_io_response['body']));
    } else {
      $error = new WP_Error(400, 'Error getting postcode geocoding information from service.', $request->get_param('postcode'));
      return rest_ensure_response($error);
    }
  } else {
    $error = new WP_Error(400, 'No postcode provided', $request->get_param('postcode'));
    return rest_ensure_response($error);
  }
}

function postcode_valid($postcode)
{
  return preg_match('/^[A-Z]{1,2}[0-9]{1,2}[A-Z]? [0-9][A-Z]{2}$/', $postcode);
}


function get_postcode_info_from_postcodes_io($postcode)
{
  $postcode_io_url      =  sprintf(__POSTCODES_IO_POSTCODE_URL__, $postcode);
  $postcode_io_request  =  new WP_Http();
  $postcode_io_response =  $postcode_io_request->request($postcode_io_url);

  if (!is_array($postcode_io_response)) {
    $postcode_io_response_body = json_decode($postcode_io_response);
    error_log(1);
  } elseif (is_object($postcode_io_response) && property_exists($postcode_io_response, 'body')) {
    $postcode_io_response_body = $postcode_io_response->body;
    error_log(2);
  } elseif (is_array($postcode_io_response)) {
    $postcode_io_response_body = (object) $postcode_io_response;
    error_log(3);
  }

  if (property_exists($postcode_io_response_body, 'body')) {
    $body = (object) $postcode_io_response_body->body;
    if ($body->status == 200) {

      error_log($body->result->longitude);
      error_log($body->result->latitude);
      return get_driving_distance_and_duration(
        floatval($body->result->longitude),
        floatval($body->result->latitude),
        true
      );
    }
  } elseif (property_exists($postcode_io_response_body, 'scalar') && $postcode_io_response_body->scalar->status == 200) {
    error_log($postcode_io_response_body->scalar->result->longitude);
    error_log($postcode_io_response_body->scalar->result->latitude);
    return get_driving_distance_and_duration(
      floatval($postcode_io_response_body->scalar->result->longitude),
      floatval($postcode_io_response_body->scalar->result->latitude),
      true
    );
  } else {
    return json_encode([
      is_array($postcode_io_response_body),
      is_object($postcode_io_response_body),
      property_exists($postcode_io_response_body, 'scalar'),
      property_exists($postcode_io_response_body, 'body'), $postcode_io_response_body
    ]);
  }
}


function fetch_postcodes_io_postcode_info($request)
{
  if ($request->has_param('postcode') && postcode_valid($request->get_param('postcode'))) {
    $postcode_io_url = sprintf(__POSTCODES_IO_POSTCODE_URL__, $request->get_param('postcode'));
    $postcode_io_request = new WP_Http();
    $postcode_io_response = $postcode_io_request->request($postcode_io_url);
    if ($postcode_io_response['response']['code'] == 200) {
      $response = json_decode($postcode_io_response['body']);
      return rest_ensure_response([$response->result,]);
      //return rest_ensure_response($postcode_io_response);
    } else {
      $error = new WP_Error(400, 'Error getting postcode geocoding information from service.', $request->get_param('postcode'));
      return rest_ensure_response($error);
    }
  } else {
    $error = new WP_Error(400, 'No postcode provided', $request->get_param('postcode'));
    return rest_ensure_response($error);
  }
}

function get_geo_info_from_postcode($request)
{
  $geocoding_info = fetch_postcodes_io_postcode_info($request); //  //($geocoding_info);
  return prepare_geo_info($geocoding_info);
}

function get_geo_info_from_lat_lng($request)
{
  $geocoding_info = fetch_postcodes_io_lat_lng_info($request);
  return prepare_geo_info($geocoding_info);
}

function prepare_geo_info($geocoding_info)
{
  if ($geocoding_info->status == 200 && !is_null($geocoding_info->data)) {
    $geo_info   = array();

    // $postcodes  = array_column($geocoding_info->data, 'postcode');
    $longitudes = array_column($geocoding_info->data, 'longitude');
    $latitudes  =  array_column($geocoding_info->data, 'latitude');
    $mapped_coords = array_unique(array_map('map_coords', $longitudes, $latitudes));
    //var_dump($mapped_coords);   
    //$destinations = implode(';', $mapped_coords);
    $driving = get_bulk_driving_distance_and_duration($mapped_coords);
    foreach ($geocoding_info->data as $key => $result) {
      $country   = $result->country;
      $city      = $result->nhs_ha;
      $latitude  = $result->latitude;
      $longitude = $result->longitude;
      $postcode  = $result->postcode;
      if (strtolower($country) == __ENGLAND__  && in_array(strtolower($city),  __CITIES__)) {
        // $driving   = get_driving_distance_and_duration($longitude, $latitude);
        if (get_class($driving) == 'WP_REST_Response'  && $driving->status == 200) {
          $geo_info[] = (object) array(
            'country' => $country,
            'city' => $city,
            'postcode' => $postcode,
            'lat' => $latitude,
            'lng' => $longitude,
            'duration' => gmdate("H:i:s", round($driving->data->durations[0][0])),
            'distance' => round($driving->data->destinations[0]->distance),
            'name' => $driving->data->destinations[0]->name
          );
        }
      }
    }
    return $geo_info;
  } else {
    $error = new WP_Error(400, 'No results returned');
    return rest_ensure_response($error);
  }
}
function show_driving_time()
{
}

function autocomplete_postcode()
{
}
function format_eta($seconds)
{
  $t = round($seconds);
  if ($t > 3600) {
    return sprintf('%02d h:%02dmin.', ($t / 3600), ($t / 60 % 60));
  } else {
    return sprintf('%02d min.', $t / 60 % 60);
  }
}

function add_admin_scripts($hook)
{
  if (strpos($hook, 'geo-map-distance-matrix')) {
    print($hook);
    wp_register_style('bulma_css_framework', 'https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css');
    wp_register_style('mapbox_css', 'https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.css');
    wp_register_script('mapbox_js', 'https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.js');
    wp_enqueue_style('bulma_css_framework');
    wp_enqueue_style('mapbox_css');
    wp_enqueue_script('mapbox_js');
    add_action('admin_footer', 'scripts_import');
    add_action('admin_footer', 'map_box_script');
  }
}

add_action('admin_enqueue_scripts', 'add_admin_scripts', 10, 1);

function jebstores_scripts_enqueuer()
{
  global $__MAPBOX_API_TOKEN__;
  global $__JEBSTORES_LONDON_REFERENCE__;

  wp_register_style('autocomplete_postcode_style', 'https://cdnjs.cloudflare.com/ajax/libs/tarekraafat-autocomplete.js/8.3.2/css/autoComplete.min.css');
  wp_register_script('check_postcode_script', WP_PLUGIN_URL . '/jebstores-product-map-matrix/check_postcode_script.js', array('jquery'));
  //wp_register_script('autocomplete_postcode_lib','https://cdnjs.cloudflare.com/ajax/libs/tarekraafat-autocomplete.js/8.3.2/js/autoComplete.js');
  wp_register_script('autocomplete_postcode_script', WP_PLUGIN_URL . '/jebstores-product-map-matrix/autocomplete_postcode_script.js', array('jquery'));
  wp_register_script('save_user_driving_matrix_script', WP_PLUGIN_URL . '/jebstores-product-map-matrix/save_user_driving_matrix.js', array('jquery'));
  wp_register_script('edit_postcode_script', WP_PLUGIN_URL . '/jebstores-product-map-matrix/edit_postcode_script.js', array('jquery'));
  wp_register_script('check_if_postcode_in_session_script', WP_PLUGIN_URL . '/jebstores-product-map-matrix/check_if_postcode_in_session_script.js', array('jquery'));

  wp_localize_script('check_postcode_script', 'jebStoresAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_localize_script('save_user_driving_matrix_script', 'jebStoresAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
  wp_localize_script('autocomplete_postcode_script', 'jebStoresPostcodes', array('postcodes' => explode(',', get_option(__JEBSTORES_DELIVERABLE_POSTCODES__))));
  wp_localize_script('autocomplete_postcode_script', 'jebStoresCoords', array('latitude' => $__JEBSTORES_LONDON_REFERENCE__->lat, 'longitude' => $__JEBSTORES_LONDON_REFERENCE__->lng));
  wp_localize_script('autocomplete_postcode_script', 'jebStoresMapBox', array('token' => $__MAPBOX_API_TOKEN__));
  wp_localize_script('check_if_postcode_in_session_script', 'jebStores', array('checkPostcodeAjaxUrl' => admin_url('admin-ajax.php?action=check_if_postcode_in_session')));

  wp_enqueue_style('autocomplete_postcode_style');
  wp_enqueue_script('jquery');
  wp_enqueue_script('check_postcode_script');
  wp_enqueue_script('save_user_driving_matrix_script');
  // wp_enqueue_script('autocomplete_postcode_lib');
  wp_enqueue_script('autocomplete_postcode_script');
  wp_enqueue_script('edit_postcode_script');
  wp_enqueue_script('check_if_postcode_in_session_script');
}

function fetch_postcode_geocoding_info($request)
{
  global $__MAPBOX_API_TOKEN__;
  if ($request->has_param('postcode') && postcode_valid($request->get_param('postcode'))) {
    $mapbox_url = __MAPBOX_BASE_URL__ . sprintf(__MAPBOX_GEOCODING_API_URL__, $request->get_param('postcode'), $__MAPBOX_API_TOKEN__);
    $mapbox_request = new WP_Http();
    $mapbox_response = $mapbox_request->request($mapbox_url);
    if ($mapbox_response['response']['code'] == 200) {
      return rest_ensure_response(json_decode($mapbox_response['body']));
    } else {
      $error = new WP_Error(400, 'Error getting postcode geocoding information from service.', $request->get_param('postcode'));
      return rest_ensure_response($error);
    }
  } else {
    $error = new WP_Error(400, 'No postcode provided');
    return rest_ensure_response($error);
  }
}

function set_active_cities($request)
{
  $dump = get_option(__JEBSTORES_MAPBOX_API_URL__);
  if ($dump) {
    update_option(__JEBSTORES_MAPBOX_API_URL__, $request['api_url']);
  } elseif ($dump != $request['api_url'] && !empty($dump)) {
    add_option(__JEBSTORES_MAPBOX_API_URL__, $request['api_url']);
  }
}

function get_driving_time($request)
{
  $longitude = floatval($request->get_param('lng'));
  $latitude = floatval($request->get_param('lat'));

  $driving   = get_driving_distance_and_duration($longitude, $latitude, true);

  if ($driving->status == 200 && $driving->data->code == 'Ok') {
    $matrix = (object) array(
      'duration' => gmdate("H:i:s", round($driving->data->durations[0][0])),
      'distance' => round($driving->data->destinations[0]->distance),
      'name' => $driving->data->destinations[0]->name
    );
    return $matrix;
  }

  return $driving;
}

function filter_products_by_postcode($query)
{
  // Check this is main query and other conditionals as needed
  if ($query->is_main_query()) {
    $query->set(
      'meta_query',
      array(
        array(
          'key' => __JEBSTORES_PRODUCT_POSTCODES__,
          'value' => $_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__],
          'compare' => 'LIKE'
        )
      )
    );
  }
}


function check_postcode()
{
  // global $wp_query;

  $postcode = $_REQUEST['postcode'];
  if (!empty($postcode)) { // && postcode_valid($postcode)) {
    $postcode_parts = explode(' ', $postcode);
    $part1 = (string) $postcode_parts[0];
    if (in_array($part1, $_SESSION['postcodes']) !== false) {
      $_SESSION[__JEBSTORES_USER_POSTCODE__] = $postcode;
      $_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__] = $part1;
      error_log('Good postcode detected: ' . $_SESSION[__JEBSTORES_USER_POSTCODE__] . ' ' . $_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__]);
      $geo_info = get_postcode_info_from_postcodes_io($postcode);
      $result = (object)  array('status' => 'Ok', 'redirect_url' => '/?post_type=product', 'geo_info' => $geo_info);
    } else {
      $result = (object) array('status' => 'Ko');
    }
  } else {
    $result = (object)  array('status' => 'No');
  }
  $result = json_encode($result);
  header('Content-Type', 'application/json');
  echo $result;
  die();
}

function save_user_driving_matrix()
{
  $postcode          = $_REQUEST['postcode'];
  $driving_duration  = $_REQUEST['duration'];
  $driving_address   = $_REQUEST['address'];
  $driving_distance  = $_REQUEST['distance'];
  $driving_latitude  = $_REQUEST['latitude'];
  $driving_longitude = $_REQUEST['longitude'];
  if (!empty($postcode)) {
    $postcode_parts = explode(' ', $postcode);
    $part1          = (string) $postcode_parts[0];
    $_SESSION[__JEBSTORES_USER_POSTCODE__]      = $postcode;
    $_SESSION[__JEBSTORES_USER_DISTANCE__]      = $driving_distance;
    $_SESSION[__JEBSTORES_USER_ADDRESS__]      = $driving_address;
    $_SESSION[__JEBSTORES_USER_DURATION__]      = $driving_duration;
    $_SESSION[__JEBSTORES_USER_LATITUDE__]       = $driving_latitude;
    $_SESSION[__JEBSTORES_USER_LONGITUDE__]       = $driving_longitude;
    $_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__] = $part1;
  }
  error_log($_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__]);
  error_log($_SESSION[__JEBSTORES_USER_POSTCODE__]);
  error_log($_SESSION[__JEBSTORES_USER_DISTANCE__]);
  error_log($_SESSION[__JEBSTORES_USER_ADDRESS__]);
  error_log($_SESSION[__JEBSTORES_USER_DURATION__]);
  error_log($_SESSION[__JEBSTORES_USER_LATITUDE__]);
  error_log($_SESSION[__JEBSTORES_USER_LONGITUDE__]);
  return json_encode(['status' => 1]);
  die();
}

function check_if_postcode_in_session()
{
  $exists = (in_array(__JEBSTORES_USER_POSTCODE__, $_SESSION) && in_array(__JEBSTORES_USER_POSTCODE_ROOT__, $_SESSION));
  wp_send_json(['result' => $exists]);
  wp_die();
}


function set_mapbox_api_credentials($request)
{

  $token = $request->get_param('api_token');
  //echo $token;
  if (!empty($token)) {
    $dump = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
    if ($dump != $token) {
      update_option(__JEBSTORES_MAPBOX_API_TOKEN__, $token);
      return rest_ensure_response(['status' => 'success', 'message' => 'Token updated.']);
    } else {
      add_option(__JEBSTORES_MAPBOX_API_TOKEN__, $token);
      return rest_ensure_response(['status' => 'success', 'message' => 'Token saved.']);
    }
    $error = new WP_Error(400, 'Nothing done', $token);
    return rest_ensure_response($error);
  } else {
    $error = new WP_Error(400, 'No token provided', $token);
    return rest_ensure_response($error);
  }
}

function warn_customer_about_deliverability()
{
  global $product;
  $id                = $product->get_id();
  $product_postcodes = get_post_meta($id, __JEBSTORES_PRODUCT_POSTCODES__);
  $product_postcodes = explode(',', $product_postcodes[0]);
  if (is_product() && !in_array($_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__], $product_postcodes)) {
    wc_add_notice(__('This product is not available for the given postcode…', 'woocommerce'), 'error');
  }
}

function filter_woocommerce_add_to_cart_validation($passed, $product_id, $quantity)
{
  $product_postcodes = get_post_meta($product_id, __JEBSTORES_PRODUCT_POSTCODES__);
  $product_postcodes = explode(',', $product_postcodes[0]);
  if (!in_array($_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__], $product_postcodes)) {
    wc_add_notice(__('Sorry!! This product can not be added to your cart…', 'woocommerce'), 'error');
    $passed = false;
  }
  return $passed;
};


function display_no_products_for_postcode()
{
  wc_get_template_html('<p class="woocommerce-info">' . esc_html_e('No products were found matching your postcode.', 'woocommerce') . '</p>');
}

function set_default_reference($request)
{
  $default_reference = get_option(__JEBSTORES_DEFAULT_REFERENCE__);
  if ($default_reference) {
    update_option(__JEBSTORES_DEFAULT_REFERENCE__, json_encode(
      array(
        'lat' => floatval($request['lat']),
        'lng' => floatval($request['lng'])
      )
    ));
  } else {
    add_option(__JEBSTORES_DEFAULT_REFERENCE__, json_encode(
      array(
        'lat' => floatval($request['lat']),
        'lng' => floatval($request['lng'])
      )
    ));
  }
  return rest_ensure_response(['lat' => $request['lat'], 'lng' => $request['lng'],]);
}

function set_deliverable_postcodes($request)
{
  $deliverable_postcodes = get_option(__JEBSTORES_DELIVERABLE_POSTCODES__);
  // var_dump($request);
  if ($deliverable_postcodes) {
    update_option(__JEBSTORES_DELIVERABLE_POSTCODES__, $request['postcodes']);
  } else {
    add_option(__JEBSTORES_DELIVERABLE_POSTCODES__, $request['postcodes']);
  }
  return rest_ensure_response(['message' => 'Success', 'postcodes' => $request['postcodes']]);
}

function get_deliverable_postcodes()
{
  if (!session_id()) {
    session_start();
    $deliverable_postcodes = get_option(__JEBSTORES_DELIVERABLE_POSTCODES__);
    if ($deliverable_postcodes) {
      if (!array_key_exists('postcode', $_SESSION)) {
        $_SESSION['postcodes'] = explode(',', $deliverable_postcodes);
        error_log('postcodes loaded');
      } else {
        error_log('postcodes already there!');
      }
    } else {
      error_log('no postcodes loaded');
    }
  }
}

function show_eta_from_postcode()
{
  $text_align = is_rtl() ? 'right' : 'left';
  wc_get_template_html('<div style="margin-bottom: 40px;">' .
    '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif;" border="1">' .
    '<thead>' .
    '<tr>' .
    '<th class="td" scope="col" style="text-align:' .
    esc_attr($text_align) .
    '">' .
    esc_html_e('Delivery', 'woocommerce') .
    '</th>' .
    '</tr>' .
    '</thead>' .
    esc_html_e('No products were found matching your postcode.', 'woocommerce') .
    '</p>');
}

function get_products_by_postcode($meta_query, $query)
{
  //print($_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__]);
  if (is_shop() || is_woocommerce() || is_search()) {
    $meta_query[] = array(
      'key'     => __JEBSTORES_PRODUCT_POSTCODES__,
      'value'   => $_SESSION[__JEBSTORES_USER_POSTCODE_ROOT__],
      'compare' => 'LIKE'
    );
    return $meta_query;
  }
}

function jebstores_maybe_remove_unsatisfied_terms(array $terms, $tax)
{
  global $wp_query, $cln_advanced_settings;

  if ('AND' === $cln_advanced_settings['filter_logic_operator']) {
    $counts = jebstores_get_filtering_terms_count(wp_list_pluck($terms, 'term_id'), $tax);
    foreach ($terms as $term_index => $term_object) {
      if ($cln_advanced_settings['filter_hide_term'] == 'NO') {
        if (isset($counts[$term_object->term_id])) {
          $terms[$term_index]->count = $counts[$term_object->term_id]; // Only count products in current filters.
        } else {;
          $terms[$term_index]->count = '0';
        }
      } else {
        if (isset($counts[$term_object->term_id])) {
          $terms[$term_index]->count = $counts[$term_object->term_id]; // Only count products in current filters.
        } else {
          unset($terms[$term_index]);
        }
      }
    }
  }

  return $terms;
}

function jebstores_get_filtering_terms_count($term_ids, $taxonomy)
{
  global $wpdb, $wp_query;

  $tax_query  = WC_Query::get_main_tax_query();
  $meta_query = WC_Query::get_main_meta_query();

  if (wp_doing_ajax()) {
    $tax_query = $wp_query->get('tax_query');
    $meta_query = $wp_query->get('meta_query');
  }

  $meta_query     = new WP_Meta_Query($meta_query);
  $tax_query      = new WP_Tax_Query($tax_query);
  $meta_query_sql = $meta_query->get_sql('post', $wpdb->posts, 'ID');
  $tax_query_sql  = $tax_query->get_sql($wpdb->posts, 'ID');

  // Generate query.
  $query           = [];
  $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
  $query['from']   = "FROM {$wpdb->posts}";
  $query['join']   = "
		INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
		INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
		INNER JOIN {$wpdb->terms} AS terms USING( term_id )
		" . $tax_query_sql['join'] . $meta_query_sql['join'];

  $query['where'] = "
		WHERE {$wpdb->posts}.post_type IN ( 'product' )
		AND {$wpdb->posts}.post_status = 'publish'"
    . $tax_query_sql['where'] . $meta_query_sql['where'] .
    'AND terms.term_id IN (' . implode(',', array_map('absint', $term_ids)) . ')';

  // $search = WC_Query::get_main_search_query_sql();
  //
  // if ($search) {
  //     $query['where'] .= ' AND ' . $search;
  // }

  $query['group_by'] = 'GROUP BY terms.term_id';
  $query = apply_filters('cln_get_filtering_term_count_query', $query);
  $query = implode(' ', $query);
  $query_hash = md5($query);
  $cached = (array)get_transient('cln_filtering_term_counts_' . $taxonomy);

  if (!isset($cached[$query_hash])) {
    $results = $wpdb->get_results($query, ARRAY_A);
    $counts = array_map('absint', wp_list_pluck($results, 'term_count', 'term_count_id'));
    $cached[$query_hash] = $counts;
    set_transient('cln_filtering_term_counts_' . $taxonomy, $cached, DAY_IN_SECONDS);
  }

  return $cached[$query_hash];
}

function jebstores_render_categories($parent_id, $categories, $selected_slugs, $content_data)
{
  $html = '';
  $loop_categories = array_filter($categories, function ($cats) use ($parent_id) {
    return $cats->parent == $parent_id;
  });
  if (count($loop_categories)) {
    //Slug on list config
    if (isset($content_data['category-slugs'])) {
      $visible_slugs = $content_data['category-slugs'];
    } else {
      $visible_slugs = array();
    }

    // if ($content_data['display-type'] == 'list') {
    $visible_class = '';
    if (!isset($content_data['list-show-child'])) {
      $hidden_child = 'style="display:none"';
    } else {
      $hidden_child = '';
      $visible_class = ' active';
    }
    // } else {
    //     $space = '';
    //     if ($parent_id != 0) {
    //         if (isset($content_data['space'])) {
    //             $space = '-' . $content_data['space'];
    //         } else {
    //             $space = $content_data['space'] = '- ';
    //         }
    //     }
    // }
    foreach ($loop_categories as $cat) {
      // if ($content_data['display-type'] == 'list') {
      $current_html = '';
      $child_html = jebstores_render_categories($cat->term_id, $categories, $selected_slugs, $content_data);
      if (in_array($cat->slug, $visible_slugs)) {
        $wrap_class = '';
        if (in_array($cat->slug, $selected_slugs)) {
          $checked = 'checked';
          $wrap_class .= ' selected';
        } else {
          $checked = '';
        }
        $current_html .= '<label class="zoo_ln_cat_filter_category_name">';
        $current_html .= '<input type="checkbox" class=""  value="' . $cat->slug . '" name="categories[]" ' . $checked . '/>';
        $current_html .= $cat->name;
        $current_html .= '</label>';
        if (isset($content_data['show-product-count']) && $content_data['show-product-count'] == 1) {
          $current_html .= '<span class="count">' . $cat->count . '</span>';
        }
        if (strlen($child_html)) {
          $current_html .= '<span class="zoo-ln-toggle-view' . $visible_class . '"><i class="cs-font clever-icon-down"></i></span><ul class="zoo-wrap-child-item" ' . $hidden_child . '>' . $child_html . '</ul>';
          $wrap_class .= ' zoo-filter-has-child';
        }
        $current_html = '<li class="zoo-filter-item' . $wrap_class . '">' . $current_html . '</li>';
      } else {
        if (strlen($child_html)) {
          $current_html .= $child_html;
        }
      }
      $html .= $current_html;
      // } else {
      //     if (in_array($cat->slug, $selected_slugs)) {
      //         $selected = 'selected';
      //     } else $selected = '';
      //     if (in_array($cat->slug, $visible_slugs)) {
      //         $html .= '<option value="' . $cat->slug . '" ' . $selected . '> ' . $space . $cat->name;
      //         if (isset($content_data['show-product-count']) && $content_data['show-product-count'] == 1) {
      //             $html .= ' (' . $cat->count . ')';
      //         }
      //         $html .= '</option>';
      //     }
      //     $html .= jebstores_render_categories($cat->term_id, $categories, $selected_slugs, $content_data);
      // }
    }
  } else {
    if (isset($content_data['space'])) {
      $content_data['space'] = '';
    }
  }
  return $html;
}


function sort_terms_hierarchically(array &$cats, array &$into, $parentId = 0)
{
  foreach ($cats as $i => $cat) {
    if ($cat->parent == $parentId) {
      $into[$cat->term_id] = $cat;
      unset($cats[$i]);
    }
  }

  foreach ($into as $topCat) {
    $topCat->children = array();
    sort_terms_hierarchically($cats, $topCat->children, $topCat->term_id);
  }
}

function jebstores_categories_menu()
{
  $cats = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false
  ]);


  $first_cat = array_shift($cats);
  $cats[] = $first_cat;



  $content_data = array();

  if (!empty($cats)) {

    foreach ($cats as $cat) {
      //echo $cat->parent . ' ' . $cat->term_id. ' ' . $cat->slug . '<br>';      
      if ($cat->parent == null) {
        $content_data[$cat->term_id] = (object) ['slug' => $cat->slug, 'name' => $cat->name, 'children' => array()];
      } else {
        $content_data[$cat->parent]->children[] = (object) ['slug' => $cat->slug, 'name' => $cat->name];
      }
    }
  }

  $html = '<ul id="categoriesmenu" class="nav header-nav header-bottom-nav nav-size-medium nav-spacing-medium nav-uppercase">';
  foreach ($content_data as $key => $value) {
    $html .= '<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children  menu-item-design-default has-dropdown"><a class="nav-top-link" href="/?post_type=product&categories='
      . $value->slug
      . '&orderby=popularity&order_type=ASC&relation=OR&hide_empty=YES&cln_do_filter=1">'
      . $value->name . '</a>';
    if (count($value->children)) {
      $html .= '<ul class="sub-menu nav-dropdown nav-dropdown-default">';

      foreach ($value->children as $v => $child) {
        $html .= '<li class="menu-item menu-item-type-post_type menu-item-object-page">'
          . '<a href="/?post_type=product&categories='
          . $child->slug
          . '&orderby=popularity&order_type=ASC&relation=OR&hide_empty=YES&cln_do_filter=1">'
          . $child->name
          . '</a>'
          . '</li>';
      }

      $html .= '</ul>';
    }
    $html .= '</li>';
  }
  $html .= '</ul>';
  return $html;
}

function print_eta_from_session()
{
  echo '<p class="delivery">You will receive your order in <span class="eta">' . format_eta($_SESSION[__JEBSTORES_USER_DURATION__]) . '</span></p>';
}

/**
 * Routes
 */

function add_custom_apis()
{
  register_rest_route('geomap/v1', '/postcode/(?P<postcode>[a-zA-Z0-9 .\-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_geo_info_from_postcode',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/lat-lng/(?P<lat>[0-9 .\-]+)/(?P<lng>[0-9 .\-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_geo_info_from_lat_lng',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/cities/add/city=(?P<city>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_custom_users_data',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/reference/lat=(?P<lat>[a-z0-9 .\-]+)/lng=(?P<lng>[a-z0-9 .\-]+)', array(
    'methods' => 'GET',
    'callback' => 'set_default_reference',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/driving/lat-lng/(?P<lng>[0-9 .\-]+)/(?P<lat>[0-9 .\-]+)', array(
    'methods' => 'GET',
    'callback' => 'get_driving_time',
    'permission_callback' => '__return_true'
  ));


  register_rest_route('geomap/v1', '/products', array(
    'methods' => 'GET',
    'callback' => 'get_products_by_postcodes',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/driving', array(
    'methods' => 'GET',
    'callback' => 'get_driving_time',
    'permission_callback' => '__return_true'
  ));

  // register_rest_route( 'geomap/v1', '/cities/city=(?P<city>[a-zA-Z0-9-]+)lat=(?P<lat>[a-z0-9 .\-]+)/lng=(?P<lng>[a-z0-9 .\-]+)', array(
  // 'methods' => 'GET',
  //'callback' => 'set_active_cities',
  // ));

  register_rest_route('geomap/v1', '/mapbox/api', array(
    'methods' => 'POST',
    'callback' => 'set_mapbox_api_credentials',
    'permission_callback' => '__return_true'
  ));

  register_rest_route('geomap/v1', '/postcodes', array(
    'methods' => 'POST',
    'callback' => 'set_deliverable_postcodes',
    'permission_callback' => '__return_true'
  ));
}

/**
 * WP actions hooks
 */

// Add hook for admin menu
add_action('init', 'jebstores_scripts_enqueuer');
add_action('init',  'get_deliverable_postcodes', 1);
add_action('admin_menu', 'geo_map_distance_matrix_menu');
//add_filter('pre_get_posts', 'get_products_by_postcode', 10, 0);
add_filter('woocommerce_product_query_meta_query', 'get_products_by_postcode', 10, 2);
// add_filter('woocommerce_before_checkout_billing_form', 'show_eta_from_postcode', 1, 2);
//add_filter('woocommerce_email_after_order_table', 'show_eta_from_postcode', 1, 2);

// Add hook for admin <head></head>
//add_action('admin_head', 'css_import');
//add_action('admin_head', 'js_import');

// Add hook for admin footer
// add_action('admin_footer', 'scripts_import');
// add_action('admin_footer', 'map_box_script');

//The Following registers an api route with multiple parameters. 
add_action('rest_api_init', 'add_custom_apis');


//add_action( 'init', function() {
//add_rewrite_tag( '%postcode%', '([^/]+)' );
//add_rewrite_rule( 'check-postcode/?postcode=([^/]+)/?', 'index.php?postcode=$matches[1]', 'top' );
//add_rewrite_tag( '%deliverable%', '([^/]+)' );
//add_rewrite_rule( 'delivery/?deliverable=([^/]+)/?', 'index.php?deliverable=$matches[1]', 'top' );
//} );
add_action("wp_ajax_check_postcode", "check_postcode");
add_action("wp_ajax_nopriv_check_postcode", "check_postcode");

add_action("wp_ajax_save_user_driving_matrix", "save_user_driving_matrix");
add_action("wp_ajax_nopriv_save_user_driving_matrix", "save_user_driving_matrix");

add_action("wp_ajax_autocomplete_postcode", "autocomplete_postcode");
add_action("wp_ajax_nopriv_autocomplete_postcode", "autocomplete_postcode");

add_action("wp_ajax_edit_postcode", "edit_postcode");
add_action("wp_ajax_nopriv_edit_postcode", "edit_postcode");

add_action("wp_ajax_check_if_postcode_in_session", "check_if_postcode_in_session");
add_action("wp_ajax_nopriv_check_if_postcode_in_session", "check_if_postcode_in_session");

//add_action( 'pre_get_posts' , 'filter_products_by_postcode' );
add_action('woocommerce_no_products_found', 'display_no_products_for_postcode');

// add the filter 
add_filter('woocommerce_add_to_cart_validation', 'filter_woocommerce_add_to_cart_validation', 11, 3);
add_action('woocommerce_before_single_product', 'warn_customer_about_deliverability');

add_action('woocommerce_product_options_shipping', 'jebstores_wc_add_postcodes_to_wc_product');
add_action('woocommerce_process_product_meta', 'jebstores_wc_save_postcodes_to_wc_product');
//add_action('woocommerce_before_checkout_form', 'print_eta_from_session');
add_action('woocommerce_order_details_after_order_table', 'print_eta_from_session');
add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);
add_shortcode('jebstores_categories', 'jebstores_categories_menu');
