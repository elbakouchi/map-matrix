jQuery(document).ready( async function(){
    let settings = {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        action: 'check_if_postcode_in_session'
    };
    const posted   = await fetch(`${jebStores.checkPostcodeAjaxUrl}`, settings);
    const response = await posted.json();
    if(!response.result){
        const event = new Event('resync');
        document.dispatchEvent(event);
    }
    console.log(response);
});  
