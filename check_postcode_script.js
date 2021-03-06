
jQuery(document).ready(function () {
    jQuery('#submitPostCode').on('click', window.deliverablePostCode );
    try{
        let postcode = window.localStorage.getItem('USER_POSTCODE');
        jQuery('#billing_postcode').val(postcode);
    }catch(e){
        console.debug(e);
    }

    document.addEventListener('validated', async function (e) {
      try{
          let deliverability = await window.deliverablePostCode(null, window.postcode);
          console.info('checking deliverability:', deliverability);
      }catch(e){
          console.debug(e);
      }
    }, false);

});

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
      window.postcode = postcode;
      const event = new Event('validated');
      document.dispatchEvent(event);
    }else{
      window.postcode = null;
      window.destroyList('autoComplete_list');
      jQuery('.no_result').hide();  
      jQuery('#notValidPostCode').show();
    }
    loader.style.display = 'none';
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
    const event = new Event('deliverable');
    document.dispatchEvent(event);
  }else{
    jQuery('#notDelivrablePostcode').show();
  //  window.localStorage.removeItem('USER_POSTCODE');
  }
  loader.style.display = 'none';
}

