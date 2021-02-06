jQuery(document).ready(function () {
    if( window.user_postcode === undefined && window.user_postcode === null){
        navigator.geolocation.getCurrentPositiong(geoFindMe().success, geoFindMe().error);
    }else{
        console.debug(window.user_postcode);
    }
    function geoFindMe() {
        const status = document.querySelector('#status');
      
        function success(position) {
            let url;
            if(position !== null){
                const latitude  = position.coords.latitude;
                const longitude = position.coords.longitude;
                url = `?rest_route=/geomap/v1/lat-lng/${longitude}/${latitude}`;   
            }else{
                const latitude  = 51.514215;
                const longitude = -0.148703; 
                url = `?rest_route=/geomap/v1/lat-lng/${longitude}/${latitude}`;   
            }
            jQuery.get(url)
                  .done(
                    function(response){  
                        jQuery('#notDelivrablePostcode').hide();
                        jQuery('#notValidPostCode').hide();
                        if(response.length == 0){
                            jQuery('#notValidPostCode').show();    
                        }
                        let initialValue = [];  
                        window.postcodes = response.reduce(function(accumulator, currentValue, currentIndex, array) {
                            accumulator.push(currentValue.postcode);
                            return accumulator;
                        }, initialValue);
                        window.postcodes.map(postcode => {
                            splitted = postcode.split(' ');
                            if (jQuery.inArray(splitted, jebStoresPostcodes.postcodes)){
                                window.user_postcode = postcode; 
                                window.location.href = '/?post_type=product';
                            }else{
                                jQuery('#notDelivrablePostcode').show();       
                            }
                        });
                    })
                    .fail(
                        function(e){
                            console.debug(e);
                        }
                    );       
            }
      
        function error() {
          alert('Unable to retrieve your location');
          success(null);
        }
      
        if(!navigator.geolocation) {
          alert('Geolocation is not supported by your browser');
        } else {
          navigator.geolocation.getCurrentPosition(success, error);
        }
      
      }
      
    document.querySelector('#find-me').addEventListener('click', geoFindMe);

    jQuery('#submitPostCode').on('click', function (e) {
        e.preventDefault();
        jQuery('#notDelivrablePostcode').hide();
        jQuery('#notValidPostCode').hide();
        let url = jebStoresAjax.ajaxurl;
        let form = jQuery("#postcodeForm");
        let postcode = form.find("input[name=postcode]").val();
        let posting = jQuery.post(url, { "action":"check_postcode", "postcode": postcode });
        posting.done(function (response, status, xhr) {
            //console.log(response, status, xhr.responseText);
            json_response = JSON.parse(response);
            if ( json_response.status === "Ko"  ) {
                jQuery('#notDelivrablePostcode').show();
            }else if ( json_response.status === "No" ) {
                jQuery('#notValidPostCode').show();
            }else if ( json_response.status === "Ok" ) {
                window.location.href = json_response.redirect_url;
            }
        });
        posting.fail(function (ev) {
            jQuery('#notDelivrablePostcode').show();
        });
    });
});



  