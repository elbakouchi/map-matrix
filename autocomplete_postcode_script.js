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
        console.log(source, data);
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
    observer: false,
    threshold: 1,
    debounce: 300,
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
      let postcode = window.getDrivingMatrix(feedback.selection.value);
    }
  });
});
window.getDrivingMatrix = async postcode => {
  const loader = document.querySelector('#loader');
  loader.style.display = 'inline-block';
  const geoDataSource = await fetch(`https://api.postcodes.io/postcodes/${postcode}`);
  const geoData = await geoDataSource.json();
  loader.style.display = 'none';

  if (geoData.hasOwnProperty('result') && geoData.result.hasOwnProperty('longitude') && geoData.result.hasOwnProperty('latitude')) {
    loader.style.display = 'inline-block';
    const drivingDataSource = await fetch(`/?rest_route=/geomap/v1/lat-lng/${geoData.result.longitude}/${geoData.result.latitude}`);
  //  const drivingMatrix = await drivingDataSource.json();
    console.log(drivingDataSource);
    loader.style.display = 'none';
    window.localStorage.drivingMatrix = JSON.stringify(drivingDataSource);
  }

}