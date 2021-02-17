jQuery(document).ready(function () {
  window.autoComplete = new autoComplete({
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
      // condition: (query) =>{
      //   console.log(query);
      //   return (query.length > this.threshold && query !== " ")
      // }
    },
    placeHolder: "Enter a postcode...",
    selector: "#autoComplete",
    observer: false,
    threshold: 3,
    debounce: 0,
    searchEngine: "loose",
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
      result.setAttribute("class", "no_result");
      result.setAttribute("tabindex", "1");
      result.innerHTML = `<span style="display: flex; align-items: center; font-weight: 100; color: rgba(0,0,0,.2);">Found No Results for "${dataFeedback.query}"</span>`
      document.querySelector('#autocomplete_list').appendChild(result);
    },
    onSelection: feedback => {
      window.feedback = feedback;
      window.deliverablePostCode(null,feedback.selection.value);
      document.querySelector('#autoComplete').value = feedback.selection.value;
    }
  });

  jQuery('#autoComplete').on('input',function(e){
    jQuery('#notDelivrablePostcode').hide();
    jQuery('#notValidPostCode').hide();
      if(e.target.value.length >= 5){
        window.validatePostCode(e.target.value);
      }
   });
});
