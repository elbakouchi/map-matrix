jQuery(document).ready(function () {
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
