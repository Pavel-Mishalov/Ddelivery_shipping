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

add_action('wp_ajax_mis_city_edit', 'mis_city_edit_callback');
add_action('wp_ajax_nopriv_mis_city_edit', 'mis_city_edit_callback');

function mis_city_edit_callback(){
    $url = 'http://cabinet.ddelivery.ru:80/daemon/?_action=autocomplete&q=' . $_POST['city'];
    $args = array();
    $res = wp_remote_get($url, $args);
    $answer = wp_remote_retrieve_body($res);

  echo $answer;
}

function custom_adjust_datepicker_range () {
  if ( is_checkout() ) {
    wp_enqueue_script( 'jquery' );
?>
<script type="text/javascript">

  function mis_edit_city(value){

    var data = {
      action: 'mis_city_edit',
      city: value,
    }

    jQuery.post(
      "\/wp-admin\/admin-ajax.php",
      data,
      function(res){
        res = JSON.parse( res.slice(0,-1) );
        jQuery('#billing_one_field select').html('<option value="">Выберите город...</option>');
        var array_region = res.options;
        var iterator = 0;
        var in_region = [];
        array_region.forEach(
            function(){
              if( value.toLowerCase() == res.options[iterator].name_index ){
                if( in_region.indexOf(res.options[iterator].region) < '0' ){
                  jQuery('#billing_one_field select').append('<option value="' + res.options[iterator]._id + '">' + res.options[iterator].region + '</option>');
                  in_region.push(res.options[iterator].region);
                }else{
                  jQuery.each(
                      jQuery('#billing_one_field select option'),
                      function(index, option){
                        if( res.options[iterator].region == jQuery( option ).html() ){
                          var optVal = jQuery( option ).val() + '|' + res.options[iterator]._id;
                          jQuery( option ).attr('value', optVal);
                        }
                      }
                    );
                }
              }else{
                // Здеся города с неполным соответствием названия
              }
              iterator++;
            }
          );
      }
    );
  }

function mis_edit_state(select){
  var value = jQuery('#billing_one_field select option').map(
      function(index, element){
        if( jQuery( element ).attr('value') == select ){
          jQuery('body.woocommerce-checkout #billing_state').attr('value', jQuery( element ).html() );
        }
      }
    );
}

jQuery( document ).ready( function ( $ ) {

  $('body.woocommerce-checkout #billing_state_field').css('display', 'none');
  $('#billing_one_field select').attr('onchange', 'mis_edit_state(this.value)');

  $('body.woocommerce-checkout #billing_city').attr('onchange', 'mis_edit_city(this.value)');
    var as = $('#billing_one_field select option').map(function(index, element){
        if(index==0){
          $(element).html('Выберите область...');
        }else{
          $(element).remove();
        }
        return element;
      });
});
</script>
<?php
  }
} // End custom_adjust_datepicker_range()
add_action( 'wp_footer', 'custom_adjust_datepicker_range', 50 );

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