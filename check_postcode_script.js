
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
      try{
        if(window.autoCompletePostcodes.length === 0){
          window.destroyList('autoComplete_list');
          jQuery('#notValidPostCode').show();
        }
      }catch(e){
        console.log(e)
      }finally{
        jQuery('.no_result').hide();  
        window.postcode = null;
      }
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
  let needle   = postcodeParts[0].toUpperCase();
  if (jebStoresPostcodes.postcodes.includes(needle)) {
    const event = new Event('deliverable');
    document.dispatchEvent(event);
  }else{
    window.postcode = null;
    window.destroyList('autoComplete_list');  
    jQuery('.no_result').hide();  
    jQuery('#notDelivrablePostcode').show();
  }
  loader.style.display = 'none';
}

