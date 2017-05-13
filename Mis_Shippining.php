<?php
 
/**
 * Plugin Name: DDelivery метод доставки
 * Plugin URI: https://vk.com/mis_develop_pro
 * Description: Обработка сервиса DDelivery для Woocommerce
 * Version: 1.0.0
 * Author: Pavel Mishalov
 * Author URI: https://vk.com/mis_develop_pro
 * Domain Path: /languages
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function mis_ddelivery_shipping_method() {
        if ( ! class_exists( 'Mis_DDelivery_Shippining_pickup_method' ) || ! class_exists( 'Mis_DDelivery_Shippining_method' ) )
        {
            class Mis_DDelivery_Shippining_pickup_method extends WC_Shipping_Method
            {

                public function __construct() {
                    $this->id                 = 'mis_ddelivery_pickup_method';
                    $this->method_title       = 'DDelivery Самовывоз';
                    $this->method_description = 'Настройка DDelivery';
 
                    $this->availability = 'including';
                    $this->countries = array(
                        'UA',
                        'RU'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : 'Самовывоз';
                }

                function init()
                {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();
                    update_option( 'mis_api_ddelivery', $this->settings['api_key']);
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                function init_form_fields()
                { 
 
                    $this->form_fields = array(
                     'enabled' => array(
                        'title' => __( 'Выводить', 'misShip' ),
                        'type' => 'checkbox',
                        'description' => __( 'Включить данный метод в список доставки', 'misShip' ),
                        'default' => 'no'
                      ),
                     /*'title' => array(
                        'title' => __( 'Название', 'misShip' ),
                        'type' => 'text',
                        'description' => __( 'Введите название данного метода доставки', 'misShip' ),
                        'placeholder' => __( 'Введите название', 'misShip' ),
                      ),*/
                     'api_key' => array(
                        'title' => __( 'API ключ', 'misShip' ),
                        'type' => 'text',
                        'description' => __( 'Введите API-ключ с личного кабинета DDelivery', 'misShip' ),
                        'placeholder' => __( 'Введите ключ', 'misShip' ),
                      ),
                    );
 
                }

                public function calculate_shipping( $package = array() )
                {
                  $userID = $package['user']['ID'];
                  $pickup_cost = $package['destination']['mis_custom_shipping_cost_' . $userID];
                  $pickup_point = $package['destination']['mis_custom_shipping_point_' . $userID];

                  if( $package['destination']['state'] != 'Выберите область...' &&
                      $package['destination']['state'] != '' &&
                      !empty( get_transient('map_points')[0]->points[0]->company_id  ) ){
                    $rate = array(
                          'id' => $this->id,
                          'label' => $this->title . $pickup_point,
                          'cost' => $pickup_cost
                      );
   
                    $this->add_rate( $rate );
                  }
                }
            }
            class Mis_DDelivery_Shippining_method extends WC_Shipping_Method
            {

                public function __construct() {
                    $this->id                 = 'mis_ddelivery_method';
                    $this->method_title       = 'DDelivery Курьерская доставка';
                    $this->method_description = 'Настройка DDelivery';
 
                    $this->availability = 'including';
                    $this->countries = array(
                        'UA',
                        'RU'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : 'Курьер';
                }

                function init()
                {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();
                    update_option( 'mis_api_ddelivery', $this->settings['api_key']);
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                function init_form_fields()
                { 
 
                    $this->form_fields = array(
                     'enabled' => array(
                        'title' => __( 'Выводить', 'misShip' ),
                        'type' => 'checkbox',
                        'description' => __( 'Включить данный метод в список доставки', 'misShip' ),
                        'default' => 'no'
                      ),
                     'title' => array(
                        'title' => __( 'Название', 'misShip' ),
                        'type' => 'text',
                        'description' => __( 'Введите название данного метода доставки', 'misShip' ),
                        'placeholder' => __( 'Введите название', 'misShip' ),
                      ),
                     'api_key' => array(
                        'title' => __( 'API ключ', 'misShip' ),
                        'type' => 'text',
                        'description' => __( 'Введите API-ключ с личного кабинета DDelivery', 'misShip' ),
                        'placeholder' => __( 'Введите ключ', 'misShip' ),
                      ),
                    );
 
                }

                public function calculate_shipping( $package = array() )
                {
                  $userID = $package['user']['ID'];
                  if( $package['destination']['state'] != 'Выберите область...' &&
                      $package['destination']['state'] != '' &&
                      !empty($package['destination']['ddelivery_company_' . $userID]) ):
                  foreach ($package['destination']['ddelivery_company_' . $userID] as $key => $value) {
                    if( !empty($value['company']) ){
                      $rate = array(
                              'id' => $this->id . '_' . $key,
                              'label' => $this->title . ' ( Компания: ' . $value['company'] . ' )',
                              'cost' => $value['price']
                          );
     
                      $this->add_rate( $rate );
                    }
                  }
                  endif;
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'mis_ddelivery_shipping_method' );
 
    function add_mis_local_shipping_method( $methods ) {
        $methods[] = new Mis_DDelivery_Shippining_pickup_method();
        $methods[] = new Mis_DDelivery_Shippining_method();
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_mis_local_shipping_method' ); 
    add_action( 'woocommerce_review_order_before_cart_contents', 'mis_shippining_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'mis_shippining_validate_order' , 10 );
}

add_action('wp_ajax_mis_city_edit', 'mis_city_edit_callback');
add_action('wp_ajax_nopriv_mis_city_edit', 'mis_city_edit_callback');

function mis_city_edit_callback(){
  set_transient('pickup_cost_' . get_current_user_id(), '', HOUR_IN_SECONDS);
  set_transient('pickup_point_' . get_current_user_id(), '', HOUR_IN_SECONDS);
  set_transient('mis_pickup_cost_' . get_current_user_id(), 0, HOUR_IN_SECONDS);
  set_transient( 'ddelivery_company_' . get_current_user_id(), '', HOUR_IN_SECONDS );

    $url = 'http://cabinet.ddelivery.ru:80/daemon/?_action=autocomplete&q=' . $_POST['city'];
    $args = array();
    $answer = file_get_contents($url);

  echo $answer;
  wp_die();
}

add_action('wp_ajax_mis_map_point', 'mis_map_point_callback');
add_action('wp_ajax_nopriv_mis_map_point', 'mis_map_point_callback');

function mis_map_point_callback(){
  $args = $_POST['city_ids'];
  $args = explode( '|', $args);

  global $woocommerce;
  $items = $woocommerce->cart->get_cart();
  $weight = 0;
  $dimensions1 = 0;
  $dimensions2 = 0;
  $dimensions3 = 0;
  $payment_price = 0;
  $delivery_company = array();
  foreach ($items as $key => $item) {
    $product = wc_get_product($item['product_id']);
    $weight += $product->get_weight() * $item['quantity'];
    $dimensions1 += $product->get_length() * $item['quantity'];
    $dimensions2 += $product->get_width() * $item['quantity'];
    $dimensions3 += $product->get_height() * $item['quantity'];
    $payment_price += $item['line_total'];
  }

  //foreach ($args as $key => $value) {
    $url = 'http://cabinet.ddelivery.ru:80/api/v1/' .
              get_option("mis_api_ddelivery") . '/calculator.json?' .
              'type=2&' .
              'city_to=' . $args[0] .'&' .
              'dimension_side1=' . $dimensions1 . '&' .
              'dimension_side2=' . $dimensions2 . '&' .
              'dimension_side3=' . $dimensions3 . '&' .
              'weight=' . $weight . '&' .
              'declared_price=' . $payment_price . '&' .
              'payment_price=' . $payment_price;
    $res = file_get_contents($url);
    $res = json_decode($res);
    foreach ($res->response as $key => $value) {
      array_push( $delivery_company, array('company'=>$value->delivery_company_name, 'price'=>$value->total_price) );
    }
  //}

  set_transient( 'ddelivery_company_' . get_current_user_id(), $delivery_company, HOUR_IN_SECONDS );

  $answer = array();
  foreach( $args as $key=>$value ){
    $url = 'http://stage.ddelivery.ru/daemon/?_action=delivery_points&cities=' . $value;
    $args = array();
    $res = file_get_contents($url);
    array_push( $answer, json_decode($res) );
  }

  set_transient( 'map_points_' . get_current_user_id(), $answer, HOUR_IN_SECONDS );
  $args = $answer;
  $answer = array();

  for( $i=0; $i<count($args); $i++){
    for( $j=0; $j<count($args[$i]->points); $j++){
      if( $args[$i]->points[$j]->status == 2 ){
        array_push( $answer, $args[$i]->points[$j] );
      }
    }
  }

  echo json_encode( $answer );
  wp_die();
}

add_action('wp_ajax_mis_edit_shipping', 'mis_edit_shipping_callback');
add_action('wp_ajax_nopriv_mis_edit_shipping', 'mis_edit_shipping_callback');

function mis_edit_shipping_callback(){
  set_transient('pickup_cost_' . get_current_user_id(), $_POST['mis_price'], HOUR_IN_SECONDS);
  set_transient('pickup_point_' . get_current_user_id(), $_POST['mis_address'], HOUR_IN_SECONDS);
  echo get_transient('pickup_point');
  wp_die();
}

add_action('wp_ajax_mis_map_edit', 'mis_map_edit_callback');
add_action('wp_ajax_nopriv_mis_map_edit', 'mis_map_edit_callback');

function mis_map_edit_callback(){
  global $woocommerce;
  $items = $woocommerce->cart->get_cart();
  $weight = 0;
  $dimensions1 = 0;
  $dimensions2 = 0;
  $dimensions3 = 0;
  $payment_price = 0;
  foreach ($items as $key => $item) {
    $product = wc_get_product($item['product_id']);
    $weight += $product->get_weight() * $item['quantity'];
    $dimensions1 += $product->get_length() * $item['quantity'];
    $dimensions2 += $product->get_width() * $item['quantity'];
    $dimensions3 += $product->get_height() * $item['quantity'];
    $payment_price += $item['line_total'];
  }
  $city_arr = explode( '|', $_POST['city']);
  $answer = array();
  $pickup = array();

  foreach ($city_arr as $key => $city) {
    $url = 'http://cabinet.ddelivery.ru:80/api/v1/' .
              get_option("mis_api_ddelivery") . '/calculator.json?' .
              'type=1&' .
              'city_to=' . $city .'&' .
              'dimension_side1=' . $dimensions1 . '&' .
              'dimension_side2=' . $dimensions2 . '&' .
              'dimension_side3=' . $dimensions3 . '&' .
              'weight=' . $weight . '&' .
              'declared_price=' . $payment_price . '&' .
              'payment_price=' . $payment_price;
    $args = array();
    $res = file_get_contents($url);
    array_push( $pickup, json_decode($res) );
  }

  $points = get_transient('map_points');

  foreach ( $points[0]->points as $key => $value ) {
    foreach ($pickup as $k => $v) {
      foreach ($v->response as $k2 => $company) {   
        if( intval( $value->company_id ) == intval( $company->delivery_company ) ){
          $x = (array)$value;
          $x['price'] = $company->total_price;
          $x['pickup_date'] = $company->pickup_date;
          array_push( $answer, $x );
        }
      }
    }
  }

  echo json_encode( $answer );
  wp_die();
}

function custom_override_checkout_fields ( $fields ) {
  $fields['billing']['billing_one']['label'] = 'Область/регион';
  $fields['billing']['billing_one']['required'] = true;
  $fields['billing']['billing_one']['type'] = 'country';
  $fields['billing']['billing_one']['class'] = $fields['billing']['billing_country']['class'];
  //$fields['billing']['billing_postcode'] ='';
  $fields['billing']['billing_postcode']['class'][] = 'mis_field_hidden';
  //$fields['billing']['billing_company'] = '';
  $fields['billing']['billing_company']['class'][] = 'mis_field_hidden';
  $fields['billing']['billing_pickup']['class'] = array('update_totals_on_change', 'mis_field_hidden');
  $fields['billing']['billing_pickup']['type'] = 'text';
  //$fields['shipping'] = '';

  return $fields;
} // End custom_override_checkout_fields()

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

function custom_shipping_package_fields ( $package ) {

  $package[0]['destination']['mis_custom_shipping_cost_' . get_current_user_id() ] = get_transient('pickup_cost_' . get_current_user_id() );
  $package[0]['destination']['mis_custom_shipping_point_' . get_current_user_id() ] = get_transient('pickup_point_' . get_current_user_id() );
  $package[0]['destination']['ddelivery_company_' . get_current_user_id() ] = get_transient('ddelivery_company_' . get_current_user_id() );

  return $package;
}

add_filter( 'woocommerce_cart_shipping_packages', 'custom_shipping_package_fields' );

add_action( 'wp_enqueue_scripts', 'mis_shippining_plugin_scripts' );

function mis_shippining_plugin_scripts() {
  if ( is_checkout() ) {
    wp_enqueue_script( 'mis-script-name', plugins_url('Mis_Shippining/conf/script.js'), array( 'jquery'), true );
    wp_enqueue_script( 'wc-checkout', plugins_url('Mis_Shippining/conf/checkout.min.js'), array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ), '2.6.14', true );
    wp_enqueue_script( 'yandex-map', '//api-maps.yandex.ru/2.1/?lang=ru_RU', array( 'jquery' ), '2.6.14', true );
  }
    wp_enqueue_style( 'mis-style-name', plugins_url('Mis_Shippining/conf/style.css') );
}