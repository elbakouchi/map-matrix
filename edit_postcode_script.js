jQuery(document).ready(function(){
    if(undefined === window.localStorage.getItem('USER_POSTCODE') || null === window.localStorage.getItem('USER_POSTCODE') ){
        jQuery('#editPostcode').hide();
    }else{
        jQuery('#editPostcode').on('click', function(){
            window.localStorage.removeItem('USER_POSTCODE');
            jQuery('#delivery').hide();
            jQuery('#getpostcode').show();
            jQuery('#editPostcode').hide();
        });
    }
});