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

  var data = {
      action: 'mis_map_point',
      city_ids: select,
    }

    jQuery.post(
      "\/wp-admin\/admin-ajax.php",
      data,
      function(res){
        console.log(res);
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
  $('body').append('<div class="map"><div id="map"></div></div>');

});