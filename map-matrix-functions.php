<?php
/**
 * Jebstores Extended Woocommerce Product
 * 
 */


function jebstores_wc_add_postcodes_to_wc_product() {
  $args = array(
  'label' => __( 'Postcodes list', 'woocommerce' ),
  'placeholder' => __( 'Enter a comma separated list of postcodes', 'woocommerce' ),
  'id' => 'wc_product_postcodes',
  'desc_tip' => true,
  'description' => __( 'This product can only be delivered to these postcodes.', 'woocommerce' ),
  );
  woocommerce_wp_textarea_input( $args );
  }

  add_action( 'woocommerce_product_options_shipping', 'jebstores_wc_add_postcodes_to_wc_product' );

function jebstores_wc_save_postcodes_to_wc_product( $post_id ) {
    // grab the custom SKU from $_POST
    $postcodes = isset( $_POST[ 'wc_product_postcodes' ] ) ? sanitize_textarea_field( $_POST[ 'wc_product_postcodes' ] ) : '';
    
    // grab the product
    $product = wc_get_product( $post_id );
    
    // save the custom SKU using WooCommerce built-in functions
    $product->update_meta_data( 'wc_product_postcodes', $postcodes );
    $product->save();
    }
    
    add_action( 'woocommerce_process_product_meta', 'jebstores_wc_save_postcodes_to_wc_product' );

/**
 * Actions Callbacks
 */


$mapbox_api_credentials = array();

function css_import() {
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">';
    echo '<link href="https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.css" rel="stylesheet" />';
}

function js_import(){
    echo '<script src="https://api.mapbox.com/mapbox-gl-js/v2.0.0/mapbox-gl.js"></script>';
}

function scripts_import(){
  global $persistApiScript, $persistActiveCitiesScript, $persistDefaultReferenceScript, $persistPostCodes;
  echo   $persistApiScript, $persistActiveCitiesScript, $persistDefaultReferenceScript, $persistPostCodes ;
}


