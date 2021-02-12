jQuery(document).ready(function () {
    if (window.localStorage.getItem('GEO_INFO') === undefined
        || window.localStorage.getItem('GEO_INFO') === null) {
        window.localStorage.setItem('GEO_INFO', '');
    } else {

    }
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
       // navigator.geolocation.getCurrentPosition(success, error);
        jQuery("#getpostcode").show();
        jQuery("#delivery").hide();
    } else {
        console.debug(localStorage.getItem('USER_POSTCODE'));
        jQuery("#getpostcode").hide();
        if (window.localStorage.getItem('GEO_INFO') !== undefined || window.localStorage.getItem('GEO_INFO') !== null) {
            let geo_info = JSON.parse(window.localStorage.getItem('GEO_INFO'));
            console.log(geo_info);
        }
        jQuery("#delivery").show();
    }

    window.onstorage = () => {
        // When local storage changes, dump the list to
        // the console.
        console.log(JSON.parse(window.localStorage.getItem('GEO_INFO')));
    };
});


jQuery(document).ready(function () {
    jQuery('#submitPostCode').on('click', function (e) {
        e.preventDefault();
        jQuery('#notDelivrablePostcode').hide();
        jQuery('#notValidPostCode').hide();
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
                window.localStorage.removeItem('USER_POSTCODE');
            } else if (json_response.status === "No") {
                jQuery('#notValidPostCode').show();
                window.localStorage.removeItem('USER_POSTCODE');
            } else if (json_response.status === "Ok") {
                window.localStorage.setItem('USER_POSTCODE', window.postcode);
                window.localStorage.setItem('GEO_INFO', JSON.stringify(json_response.geo_info));
                window.location.href = json_response.redirect_url;
            }
        });
        posting.fail(function (ev) {
            jQuery('#notDelivrablePostcode').show();
            window.localStorage.removeItem('USER_POSTCODE');
        });
    });
});


