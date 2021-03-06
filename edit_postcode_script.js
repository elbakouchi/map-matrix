jQuery(document).ready(function(){
    const loader = document.querySelector('#loader');
    loader.style.display = 'none';
    if(null === window.localStorage.getItem('USER_POSTCODE') || 0 === parseInt(window.localStorage.getItem('USER_POSTCODE')) ){
           jQuery('#editPostcode0').hide();
           jQuery('#startshopping').hide();
           jQuery('#getpostcode0').show();
           jQuery('#delivery').hide();
           const loader = document.querySelector('#loader');
           loader.style.display = 'none';
           
           setTimeout(function(){ 
               if(window.location.pathname !== '/' ){
                   window.location.href= '/';
               }
             }, 3000);
    }else{
        jQuery('#getpostcode0').hide();
        jQuery('#editPostcode0').on('click', function(){
            jQuery('#delivery').hide();
            jQuery('#editPostcode0').hide();
            jQuery('#startshopping').hide();
            jQuery('#getpostcode0').show();
        });
    }
});