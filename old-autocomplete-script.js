
/*
jQuery(document).ready(function () {

  jQuery('#autoComplete').change(async function(){
      let resultItem = {
        content: (data, source) => {
          source.innerHTML = data.value;
        },
        element: "li"
      };
      let resultsList = {
        container: source => {
          source.setAttribute("id", "autocomplete_list");
        },
        destination: "#autoComplete",
        position: "afterend",
        element: "ul",
      };
     
      const loader = document.querySelector('#loader');
      const postcode = document.querySelector("#autoComplete").value;
     
      loader.style.display = 'inline-block';
     
      const source = await fetch(`https://api.postcodes.io/postcodes/${postcode}/autocomplete`);
      const data = await source.json();
      
      var list = document.createElement(resultsList.element);

      list.setAttribute("id", resultsList.idName);
      list.setAttribute("aria-label", name);
      list.setAttribute("class", resultsList.className);
      list.setAttribute("role", "listbox");
      list.setAttribute("tabindex", "-1");
      
      for (var index = 0; index < data.result.length; index++) {
        var item = data.result[index];
        if (resultsList.container) resultsList.container(list);
        var destination = document.querySelector(resultsList.destination);
        destination.insertAdjacentElement(resultsList.position, list);
        var result = document.createElement(resultItem.element);
        result.setAttribute("id", "".concat("autoComplete_result", "_").concat(index));
        result.setAttribute("class", "autoComplete_result");
        result.setAttribute("role", "option");
        jQuery("#".concat("autoComplete_result", "_").concat(index)).css('display', 'block');
        result.innerHTML = item;
        if (resultItem.content) resultItem.content(item, result);
      }
      jQuery('#autocomplete_list').css('display', 'block');
      loader.style.display = 'none'; 
  });

  jQuery('#autoComplete').on('input',function(e){
    jQuery('#notDelivrablePostcode').hide();
    jQuery('#notValidPostCode').hide();
      if(e.target.value.length >= 5){
        window.validatePostCode(e.target.value);
      }
   });
/*
  window.autoComplete3 = new autoComplete({
    data: {
      src: async () => {
        const loader = document.querySelector('#loader');
        const postcode = document.querySelector("#autoComplete").value;
        loader.style.display = 'inline-block';
        const source = await fetch(`https://api.postcodes.io/postcodes/${postcode}/autocomplete`);
        const data = await source.json();
        loader.style.display = 'none'; 
        return (data.result === null) ? [] : data.result;
      },
      cache: true
    },
    query: {
      manipulate: (query) => {
        return query;
      }
    },
    trigger: {
      event: ["input", "focus"],
    },
    placeHolder: "Enter a postcode...",
    selector: "#autoComplete",
    observer: true,
    threshold: 3,
    debounce: 300,
    rendered: function(d){
      console.log(d, this.data);
      this.data = null;
    },
    searchEngine: 'strict',
    resultsList: {
      container: source => {
        source.setAttribute("id", "autocomplete_list");
      },
      destination: "#autoComplete",
      position: "afterend",
      element: "ul",
    },
    maxResults: 15,
    highlight: true,
    resultItem: {
      content: (data, source) => {
        source.innerHTML = data.value;
      },
      element: "li"
    },
    noResults: (dataFeedback, generateList) => {
      generateList(window.autoComplete, dataFeedback, dataFeedback.results);
      const result = document.createElement("li");
      result.setAttribute("class", "autoComplete_result");
      result.setAttribute("tabindex", "1");
      result.innerHTML = `<span style="display: flex; align-items: center; font-weight: 100; color: rgba(0,0,0,.2);">No results found  for "${dataFeedback.query.toUpperCase()}"</span>`
      document.querySelector('#autocomplete_list').appendChild(result);

    },
    onSelection: feedback => {
      window.feedback = feedback;
      window.deliverablePostCode(null,feedback.selection.value);
      document.querySelector('#autoComplete').value = feedback.selection.value;
    }
  });
});
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
                           ('USER_POSTCODE', postcode);
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
        jQuery("#delivery").show();
        console.debug(localStorage.getItem('USER_POSTCODE'));
       // jQuery("#getpostcode").hide();
        // if (window.localStorage.getItem('GEO_INFO') !== undefined || window.localStorage.getItem('GEO_INFO') !== null) {
        //     let geo_info = JSON.parse(window.localStorage.getItem('GEO_INFO'));
        //     console.log(geo_info);
        // }
    }

    window.onstorage = () => {
        console.log(JSON.parse(window.localStorage.getItem('GEO_INFO')));
    };
});

window.checkPostCodeDeliverabilty = function (_postcode) {
    window.postcode = _postcode;
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
           // window.localStorage.removeItem('USER_POSTCODE');
            window.destroyList('autoComplete_list');
        } else if (json_response.status === "No") {
            window.destroyList('autoComplete_list');
            jQuery('#notValidPostCode').show();
            jQuery('.no_result').hide();
           // window.localStorage.removeItem('USER_POSTCODE');
        } else if (json_response.status === "Ok") {
            try{
                let postcode = window.getDrivingMatrix(window.postocde);
                window.location.href = json_response.redirect_url;
            }catch(e){
               console.error(e);
            }
           // window.localStorage.setItem('USER_POSTCODE', window.feedback.selection.value);
        }
    });
    posting.fail(function (ev) {
        jQuery('#notDelivrablePostcode').show();
       // window.localStorage.removeItem('USER_POSTCODE');
    });
}


  

*/

