function mis_edit_city(value){

    var data = {
      action: 'mis_city_edit',
      city: value,
    }

    jQuery.ajax({
      type: 'POST',
      url : "\/wp-admin\/admin-ajax.php",
      data: data,
      beforeSend: function(){
        jQuery('#billing_one_field select').attr('value', '');
        jQuery('#billing_one_field #select2-chosen-2').html('Выберите область...');
        jQuery('body.woocommerce-checkout #billing_state').attr('value', '');
      },
      success: function(res){
        res = JSON.parse( res );
        jQuery('#billing_one_field select').html('<option value="">Выберите область...</option>');
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
        jQuery('body.woocommerce-checkout #billing_one_field select').removeAttr('disabled');
        setTimeout( "jQuery('body').trigger('update_checkout')", 1000 );
      }
    });
  }

function mis_edit_state(select){
  var value = jQuery('#billing_one_field select option').map(
      function(index, element){
        if( jQuery( element ).attr('value') == select ){
          jQuery('body.woocommerce-checkout #billing_state').attr('value', jQuery( element ).html() );
        }
      }
    );

  var mis_data = {
      action: 'mis_map_point',
      city_ids: select,
    }

    jQuery.ajax({
      method: "POST",
      url : "\/wp-admin\/admin-ajax.php",
      data : mis_data,
      beforeSend: function(){
        setTimeout(function(){
          jQuery('table.shop_table.woocommerce-checkout-review-order-table').css('position', 'relative');
          jQuery('table.shop_table.woocommerce-checkout-review-order-table').append('<div id="mis_ajax" class="blockUI blockOverlay" style="z-index: 1000; border: medium none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; opacity: 0.6; cursor: default; position: absolute;"></div>');
        }, 1000);
      },
      success : function(res){
        jQuery('table.shop_table.woocommerce-checkout-review-order-table').removeAttr('style');
        jQuery('#mis_ajax').remove();
        jQuery('body').trigger('update_checkout');
      }
    });
}

function destMap(){
  jQuery('body.woocommerce-checkout .map').css('display', 'none');
  jQuery('body.woocommerce-checkout #map').html('');
  jQuery('body.woocommerce-checkout #shipping_method_0_mis_ddelivery_method').removeAttr("checked");
}

function get_point(price, address){
  jQuery('body.woocommerce-checkout .map').css('display', 'none');
  jQuery('body.woocommerce-checkout #map').html('');
  data = {
    action: 'mis_edit_shipping',
    mis_price: price,
    mis_address: address
  }

  jQuery.post(
      "\/wp-admin\/admin-ajax.php",
      data,
      function(res){
        console.log(res);
        jQuery('body').trigger('update_checkout');
      }
    );
}

jQuery( document ).ready( function ( $ ) {

  $('body.woocommerce-checkout #billing_state_field').css('display', 'none');
  $('#billing_one_field select').attr('onchange', 'mis_edit_state(this.value)');

  $('body.woocommerce-checkout #billing_one_field select').attr('disabled','disabled');
  $('body.woocommerce-checkout #billing_city').attr('value', '');
  $('body.woocommerce-checkout #billing_city').keydown(function(){
    $('body.woocommerce-checkout #billing_one_field select').attr('disabled','disabled');
  });
  $('body.woocommerce-checkout #billing_state').attr('value', '');
  $('body.woocommerce-checkout #billing_city').attr('onchange', 'mis_edit_city(this.value)');
    var as = $('#billing_one_field select option').map(function(index, element){
        if(index==0){
          $(element).html('Выберите область...');
        }else{
          $(element).remove();
        }
        return element;
      });
  $('body').append('<div class="map">'+
  '<div id="all-map">'+
    '<div id="png_before" style="background: #fff;position: absolute;width: 100%;height: 100%;top: 0;z-index: 1;background-image: url(\'https://sdk.ddelivery.ru/assets/module/img/loader-2.gif\');background-repeat: no-repeat;background-position: center;"></div>' +
    '<div id="map-close">'+
      '<p>Выберите точку самовывоза</p>'+
      '<p class="mis-close-icon" onclick="destMap();">&times;</p>'+
    '</div>'+
    '<div id="map"></div>'+
  '</div>'+
'</div>');

});