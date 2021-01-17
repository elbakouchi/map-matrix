<?php

$persistApiScript = '
<script>
jQuery(document).ready(
function(){
  jQuery("#submitApiCredentials").click(function(e){
    e.preventDefault();
    console.log("click1");
    let url      = "/?rest_route=/geomap/v1/mapbox/api";
    let form     = jQuery("#apiForm");
    //let apiUrl   = form.find("input[name=api_url]").val();
    //let apiLogin = form.find("input[name=api_login]").val();
    let apiToken = form.find("textarea[name=api_token]").val();
    //let posting  = jQuery.post(url, {"api_url":apiUrl, "api_login":apiLogin,"api_token":apiToken});
    let posting  = jQuery.post(url, {"api_token":apiToken});
    posting.done(function(e){
                      console.log(e);
                      alert(e.message+"\nAPI credentials successfully saved!");
                      window.location.reload();
                    });
    posting.fail(function(e){
                      console.log(e);
                      alert(e.responseJSON.message+"\nSaving API credentials failed.\nPlease try again!");
                      window.location.reload();
                    });
  });
}
);
</script>
';

$persistActiveCitiesScript = '
<script>
jQuery(document).ready(
function(){
  jQuery("#submitCities").click(function(e){
    //$.post()
    e.preventDefault();
    console.log("click2");

  });
}
);
</script>
';

$persistDefaultReferenceScript = '
<script>
jQuery(document).ready(
function(){
  jQuery("#").click(function(e){
    //$.post()
    e.preventDefault();
    console.log("click3");

  });
}
);
</script>
';

$mapBoxScript = '<script>
mapboxgl.accessToken = "%s";
var map = new mapboxgl.Map({
container: "map",
style: "mapbox://styles/mapbox/streets-v11",
center: %s, // starting position
zoom: %s // starting zoom
});
map.addControl(new mapboxgl.NavigationControl());
var marker = new mapboxgl.Marker();
let lat = %f;
let lng = %f;
if (lat && lng){
  marker.setLngLat([lng,lat]).addTo(map);
}
map.on("click", function(e) {
    // The event object (e) contains information like the
    // coordinates of the point on the map that was clicked.
    console.log("A click event has occurred at " + e.lngLat);
    marker.setLngLat(e.lngLat).addTo(map);
    var lngLat = marker.getLngLat();
    //document.getElementsByClassName("modal")[0].setAttribute("class", "is-active");
    alert(`The coords ${lngLat.lng} ${lngLat.lat} are your current reference point of delivery.`);
    window.localStorage.setItem("posLng", lngLat.lng);
    window.localStorage.setItem("posLat", lngLat.lat);
    jQuery(document).ready(function(e){
          let lng = window.localStorage.getItem("posLng");
          let lat = window.localStorage.getItem("posLat");
          let url = `/?rest_route=/geomap/v1/reference/lat=${lat}/lng=${lng}`;
          jQuery.get(url, function(e){console.debug(e)}).done(function(){alert("Coords successfuly saved")}).fail(function(){alert("Failed saving coords. Please try again!")});
      });
    });

</script>';



?>