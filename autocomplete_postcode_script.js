jQuery(document).ready(function(){
  new autoComplete({
    data: {                              // Data src [Array, Function, Async] | (REQUIRED)
      src: async () => {
        // User search query
        const postcode = document.querySelector("#autoComplete").value;
        // Fetch External Data Source api.postcodes.io/postcodes//autocomplete
        const source = await fetch(`https://api.postcodes.io/postcodes/${postcode}/autocomplete`);
        // Format data into JSON
        const data = await source.json();
        // Return Fetched data
        const store = 1;
        console.log(source, data);
        return (data.result === null)?['Nothing found']:data.result;
      },
    //  key: ["title"],
      cache: false
    },
    query: {                             // Query Interceptor               | (Optional)
          manipulate: (query) => {
            console.debug(query);
            return query;
            //return query.replace("pizza", "burger");
          }
    },
    trigger: {
      event: ["input", "focus"],
    },
    // sort: (a, b) => {                    // Sort rendered results ascendingly | (Optional)
    //     if (a.match < b.match) return -1;
    //     if (a.match > b.match) return 1;
    //     return 0;
    // },
    placeHolder: "Enter a postcode...",     // Place Holder text                 | (Optional)
    selector: "#autoComplete",           // Input field selector              | (Optional)
    observer: false,                      // Input field observer | (Optional)
    threshold: 1,                        // Min. Chars length to start Engine | (Optional)
    debounce: 300,                       // Post duration for engine to start | (Optional)
    searchEngine: "loose",              // Search Engine type/mode           | (Optional)
    resultsList: {                       // Rendered results list object      | (Optional)
        container: source => {
          console.debug(source);
            source.setAttribute("id", "postcodes_list");
        },
        destination: "#autoComplete",
        position: "afterend",
        element: "div"
    },
    maxResults: 15,                         // Max. number of rendered results | (Optional)
    highlight: true,                       // Highlight matching results      | (Optional)
    resultItem: {                          // Rendered result item            | (Optional)
        content: (data, source) => {
            source.innerHTML = data.match;
        },
        element: "div"
    },
    noResults: (dataFeedback, generateList) => {
        // Generate autoComplete List
        generateList(autoCompleteJS, dataFeedback, dataFeedback.results);
        // No Results List Item
        const result = document.createElement("li");
        result.setAttribute("class", "no_result");
        result.setAttribute("tabindex", "1");
        result.innerHTML = `<span style="display: flex; align-items: center; font-weight: 100; color: rgba(0,0,0,.2);">Found No Results for "${dataFeedback.query}"</span>`;
        document.querySelector(`#${autoCompleteJS.resultsList.idName}`).appendChild(result);
    },
    onSelection: feedback => {             // Action script onSelection event | (Optional)
        console.log(feedback.selection.value.image_url);
    }
});

});