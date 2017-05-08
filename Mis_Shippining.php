<?php
 
/**
 * Plugin Name: DDelivery метод доставки
 * Plugin URI: http://localhost
 * Description: Обработка сервиса DDelivery для Woocommerce
 * Version: 1.0.0
 * Author: Pavel Mishalov
 * Author URI: http://localhost
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
        if ( ! class_exists( 'Mis_DDelivery_Shippining_method' ) )
        {
            class Mis_DDelivery_Shippining_method extends WC_Shipping_Method
            {

                public function __construct() {
                    $this->id                 = 'mis_ddelivery_method';
                    $this->method_title       = 'DDelivery';
                    $this->method_description = 'Настройка DDelivery';
 
                    $this->availability = 'including';
                    $this->countries = array(
                        'RU',
                        'UA'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : 'Собственная доставка';
                }

                function init()
                {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();
 
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
 
                     );
 
                }

                public function calculate_shipping( $package = array() )
                {/*
                    
                    $totalCost = 0;
                    $deliveryCost = $this->settings['deliveryCost'];

                    foreach ($package['contents'] as $item_id => $value) {
                        $_product = $value['data'];
                        $totalCost += $_product->get_price() * $value['quantity'];
                    }

                    $minTotalCost = $this->settings['minCost'];
                    $maxTotalCost = $this->settings['maxCost'];

                    if( $maxTotalCost < $totalCost ){
                      $rate = array(
                          'id' => $this->id,
                          'label' => $this->title,
                          'cost' => 0
                      );
                      $this->add_rate( $rate );
                    }elseif( $maxTotalCost > $totalCost && $minTotalCost < $totalCost ){
                      $rate = array(
                          'id' => $this->id,
                          'label' => $this->title,
                          'cost' => $deliveryCost
                      );
   
                      $this->add_rate( $rate );
                    }*/
                    $rate = array(
                          'id' => $this->id,
                          'label' => $this->title,
                          'cost' => 0
                      );
   
                      $this->add_rate( $rate );
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'mis_ddelivery_shipping_method' );
 
    function add_mis_local_shipping_method( $methods ) {
        $methods[] = new Mis_DDelivery_Shippining_method();
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_mis_local_shipping_method' );
 
    function mis_shippining_validate_order( $posted )   {
 /*
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'mis_custom_method', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "mis_custom_method" ) {
                             
                    continue;
                             
                }
                
                $Mis_Custom_Shippining_method = new Mis_Custom_Shippining_method();
                $totalCostLimit = (int) $Mis_Custom_Shippining_method->settings['minCost'];
                $totalCost = 0;
 
                foreach ( $package['contents'] as $item_id => $values ){
                  $_product = $values['data'];
                  $totalCost += $_product->get_price() * $values['quantity'];
                }

                $allTownState = array();
                $file = file( plugin_dir_path( __FILE__ ) . $Mis_Custom_Shippining_method->settings['state'] . '.txt' );
                foreach( $file as $value ):
                    $allTownState[] = trim($value);
                endforeach;
                
                if( $totalCostLimit > $totalCost ) {
 
                        $message = sprintf( 'Просим прошения, для предоставления услуги "'.$Mis_Custom_Shippining_method->title.'" неибходимо сделать минимальный заказ на '.$totalCostLimit.' рублей' );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }

                }elseif( !in_array( $package['destination']['city'], $allTownState ) ){
 
                        $message = sprintf( 'Просим прошения, услуга "'.$Mis_Custom_Shippining_method->title.'" предоставляется только на области ' . $Mis_Custom_Shippining_method->settings['state'] );
                          
                        foreach ($package['destination'] as $key => $value) {
                          $message .= '<br>'.$key.'   :   '.$value;
                        }
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }

                }
            }       
        }*/
    }
 
    add_action( 'woocommerce_review_order_before_cart_contents', 'mis_shippining_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', 'mis_shippining_validate_order' , 10 );
}

add_action('wp_ajax_mis_city_edit', 'mis_city_edit_callback');
add_action('wp_ajax_nopriv_mis_city_edit', 'mis_city_edit_callback');

function mis_city_edit_callback(){
    $url = 'http://cabinet.ddelivery.ru:80/daemon/?_action=autocomplete&q=' . $_POST['city'];
    $args = array();
    $res = wp_remote_get($url, $args);
    $answer = wp_remote_retrieve_body($res);

  echo $answer;
}

add_action('wp_ajax_mis_map_point', 'mis_map_point_callback');
add_action('wp_ajax_nopriv_mis_map_point', 'mis_map_point_callback');

function mis_map_point_callback(){

  $args = $_POST['city_ids'];
  $args = explode( '|', $args);

  $answer = array();

  foreach( $args as $key=>$value ){
    $url = 'http://stage.ddelivery.ru/daemon/?_action=delivery_points&cities=' . $value;
    $args = array();
    $res = wp_remote_get($url, $args);
    $res = wp_remote_retrieve_body($res);
    array_push( $answer, json_decode($res) );
  }

  set_transient( 'map_points', $answer, HOUR_IN_SECONDS );
  echo json_encode( get_transient('map_points') );
}

add_action('wp_ajax_mis_map_edit', 'mis_map_edit_callback');
add_action('wp_ajax_nopriv_mis_map_edit', 'mis_map_edit_callback');

function mis_map_edit_callback(){
  $args = get_transient('map_points');
  $answer = array();

  for( $i=0; $i<count($args); $i++){
    for( $j=0; $j<count($args[$i]->points); $j++){
      if( $args[$i]->points[$j]->status == 2 ){
        array_push( $answer, $args[$i]->points[$j] );
      }
    }
  }

  echo json_encode( $answer );
}

function custom_override_checkout_fields ( $fields ) {
  $fields['billing']['billing_one']['label'] = 'Область/регион';
  $fields['billing']['billing_one']['required'] = true;
  $fields['billing']['billing_one']['type'] = 'country';
  $fields['billing']['billing_one']['class'] = array('form-row-wide', 'update_totals_on_change', 'address-field');
  unset( $fields['billing']['billing_postcode'] );
  unset( $fields['billing']['billing_company'] );

  PC::debug($fields['billing'], 'billing');
  PC::debug($fields['shipping'], 'shipping');
  return $fields;
} // End custom_override_checkout_fields()

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

add_action( 'wp_enqueue_scripts', 'mis_shippining_plugin_scripts' );

function mis_shippining_plugin_scripts() {
  if ( is_checkout() ) {
    wp_enqueue_script( 'mis-script-name', plugins_url('Mis_Shippining/conf/script.js'), array( 'jquery'), true );
    wp_enqueue_script( 'wc-checkout', plugins_url('Mis_Shippining/conf/checkout.min.js'), array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ), '2.6.14', true );
    wp_enqueue_script( 'yandex-map', '//api-maps.yandex.ru/2.1/?lang=ru_RU', array( 'jquery' ), '2.6.14', true );
    wp_enqueue_style( 'mis-style-name', plugins_url('Mis_Shippining/conf/style.css') );
  }
}