function geo_map_distance_matrix_menu(){    
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


function geo_map_distance_matrix_page($e){
  $login = get_option(__JEBSTORES_MAPBOX_API_LOGIN__);
  $token = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
  $url   = get_option( __JEBSTORES_MAPBOX_API_URL__ );
  $postcodes   = get_option( __JEBSTORES_DELIVERABLE_POSTCODES__ );
  
  global $mapBox, $mapPanel, $apiFormBox, $listOfDelieverablePostcodesForm;// , $activateCitiesForm; 
  
  $filledApiFormBox = sprintf($apiFormBox, $url, $login, $token);
  $filledPostcodesForm = sprintf($listOfDelieverablePostcodesForm, $postcodes);

  
  $map   = sprintf($mapPanel, 'is-primary', '', __JEBSTORES_MAP_PANEL_TITLE__, 'is-full' , $mapBox             );
  $form1 = sprintf($mapPanel, 'is-info',    '', __JEBSTORES_MAP_API_TITLE__,   'is-full' , $filledApiFormBox   );
  $form2 = null; //sprintf($mapPanel, 'is-warning', '', __JEBSTORES_POSTCODES_PANEL__, 'is-full' , $persistPostCodes );
  $form3 = sprintf($mapPanel, 'is-warning', '', __JEBSTORES_POSTCODES_PANEL__, 'is-full' , $filledPostcodesForm );
  
  template($map, $form1, $form2, $form3);
}
  
function map_box_script(){
  global $mapBoxScript;
  $token    = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
  $position = json_decode(get_option(__JEBSTORES_DEFAULT_REFERENCE__));
  $filledMapBoxScript = sprintf($mapBoxScript, $token, __JEBSTORES_INITIAL_LONDON_POINT__, 
                    __JEBSTORES_MAP_ZOOM__, $position->lat, $position->lng);
  echo $filledMapBoxScript;
}

// function get_mapbox_api_credentialst(){
//   global $mapbox_api_credentials;
// }

/**
 * Helpers
 */

function jebstores_wc_register_widgets() {
	register_widget( 'Jebstores_WC_Widget_Product_Postcode_Filter' );
}

function template($map, $form1, $form2=null, $form3){
  echo 
    '<div class="wrap has-background-white">'
   . '<h1 class="wp-heading-inline">'.__THIS_PLUGIN_NAME__.'</h1><h2><subtitle>'.__VERSION__.'</subtitle></h2>'
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

function get_bulk_driving_distance_and_duration($mapped_coords){
  global $__MAPBOX_API_TOKEN__;
  global $__JEBSTORES_LONDON_REFERENCE__;
  if(count($mapped_coords)){
    $destinations = implode(';', $mapped_coords);
    $dump = implode(',', range(1, count($mapped_coords)));
    $mapbox_url = __MAPBOX_BASE_URL__ 
                . __MAPBOX_DRIVING_API_URL__ 
                . sprintf(__MAPBOX_DRIVING_API_BULK_QUERY__,
                      $__JEBSTORES_LONDON_REFERENCE__->lng,
                      $__JEBSTORES_LONDON_REFERENCE__->lat,
                      $destinations, $dump,
                      $__MAPBOX_API_TOKEN__
                );
    $mapbox_request = new WP_Http(); 
    $mapbox_response = $mapbox_request->request($mapbox_url);//echo $mapbox_url;//var_dump($mapbox_response);
    
    if($mapbox_response['response']['code']==200){
      return rest_ensure_response(json_decode($mapbox_response['body']));
    }elseif($mapbox_response['response']['code'] == 'InvalidInput'){
      $error = new WP_Error(400,$mapbox_response['response']['message']);
      return rest_ensure_response($error);
    }else{
      $error = new WP_Error(400,'Error getting postcode geocoding information from service.');
      return rest_ensure_response($error);
    }
  }else{
    $error = new WP_Error(400,'No postcode provided');
      return rest_ensure_response($error);
  }                  
}

function get_driving_distance_and_duration($longitude, $latitude){
  global $__MAPBOX_API_TOKEN__;
  global $__JEBSTORES_LONDON_REFERENCE__;
  if (is_float($longitude) && is_float($latitude)){
     $mapbox_url = __MAPBOX_BASE_URL__ 
                 . __MAPBOX_DRIVING_API_URL__ 
                 . sprintf(__MAPBOX_DRIVING_API_QUERY_PARAMS__,
                          $__JEBSTORES_LONDON_REFERENCE__->lng,
                          $__JEBSTORES_LONDON_REFERENCE__->lat,
                          $longitude, $latitude,
                          $__MAPBOX_API_TOKEN__);
     $mapbox_request = new WP_Http(); 
     $mapbox_response = $mapbox_request->request($mapbox_url);
     if($mapbox_response['response']['code']==200){
       return rest_ensure_response(json_decode($mapbox_response['body']));
     }else{
      $error = new WP_Error(400,'Error getting postcode geocoding information from service.');
      return rest_ensure_response($error);
     }
  }else{
    $error = new WP_Error(400,'No postcode provided');
      return rest_ensure_response($error);
  }
}

function map_coords($a, $b){
  return $a . ',' . $b;
}

/**
 * Rest APIs & Controllers
 */

function fetch_postcodes_io_lat_lng_info($request){
  if ($request->has_param('lat') && $request->has_param('lng') ){
    $postcode_io_url = sprintf(__POSTCODES_IO_LAT_LNG_URL__, $request->get_param('lat'), $request->get_param('lng') );
   // echo $postcode_io_url;
    $postcode_io_request = new WP_Http(); 
    $postcode_io_response = $postcode_io_request->request($postcode_io_url);
    ////($postcode_io_response);
    if($postcode_io_response['response']['code']==200){
      $response = json_decode($postcode_io_response['body']);
      return rest_ensure_response($response->result);
      // return rest_ensure_response(json_decode($postcode_io_response['body']));
    }else{
      $error = new WP_Error(400,'Error getting postcode geocoding information from service.',$request->get_param('postcode'));
      return rest_ensure_response($error);
    }
  }else{
    $error = new WP_Error(400, 'No postcode provided', $request->get_param('postcode'));
    return rest_ensure_response($error);
  }  
}

function postcode_valid($postcode) {
  return preg_match('/^[A-Z]{1,2}[0-9]{1,2}[A-Z]? [0-9][A-Z]{2}$/', $postcode);
}

function fetch_postcodes_io_postcode_info($request){
  if ($request->has_param('postcode') && postcode_valid($request->get_param('postcode'))){
    $postcode_io_url = sprintf(__POSTCODES_IO_POSTCODE_URL__, $request->get_param('postcode') );
    $postcode_io_request = new WP_Http(); 
    $postcode_io_response = $postcode_io_request->request($postcode_io_url);
    if($postcode_io_response['response']['code']==200){
      $response = json_decode($postcode_io_response['body']);
      return rest_ensure_response([$response->result,]);
      //return rest_ensure_response($postcode_io_response);
    }else{
      $error = new WP_Error(400,'Error getting postcode geocoding information from service.',$request->get_param('postcode'));
      return rest_ensure_response($error);
    }
  }else{
    $error = new WP_Error(400, 'No postcode provided', $request->get_param('postcode'));
    return rest_ensure_response($error);
  }  
}

function get_geo_info_from_postcode($request){
  $geocoding_info = fetch_postcodes_io_postcode_info($request);//  //($geocoding_info);
  return prepare_geo_info($geocoding_info);
  
}

function get_geo_info_from_lat_lng($request){
  $geocoding_info = fetch_postcodes_io_lat_lng_info($request);
  return prepare_geo_info($geocoding_info);
}

function prepare_geo_info($geocoding_info){
  if($geocoding_info->status == 200 && !is_null($geocoding_info->data)){
    $geo_info   = array();
   
    // $postcodes  = array_column($geocoding_info->data, 'postcode');
    $longitudes = array_column($geocoding_info->data, 'longitude');
    $latitudes  =  array_column($geocoding_info->data, 'latitude');
    $mapped_coords = array_unique(array_map('map_coords',$longitudes,$latitudes)); 
    //var_dump($mapped_coords);   
    //$destinations = implode(';', $mapped_coords);
    $driving = get_bulk_driving_distance_and_duration($mapped_coords);                 
    foreach($geocoding_info->data as $key=>$result){
      $country   = $result->country;
      $city      = $result->nhs_ha;
      $latitude  = $result->latitude;
      $longitude = $result->longitude;
      $postcode  = $result->postcode;
      if( strtolower( $country ) == __ENGLAND__  && in_array( strtolower( $city ),  __CITIES__ ) ){
       // $driving   = get_driving_distance_and_duration($longitude, $latitude);
        if( get_class($driving) == 'WP_REST_Response'  && $driving->status == 200  ){
          $geo_info[] = (object) array( 
            'country'=>$country,
            'city'=>$city,
            'postcode'=>$postcode,
            'lat'=>$latitude,
            'lng'=>$longitude,
            'duration'=>gmdate("H:i:s", round($driving->data->durations[0][0])),
            'distance'=>round($driving->data->destinations[0]->distance),
            'name'=>$driving->data->destinations[0]->name
          );
        }
      }
    }
    return $geo_info;
  }else{
    $error = new WP_Error(400, 'No results returned');
    return rest_ensure_response($error);
  }
}

function check_postcode_enqueuer() {
  wp_register_script( "check_postcode_script", WP_PLUGIN_URL.'/jebstores-product-map-matrix/check_postcode_script.js', array('jquery') );
  wp_localize_script( 'check_postcode_script', 'jebStoresAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));  
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'check_postcode_script' ); 
} 

function fetch_postcode_geocoding_info($request){
  global $__MAPBOX_API_TOKEN__;
  if ($request->has_param('postcode') && postcode_valid($request->get_param('postcode'))){
     $mapbox_url = __MAPBOX_BASE_URL__ . sprintf(__MAPBOX_GEOCODING_API_URL__, $request->get_param('postcode'), $__MAPBOX_API_TOKEN__);
     $mapbox_request = new WP_Http(); 
     $mapbox_response = $mapbox_request->request($mapbox_url);
     if($mapbox_response['response']['code']==200){
       return rest_ensure_response(json_decode($mapbox_response['body']));
     }else{
      $error = new WP_Error(400,'Error getting postcode geocoding information from service.',$request->get_param('postcode'));
      return rest_ensure_response($error);
     }
  }else{
    $error = new WP_Error(400,'No postcode provided');
      return rest_ensure_response($error);
  }
}

function set_active_cities($request){
    $dump = get_option(__JEBSTORES_MAPBOX_API_URL__);
    if($dump){
      update_option(__JEBSTORES_MAPBOX_API_URL__, $request['api_url'] );
    }elseif($dump != $request['api_url'] && !empty($dump)){
      add_option(__JEBSTORES_MAPBOX_API_URL__, $request['api_url'] );
    }
}

function get_driving_time($request){
  $longitude = floatval($request->get_param('lng'));
  $latitude = floatval($request->get_param('lat'));

  $driving   = get_driving_distance_and_duration($longitude, $latitude);

  if($driving->status == 200 && $driving->data->code == 'Ok'){
    $matrix = (object) array('duration'=>gmdate("H:i:s", round($driving->data->durations[0][0])),
                             'distance'=>round($driving->data->destinations[0]->distance),
                             'name'=>$driving->data->destinations[0]->name);
    return $matrix;                         
  }

  return $driving;
}

function get_products_by_postcodes($request){
  
}

function check_postcode(){
    global $wp_query;
   // $postcode = $wp_query->get( 'postcode' );
    $postcode = $_REQUEST['postcode'];
    if ( ! empty( $postcode ) ){ // && postcode_valid($postcode)) {
      $postcode_parts = explode(' ', $postcode);
      $part1 = (string) $postcode_parts[0];
      if( in_array( $part1, $_SESSION['postcodes'] ) !== false ) {
        $msg = 'Good postcode: ' . $postcode;
        error_log($msg);
        $result = (object)  array('status'=>'Ok', 'redirect_url'=>'/products');
      }else{
        $result = (object) array('status'=>'Ko');
      }
    }else{
      $result = (object)  array('status'=>'No');
    }

    //if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      header('Content-Type', 'application/json');
      echo $result;
    /*}
    else {
      try{
        header("Location: ".$_SERVER["HTTP_REFERER"]);
      }catch(Exception $e){
        header("Location: / ");
      }
    }
*/
    die();
} 

function set_mapbox_api_credentials($request){
    
    $token = $request->get_param('api_token');  
    //echo $token;
    if(!empty($token)){
      $dump = get_option(__JEBSTORES_MAPBOX_API_TOKEN__);
      if($dump != $token){
        update_option(__JEBSTORES_MAPBOX_API_TOKEN__, $token );
        return rest_ensure_response( ['status'=>'success', 'message'=>'Token updated.'] );
      }else{
        add_option(__JEBSTORES_MAPBOX_API_TOKEN__, $token );
        return rest_ensure_response( ['status'=>'success', 'message'=>'Token saved.'] );
      }
      $error = new WP_Error(400,'Nothing done',$token);
      return rest_ensure_response($error);
    }else{
      $error = new WP_Error(400,'No token provided',$token);
      return rest_ensure_response($error);
    }
}

function set_default_reference($request){
  $default_reference = get_option(__JEBSTORES_DEFAULT_REFERENCE__);
  if($default_reference){
    update_option(__JEBSTORES_DEFAULT_REFERENCE__, json_encode( 
                      array('lat'=>floatval($request['lat']), 
                            'lng'=>floatval($request['lng'])) ) );
  }else{
    add_option(__JEBSTORES_DEFAULT_REFERENCE__, json_encode( 
                        array('lat'=>floatval($request['lat']), 
                              'lng'=>floatval($request['lng'])) ) );
  }
  return rest_ensure_response( ['lat'=>$request['lat'], 'lng'=>$request['lng'],] );
}

function set_deliverable_postcodes($request){
  $deliverable_postcodes = get_option(__JEBSTORES_DELIVERABLE_POSTCODES__);
 // var_dump($request);
  if($deliverable_postcodes){
    update_option(__JEBSTORES_DELIVERABLE_POSTCODES__, $request['postcodes']);
  }else{
    add_option(__JEBSTORES_DELIVERABLE_POSTCODES__, $request['postcodes']);
  }
  return rest_ensure_response(['message'=>'Success','postcodes'=>$request['postcodes']]);
}

function get_deliverable_postcodes(){
  if(!session_id()) {
    session_start();
    $deliverable_postcodes = get_option(__JEBSTORES_DELIVERABLE_POSTCODES__);
    if($deliverable_postcodes){
      if( !array_key_exists('postcode', $_SESSION) ){
        $_SESSION['postcodes'] = explode(',', $deliverable_postcodes);
        error_log('postcodes loaded');
      }else{
        error_log('postcodes already there!');
      }
    }else{
      error_log('no postcodes loaded');
    }
  }
}


function postcode_exists($request){
  var_dump($request);
  return rest_ensure_response(['status'=>'Ok']);
}

function postcode_does_not_exists($request){
  var_dump($request);
  return rest_ensure_response(['status'=>'Ko']);
}

/**
 * Routes
 */

function add_custom_apis(){
    register_rest_route( 'geomap/v1', '/postcode/(?P<postcode>[a-zA-Z0-9 .\-]+)', array(
      'methods' => 'GET',
      'callback' => 'get_geo_info_from_postcode',
      'permission_callback' => '__return_true'
    ));

    register_rest_route( 'geomap/v1', '/lat-lng/(?P<lat>[0-9 .\-]+)/(?P<lng>[0-9 .\-]+)', array(
      'methods' => 'GET',
      'callback' => 'get_geo_info_from_lat_lng',
      'permission_callback' => '__return_true'
    ));

    register_rest_route( 'geomap/v1', '/cities/add/city=(?P<city>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_custom_users_data',
        'permission_callback' => '__return_true'
    ));

    register_rest_route( 'geomap/v1', '/reference/lat=(?P<lat>[a-z0-9 .\-]+)/lng=(?P<lng>[a-z0-9 .\-]+)', array(
      'methods' => 'GET',
      'callback' => 'set_default_reference',
      'permission_callback' => '__return_true'
    ));

    register_rest_route( 'geomap/v1', '/driving/lat-lng/(?P<lat>[0-9 .\-]+)/(?P<lng>[0-9 .\-]+)', array(
      'methods' => 'GET',
      'callback' => 'get_driving_time',
      'permission_callback' => '__return_true'
    ));


    register_rest_route( 'geomap/v1', '/products', array(
      'methods' => 'GET',
      'callback' => 'get_products_by_postcodes',
      'permission_callback' => '__return_true'
    ));

    register_rest_route( 'geomap/v1', '/driving', array(
      'methods' => 'GET',
      'callback' => 'get_driving_time',
      'permission_callback' => '__return_true'
    ));

   // register_rest_route( 'geomap/v1', '/cities/city=(?P<city>[a-zA-Z0-9-]+)lat=(?P<lat>[a-z0-9 .\-]+)/lng=(?P<lng>[a-z0-9 .\-]+)', array(
     // 'methods' => 'GET',
      //'callback' => 'set_active_cities',
   // ));

    register_rest_route( 'geomap/v1', '/mapbox/api', array(
      'methods' => 'POST',
      'callback' => 'set_mapbox_api_credentials',
      'permission_callback' => '__return_true'
    ));  

    register_rest_route( 'geomap/v1', '/postcodes', array(
      'methods' => 'POST',
      'callback' => 'set_deliverable_postcodes',
      'permission_callback' => '__return_true'
    )); 

    register_rest_route( 'geomap/v1', '/postcode/exists', array(
      'methods' => 'GET',
      'callback' => 'postcode_exists',
      'permission_callback' => '__return_true'
    )); 

    register_rest_route( 'geomap/v1', '/postcode/does-not-exists', array(
      'methods' => 'GET',
      'callback' => 'postcode_does_not_exists',
      'permission_callback' => '__return_true'
    )); 
}

/**
 * WP actions hooks
 */

// Add hook for admin menu
add_action( 'init', 'check_postcode_enqueuer' );
add_action( 'init',  'get_deliverable_postcodes', 1);
add_action( 'admin_menu', 'geo_map_distance_matrix_menu' );

// Add hook for admin <head></head>
add_action( 'admin_head', 'css_import' );
add_action( 'admin_head', 'js_import'  );

// Add hook for admin footer
add_action('admin_footer', 'scripts_import');
add_action('admin_footer', 'map_box_script');

//The Following registers an api route with multiple parameters. 
add_action( 'rest_api_init', 'add_custom_apis');


//add_action( 'init', function() {
  //add_rewrite_tag( '%postcode%', '([^/]+)' );
  //add_rewrite_rule( 'check-postcode/?postcode=([^/]+)/?', 'index.php?postcode=$matches[1]', 'top' );
  //add_rewrite_tag( '%deliverable%', '([^/]+)' );
  //add_rewrite_rule( 'delivery/?deliverable=([^/]+)/?', 'index.php?deliverable=$matches[1]', 'top' );
//} );
add_action("wp_ajax_check_postcode", "check_postcode");
add_action("wp_ajax_nopriv_check_postcode", "check_postcode");
 
//add_action( 'template_redirect', 'check_postcode');

//add_action('get_mapbox_api_credentials', 'get_mapbox_api_credentialst')

//add_action( 'widgets_init', 'jebstores_wc_register_widgets' );