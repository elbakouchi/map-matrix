jQuery(document).ready(function () {
    function success(position) {
        let url;
        if (position !== null) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            url = `?rest_route=/geomap/v1/lat-lng/${longitude}/${latitude}`;
        } else {
            const latitude = 51.514215;
            const longitude = -0.148703;
            url = `?rest_route=/geomap/v1/lat-lng/${longitude}/${latitude}`;
        }
        jQuery.get(url)
            .done(
                function (response) {
                    jQuery('#notDelivrablePostcode').hide();
                    jQuery('#notValidPostCode').hide();
                    if (response.length == 0) {
                        jQuery('#notValidPostCode').show();
                    }
                    let initialValue = [];
                    window.postcodes = response.reduce(function (accumulator, currentValue, currentIndex, array) {
                        accumulator.push(currentValue.postcode);
                        return accumulator;
                    }, initialValue);
                    window.postcodes.map(postcode => {
                        splitted = postcode.split(' ');
                        if (jQuery.inArray(splitted, jebStoresPostcodes.postcodes)) {
                            let url = `/?rest_route=/geomap/v1/postcode/${postcode}`;
                            jQuery.get(url)
                                .done(function (response) { window.localStorage.setItem('GEO_INFO', response); });
                            window.localStorage.setItem('USER_POSTCODE', postcode);
                            window.location.href = '/?post_type=product';
                        } else {
                            jQuery('#notDelivrablePostcode').show();
                        }
                    });
                })
            .fail(
                function (e) {
                    console.debug(e);
                }
            );
    }
    function error() {
        alert('Unable to retrieve your location');
        //success(null);
    }
    function geoFindMe() {
        const status = document.querySelector('#status');
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
        } else {
           // navigator.geolocation.getCurrentPosition(success, error);
        }
    }
    //document.querySelector('#find-me').addEventListener('click', geoFindMe);

    if (localStorage.getItem('USER_POSTCODE') === null) {
        jQuery("#getpostcode").show();
        jQuery("#delivery").hide();
    } else {
        console.debug(localStorage.getItem('USER_POSTCODE'));
        jQuery("#getpostcode").hide();
        // if (window.localStorage.getItem('GEO_INFO') !== undefined || window.localStorage.getItem('GEO_INFO') !== null) {
        //     let geo_info = JSON.parse(window.localStorage.getItem('GEO_INFO'));
        //     console.log(geo_info);
        // }
        jQuery("#delivery").show();
    }

    window.onstorage = () => {
        console.log(JSON.parse(window.localStorage.getItem('GEO_INFO')));
    };
});


jQuery(document).ready(function () {
    jQuery('#submitPostCode').on('click', window.deliverablePostCode );
});

window.checkPostCodeDeliverabilty = function (e) {
    try{
        e.preventDefault();
    }catch(e){
        console.debug(e);
    }
    jQuery('#notDelivrablePostcode').hide();
    jQuery('#notValidPostCode').hide();
    jQuery('.no_result').hide();
    let url = jebStoresAjax.ajaxurl;
    let form = jQuery("#postcodeForm");
    let postcode = form.find("input[name=postcode]").val();
    let posting = jQuery.post(url, { "action": "check_postcode", "postcode": postcode });
    window.postcode = postcode;
    posting.done(function (response, status, xhr) {
        //console.log(response, status, xhr.responseText);
        json_response = JSON.parse(response);
        if (json_response.status === "Ko") {
            jQuery('#notDelivrablePostcode').show();
            jQuery('.no_result').hide();
            window.localStorage.removeItem('USER_POSTCODE');
        } else if (json_response.status === "No") {
            jQuery('#notValidPostCode').show();
            jQuery('.no_result').hide();
            window.localStorage.removeItem('USER_POSTCODE');
        } else if (json_response.status === "Ok") {
            try{
                let postcode = window.getDrivingMatrix(window.feedback.selection.value);
                window.location.href = json_response.redirect_url;
            }catch(e){
               console.error(e);
            }
           // window.localStorage.setItem('USER_POSTCODE', window.feedback.selection.value);
        }
    });
    posting.fail(function (ev) {
        jQuery('#notDelivrablePostcode').show();
        window.localStorage.removeItem('USER_POSTCODE');
    });
}

window.getDrivingMatrix = async postcode => {
    const loader = document.querySelector('#loader');
    loader.style.display = 'inline-block';
    const geoDataSource = await fetch(`https://api.postcodes.io/postcodes/${postcode}`);
    const geoData = await geoDataSource.json();
    loader.style.display = 'none';
  
    if (geoData.hasOwnProperty('result') && geoData.result.hasOwnProperty('longitude') && geoData.result.hasOwnProperty('latitude')) {
      loader.style.display = 'inline-block';
     // const drivingDataSource = await fetch(`/?rest_route=/geomap/v1/driving/lat-lng/${geoData.result.longitude}/${geoData.result.latitude}`);
      const drivingDataSource = await fetch(`https://api.mapbox.com/directions-matrix/v1/mapbox/driving-traffic/${jebStoresCoords.longitude},${jebStoresCoords.latitude};${geoData.result.longitude},${geoData.result.latitude}?sources=0&annotations=duration&destinations=1&fallback_speed=20&access_token=${jebStoresMapBox.token}`);     
      const drivingMatrix = await drivingDataSource.json();
      console.log(drivingDataSource, drivingMatrix);
      loader.style.display = 'none';
      window.localStorage.drivingMatrix = JSON.stringify(drivingMatrix);
      await window.saveUserDrivingMatrix(postcode, drivingMatrix);
    }
  
  }
  
  
  window.deliverablePostCode = async (e,postcode) => {
    jQuery('#notDelivrablePostcode').hide();
    jQuery('#notValidPostCode').hide();
    jQuery('.no_result').hide();
    try{
      e.preventDefault();
    }catch(e){}
    if(undefined === postcode) postcode = jQuery("#autoComplete").val();
    const loader = document.querySelector('#loader');
    postcodeParts = postcode.split(' ');
    if(jebStoresPostcodes.postcodes.includes(postcodeParts[0])){
      window.localStorage.setItem('USER_POSTCODE', postcode);
      window.getDrivingMatrix(postcode);
    }else{
      jQuery('#notDelivrablePostcode').show();
      window.localStorage.removeItem('USER_POSTCODE');
    }
    loader.style.display = 'none';
  }
  
  window.validatePostCode = async postcode => {
    jQuery('#notDelivrablePostcode').hide();
    jQuery('#notValidPostCode').hide();
    const loader = document.querySelector('#loader');
    loader.style.display = 'inline-block';
    const validationDataSource = await fetch(`https://api.postcodes.io/postcodes/${postcode}/validate`);
    const validationData = await validationDataSource.json();
    loader.style.display = 'none';
    console.log(validationData);
    if(validationData.result){
      window.checkPostCodeDeliverabilty(postcode);
    }else{
      jQuery('.no_result').hide();  
      jQuery('#notValidPostCode').show();
    }
  }



