jQuery(document).ready(function () {
    if (null !== window.localStorage.getItem('drivingMatrix')
        && 0 !== parseInt(window.localStorage.getItem('drivingMatrix')) ) {
        let matrix = JSON.parse(window.localStorage.getItem('drivingMatrix'));
        let time = secondsToTime(matrix.durations[0][0]);
     //   let distance = Math.ceil(matrix.destinations[0].distance);
        jQuery('#address').text(`P.O ${window.localStorage.getItem('USER_POSTCODE')}, ${matrix.destinations[0].name}`);
        jQuery('#driving').text(`ETA ${time}`);
    }

    function secondsToTime(secs) {
        var hours = Math.floor(secs / (60 * 60));

        var divisor_for_minutes = secs % (60 * 60);
        var minutes = Math.floor(divisor_for_minutes / 60);

        var divisor_for_seconds = divisor_for_minutes % 60;
        var seconds = Math.ceil(divisor_for_seconds);

        var obj = {
            "h": hours,
            "m": minutes,
            "s": seconds
        };
        if(hours === 0) return `${obj.m} Min.`;
        else return `${obj.h} H and ${obj.m} Min.`

        //return obj;
    }
    async function postData(url = '', data = {}) {
        const response = await fetch(url, {
            method: 'POST',
            mode: 'same-origin',
            cache: 'default',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: 'follow',
            referrerPolicy: 'no-referrer',
            body: data
        });
        const status = await response.json();
        return status;
    }

    document.addEventListener('deliverable', async function (e) {
        // try{
            window.localStorage.setItem('USER_POSTCODE', window.postcode);
            const deliverable = await window.getDrivingMatrix(window.postcode);
            console.info('deliverable:', deliverable);
        // }catch(e){
        //     console.debug(e);
        //     window.localStorage.setItem('USER_POSTCODE', 0);
        //     window.localStorage.setItem('drivingMatrix', 0);
        // }
    }, false);

    document.addEventListener('resync', async function (e) {
        try{
            const postcode = window.localStorage.getItem('USER_POSTCODE');
            const matrix   = JSON.parse(window.localStorage.getItem('drivingMatrix'));
            const resynced = await window.saveUserDrivingMatrix(postcode,matrix);
            console.info('resynced:', resynced);
        }catch(e){
            console.debug(e);
            window.localStorage.setItem('USER_POSTCODE', 0);
            window.localStorage.setItem('drivingMatrix', 0);
        }
      }, false);  
    
    document.addEventListener('saveMatrix', async function (e) {
       const saved = await window.saveUserDrivingMatrix(window.postcode, window.drivingMatrix, true);
       console.info('savedMatrix:', saved);
    }, false);
});

window.jPostData = async function (url, data) {
    let posting = await jQuery.post(url, data);
    return posting;
}


window.saveUserDrivingMatrix = async (postcode, matrix, redirect) => {
    loader.style.display = 'inline-block';
    let data = {
        'action': 'save_user_driving_matrix',
        'postcode': postcode,
        'distance': matrix.destinations[0].distance,
        'address': matrix.destinations[0].name,
        'duration': matrix.durations[0][0],
        'longitude': matrix.destinations[0].location[0],
        'latitude': matrix.destinations[0].location[1]
    };
    const posting =  await window.jPostData(jebStoresAjax.ajaxurl, data);
    // try {
    //     posting.done(function (response) {
    //         window.localStorage.setItem('SESSION_SAVED', reponse);
    //     });
    // } catch (e) {
    //     console.error(e);
    // }
    window.localStorage.setItem('USER_POSTCODE', postcode);
    loader.style.display = 'none';
    if(redirect) window.location.href = '/?post_type=product';
}

window.asyncFetch = async function(){
    loader.style.display = 'inline-block';
    const geoDataSource = await fetch(`https://api.postcodes.io/postcodes/${postcode}`);
    const geoData = await geoDataSource.json();
    loader.style.display = 'none';
}

window.getDrivingMatrix = async postcode => {
   const loader = document.querySelector('#loader');
    loader.style.display = 'inline-block';
    const geoDataSource = await fetch(`https://api.postcodes.io/postcodes/${postcode}`);
    const geoData = await geoDataSource.json();
    loader.style.display = 'none';
  
    if (geoData.hasOwnProperty('result') && geoData.result.hasOwnProperty('longitude') && geoData.result.hasOwnProperty('latitude')) {
      loader.style.display = 'inline-block';
      const drivingDataSource = await fetch(`https://api.mapbox.com/directions-matrix/v1/mapbox/driving-traffic/${jebStoresCoords.longitude},${jebStoresCoords.latitude};${geoData.result.longitude},${geoData.result.latitude}?sources=0&annotations=duration&destinations=1&fallback_speed=20&access_token=${jebStoresMapBox.token}`);     
      const drivingMatrix = await drivingDataSource.json();
      console.log(drivingDataSource, drivingMatrix);
      loader.style.display = 'none';
      window.drivingMatrix = drivingMatrix;
      window.localStorage.drivingMatrix = JSON.stringify(drivingMatrix);
      const event = new Event('saveMatrix');
      document.dispatchEvent(event);
    }
  
  }