 jQuery(document).ready(function(){
    var createList = function(data){
        window.destroyList('autoComplete_list');
        var list = document.createElement('ul');
        list.setAttribute("id", "autoComplete_list");
        list.setAttribute("aria-label", "postocodes");
        list.setAttribute("class", "autoComplete_list");
        list.setAttribute("role", "listbox");
        list.setAttribute("tabindex", "-1");
        if ('result' in data && data.result !== null && data.result.length > 0){
            for (var index = 0; index < data.result.length; index++) {
                var postcode = data.result[index];
                var listItem = document.createElement('li');
                listItem.setAttribute("id", "".concat("autoComplete_result", "_").concat(index));
                listItem.setAttribute("class", "autoComplete_result");
                listItem.setAttribute("role", "option");
                //jQuery("#".concat("autoComplete_result", "_").concat(index)).css('display', 'block');
                listItem.innerHTML = postcode;    
                list.appendChild(listItem);         
            }
            var destination = document.querySelector('#autoComplete');
            destination.insertAdjacentElement('afterend', list);
        }else{
            var listItem = document.createElement('li');
            listItem.setAttribute("id", "".concat("autoComplete_result", "_").concat(1));
            listItem.setAttribute("class", "autoComplete_result");
            listItem.setAttribute("role", "option");
            //jQuery("#".concat("autoComplete_result", "_").concat(index)).css('display', 'block');
            listItem.innerHTML = "No results found";    
            list.appendChild(listItem);  
            var destination = document.querySelector('#autoComplete');
            destination.insertAdjacentElement('afterend', list);
        }
    }

    jQuery(document).on('click', "#autoComplete_list li", function(e) {
     //   console.log(e);
      jQuery('#autoComplete').val(e.target.innerText.toUpperCase());
      window.validatePostCode(e.target.innerText);
    });

    jQuery('#autoComplete').on('input', async function(e){
        const postcode = document.querySelector("#autoComplete").value;
        const source = await fetch(`https://api.postcodes.io/postcodes/${postcode}/autocomplete`);
        const data = await source.json();
        window.autoCompletePostcodes = data;
        jQuery('#notDelivrablePostcode').hide();
        jQuery('#notValidPostCode').hide();
        if(e.target.value.length >= 5){
          window.validatePostCode(e.target.value);
        }
      //  console.debug(data);
        createList(data);
    });
});

window.destroyList = function(id){
    try{
        list = document.getElementById(id);
        list.remove(); 
    }catch(e){

    }
}
